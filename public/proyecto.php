<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::estaLogueado()) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: index.php');
    exit;
}

$proyectoModel = new Proyecto();
$proyecto = $proyectoModel->obtenerPorId($id);
if (!$proyecto) {
    header('Location: index.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$esLider = ($proyecto['lider_id'] == $usuario_id);
$esMiembro = $proyectoModel->esMiembro($id, $usuario_id);
$solicitudPendiente = $proyectoModel->solicitudPendiente($id, $usuario_id);

// Procesar solicitud
$mensaje = '';
$error = '';
if (isset($_POST['solicitar']) && !$esMiembro && !$solicitudPendiente) {
    $resultado = $proyectoModel->solicitar($id, $usuario_id);
    if ($resultado === true) {
        $mensaje = 'Solicitud enviada correctamente.';
        $solicitudPendiente = true;
    } else {
        $error = $resultado;
    }
}

// Si es líder, obtener solicitudes
$solicitudes = [];
if ($esLider) {
    $solicitudes = $proyectoModel->obtenerSolicitudes($id);
    // Procesar aprobación/rechazo
    if (isset($_POST['aprobar'])) {
        $usuario_solicitante = $_POST['usuario_id'];
        $rol = $_POST['rol'] ?? 'miembro';
        if ($proyectoModel->aprobarSolicitud($id, $usuario_solicitante, $rol)) {
            $mensaje = 'Solicitud aprobada.';
            $solicitudes = $proyectoModel->obtenerSolicitudes($id);
        } else {
            $error = 'Error al aprobar solicitud.';
        }
    }
    if (isset($_POST['rechazar'])) {
        $usuario_solicitante = $_POST['usuario_id'];
        if ($proyectoModel->rechazarSolicitud($id, $usuario_solicitante)) {
            $mensaje = 'Solicitud rechazada.';
            $solicitudes = $proyectoModel->obtenerSolicitudes($id);
        } else {
            $error = 'Error al rechazar solicitud.';
        }
    }
}

$miembros = $proyectoModel->obtenerMiembros($id);
$recursoModel = new Recurso();
$recursos = $recursoModel->obtenerPorProyecto($id);

$extra_css = '
        .project-header {
            background: linear-gradient(135deg, var(--color-dark-blue), var(--color-medium-blue));
            color: white;
            padding: var(--space-xl);
            border-radius: var(--radius-xl);
            margin-bottom: var(--space-xl);
        }
        .project-header h1 { color: white; margin-bottom: var(--space-sm); }
        .project-meta { display: flex; gap: var(--space-md); flex-wrap: wrap; margin-bottom: var(--space-md); }
        .project-meta span { background: rgba(255,255,255,0.2); padding: var(--space-xs) var(--space-sm); border-radius: var(--radius-md); font-size: 0.9rem; }
        .project-section { background: white; padding: var(--space-lg); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); margin-bottom: var(--space-lg); }
        .team-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: var(--space-md); margin-top: var(--space-md); }
        .team-member { background: #f8f9fa; padding: var(--space-sm); border-radius: var(--radius-md); text-align: center; }
        .team-member a { font-weight: bold; color: var(--color-dark-blue); }
        .resource-item { display: flex; justify-content: space-between; align-items: center; padding: var(--space-sm); border-bottom: 1px solid #eee; }
        .resource-item:last-child { border-bottom: none; }
        .solicitud-item { background: #fff3cd; padding: var(--space-sm); margin-bottom: var(--space-sm); border-radius: var(--radius-md); display: flex; justify-content: space-between; align-items: center; }
        .btn-solicitar { background: var(--color-success); }
        .btn-pendiente { background: var(--color-warning); cursor: default; }
        .alert { padding: var(--space-md); border-radius: var(--radius-md); margin-bottom: var(--space-lg); }
        .alert.success { background: rgba(39,174,96,0.1); color: var(--color-success); border: 1px solid rgba(39,174,96,0.3); }
        .alert.error { background: rgba(231,76,60,0.1); color: var(--color-accent); border: 1px solid rgba(231,76,60,0.3); }
        .btn-group { display: flex; gap: var(--space-sm); margin-top: var(--space-lg); }
    ';
$page_title = 'Proyecto - CIAUBA';
require_once __DIR__ . '/header.php';
?>

    <main>
        <div class="project-header">
            <h1><?php echo htmlspecialchars($proyecto['titulo']); ?></h1>
            <div class="project-meta">
                <span><i class="fas fa-tag"></i> <?php echo $proyecto['estado']; ?></span>
                <?php if ($proyecto['fecha_inicio']): ?>
                    <span><i class="fas fa-calendar-alt"></i> Inicio: <?php echo date('d/m/Y', strtotime($proyecto['fecha_inicio'])); ?></span>
                <?php endif; ?>
                <?php if ($proyecto['fecha_fin_estimada']): ?>
                    <span><i class="fas fa-calendar-check"></i> Fin estimado: <?php echo date('d/m/Y', strtotime($proyecto['fecha_fin_estimada'])); ?></span>
                <?php endif; ?>
                <?php if ($proyecto['lider_nombre']): ?>
                    <span><i class="fas fa-user-tie"></i> Líder: <?php echo htmlspecialchars($proyecto['lider_nombre']); ?></span>
                <?php endif; ?>
                <?php if ($proyecto['presupuesto_asignado'] > 0): ?>
                    <span><i class="fas fa-dollar-sign"></i> Presupuesto: $<?php echo number_format($proyecto['presupuesto_asignado'], 2); ?></span>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert success"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="project-section">
            <h3><i class="fas fa-align-left"></i> Descripción</h3>
            <p><?php echo nl2br(htmlspecialchars($proyecto['descripcion'])); ?></p>
        </div>

        <?php if (!empty($proyecto['objetivos'])): ?>
        <div class="project-section">
            <h3><i class="fas fa-bullseye"></i> Objetivos</h3>
            <p><?php echo nl2br(htmlspecialchars($proyecto['objetivos'])); ?></p>
        </div>
        <?php endif; ?>

        <div class="project-section">
            <h3><i class="fas fa-users"></i> Equipo del proyecto (<?php echo count($miembros); ?>)</h3>
            <?php if (empty($miembros)): ?>
                <p>No hay miembros asignados todavía.</p>
            <?php else: ?>
                <div class="team-grid">
                    <?php foreach ($miembros as $miembro): ?>
                        <div class="team-member">
                            <a href="perfil.php?id=<?php echo $miembro['id']; ?>">
                                <?php echo htmlspecialchars($miembro['nombre']); ?>
                            </a>
                            <div class="role">(<?php echo htmlspecialchars($miembro['rol_en_proyecto']); ?>)</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Botón de solicitud (solo si no es líder ni miembro) -->
        <?php if (!$esLider && !$esMiembro): ?>
            <div class="project-section">
                <h3><i class="fas fa-hand-paper"></i> ¿Quieres unirte al proyecto?</h3>
                <?php if ($solicitudPendiente): ?>
                    <div class="alert info">
                        <i class="fas fa-clock"></i> Ya has enviado una solicitud. Espera la respuesta del líder.
                    </div>
                <?php else: ?>
                    <form method="POST" action="">
                        <button type="submit" name="solicitar" class="cta-button btn-solicitar">
                            <i class="fas fa-paper-plane"></i> Solicitar unirme
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Panel de solicitudes (solo para el líder) -->
        <?php if ($esLider && !empty($solicitudes)): ?>
            <div class="project-section">
                <h3><i class="fas fa-bell"></i> Solicitudes pendientes (<?php echo count($solicitudes); ?>)</h3>
                <?php foreach ($solicitudes as $sol): ?>
                    <div class="solicitud-item">
                        <div>
                            <strong><?php echo htmlspecialchars($sol['nombre']); ?></strong><br>
                            <small>Solicitó: <?php echo date('d/m/Y H:i', strtotime($sol['fecha_solicitud'])); ?></small>
                        </div>
                        <div class="btn-group">
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="usuario_id" value="<?php echo $sol['id']; ?>">
                                <input type="hidden" name="rol" value="miembro">
                                <button type="submit" name="aprobar" class="action-btn approve">Aprobar</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="usuario_id" value="<?php echo $sol['id']; ?>">
                                <button type="submit" name="rechazar" class="action-btn reject" onclick="return confirm('¿Rechazar esta solicitud?')">Rechazar</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($recursos)): ?>
        <div class="project-section">
            <h3><i class="fas fa-paperclip"></i> Recursos compartidos</h3>
            <?php foreach ($recursos as $recurso): ?>
                <div class="resource-item">
                    <div>
                        <strong><?php echo htmlspecialchars($recurso['titulo']); ?></strong>
                        <p class="small"><?php echo htmlspecialchars($recurso['descripcion']); ?></p>
                    </div>
                    <div>
                        <?php if ($recurso['tipo'] == 'enlace' || $recurso['tipo'] == 'video'): ?>
                            <a href="<?php echo htmlspecialchars($recurso['url']); ?>" target="_blank" class="btn-accion btn-ver"><i class="fas fa-external-link-alt"></i> Ver</a>
                        <?php elseif ($recurso['tipo'] == 'archivo'): ?>
                            <a href="uploads/recursos/<?php echo htmlspecialchars($recurso['archivo_ruta']); ?>" download class="btn-accion btn-descargar"><i class="fas fa-download"></i> Descargar</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="text-center" style="margin-top: var(--space-xl);">
            <a href="index.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Volver al inicio</a>
            <?php if (User::esAdmin()): ?>
                <a href="admin_proyecto_form.php?id=<?php echo $id; ?>" class="btn-primary"><i class="fas fa-edit"></i> Editar proyecto</a>
            <?php endif; ?>
        </div>
    </main>

<?php require_once __DIR__ . '/footer.php'; ?>
</body>
</html>
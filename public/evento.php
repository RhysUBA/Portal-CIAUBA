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

$eventoModel = new Evento();
$evento = $eventoModel->obtenerPorId($id);
if (!$evento) {
    header('Location: index.php');
    exit;
}

$asistenteModel = new AsistenteEvento();
$usuario_id = $_SESSION['usuario_id'];
$yaRegistrado = $asistenteModel->estaRegistrado($id, $usuario_id);
$fecha_pasado = (strtotime($evento['fecha_inicio']) < time());

// Procesar inscripción/cancelación
$mensaje = '';
$error = '';
if (isset($_POST['inscribir']) && !$fecha_pasado) {
    $resultado = $asistenteModel->registrar($id, $usuario_id);
    if ($resultado === true) {
        $mensaje = 'Te has inscrito correctamente al evento.';
        $yaRegistrado = true;
    } else {
        $error = $resultado;
    }
} elseif (isset($_POST['cancelar']) && !$fecha_pasado) {
    if ($asistenteModel->cancelar($id, $usuario_id)) {
        $mensaje = 'Has cancelado tu inscripción.';
        $yaRegistrado = false;
    } else {
        $error = 'Error al cancelar la inscripción.';
    }
}

// Obtener lista de asistentes (solo para admin y organizador)
$esAdmin = User::esAdmin();
$esOrganizador = ($evento['organizador_id'] == $usuario_id);
$asistentes = [];
if ($esAdmin || $esOrganizador) {
    $asistentes = $asistenteModel->obtenerAsistentes($id);
}
$totalAsistentes = $asistenteModel->contarAsistentes($id);

$extra_css = '
        .event-header {
            background: linear-gradient(135deg, var(--color-dark-blue), var(--color-medium-blue));
            color: white;
            padding: var(--space-xl);
            border-radius: var(--radius-xl);
            margin-bottom: var(--space-xl);
        }
        .event-header h1 { color: white; margin-bottom: var(--space-sm); }
        .event-meta { display: flex; gap: var(--space-md); flex-wrap: wrap; margin-bottom: var(--space-md); }
        .event-meta span { background: rgba(255,255,255,0.2); padding: var(--space-xs) var(--space-sm); border-radius: var(--radius-md); font-size: 0.9rem; }
        .event-section { background: white; padding: var(--space-lg); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); margin-bottom: var(--space-lg); }
        .attendee-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: var(--space-md); margin-top: var(--space-md); }
        .attendee-item { background: #f8f9fa; padding: var(--space-sm); border-radius: var(--radius-md); text-align: center; }
        .btn-inscribir { background: var(--color-success); }
        .btn-cancelar { background: var(--color-accent); }
        .alert { padding: var(--space-md); border-radius: var(--radius-md); margin-bottom: var(--space-lg); }
        .alert.success { background: rgba(39,174,96,0.1); color: var(--color-success); border: 1px solid rgba(39,174,96,0.3); }
        .alert.error { background: rgba(231,76,60,0.1); color: var(--color-accent); border: 1px solid rgba(231,76,60,0.3); }
        .alert.info { background: rgba(52,152,219,0.1); color: var(--color-info); border: 1px solid rgba(52,152,219,0.3); }
        .btn-group { display: flex; gap: var(--space-sm); margin-top: var(--space-lg); }
    ';
$page_title = 'Evento - CIAUBA';
require_once __DIR__ . '/header.php';
?>

    <main>
        <div class="event-header">
            <h1><?php echo htmlspecialchars($evento['titulo']); ?></h1>
            <div class="event-meta">
                <span><i class="fas fa-tag"></i> <?php echo $evento['tipo']; ?></span>
                <span><i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y H:i', strtotime($evento['fecha_inicio'])); ?></span>
                <?php if ($evento['fecha_fin']): ?>
                    <span><i class="fas fa-calendar-check"></i> Hasta: <?php echo date('d/m/Y H:i', strtotime($evento['fecha_fin'])); ?></span>
                <?php endif; ?>
                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($evento['lugar']); ?></span>
                <?php if ($evento['organizador_nombre']): ?>
                    <span><i class="fas fa-user"></i> Organiza: <?php echo htmlspecialchars($evento['organizador_nombre']); ?></span>
                <?php endif; ?>
                <span><i class="fas fa-users"></i> Asistentes: <?php echo $totalAsistentes; ?> / <?php echo $evento['max_asistentes'] > 0 ? $evento['max_asistentes'] : '∞'; ?></span>
            </div>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert success"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="event-section">
            <h3><i class="fas fa-align-left"></i> Descripción</h3>
            <p><?php echo nl2br(htmlspecialchars($evento['descripcion'])); ?></p>
        </div>

        <!-- Botón de inscripción (solo si no es organizador y el evento no ha pasado) -->
        <?php if (!$esOrganizador && !$fecha_pasado): ?>
            <div class="event-section">
                <h3><i class="fas fa-hand-paper"></i> Participación</h3>
                <?php if ($yaRegistrado): ?>
                    <div class="alert info">
                        <i class="fas fa-check-circle"></i> Ya estás inscrito en este evento.
                    </div>
                    <form method="POST" action="">
                        <button type="submit" name="cancelar" class="cta-button btn-cancelar" onclick="return confirm('¿Cancelar tu inscripción?')">
                            <i class="fas fa-times"></i> Cancelar inscripción
                        </button>
                    </form>
                <?php else: ?>
                    <?php if ($evento['max_asistentes'] > 0 && $totalAsistentes >= $evento['max_asistentes']): ?>
                        <div class="alert error">
                            <i class="fas fa-ban"></i> El evento ha alcanzado su límite de asistentes.
                        </div>
                    <?php else: ?>
                        <form method="POST" action="">
                            <button type="submit" name="inscribir" class="cta-button btn-inscribir">
                                <i class="fas fa-calendar-plus"></i> Inscribirme
                            </button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php elseif ($fecha_pasado): ?>
            <div class="event-section">
                <h3><i class="fas fa-calendar-times"></i> Evento finalizado</h3>
                <div class="alert info">Este evento ya ocurrió. No es posible inscribirse.</div>
            </div>
        <?php endif; ?>

        <!-- Lista de asistentes (solo para organizador y admin) -->
        <?php if (($esAdmin || $esOrganizador) && !empty($asistentes)): ?>
            <div class="event-section">
                <h3><i class="fas fa-users"></i> Lista de asistentes (<?php echo count($asistentes); ?>)</h3>
                <div class="attendee-list">
                    <?php foreach ($asistentes as $asistente): ?>
                        <div class="attendee-item">
                            <a href="perfil.php?id=<?php echo $asistente['id']; ?>">
                                <?php echo htmlspecialchars($asistente['nombre']); ?>
                            </a>
                            <div class="small">
                                <?php echo date('d/m/Y', strtotime($asistente['fecha_registro'])); ?>
                                <?php if ($asistente['asistio']): ?>
                                    <i class="fas fa-check-circle" style="color: green;"></i>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="text-center" style="margin-top: var(--space-xl);">
            <a href="eventos.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Volver a eventos</a>
            <?php if ($esAdmin): ?>
                <a href="admin_evento_form.php?id=<?php echo $id; ?>" class="btn-primary"><i class="fas fa-edit"></i> Editar evento</a>
            <?php endif; ?>
        </div>
    </main>

<?php require_once __DIR__ . '/footer.php'; ?>
</body>
</html>
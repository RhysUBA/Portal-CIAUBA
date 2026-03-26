<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::esAdmin()) {
    header('Location: index.php');
    exit;
}

$proyectoModel = new Proyecto();
$userModel = new User();
$miembrosActivos = $userModel->obtenerMiembros(true); // lista de usuarios para líder

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$proyecto = null;
$esEdicion = false;
if ($id > 0) {
    $proyecto = $proyectoModel->obtenerPorId($id);
    if ($proyecto) $esEdicion = true;
}

$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Error de validación.';
    } else {
        $datos = [
            'titulo' => trim($_POST['titulo']),
            'descripcion' => trim($_POST['descripcion']),
            'objetivos' => trim($_POST['objetivos']),
            'estado' => $_POST['estado'],
            'fecha_inicio' => $_POST['fecha_inicio'] ?: null,
            'fecha_fin_estimada' => $_POST['fecha_fin_estimada'] ?: null,
            'lider_id' => $_POST['lider_id'] ?: null,
            'presupuesto_asignado' => $_POST['presupuesto_asignado'] ?: 0
        ];

        if (empty($datos['titulo'])) {
            $error = 'El título es obligatorio.';
        } elseif (empty($datos['descripcion'])) {
            $error = 'La descripción es obligatoria.';
        } else {
            if ($esEdicion) {
                $resultado = $proyectoModel->actualizar($id, $datos);
            } else {
                $resultado = $proyectoModel->crear($datos);
            }

            if ($resultado) {
                $exito = $esEdicion ? 'Proyecto actualizado correctamente.' : 'Proyecto creado correctamente.';
                if (!$esEdicion) {
                    // Redirigir al listado
                    header('Location: admin_proyectos.php?mensaje=' . urlencode($exito) . '&tipo=success');
                    exit;
                }
            } else {
                $error = 'Error al guardar el proyecto.';
            }
        }
    }
}

// Generar token CSRF
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$page_title = 'Formulario de Proyecto - CIAUBA';
require_once __DIR__ . '/header.php';
?>

    <main>
        <div class="form-container">
            <h2><?php echo $esEdicion ? 'Editar' : 'Nuevo'; ?> Proyecto</h2>

            <?php if ($error): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($exito): ?>
                <div class="alert success"><?php echo $exito; ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div class="form-group">
                    <label for="titulo">Título *</label>
                    <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($proyecto['titulo'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción *</label>
                    <textarea id="descripcion" name="descripcion" rows="5" required><?php echo htmlspecialchars($proyecto['descripcion'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="objetivos">Objetivos</label>
                    <textarea id="objetivos" name="objetivos" rows="3"><?php echo htmlspecialchars($proyecto['objetivos'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado">
                        <option value="planning" <?php echo isset($proyecto['estado']) && $proyecto['estado'] == 'planning' ? 'selected' : ''; ?>>Planificación</option>
                        <option value="in_progress" <?php echo isset($proyecto['estado']) && $proyecto['estado'] == 'in_progress' ? 'selected' : ''; ?>>En progreso</option>
                        <option value="testing" <?php echo isset($proyecto['estado']) && $proyecto['estado'] == 'testing' ? 'selected' : ''; ?>>Pruebas</option>
                        <option value="completed" <?php echo isset($proyecto['estado']) && $proyecto['estado'] == 'completed' ? 'selected' : ''; ?>>Completado</option>
                        <option value="cancelled" <?php echo isset($proyecto['estado']) && $proyecto['estado'] == 'cancelled' ? 'selected' : ''; ?>>Cancelado</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="lider_id">Líder del proyecto</label>
                    <select id="lider_id" name="lider_id">
                        <option value="">-- Sin líder --</option>
                        <?php foreach ($miembrosActivos as $m): ?>
                            <option value="<?php echo $m['id']; ?>" <?php echo isset($proyecto['lider_id']) && $proyecto['lider_id'] == $m['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($m['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fecha_inicio">Fecha de inicio</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo isset($proyecto['fecha_inicio']) ? date('Y-m-d', strtotime($proyecto['fecha_inicio'])) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="fecha_fin_estimada">Fecha fin estimada</label>
                    <input type="date" id="fecha_fin_estimada" name="fecha_fin_estimada" value="<?php echo isset($proyecto['fecha_fin_estimada']) ? date('Y-m-d', strtotime($proyecto['fecha_fin_estimada'])) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="presupuesto_asignado">Presupuesto asignado ($)</label>
                    <input type="number" step="0.01" id="presupuesto_asignado" name="presupuesto_asignado" value="<?php echo $proyecto['presupuesto_asignado'] ?? 0; ?>">
                </div>

                <div class="button-group">
                    <button type="submit" class="btn-primary"><?php echo $esEdicion ? 'Actualizar' : 'Crear'; ?> proyecto</button>
                    <a href="admin_proyectos.php" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </main>

<?php require_once __DIR__ . '/footer.php'; ?>
</body>
</html>
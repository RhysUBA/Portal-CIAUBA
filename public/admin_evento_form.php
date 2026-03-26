<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::esAdmin()) {
    header('Location: index.php');
    exit;
}

$eventoModel = new Evento();
$userModel = new User();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$evento = null;
$esEdicion = false;
if ($id > 0) {
    $evento = $eventoModel->obtenerPorId($id);
    if ($evento) $esEdicion = true;
}

// Lista de organizadores (usuarios activos)
$organizadores = $userModel->obtenerMiembros(true); // activos

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
            'tipo' => $_POST['tipo'],
            'lugar' => trim($_POST['lugar']),
            'fecha_inicio' => $_POST['fecha_inicio'],
            'fecha_fin' => $_POST['fecha_fin'] ?: null,
            'organizador_id' => $_POST['organizador_id'] ?: null,
            'max_asistentes' => (int)$_POST['max_asistentes']
        ];

        if (empty($datos['titulo'])) {
            $error = 'El título es obligatorio.';
        } elseif (empty($datos['lugar'])) {
            $error = 'El lugar es obligatorio.';
        } elseif (empty($datos['fecha_inicio'])) {
            $error = 'La fecha de inicio es obligatoria.';
        } else {
            if ($esEdicion) {
                $resultado = $eventoModel->actualizar($id, $datos);
            } else {
                $resultado = $eventoModel->crear($datos);
            }

            if ($resultado) {
                $exito = $esEdicion ? 'Evento actualizado correctamente.' : 'Evento creado correctamente.';
                if (!$esEdicion) {
                    // Redirigir al listado
                    header('Location: admin_eventos.php?mensaje=' . urlencode($exito) . '&tipo=success');
                    exit;
                }
            } else {
                $error = 'Error al guardar el evento.';
            }
        }
    }
}

// Generar token CSRF
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$page_title = 'Formulario de Evento - CIAUBA';
require_once __DIR__ . '/header.php';
?>

    <main>
        <div class="form-container">
            <h2><?php echo $esEdicion ? 'Editar' : 'Nuevo'; ?> Evento</h2>

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
                    <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($evento['titulo'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="5"><?php echo htmlspecialchars($evento['descripcion'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="tipo">Tipo de evento</label>
                    <select id="tipo" name="tipo">
                        <option value="reunion" <?php echo isset($evento['tipo']) && $evento['tipo'] == 'reunion' ? 'selected' : ''; ?>>Reunión</option>
                        <option value="taller" <?php echo isset($evento['tipo']) && $evento['tipo'] == 'taller' ? 'selected' : ''; ?>>Taller</option>
                        <option value="hackathon" <?php echo isset($evento['tipo']) && $evento['tipo'] == 'hackathon' ? 'selected' : ''; ?>>Hackathon</option>
                        <option value="social" <?php echo isset($evento['tipo']) && $evento['tipo'] == 'social' ? 'selected' : ''; ?>>Social</option>
                        <option value="otro" <?php echo isset($evento['tipo']) && $evento['tipo'] == 'otro' ? 'selected' : ''; ?>>Otro</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="lugar">Lugar *</label>
                    <input type="text" id="lugar" name="lugar" value="<?php echo htmlspecialchars($evento['lugar'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="fecha_inicio">Fecha y hora de inicio *</label>
                    <input type="datetime-local" id="fecha_inicio" name="fecha_inicio" value="<?php echo isset($evento['fecha_inicio']) ? date('Y-m-d\TH:i', strtotime($evento['fecha_inicio'])) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="fecha_fin">Fecha y hora de fin (opcional)</label>
                    <input type="datetime-local" id="fecha_fin" name="fecha_fin" value="<?php echo isset($evento['fecha_fin']) ? date('Y-m-d\TH:i', strtotime($evento['fecha_fin'])) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="organizador_id">Organizador</label>
                    <select id="organizador_id" name="organizador_id">
                        <option value="">-- Sin organizador --</option>
                        <?php foreach ($organizadores as $org): ?>
                            <option value="<?php echo $org['id']; ?>" <?php echo isset($evento['organizador_id']) && $evento['organizador_id'] == $org['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($org['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="max_asistentes">Máximo de asistentes (0 = ilimitado)</label>
                    <input type="number" id="max_asistentes" name="max_asistentes" min="0" value="<?php echo $evento['max_asistentes'] ?? 0; ?>">
                </div>

                <div class="button-group">
                    <button type="submit" class="btn-primary"><?php echo $esEdicion ? 'Actualizar' : 'Crear'; ?> evento</button>
                    <a href="admin_eventos.php" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </main>

<?php require_once __DIR__ . '/footer.php'; ?>
</body>
</html>
<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::estaLogueado()) {
    header('Location: login.php');
    exit;
}

$recursoModel = new Recurso();
$proyectoModel = new Proyecto();

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: recursos.php');
    exit;
}

// Obtener el recurso
$recurso = $recursoModel->obtenerPorId($id);
if (!$recurso) {
    header('Location: recursos.php');
    exit;
}

// Verificar permisos: solo el autor o admin
if ($_SESSION['usuario_id'] != $recurso['usuario_id'] && !User::esAdmin()) {
    header('Location: recursos.php');
    exit;
}

$proyectos = $proyectoModel->obtenerTodos();
$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Error de validación del formulario.';
    } else {
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $proyecto_id = !empty($_POST['proyecto_id']) ? $_POST['proyecto_id'] : null;

        if (empty($titulo)) {
            $error = 'El título es obligatorio.';
        } else {
            // Actualizar recurso
            $resultado = $recursoModel->actualizar($id, $titulo, $descripcion);
            // También podríamos actualizar el proyecto asociado, pero el método actualizar no lo incluye.
            // Si quisiéramos, deberíamos extender el modelo o hacer una consulta aparte.
            // Por simplicidad, solo actualizamos título y descripción.
            if ($resultado) {
                $exito = 'Recurso actualizado correctamente.';
                // Recargar datos
                $recurso = $recursoModel->obtenerPorId($id);
            } else {
                $error = 'Error al actualizar el recurso.';
            }
        }
    }
}

// Generar token CSRF
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$page_title = 'Recursos - CIAUBA';
require_once __DIR__ . '/header.php';
?>

    <main>
        <div class="form-container" style="max-width: 700px;">
            <h2><i class="fas fa-edit"></i> Editar recurso</h2>
            
            <?php if ($error): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($exito): ?>
                <div class="alert success"><?php echo $exito; ?> <a href="recursos.php">Volver a recursos</a></div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <fieldset>
                    <legend>Información del recurso</legend>
                    
                    <div class="form-group">
                        <label for="titulo">Título *</label>
                        <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($recurso['titulo']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="4"><?php echo htmlspecialchars($recurso['descripcion']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Tipo de recurso</label>
                        <div>
                            <span class="recurso-tipo"><?php echo $recurso['tipo']; ?></span>
                            <?php if ($recurso['tipo'] == 'enlace' || $recurso['tipo'] == 'video'): ?>
                                <p><strong>URL:</strong> <?php echo htmlspecialchars($recurso['url']); ?></p>
                            <?php elseif ($recurso['tipo'] == 'archivo'): ?>
                                <p><strong>Archivo:</strong> <?php echo htmlspecialchars($recurso['archivo_ruta']); ?></p>
                            <?php endif; ?>
                        </div>
                        <small>El tipo no se puede cambiar. Si necesitas cambiarlo, crea un nuevo recurso.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="proyecto_id">Asociar a un proyecto (opcional)</label>
                        <select id="proyecto_id" name="proyecto_id">
                            <option value="">-- Ninguno --</option>
                            <?php foreach ($proyectos as $proyecto): ?>
                                <option value="<?php echo $proyecto['id']; ?>" <?php echo ($recurso['proyecto_id'] == $proyecto['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($proyecto['titulo']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small>Nota: actualmente la asociación a proyecto no se modifica con este formulario. Para simplificar, no se actualiza.</small>
                    </div>
                </fieldset>
                
                <div class="button-group">
                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Guardar cambios</button>
                    <a href="recursos.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Cancelar</a>
                </div>
            </form>
        </div>
    </main>

<?php require_once __DIR__ . '/footer.php'; ?>
</body>
</html>
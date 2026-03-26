<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::estaLogueado()) {
    header('Location: login.php');
    exit;
}

$recursoModel = new Recurso();

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

// Si se confirma la eliminación (método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Error de validación.';
    } else {
        if ($recursoModel->eliminar($id)) {
            $_SESSION['mensaje'] = 'Recurso eliminado correctamente.';
            $_SESSION['mensaje_tipo'] = 'success';
        } else {
            $_SESSION['mensaje'] = 'Error al eliminar el recurso.';
            $_SESSION['mensaje_tipo'] = 'error';
        }
        header('Location: recursos.php');
        exit;
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
        <div class="form-container" style="max-width: 500px; text-align: center;">
            <h2><i class="fas fa-trash-alt" style="color: var(--color-accent);"></i> Eliminar recurso</h2>
            
            <p style="font-size: 1.1rem; margin: var(--space-lg) 0;">
                ¿Estás seguro de que deseas eliminar el recurso <strong>"<?php echo htmlspecialchars($recurso['titulo']); ?>"</strong>?
            </p>
            
            <?php if ($recurso['tipo'] == 'archivo' && $recurso['archivo_ruta']): ?>
                <p class="alert warning">El archivo físico también será eliminado.</p>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="button-group" style="justify-content: center;">
                    <button type="submit" class="btn-danger"><i class="fas fa-check"></i> Sí, eliminar</button>
                    <a href="recursos.php" class="btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
                </div>
            </form>
        </div>
    </main>

<?php require_once __DIR__ . '/footer.php'; ?>
</body>
</html>
<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::estaLogueado()) {
    header('Location: login.php');
    exit;
}

$userModel = new User();
$usuario_id = $_SESSION['usuario_id'];

$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Error de validación del formulario.';
    } else {
        $password_actual = $_POST['password_actual'] ?? '';
        $password_nueva = $_POST['password_nueva'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        $errores = [];

        if (empty($password_actual)) {
            $errores[] = "Debes ingresar tu contraseña actual.";
        }

        if (empty($password_nueva)) {
            $errores[] = "Debes ingresar una nueva contraseña.";
        } elseif (strlen($password_nueva) < 8) {
            $errores[] = "La nueva contraseña debe tener al menos 8 caracteres.";
        } elseif (!preg_match('/[A-Z]/', $password_nueva)) {
            $errores[] = "La nueva contraseña debe contener al menos una mayúscula.";
        } elseif (!preg_match('/[0-9]/', $password_nueva)) {
            $errores[] = "La nueva contraseña debe contener al menos un número.";
        } elseif (!preg_match('/[^a-zA-Z0-9]/', $password_nueva)) {
            $errores[] = "La nueva contraseña debe contener al menos un carácter especial.";
        }

        if ($password_nueva !== $password_confirm) {
            $errores[] = "Las contraseñas no coinciden.";
        }

        if (!empty($errores)) {
            $error = implode('<br>', $errores);
        } else {
            $resultado = $userModel->cambiarPassword($usuario_id, $password_actual, $password_nueva);
            
            if ($resultado === true) {
                $exito = 'Contraseña cambiada correctamente.';
            } else {
                $error = $resultado;
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
$page_title = 'Cambiar Contraseña - CIAUBA';
require_once __DIR__ . '/header.php';
?>

    <main>
        <div class="form-container" style="max-width: 500px;">
            <h2>Cambiar Contraseña</h2>
            
            <?php if ($error): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($exito): ?>
                <div class="alert success"><?php echo $exito; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div class="form-group">
                    <label for="password_actual">Contraseña actual *</label>
                    <input type="password" id="password_actual" name="password_actual" required>
                </div>

                <div class="form-group">
                    <label for="password_nueva">Nueva contraseña *</label>
                    <input type="password" id="password_nueva" name="password_nueva" required>
                    <small>Mínimo 8 caracteres, una mayúscula, un número y un carácter especial</small>
                </div>

                <div class="form-group">
                    <label for="password_confirm">Confirmar nueva contraseña *</label>
                    <input type="password" id="password_confirm" name="password_confirm" required>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-key"></i> Cambiar contraseña
                    </button>
                    <a href="perfil.php" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al perfil
                    </a>
                </div>
            </form>
        </div>
    </main>

<?php require_once __DIR__ . '/footer.php'; ?>
</body>
</html>
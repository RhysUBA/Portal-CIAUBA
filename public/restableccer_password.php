<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (User::estaLogueado()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$exito = '';
$token_valido = false;
$token = $_GET['token'] ?? '';

if (empty($token)) {
    header('Location: recuperar_password.php');
    exit;
}

$userModel = new User();

// Validar token
$usuario_id = $userModel->validarTokenRecuperacion($token);
if ($usuario_id) {
    $token_valido = true;
} else {
    $error = 'El enlace de recuperación es inválido o ha expirado.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valido) {
    // Verificar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Error de validación del formulario.';
    } else {
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        
        $errores = [];
        
        if (empty($password)) {
            $errores[] = "La contraseña es obligatoria.";
        } elseif (strlen($password) < 8) {
            $errores[] = "La contraseña debe tener al menos 8 caracteres.";
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $errores[] = "La contraseña debe contener al menos una mayúscula.";
        } elseif (!preg_match('/[0-9]/', $password)) {
            $errores[] = "La contraseña debe contener al menos un número.";
        } elseif (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errores[] = "La contraseña debe contener al menos un carácter especial.";
        }
        
        if ($password !== $password_confirm) {
            $errores[] = "Las contraseñas no coinciden.";
        }
        
        if (!empty($errores)) {
            $error = implode('<br>', $errores);
        } else {
            $resultado = $userModel->restablecerPassword($token, $password);
            
            if ($resultado === true) {
                $exito = 'Contraseña restablecida correctamente. Serás redirigido al inicio de sesión.';
                header('refresh:3;url=login.php');
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
$extra_css = '
        .restablecer-container {
            max-width: 450px;
            margin: var(--space-xxl) auto;
            padding: var(--space-xl);
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
        }
        
        .password-requirements {
            background: #f8f9fa;
            padding: var(--space-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-lg);
            font-size: 0.9rem;
        }
        
        .password-requirements ul {
            margin-left: var(--space-lg);
            color: #666;
        }
    ';
$page_title = 'Restablecer Contraseña - CIAUBA';
require_once __DIR__ . '/header.php';
?>

    <main>
        <div class="restablecer-container">
            <h2 class="text-center">Restablecer Contraseña</h2>
            
            <?php if ($error): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($exito): ?>
                <div class="alert success"><?php echo $exito; ?></div>
            <?php endif; ?>

            <?php if ($token_valido && !$exito): ?>
                <div class="password-requirements">
                    <strong><i class="fas fa-info-circle"></i> Requisitos:</strong>
                    <ul>
                        <li>Mínimo 8 caracteres</li>
                        <li>Al menos una letra mayúscula</li>
                        <li>Al menos un número</li>
                        <li>Al menos un carácter especial (!@#$%^&*)</li>
                    </ul>
                </div>
                
                <form method="POST" action="?token=<?php echo htmlspecialchars($token); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="form-group">
                        <label for="password">Nueva contraseña</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirm">Confirmar contraseña</label>
                        <input type="password" id="password_confirm" name="password_confirm" required>
                    </div>
                    
                    <button type="submit" class="btn-primary" style="width: 100%;">
                        <i class="fas fa-save"></i> Restablecer contraseña
                    </button>
                </form>
            <?php endif; ?>
            
            <div class="text-center" style="margin-top: var(--space-lg);">
                <p><a href="login.php"><i class="fas fa-arrow-left"></i> Volver al inicio de sesión</a></p>
            </div>
        </div>
    </main>

<?php require_once __DIR__ . '/footer.php'; ?>
</body>
</html>
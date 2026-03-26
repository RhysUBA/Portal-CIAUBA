<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/mail_helper.php';

if (User::estaLogueado()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Por favor, ingresa tu email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El email no es válido.';
    } else {
        $userModel = new User();
        $usuario = $userModel->obtenerPorEmail($email);
        
        // Siempre mostramos el mismo mensaje por seguridad (no revelar si existe)
        $exito = 'Si el email está registrado, recibirás un enlace de recuperación.';
        
        if ($usuario) {
            // Generar token
            $token = $userModel->generarTokenRecuperacion($email);
            
            if ($token) {
                // Construir enlace de recuperación
                $enlace = "http://" . $_SERVER['HTTP_HOST'] . "/Portal%20CIAUBA/public/restablecer_password.php?token=" . urlencode($token);
                
                // Obtener cuerpo del email
                $cuerpo = cuerpoEmailRecuperacion($usuario['nombre'], $enlace);
                
                // Enviar correo
                $resultado = enviarCorreo(
                    $usuario['email'],
                    $usuario['nombre'],
                    'Recuperación de contraseña - CIAUBA',
                    $cuerpo['html'],
                    $cuerpo['texto']
                );
                
                // Si hay error en el envío, lo podemos loguear, pero al usuario no le decimos
                if (!$resultado['success']) {
                    // Opcional: guardar error en log
                    error_log("Error al enviar email de recuperación a {$usuario['email']}: " . $resultado['message']);
                }
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
        .recuperar-container {
            max-width: 450px;
            margin: var(--space-xxl) auto;
            padding: var(--space-xl);
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
        }
        .info-text {
            background: rgba(52, 152, 219, 0.1);
            padding: var(--space-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-lg);
            color: var(--color-info);
            border-left: 4px solid var(--color-info);
        }
    ';
$page_title = 'Recuperar Contraseña - CIAUBA';
require_once __DIR__ . '/header.php';
?>

    <main>
        <div class="recuperar-container">
            <h2 class="text-center">Recuperar Contraseña</h2>
            
            <div class="info-text">
                <i class="fas fa-info-circle"></i>
                Ingresa tu email y te enviaremos un enlace para restablecer tu contraseña.
            </div>
            
            <?php if ($error): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($exito): ?>
                <div class="alert success"><?php echo $exito; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required placeholder="tu@email.com">
                </div>
                
                <button type="submit" class="btn-primary" style="width: 100%;">
                    <i class="fas fa-paper-plane"></i> Enviar enlace
                </button>
            </form>
            
            <div class="text-center" style="margin-top: var(--space-lg);">
                <p><a href="login.php"><i class="fas fa-arrow-left"></i> Volver al inicio de sesión</a></p>
            </div>
        </div>
    </main>

<?php require_once __DIR__ . '/footer.php'; ?>
</body>
</html>
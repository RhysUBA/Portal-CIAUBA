<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (User::estaLogueado()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identificador = trim($_POST['identificador'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($identificador) || empty($password)) {
        $error = 'Todos los campos son obligatorios.';
    } else {
        $user = new User();
        $resultado = $user->login($identificador, $password);
        if ($resultado === true) {
            header('Location: dashboard.php');
            exit;
        } else {
            $error = $resultado;
        }
    }
}
$page_title = 'Iniciar Sesión - CIAUBA';
require_once __DIR__ . '/header.php';
?>

    <main>
        <section class="registration-form">
            <h2>Iniciar Sesión</h2>
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="identificador">Email o Usuario:</label>
                    <input type="text" id="identificador" name="identificador" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit">Entrar</button>
                    <div style="text-align: right; margin-top: var(--space-xs);">
                        <a href="recuperar_password.php" style="font-size: 0.9rem;">¿Olvidaste tu contraseña?</a>
                    </div>
            </form>
            <p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
        </section>
    </main>

<?php require_once __DIR__ . '/footer.php'; ?>
</body>
</html>
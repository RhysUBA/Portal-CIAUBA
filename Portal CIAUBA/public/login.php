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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - CIAUBA</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <img src="img/logo-uba-horizontal1.png" alt="uba_logo">
        <div class="logo">
            <h1>Club de Ingeniería Aplicada UBA</h1>
            <p>Aprende • Construye • Mejora</p>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Inicio</a></li>
                <li><a href="information.php">Información</a></li>
                <li><a href="register.php">Registro</a></li>
            </ul>
        </nav>
    </header>

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
            </form>
            <p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
        </section>
    </main>

    <footer>
        <p>Club de Ingeniería Aplicada UBA &copy; 2025</p>
        <p>Contacto: rhysuba@gmail.com</p>
    </footer>
</body>
</html>
<?php
// Si no se ha iniciado sesión, asegurar que la sesión está disponible para User::estaLogueado()
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Definir título y descripción por defecto (se pueden sobreescribir antes de incluir)
$page_title = $page_title ?? 'CIAUBA - Club de Ingeniería Aplicada';
$page_description = $page_description ?? 'Club de Ingeniería Aplicada de la Universidad Bicentenaria de Aragua. Aprende, construye y mejora con nosotros.';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php if (isset($extra_css)): ?>
        <style><?php echo $extra_css; ?></style>
    <?php endif; ?>
</head>
<body>
    <header class="sticky-header">
        <div class="header-container">
            <div class="logo-area">
                <div class="header-logos">
                    <img src="img/logo-uba-horizontal1.png" alt="UBA Logo" class="header-logo">
                    <img src="img/logo-cia.png" alt="CIAUBA Logo" class="header-logo">
                </div>
                <div class="logo-text">
                    <h1>Club de Ingeniería Aplicada UBA</h1>
                    <p>Aprende • Construye • Mejora</p>
                </div>
            </div>
            <nav class="main-nav" id="mainNav">
                <ul>
                    <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Inicio</a></li>
                    <li><a href="information.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'information.php' ? 'active' : ''; ?>">Información</a></li>
                    <?php if (User::estaLogueado()): ?>
                        <li><a href="members.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'members.php' ? 'active' : ''; ?>">Miembros</a></li>
                        <li><a href="work_together.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'work_together.php' ? 'active' : ''; ?>">Foro</a></li>
                        <li><a href="recursos.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'recursos.php' ? 'active' : ''; ?>">Recursos</a></li>
                        <li><a href="eventos.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'eventos.php' ? 'active' : ''; ?>">Eventos</a></li>
                        <li><a href="contacto.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'contacto.php' ? 'active' : ''; ?>">Contacto</a></li>
                        <li><a href="perfil.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'perfil.php' ? 'active' : ''; ?>"><i class="fas fa-user"></i> Mi Perfil</a></li>
                        <?php if (User::esAdmin()): ?>
                            <li><a href="admin.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : ''; ?>">Admin</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php">Cerrar sesión (<?php echo $_SESSION['usuario_nombre']; ?>)</a></li>
                    <?php else: ?>
                        <li><a href="login.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : ''; ?>">Iniciar sesión</a></li>
                        <li><a href="register.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'register.php' ? 'active' : ''; ?>">Registro</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main>
<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::estaLogueado()) {
    header('Location: login.php');
    exit;
}

$userModel = new User();
$miembros = $userModel->obtenerMiembros(true); // solo activos
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIAUBA - Miembros</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <img src="img/logo-uba-horizontal1.png" alt="uba_logo">
        <div class="logo">
            <h1>Club de Ingeniería Aplicada de la Universidad Bicentenaria de Aragua</h1>
            <p>Aprende • Construye • Mejora</p>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Inicio</a></li>
                <li><a href="information.php">Información</a></li>
                <li><a href="members.php">Miembros</a></li>
                <li><a href="work_together.php">Foro</a></li>
                <li><a href="perfil.php"><i class="fas fa-user"></i> Mi Perfil</a></li>
                <?php if (User::esAdmin()): ?>
                    <li><a href="admin.php">Admin</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Cerrar sesión (<?php echo $_SESSION['usuario_nombre']; ?>)</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="members-directory">
            <h2>Directorio de Miembros</h2>
            <p>Conecta con otros estudiantes de ingeniería que trabajan en proyectos emocionantes.</p>
            
            <div class="members-grid">
                <?php if (empty($miembros)): ?>
                    <p>No hay miembros activos aún.</p>
                <?php else: ?>
                    <?php foreach ($miembros as $miembro): ?>
                    <article class="member-card">
                        <div class="member-avatar" onclick="window.location='perfil.php?id=<?php echo $miembro['id']; ?>'" style="cursor: pointer;">
                            <?php echo strtoupper(substr($miembro['nombre'], 0, 2)); ?></div>
                        <h3><?php echo htmlspecialchars($miembro['nombre']); ?></h3>
                        <p class="member-major"><?php echo htmlspecialchars($miembro['carrera']); ?></p>
                        <p class="member-skills">
                            <?php 
                            $intereses = explode(',', $miembro['intereses'] ?? '');
                            foreach ($intereses as $interes): 
                                if (!empty(trim($interes))):
                            ?>
                                <span class="skill-tag"><?php echo htmlspecialchars(trim($interes)); ?></span>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </p>
                        <p class="member-project">Rol: <?php echo $miembro['rol']; ?></p>
                        <a href="perfil.php?id=<?php echo $miembro['id']; ?>" class="contact-btn">
                            <i class="fas fa-user"></i> Ver perfil
                        </a>
                    </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <p>Club de Ingeniería Aplicada UBA &copy; 2025</p>
        <p>Contacto: rhysuba@gmail.com | Campus Edificio de Ingeniería, Salón de Realidad Virtual</p>
    </footer>
</body>
</html>
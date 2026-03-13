<?php
require_once __DIR__ . '/../vendor/autoload.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIAUBA - Home</title>
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
        <?php if (User::estaLogueado()): ?>
            <li><a href="members.php">Miembros</a></li>
            <li><a href="work_together.php">Foro</a></li>
            <li><a href="perfil.php"><i class="fas fa-user"></i> Mi Perfil</a></li>
            <?php if (User::esAdmin()): ?>
                <li><a href="admin.php">Admin</a></li>
            <?php endif; ?>
            <li><a href="logout.php">Cerrar sesión (<?php echo $_SESSION['usuario_nombre']; ?>)</a></li>
        <?php else: ?>
            <li><a href="login.php">Iniciar sesión</a></li>
            <li><a href="register.php">Registro</a></li>
        <?php endif; ?>
    </ul>
</nav>
    </header>

    <main>
        <section class="hero">
            <h2>Bienvenido al CIA</h2>
            <p>Un entorno controlado para que estudiantes desarrollen sus habilidades con proyectos prácticos, construyan portafolios y aprendan colaborando.</p>
            <?php if (!User::estaLogueado()): ?>
                <a href="register.php" class="cta-button">Únete</a>
            <?php endif; ?>
        </section>

        <section class="recent-projects">
            <h2>Proyectos recientes</h2>
            <div class="project-grid">
                <article class="project-card">
                    <h3>Mouse virtual</h3>
                    <p>Reconocimiento de manos y expresiones como herramientas de control</p>
                    <span class="project-status">Completado</span>
                </article>
                <article class="project-card">
                    <h3>Asistente virtual</h3>
                    <p>Sistema potenciado por IA y técnicas de machine learning</p>
                    <span class="project-status">En progreso</span>
                </article>
                <article class="project-card">
                    <h3>Página Web CIAUBA</h3>
                    <p>Foro de información y apoyo mutuo entre estudiantes</p>
                    <span class="project-status">Planeación</span>
                </article>
            </div>
        </section>

        <section class="forum-preview">
            <h2>Últimas discusiones del foro</h2>
            <div class="discussion-list">
                <article class="discussion">
                    <h3><a href="#">PCB Design Best Practices</a></h3>
                    <p>Publicado por: Alex Chen • hace 2 días • 15 comentarios</p>
                </article>
                <article class="discussion">
                    <h3><a href="#">3D Printing Failures & Solutions</a></h3>
                    <p>Publicado por: Maria Rodriguez • hace 5 días • 22 comentarios</p>
                </article>
                <article class="discussion">
                    <h3><a href="#">Upcoming Hackathon Team Formation</a></h3>
                    <p>Publicado por: Club President • hace 1 semana • 32 comentarios</p>
                </article>
            </div>
            <?php if (User::estaLogueado()): ?>
                <a href="work_together.php" class="view-all">Ver todas las discusiones</a>
            <?php else: ?>
                <a href="login.php" class="view-all">Inicia sesión para ver el foro</a>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>Club de Ingeniería Aplicada UBA &copy; 2025</p>
        <p>Contacto: rhysuba@gmail.com | Campus Edificio de Ingeniería, Salón de Realidad Virtual</p>
    </footer>
</body>
</html>
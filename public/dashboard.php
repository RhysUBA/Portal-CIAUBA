<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::estaLogueado()) {
    header('Location: login.php');
    exit;
}

$userModel = new User();
$usuario = $userModel->obtenerPorId($_SESSION['usuario_id']);
$estadisticas = $userModel->obtenerEstadisticas($_SESSION['usuario_id']);

$nombre = $_SESSION['usuario_nombre'];
$rol = $_SESSION['usuario_rol'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - CIAUBA</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                <li><a href="members.php">Miembros</a></li>
                <li><a href="work_together.php">Foro</a></li>
                <!-- NUEVO: Enlace al perfil -->
                <li><a href="perfil.php"><i class="fas fa-user"></i> Mi Perfil</a></li>
                <?php if ($rol === 'admin'): ?>
                    <li><a href="admin.php">Admin</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Cerrar sesión (<?php echo $nombre; ?>)</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="dashboard">
            <h2>Bienvenido, <?php echo htmlspecialchars($nombre); ?>!</h2>
            <p>Has iniciado sesión correctamente. Desde aquí puedes acceder a las diferentes secciones del club.</p>
            
            <div class="dashboard-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: var(--space-md); margin-top: var(--space-xl);">
                <div class="card" style="background: linear-gradient(135deg, var(--color-dark-blue), var(--color-medium-blue)); color: white;">
                    <div style="display: flex; align-items: center; gap: var(--space-sm); margin-bottom: var(--space-sm);">
                        <div style="width: 50px; height: 50px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; color: var(--color-dark-blue); font-weight: bold; font-size: 1.5rem;">
                            <?php if ($usuario['avatar']): ?>
                                <img src="uploads/avatars/<?php echo $usuario['avatar']; ?>" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                            <?php else: ?>
                                <?php echo strtoupper(substr($nombre, 0, 2)); ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h3 style="color: white; margin: 0;"><?php echo htmlspecialchars($nombre); ?></h3>
                            <p style="color: rgba(255,255,255,0.8); margin: 0;">@<?php echo $_SESSION['usuario_username']; ?></p>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-around; margin: var(--space-sm) 0;">
                        <div style="text-align: center;">
                            <div style="font-weight: bold; font-size: 1.2rem;"><?php echo $estadisticas['temas']; ?></div>
                            <div style="font-size: 0.8rem; opacity: 0.8;">Temas</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-weight: bold; font-size: 1.2rem;"><?php echo $estadisticas['respuestas']; ?></div>
                            <div style="font-size: 0.8rem; opacity: 0.8;">Respuestas</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-weight: bold; font-size: 1.2rem;"><?php echo $estadisticas['proyectos']; ?></div>
                            <div style="font-size: 0.8rem; opacity: 0.8;">Proyectos</div>
                        </div>
                    </div>
                    <a href="perfil.php" style="display: block; text-align: center; padding: var(--space-xs); background: rgba(255,255,255,0.2); border-radius: var(--radius-md); color: white; text-decoration: none; margin-top: var(--space-sm);">
                        <i class="fas fa-eye"></i> Ver mi perfil completo
                    </a>
                </div>

                <div class="card">
                    <i class="fas fa-users" style="font-size: 2rem; color: var(--color-light-blue);"></i>
                    <h3>Miembros</h3>
                    <p>Conoce a los otros miembros del club.</p>
                    <a href="members.php" class="btn">Ver miembros</a>
                </div>
                
                <div class="card">
                    <i class="fas fa-comments" style="font-size: 2rem; color: var(--color-light-blue);"></i>
                    <h3>Foro</h3>
                    <p>Participa en las discusiones técnicas.</p>
                    <a href="work_together.php" class="btn">Ir al foro</a>
                </div>
                
                <?php if ($rol === 'admin'): ?>
                <div class="card">
                    <i class="fas fa-cog" style="font-size: 2rem; color: var(--color-light-blue);"></i>
                    <h3>Administración</h3>
                    <p>Gestiona miembros y contenido.</p>
                    <a href="admin.php" class="btn">Panel admin</a>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <p>Club de Ingeniería Aplicada UBA &copy; 2025</p>
        <p>Contacto: rhysuba@gmail.com</p>
    </footer>
</body>
</html>
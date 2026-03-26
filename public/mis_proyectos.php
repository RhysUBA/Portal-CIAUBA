<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::estaLogueado()) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$miembroProyectoModel = new MiembroProyecto();

// Obtener proyectos del usuario (activos)
$proyectos_activos = $miembroProyectoModel->obtenerProyectosDeUsuario($usuario_id, true);

// También podríamos obtener proyectos donde fue líder (pero ya vienen en la misma lista)
$page_title = 'Mis Proyectos - CIAUBA';
require_once __DIR__ . '/header.php';
?>

    <main>
        <section class="user-section">
            <h2>Mis Proyectos</h2>
            <?php if (empty($proyectos_activos)): ?>
                <div class="alert info">No estás participando en ningún proyecto actualmente.</div>
            <?php else: ?>
                <div class="project-list">
                    <?php foreach ($proyectos_activos as $proyecto): ?>
                        <article class="project-card">
                            <h3><?php echo htmlspecialchars($proyecto['titulo']); ?></h3>
                            <p><?php echo htmlspecialchars(substr($proyecto['descripcion'], 0, 200)) . '...'; ?></p>
                            <div class="project-meta">
                                <span class="status-badge status-<?php echo $proyecto['estado']; ?>">
                                    <?php echo $proyecto['estado']; ?>
                                </span>
                                <span><i class="fas fa-users"></i> <?php echo $proyecto['num_miembros']; ?> miembros</span>
                                <span><i class="fas fa-calendar"></i> Inicio: <?php echo date('d/m/Y', strtotime($proyecto['fecha_inicio'])); ?></span>
                                <span><i class="fas fa-tag"></i> Rol: <?php echo $proyecto['rol_en_proyecto']; ?></span>
                            </div>
                            <a href="proyecto.php?id=<?php echo $proyecto['id']; ?>" class="btn">Ver detalles</a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

<?php require_once __DIR__ . '/footer.php'; ?>
</body>
</html>
<?php
require_once __DIR__ . '/../vendor/autoload.php';

function tiempoTranscurrido($fecha) {
    $ahora = new DateTime();
    $fecha_creacion = new DateTime($fecha);
    $diferencia = $ahora->diff($fecha_creacion);
    if ($diferencia->d > 0) return $diferencia->d . ' día(s)';
    if ($diferencia->h > 0) return $diferencia->h . ' hora(s)';
    return $diferencia->i . ' minuto(s)';
}

$proyectoModel = new Proyecto();
$temaModel = new Tema();

$todos_proyectos = $proyectoModel->obtenerTodos();
usort($todos_proyectos, function($a, $b) {
    return strtotime($b['creado_en']) - strtotime($a['creado_en']);
});
$proyectos_recientes = array_slice(array_filter($todos_proyectos, function($p) {
    return $p['estado'] !== 'cancelled';
}), 0, 3);

$temas_recientes = $temaModel->obtenerRecientes(3);

$page_title = 'CIAUBA - Home';
require_once __DIR__ . '/header.php';
?>

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
                <?php if (empty($proyectos_recientes)): ?>
                    <p>No hay proyectos publicados aún. Sé el primero en <a href="admin_proyectos.php">crear un proyecto</a>.</p>
                <?php else: ?>
                    <?php foreach ($proyectos_recientes as $proyecto): ?>
                        <a href="proyecto.php?id=<?php echo $proyecto['id']; ?>" class="clickable-block">
                            <article class="project-card">
                                <h3><?php echo htmlspecialchars($proyecto['titulo']); ?></h3>
                                <p><?php echo htmlspecialchars(substr($proyecto['descripcion'], 0, 120)) . (strlen($proyecto['descripcion']) > 120 ? '...' : ''); ?></p>
                                <span class="project-status"><?php echo $proyecto['estado']; ?></span>
                                <!-- Botón eliminado, el bloque completo es clickable -->
                            </article>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="forum-preview">
            <h2>Últimas discusiones del foro</h2>
            <div class="discussion-list">
                <?php if (empty($temas_recientes)): ?>
                    <p>No hay temas en el foro. <a href="nuevo_tema.php">¡Crea el primero!</a></p>
                <?php else: ?>
                    <?php foreach ($temas_recientes as $tema): ?>
                        <a href="tema.php?id=<?php echo $tema['id']; ?>" class="clickable-block">
                            <article class="discussion">
                                <h3><?php echo htmlspecialchars($tema['titulo']); ?></h3>
                                <p>Publicado por: <?php echo htmlspecialchars($tema['nombre']); ?> • <?php echo $tema['num_respuestas']; ?> comentarios • hace <?php echo tiempoTranscurrido($tema['creado_en']); ?></p>
                            </article>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php if (User::estaLogueado()): ?>
                <a href="work_together.php" class="view-all">Ver todas las discusiones</a>
            <?php else: ?>
                <a href="login.php" class="view-all">Inicia sesión para ver el foro</a>
            <?php endif; ?>
        </section>
    </main>

<?php require_once __DIR__ . '/footer.php'; ?>
</body>
</html>
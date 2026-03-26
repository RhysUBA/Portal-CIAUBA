<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::estaLogueado()) {
    header('Location: login.php');
    exit;
}

$userModel = new User();
$miembros = $userModel->obtenerMiembros(true); // solo activos

$extra_css = '
        .clickable-member {
            display: block;
            text-decoration: none;
            color: inherit;
            transition: transform var(--transition-normal), box-shadow var(--transition-normal);
        }
        .clickable-member:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }
        .member-card {
            transition: none;
        }
        .clickable-member:hover .member-card {
            transform: none;
            box-shadow: none;
        }
    ';
$page_title = 'Miembros - CIAUBA';
require_once __DIR__ . '/header.php';
?>

    <main>
        <section class="members-directory">
            <h2>Directorio de Miembros</h2>
            <p>Conecta con otros estudiantes de ingeniería que trabajan en proyectos emocionantes.</p>
            
            <div class="members-grid">
                <?php if (empty($miembros)): ?>
                    <p>No hay miembros activos aún.</p>
                <?php else: ?>
                    <?php foreach ($miembros as $miembro): ?>
                        <a href="perfil.php?id=<?php echo $miembro['id']; ?>" class="clickable-member">
                            <article class="member-card">
                                <div class="member-avatar" style="cursor: pointer;">
                                    <?php echo strtoupper(substr($miembro['nombre'], 0, 2)); ?>
                                </div>
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
                            </article>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

<?php require_once __DIR__ . '/footer.php'; ?>
</body>
</html>
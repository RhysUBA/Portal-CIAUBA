<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::estaLogueado()) {
    header('Location: login.php');
    exit;
}

$temaModel = new Tema();
$categoriaModel = new Categoria();

$categorias = $categoriaModel->obtenerTodas();

$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$por_pagina = 10;
$offset = ($pagina - 1) * $por_pagina;

$busqueda = trim($_GET['q'] ?? '');
$categoria_id = $_GET['cat'] ?? null;

$total_temas = $temaModel->contarTodos($categoria_id, $busqueda);
$total_paginas = ceil($total_temas / $por_pagina);

$temas = $temaModel->obtenerTodosPaginado($categoria_id, $busqueda, $por_pagina, $offset);
$extra_css = '
        /* Hacer que los bloques de temas sean clickables */
        .clickable-topic {
            display: block;
            text-decoration: none;
            color: inherit;
            transition: transform var(--transition-normal), box-shadow var(--transition-normal);
        }
        .clickable-topic:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        .discussion {
            transition: none;
        }
        .clickable-topic:hover .discussion {
            transform: none;
            box-shadow: none;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: var(--space-sm);
            margin-top: var(--space-xl);
        }
        .pagination a, .pagination span {
            padding: var(--space-sm) var(--space-md);
            border-radius: var(--radius-md);
            background: white;
            text-decoration: none;
            color: var(--color-dark-blue);
        }
        .pagination a:hover {
            background: var(--color-light-blue);
            color: white;
        }
        .pagination .active {
            background: var(--color-light-blue);
            color: white;
        }
    ';
$page_title = 'Foro - CIAUBA';
require_once __DIR__ . '/header.php';
?>

    <main>
        <section class="forum-header text-center" style="background: transparent; box-shadow: none; border: none; padding-bottom: 0;">
            <h2>Foro CIAUBA: Work Together</h2>
            <p>Comparte ideas, resuelve dudas y colabora con otros miembros.</p>

            <form method="GET" action="work_together.php" style="max-width: 600px; margin: var(--space-lg) auto; display: flex; gap: var(--space-sm);">
                <input type="text" name="q" placeholder="Buscar palabras clave..." value="<?php echo htmlspecialchars($busqueda); ?>" style="flex: 1;">
                <?php if ($categoria_id): ?>
                    <input type="hidden" name="cat" value="<?php echo htmlspecialchars($categoria_id); ?>">
                <?php endif; ?>
                <input type="hidden" name="pagina" value="1">
                <button type="submit" class="action-btn">Buscar</button>
                <?php if ($busqueda || $categoria_id): ?>
                    <a href="work_together.php" class="action-btn reject" style="text-decoration: none;">Limpiar</a>
                <?php endif; ?>
            </form>

            <div style="display: flex; justify-content: center; gap: var(--space-sm); flex-wrap: wrap; margin-bottom: var(--space-xl);">
                <a href="work_together.php?pagina=1<?php echo $busqueda ? '&q='.urlencode($busqueda) : ''; ?>" 
                   class="filter-btn <?php echo !$categoria_id ? 'active' : ''; ?>">
                   Todos
                </a>
                <?php foreach ($categorias as $cat): ?>
                    <a href="work_together.php?cat=<?php echo $cat['id']; ?>&pagina=1<?php echo $busqueda ? '&q='.urlencode($busqueda) : ''; ?>" 
                       class="filter-btn <?php echo $categoria_id == $cat['id'] ? 'active' : ''; ?>">
                       <?php echo htmlspecialchars($cat['nombre']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <div class="text-right mb-md">
                <a href="nuevo_tema.php" class="cta-button" style="padding: var(--space-sm) var(--space-md); font-size: 1rem;">+ Nuevo Tema</a>
            </div>
        </section>

        <section class="forum-content">
            <div class="grid-system grid-1">
                <?php if (empty($temas)): ?>
                    <div class="text-center p-xl">
                        <h3>No se encontraron resultados</h3>
                        <p>Intenta con otras palabras clave o cambia de categoría.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($temas as $tema): ?>
                        <a href="tema.php?id=<?php echo $tema['id']; ?>" class="clickable-topic">
                            <article class="discussion">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-xs);">
                                    <h3>
                                        <?php if ($tema['fijo']): ?>📌 <?php endif; ?>
                                        <?php echo htmlspecialchars($tema['titulo']); ?>
                                    </h3>
                                    <span class="status-badge status-inprogress"><?php echo htmlspecialchars($tema['categoria_nombre']); ?></span>
                                </div>
                                <p style="margin-bottom: var(--space-xs); color: #555;">
                                    <?php echo htmlspecialchars(substr($tema['contenido'], 0, 150)) . (strlen($tema['contenido']) > 150 ? '...' : ''); ?>
                                </p>
                                <div style="font-size: 0.85rem; color: #888; margin-bottom: 0.25rem;">
                                    👤 Publicado por <strong><?php echo htmlspecialchars($tema['nombre']); ?></strong> 
                                    • 📅 <?php echo date('d M Y, H:i', strtotime($tema['creado_en'])); ?> 
                                    • 💬 <?php echo $tema['num_respuestas']; ?> respuestas
                                    • 👁️ <?php echo $tema['visitas']; ?> vistas
                                </div>
                                <?php if (!empty($tema['actualizado_en']) && $tema['actualizado_en'] != $tema['creado_en']): ?>
                                    <div style="font-size: 0.8rem; color: #999;">
                                        Última actividad: <?php echo date('d/m/Y H:i', strtotime($tema['actualizado_en'])); ?>
                                    </div>
                                <?php endif; ?>
                            </article>
                        </a>
                    <?php endforeach; ?>

                    <?php if ($total_paginas > 1): ?>
                        <div class="pagination">
                            <?php if ($pagina > 1): ?>
                                <a href="?pagina=<?php echo $pagina-1; ?><?php echo $categoria_id ? '&cat='.$categoria_id : ''; ?><?php echo $busqueda ? '&q='.urlencode($busqueda) : ''; ?>">&laquo; Anterior</a>
                            <?php endif; ?>
                            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                <?php if ($i == $pagina): ?>
                                    <span class="active"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?pagina=<?php echo $i; ?><?php echo $categoria_id ? '&cat='.$categoria_id : ''; ?><?php echo $busqueda ? '&q='.urlencode($busqueda) : ''; ?>"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            <?php if ($pagina < $total_paginas): ?>
                                <a href="?pagina=<?php echo $pagina+1; ?><?php echo $categoria_id ? '&cat='.$categoria_id : ''; ?><?php echo $busqueda ? '&q='.urlencode($busqueda) : ''; ?>">Siguiente &raquo;</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

<?php require_once __DIR__ . '/footer.php'; ?>
</body>
</html>
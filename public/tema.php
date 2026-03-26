<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::estaLogueado()) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: work_together.php');
    exit;
}

$temaModel = new Tema();
$postModel = new Post();

$tema = $temaModel->obtenerPorId($id);
if (!$tema) {
    header('Location: work_together.php');
    exit;
}

$temaModel->incrementarVisitas($id);

// Paginación de respuestas
$pagina_resp = isset($_GET['resp_pagina']) ? max(1, (int)$_GET['resp_pagina']) : 1;
$resp_por_pagina = 5;
$resp_offset = ($pagina_resp - 1) * $resp_por_pagina;

$respuestas = $postModel->obtenerRespuestasPaginadas($id, $resp_por_pagina, $resp_offset);
$total_respuestas = $postModel->contarRespuestasPorTema($id);
$total_paginas_resp = ceil($total_respuestas / $resp_por_pagina);

$error = '';
$exito = '';

if (isset($_POST['like'])) {
    $post_id = $_POST['post_id'];
    $postModel->darLike($post_id, $_SESSION['usuario_id']);
    header("Location: tema.php?id=$id");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['respuesta'])) {
    $contenido = trim($_POST['contenido'] ?? '');
    if (empty($contenido)) {
        $error = 'La respuesta no puede estar vacía.';
    } else {
        $resultado = $postModel->crear($id, $_SESSION['usuario_id'], $contenido);
        if ($resultado) {
            $exito = 'Respuesta publicada.';
            header("Location: tema.php?id=$id");
            exit;
        } else {
            $error = 'Error al publicar la respuesta.';
        }
    }
}

// Configuración para header.php
$page_title = htmlspecialchars($tema['titulo']) . ' - CIAUBA';

$extra_css = '
    .like-btn {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        color: #666;
    }
    .like-btn:hover {
        color: #e74c3c;
    }
    .like-btn.liked {
        color: #e74c3c;
    }
    .respuesta {
        border-bottom: 1px solid #eee;
        padding: 15px 0;
    }
    .respuesta-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 8px;
    }
    .pagination {
        display: flex;
        justify-content: center;
        gap: var(--space-sm);
        margin-top: var(--space-lg);
    }
    .pagination a, .pagination span {
        padding: var(--space-xs) var(--space-sm);
        border-radius: var(--radius-md);
        background: #f0f0f0;
        text-decoration: none;
        color: var(--color-dark-blue);
    }
    .pagination .active {
        background: var(--color-light-blue);
        color: white;
    }
';

$extra_js = '
    tinymce.init({
        selector: "#contenido",
        height: 300,
        plugins: "advlist autolink lists link image charmap preview anchor pagebreak",
        toolbar_mode: "floating",
        toolbar: "undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link image",
        branding: false
    });
';

require_once __DIR__ . '/header.php';
?>

<section class="tema">
    <h2><?php echo htmlspecialchars($tema['titulo']); ?></h2>
    <p class="tema-meta">
        Publicado por <strong><?php echo htmlspecialchars($tema['username']); ?></strong>
        en <?php echo date('d/m/Y H:i', strtotime($tema['creado_en'])); ?>
        | Categoría: <?php echo htmlspecialchars($tema['categoria_nombre']); ?>
        | <?php echo $total_respuestas; ?> respuestas
    </p>
    <div class="tema-contenido">
        <?php echo nl2br(htmlspecialchars($tema['contenido'])); ?>
    </div>
</section>

<section class="respuestas">
    <h3>Respuestas</h3>

    <?php if (empty($respuestas)): ?>
        <p>Aún no hay respuestas. ¡Sé el primero en responder!</p>
    <?php else: ?>
        <?php foreach ($respuestas as $resp): ?>
            <?php
            $likes = $postModel->contarLikes($resp['id']);
            $usuario_dio_like = $postModel->yaDioLike($resp['id'], $_SESSION['usuario_id']);
            ?>
            <article class="respuesta">
                <div class="respuesta-meta">
                    <div>
                        <strong><?php echo htmlspecialchars($resp['username']); ?></strong>
                        <span style="margin-left: 10px; font-size: 0.8rem; color: #999;"><?php echo date('d/m/Y H:i', strtotime($resp['creado_en'])); ?></span>
                        <?php if ($resp['editado']): ?>
                            <span style="font-size: 0.7rem; color: #999;">(Editado)</span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="post_id" value="<?php echo $resp['id']; ?>">
                            <button type="submit" name="like" class="like-btn <?php echo $usuario_dio_like ? 'liked' : ''; ?>">
                                <i class="fas fa-heart"></i> <?php echo $likes; ?>
                            </button>
                        </form>
                        <?php if (User::esAdmin() || $_SESSION['usuario_id'] == $resp['usuario_id']): ?>
                            <a href="editar_post.php?id=<?php echo $resp['id']; ?>&tema=<?php echo $id; ?>" class="action-btn edit" style="padding: 2px 8px; font-size: 0.8rem;">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="eliminar_post.php?id=<?php echo $resp['id']; ?>&tema=<?php echo $id; ?>" class="action-btn reject" style="padding: 2px 8px; font-size: 0.8rem;" onclick="return confirm('¿Eliminar este mensaje?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="respuesta-contenido">
                    <?php echo nl2br(htmlspecialchars($resp['contenido'])); ?>
                </div>
            </article>
        <?php endforeach; ?>

        <?php if ($total_paginas_resp > 1): ?>
            <div class="pagination">
                <?php if ($pagina_resp > 1): ?>
                    <a href="?id=<?php echo $id; ?>&resp_pagina=<?php echo $pagina_resp - 1; ?>">&laquo; Anterior</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_paginas_resp; $i++): ?>
                    <?php if ($i == $pagina_resp): ?>
                        <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?id=<?php echo $id; ?>&resp_pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if ($pagina_resp < $total_paginas_resp): ?>
                    <a href="?id=<?php echo $id; ?>&resp_pagina=<?php echo $pagina_resp + 1; ?>">Siguiente &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <section class="nueva-respuesta">
        <h4>Publicar una respuesta</h4>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <textarea name="contenido" id="contenido" rows="5"></textarea>
            </div>
            <button type="submit" name="respuesta">Responder</button>
        </form>
    </section>
</section>

<?php require_once __DIR__ . '/footer.php'; ?>
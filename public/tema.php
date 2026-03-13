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

// Obtener tema
$tema = $temaModel->obtenerPorId($id);
if (!$tema) {
    header('Location: work_together.php');
    exit;
}

// Incrementar visitas
$temaModel->incrementarVisitas($id);

// Obtener respuestas
$respuestas = $postModel->obtenerPorTema($id);

$error = '';
$exito = '';

// Procesar nueva respuesta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['respuesta'])) {
    $contenido = trim($_POST['contenido'] ?? '');
    if (empty($contenido)) {
        $error = 'La respuesta no puede estar vacía.';
    } else {
        $resultado = $postModel->crear($id, $_SESSION['usuario_id'], $contenido);
        if ($resultado) {
            $exito = 'Respuesta publicada.';
            // Recargar para ver la nueva respuesta
            header('Location: tema.php?id=' . $id);
            exit;
        } else {
            $error = 'Error al publicar la respuesta.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tema['titulo']); ?> - CIAUBA</title>
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
                <li><a href="members.php">Miembros</a></li>
                <li><a href="work_together.php">Foro</a></li>
                <?php if (User::esAdmin()): ?>
                    <li><a href="admin.php">Admin</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Cerrar sesión (<?php echo $_SESSION['usuario_nombre']; ?>)</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="tema">
            <h2><?php echo htmlspecialchars($tema['titulo']); ?></h2>
            <p class="tema-meta">
                Publicado por <strong><?php echo htmlspecialchars($tema['username']); ?></strong> 
                en <?php echo date('d/m/Y H:i', strtotime($tema['creado_en'])); ?>
                | Categoría: <?php echo htmlspecialchars($tema['categoria_nombre']); ?>
                | <?php echo count($respuestas); ?> respuestas
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
                <article class="respuesta">
                    <div class="respuesta-meta">
                        <strong><?php echo htmlspecialchars($resp['username']); ?></strong>
                        <span><?php echo date('d/m/Y H:i', strtotime($resp['creado_en'])); ?></span>
                    </div>
                    <div class="respuesta-contenido">
                        <?php echo nl2br(htmlspecialchars($resp['contenido'])); ?>
                    </div>
                </article>
                <?php endforeach; ?>
            <?php endif; ?>

            <section class="nueva-respuesta">
                <h4>Publicar una respuesta</h4>
                <?php if ($error): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if ($exito): ?>
                    <div class="exito"><?php echo htmlspecialchars($exito); ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <textarea name="contenido" rows="5" required></textarea>
                    </div>
                    <button type="submit" name="respuesta">Responder</button>
                </form>
            </section>
        </section>
    </main>

    <footer>
        <p>Club de Ingeniería Aplicada UBA &copy; 2025</p>
    </footer>
</body>
</html>
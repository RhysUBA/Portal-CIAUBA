<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::estaLogueado()) {
    header('Location: login.php');
    exit;
}

$post_id = $_GET['id'] ?? 0;
$tema_id = $_GET['tema'] ?? 0;

if (!$post_id || !$tema_id) {
    header('Location: work_together.php');
    exit;
}

$postModel = new Post();
$post = $postModel->obtenerPorId($post_id);

if (!$post || ($post['usuario_id'] != $_SESSION['usuario_id'] && !User::esAdmin())) {
    header('Location: tema.php?id=' . $tema_id);
    exit;
}

$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contenido = trim($_POST['contenido'] ?? '');
    if (empty($contenido)) {
        $error = 'El contenido no puede estar vacío.';
    } else {
        $resultado = $postModel->editar($post_id, $contenido, $_SESSION['usuario_id']);
        if ($resultado === true) {
            header('Location: tema.php?id=' . $tema_id);
            exit;
        } else {
            $error = $resultado;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar mensaje - CIAUBA</title>
    <link rel="stylesheet" href="css/styles.css">
    <!-- TinyMCE con API key -->
    <script src="https://cdn.tiny.cloud/1/githk1bira8yu82a7lq41ysc54ouef67jfog7djn599fu3ul/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#contenido',
            height: 300,
            plugins: 'advlist autolink lists link image charmap preview anchor pagebreak',
            toolbar_mode: 'floating',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link image',
            branding: false
        });
    </script>
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
                <li><a href="recursos.php">Recursos</a></li>
                <li><a href="perfil.php">Mi Perfil</a></li>
                <?php if (User::esAdmin()): ?>
                    <li><a href="admin.php">Admin</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Cerrar sesión</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="form-container">
            <h2>Editar mensaje</h2>
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <textarea name="contenido" id="contenido" rows="10"><?php echo htmlspecialchars($post['contenido']); ?></textarea>
                </div>
                <button type="submit">Guardar cambios</button>
                <a href="tema.php?id=<?php echo $tema_id; ?>" class="button">Cancelar</a>
            </form>
        </div>
    </main>

    <footer>
        <p>Club de Ingeniería Aplicada UBA &copy; 2025</p>
    </footer>
</body>
</html>
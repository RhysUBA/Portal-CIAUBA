<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::estaLogueado()) {
    header('Location: login.php');
    exit;
}

$categoriaModel = new Categoria();
$categorias = $categoriaModel->obtenerTodas();

$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $contenido = trim($_POST['contenido'] ?? '');
    $categoria_id = $_POST['categoria_id'] ?? 0;

    if (empty($titulo) || empty($contenido) || empty($categoria_id)) {
        $error = 'Todos los campos son obligatorios.';
    } else {
        $temaModel = new Tema();
        $resultado = $temaModel->crear($titulo, $contenido, $categoria_id, $_SESSION['usuario_id']);
        if ($resultado) {
            $exito = 'Tema creado correctamente. Redirigiendo...';
            header('refresh:2;url=work_together.php');
        } else {
            $error = 'Error al crear el tema.';
        }
    }
}

// Configuración para header.php
$page_title = 'Nuevo Tema - CIAUBA';

$extra_js = '
    tinymce.init({
        selector: "#contenido",
        height: 400,
        plugins: "advlist autolink lists link image charmap preview anchor pagebreak",
        toolbar_mode: "floating",
        toolbar: "undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link image",
        branding: false
    });
';

require_once __DIR__ . '/header.php';
?>

<section class="registration-form">
    <h2>Crear nuevo tema</h2>
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($exito): ?>
        <div class="exito"><?php echo htmlspecialchars($exito); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="categoria_id">Categoría</label>
            <select name="categoria_id" id="categoria_id" required>
                <option value="">Selecciona una categoría</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="titulo">Título</label>
            <input type="text" name="titulo" id="titulo" required>
        </div>

        <div class="form-group">
            <label for="contenido">Contenido</label>
            <textarea name="contenido" id="contenido" rows="10" required></textarea>
        </div>

        <button type="submit">Publicar tema</button>
        <a href="work_together.php" class="button">Cancelar</a>
    </form>
</section>

<?php require_once __DIR__ . '/footer.php'; ?>
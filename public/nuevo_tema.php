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
            $exito = 'Tema creado correctamente.';
            // Redirigir al foro después de 2 segundos
            header('refresh:2;url=work_together.php');
        } else {
            $error = 'Error al crear el tema.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Tema - CIAUBA</title>
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
                <li><a href="perfil.php"><i class="fas fa-user"></i> Mi Perfil</a></li>
                <?php if (User::esAdmin()): ?>
                    <li><a href="admin.php">Admin</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Cerrar sesión</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="registration-form">
            <h2>Crear nuevo tema</h2>
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($exito): ?>
                <div class="exito"><?php echo htmlspecialchars($exito); ?> Redirigiendo...</div>
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
    </main>

    <footer>
        <p>Club de Ingeniería Aplicada UBA &copy; 2025</p>
    </footer>
</body>
</html>
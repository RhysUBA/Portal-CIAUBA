<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::estaLogueado()) {
    header('Location: login.php');
    exit;
}

$userModel = new User();
$usuario_id = $_SESSION['usuario_id'];

$error = '';
$exito = '';

// Crear directorio de avatares si no existe
$upload_dir = __DIR__ . '/uploads/avatars/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Error de validación del formulario.';
    } else {
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $archivo = $_FILES['avatar'];
            
            // Validaciones
            $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
            $tamano_maximo = 2 * 1024 * 1024; // 2MB
            
            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            
            if (!in_array($extension, $extensiones_permitidas)) {
                $error = "Solo se permiten archivos JPG, JPEG, PNG y GIF.";
            } elseif ($archivo['size'] > $tamano_maximo) {
                $error = "El archivo no puede ser mayor a 2MB.";
            } else {
                // Generar nombre único
                $nombre_archivo = $usuario_id . '_' . time() . '.' . $extension;
                $ruta_destino = $upload_dir . $nombre_archivo;
                
                // Mover archivo
                if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                    // Actualizar BD
                    if ($userModel->actualizarAvatar($usuario_id, $nombre_archivo)) {
                        $exito = 'Foto de perfil actualizada correctamente.';
                    } else {
                        $error = 'Error al actualizar la base de datos.';
                        // Eliminar archivo si falló la BD
                        unlink($ruta_destino);
                    }
                } else {
                    $error = 'Error al subir el archivo.';
                }
            }
        } elseif (isset($_POST['eliminar'])) {
            // Eliminar avatar
            if ($userModel->eliminarAvatar($usuario_id)) {
                $exito = 'Foto de perfil eliminada.';
            } else {
                $error = 'Error al eliminar la foto.';
            }
        } else {
            $error = 'No se seleccionó ningún archivo.';
        }
    }
}

$usuario = $userModel->obtenerPorId($usuario_id);

// Generar token CSRF
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Foto de Perfil - CIAUBA</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .avatar-preview {
            text-align: center;
            margin-bottom: var(--space-xl);
        }
        
        .avatar-preview img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--color-light-blue);
            box-shadow: var(--shadow-md);
        }
        
        .avatar-preview .no-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--color-medium-blue), var(--color-light-blue));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: bold;
            margin: 0 auto;
            border: 4px solid white;
            box-shadow: var(--shadow-md);
        }
        
        .file-input-wrapper {
            position: relative;
            margin-bottom: var(--space-lg);
        }
        
        .file-input-wrapper input[type="file"] {
            position: absolute;
            left: -9999px;
        }
        
        .file-input-wrapper label {
            display: inline-block;
            padding: var(--space-sm) var(--space-lg);
            background: var(--color-light-blue);
            color: white;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: background var(--transition-fast);
        }
        
        .file-input-wrapper label:hover {
            background: var(--color-medium-blue);
        }
        
        #file-name {
            margin-left: var(--space-sm);
            color: #666;
        }
    </style>
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
        <div class="form-container" style="max-width: 500px;">
            <h2>Foto de Perfil</h2>
            
            <?php if ($error): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($exito): ?>
                <div class="alert success"><?php echo $exito; ?></div>
            <?php endif; ?>

            <div class="avatar-preview">
                <?php if ($usuario['avatar']): ?>
                    <img src="uploads/avatars/<?php echo htmlspecialchars($usuario['avatar']); ?>" alt="Avatar">
                <?php else: ?>
                    <div class="no-avatar">
                        <?php echo strtoupper(substr($usuario['nombre'], 0, 2)); ?>
                    </div>
                <?php endif; ?>
            </div>

            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div class="file-input-wrapper">
                    <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/gif" onchange="updateFileName()">
                    <label for="avatar"><i class="fas fa-camera"></i> Seleccionar imagen</label>
                    <span id="file-name">Ningún archivo seleccionado</span>
                </div>

                <p><small>Formatos permitidos: JPG, JPEG, PNG, GIF. Tamaño máximo: 2MB</small></p>

                <div class="button-group">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-upload"></i> Subir foto
                    </button>
                    
                    <?php if ($usuario['avatar']): ?>
                        <button type="submit" name="eliminar" value="1" class="btn-danger" onclick="return confirm('¿Estás seguro de eliminar tu foto de perfil?')">
                            <i class="fas fa-trash"></i> Eliminar foto
                        </button>
                    <?php endif; ?>
                    
                    <a href="perfil.php" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al perfil
                    </a>
                </div>
            </form>
        </div>
    </main>

    <script>
        function updateFileName() {
            const input = document.getElementById('avatar');
            const fileName = document.getElementById('file-name');
            if (input.files.length > 0) {
                fileName.textContent = input.files[0].name;
            } else {
                fileName.textContent = 'Ningún archivo seleccionado';
            }
        }
    </script>

    <footer>
        <p>Club de Ingeniería Aplicada UBA &copy; 2025</p>
        <p>Contacto: rhysuba@gmail.com</p>
    </footer>
</body>
</html>
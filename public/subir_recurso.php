<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::estaLogueado()) {
    header('Location: login.php');
    exit;
}

$proyectoModel = new Proyecto();
$proyectos = $proyectoModel->obtenerTodos(); // Para asociar recurso a proyecto

$error = '';
$exito = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Error de validación. Intenta de nuevo.';
    } else {
        $tipo = $_POST['tipo'] ?? '';
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $proyecto_id = !empty($_POST['proyecto_id']) ? $_POST['proyecto_id'] : null;
        $usuario_id = $_SESSION['usuario_id'];

        $recursoModel = new Recurso();

        if ($tipo == 'enlace' || $tipo == 'video') {
            $url = trim($_POST['url'] ?? '');
            if (empty($titulo) || empty($url)) {
                $error = 'El título y la URL son obligatorios.';
            } elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
                $error = 'La URL no es válida.';
            } else {
                if ($tipo == 'enlace') {
                    $resultado = $recursoModel->crearEnlace($titulo, $descripcion, $url, $usuario_id, $proyecto_id);
                } else {
                    $resultado = $recursoModel->crearVideo($titulo, $descripcion, $url, $usuario_id, $proyecto_id);
                }
                if ($resultado) {
                    $exito = 'Recurso subido correctamente.';
                } else {
                    $error = 'Error al guardar el recurso.';
                }
            }
        } elseif ($tipo == 'archivo') {
            if (empty($titulo)) {
                $error = 'El título es obligatorio.';
            } elseif (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
                $error = 'Debes seleccionar un archivo válido.';
            } else {
                $archivo = $_FILES['archivo'];
                $extensiones_permitidas = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'rar', 'jpg', 'jpeg', 'png', 'gif'];
                $tamano_maximo = 10 * 1024 * 1024; // 10 MB

                $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
                if (!in_array($extension, $extensiones_permitidas)) {
                    $error = 'Tipo de archivo no permitido. Extensiones permitidas: ' . implode(', ', $extensiones_permitidas);
                } elseif ($archivo['size'] > $tamano_maximo) {
                    $error = 'El archivo no puede ser mayor a 10 MB.';
                } else {
                    // Crear nombre único
                    $nombre_archivo = uniqid() . '_' . time() . '.' . $extension;
                    $ruta_destino = __DIR__ . '/uploads/recursos/' . $nombre_archivo;

                    if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                        $resultado = $recursoModel->crearArchivo($titulo, $descripcion, $nombre_archivo, $usuario_id, $proyecto_id);
                        if ($resultado) {
                            $exito = 'Archivo subido correctamente.';
                        } else {
                            unlink($ruta_destino); // Borrar archivo si falla la BD
                            $error = 'Error al guardar el recurso en la base de datos.';
                        }
                    } else {
                        $error = 'Error al subir el archivo.';
                    }
                }
            }
        } else {
            $error = 'Tipo de recurso no válido.';
        }
    }
}

// Generar token CSRF
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$extra_css = '
        .subir-container {
            max-width: 700px;
            margin: 0 auto;
        }
        .tabs {
            display: flex;
            gap: var(--space-xs);
            margin-bottom: var(--space-lg);
            border-bottom: 2px solid #eee;
            padding-bottom: var(--space-xs);
        }
        .tab-btn {
            background: none;
            border: none;
            padding: var(--space-sm) var(--space-lg);
            cursor: pointer;
            color: var(--color-medium-blue);
            font-weight: 600;
            border-radius: var(--radius-md) var(--radius-md) 0 0;
        }
        .tab-btn.active {
            color: var(--color-light-blue);
            border-bottom: 3px solid var(--color-light-blue);
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .info-ayuda {
            background: #f8f9fa;
            padding: var(--space-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-lg);
            font-size: 0.9rem;
            color: #666;
        }
    ';
$page_title = 'Recursos - CIAUBA';
require_once __DIR__ . '/header.php';
?>

    <main>
        <div class="subir-container">
            <h2><i class="fas fa-upload"></i> Subir nuevo recurso</h2>

            <?php if ($error): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($exito): ?>
                <div class="alert success"><?php echo $exito; ?> <a href="recursos.php">Ver recursos</a></div>
            <?php endif; ?>

            <div class="tabs">
                <button class="tab-btn active" onclick="mostrarTab('enlace')">🔗 Enlace</button>
                <button class="tab-btn" onclick="mostrarTab('archivo')">📁 Archivo</button>
                <button class="tab-btn" onclick="mostrarTab('video')">🎥 Video</button>
            </div>

            <!-- Formulario Enlace -->
            <div id="tab-enlace" class="tab-content active">
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="tipo" value="enlace">

                    <div class="form-group">
                        <label for="titulo_enlace">Título *</label>
                        <input type="text" id="titulo_enlace" name="titulo" required>
                    </div>

                    <div class="form-group">
                        <label for="url_enlace">URL *</label>
                        <input type="text" id="url_enlace" name="url" placeholder="https://..." required>
                    </div>

                    <div class="form-group">
                        <label for="descripcion_enlace">Descripción (opcional)</label>
                        <textarea id="descripcion_enlace" name="descripcion" rows="4"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="proyecto_enlace">Asociar a proyecto (opcional)</label>
                        <select id="proyecto_enlace" name="proyecto_id">
                            <option value="">-- Ninguno --</option>
                            <?php foreach ($proyectos as $proy): ?>
                                <option value="<?php echo $proy['id']; ?>"><?php echo htmlspecialchars($proy['titulo']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="cta-button">Subir enlace</button>
                    <a href="recursos.php" class="button">Cancelar</a>
                </form>
            </div>

            <!-- Formulario Archivo -->
            <div id="tab-archivo" class="tab-content">
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="tipo" value="archivo">

                    <div class="info-ayuda">
                        <i class="fas fa-info-circle"></i> Tamaño máximo: 10 MB. Formatos permitidos: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, ZIP, RAR, JPG, JPEG, PNG, GIF.
                    </div>

                    <div class="form-group">
                        <label for="titulo_archivo">Título *</label>
                        <input type="text" id="titulo_archivo" name="titulo" required>
                    </div>

                    <div class="form-group">
                        <label for="archivo">Seleccionar archivo *</label>
                        <input type="file" id="archivo" name="archivo" required>
                    </div>

                    <div class="form-group">
                        <label for="descripcion_archivo">Descripción (opcional)</label>
                        <textarea id="descripcion_archivo" name="descripcion" rows="4"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="proyecto_archivo">Asociar a proyecto (opcional)</label>
                        <select id="proyecto_archivo" name="proyecto_id">
                            <option value="">-- Ninguno --</option>
                            <?php foreach ($proyectos as $proy): ?>
                                <option value="<?php echo $proy['id']; ?>"><?php echo htmlspecialchars($proy['titulo']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="cta-button">Subir archivo</button>
                    <a href="recursos.php" class="button">Cancelar</a>
                </form>
            </div>

            <!-- Formulario Video -->
            <div id="tab-video" class="tab-content">
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="tipo" value="video">

                    <div class="info-ayuda">
                        <i class="fas fa-info-circle"></i> Puedes poner enlaces de YouTube, Vimeo, etc.
                    </div>

                    <div class="form-group">
                        <label for="titulo_video">Título *</label>
                        <input type="text" id="titulo_video" name="titulo" required>
                    </div>

                    <div class="form-group">
                        <label for="url_video">URL del video *</label>
                        <input type="url" id="url_video" name="url" placeholder="https://youtube.com/..." required>
                    </div>

                    <div class="form-group">
                        <label for="descripcion_video">Descripción (opcional)</label>
                        <textarea id="descripcion_video" name="descripcion" rows="4"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="proyecto_video">Asociar a proyecto (opcional)</label>
                        <select id="proyecto_video" name="proyecto_id">
                            <option value="">-- Ninguno --</option>
                            <?php foreach ($proyectos as $proy): ?>
                                <option value="<?php echo $proy['id']; ?>"><?php echo htmlspecialchars($proy['titulo']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="cta-button">Subir video</button>
                    <a href="recursos.php" class="button">Cancelar</a>
                </form>
            </div>
        </div>
    </main>

    <script>
        function mostrarTab(tab) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            document.querySelector(`.tab-btn[onclick*="'${tab}'"]`).classList.add('active');
            document.getElementById(`tab-${tab}`).classList.add('active');
        }
    </script>

<?php require_once __DIR__ . '/footer.php'; ?>
</body>
</html>
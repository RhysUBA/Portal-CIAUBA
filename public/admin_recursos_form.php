<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::esAdmin()) {
    header('Location: index.php');
    exit;
}

$recursoModel = new Recurso();
$proyectoModel = new Proyecto();
$userModel = new User();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$recurso = null;
$esEdicion = false;
if ($id > 0) {
    $recurso = $recursoModel->obtenerPorId($id);
    if ($recurso) $esEdicion = true;
}

$proyectos = $proyectoModel->obtenerTodos();
$usuarios = $userModel->obtenerMiembros(true); // activos

$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Error de validación.';
    } else {
        $tipo = $_POST['tipo'];
        $titulo = trim($_POST['titulo']);
        $descripcion = trim($_POST['descripcion']);
        $proyecto_id = !empty($_POST['proyecto_id']) ? $_POST['proyecto_id'] : null;
        $usuario_id = $_POST['usuario_id'];

        if (empty($titulo)) {
            $error = 'El título es obligatorio.';
        } elseif (!in_array($tipo, ['enlace', 'archivo', 'video'])) {
            $error = 'Tipo de recurso no válido.';
        } else {
            if ($esEdicion) {
                // 1. Actualizar campos básicos (título, descripción, tipo, proyecto_id, usuario_id)
                $campos = [
                    'titulo' => $titulo,
                    'descripcion' => $descripcion,
                    'tipo' => $tipo,
                    'proyecto_id' => $proyecto_id,
                    'usuario_id' => $usuario_id
                ];
                $ok = $recursoModel->actualizarCampos($id, $campos);

                if ($ok) {
                    // 2. Manejar contenido según tipo
                    if ($tipo == 'enlace' || $tipo == 'video') {
                        $url = trim($_POST['url']);
                        if (empty($url)) {
                            $error = 'La URL es obligatoria para enlaces y videos.';
                        } elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
                            $error = 'La URL no es válida.';
                        } else {
                            $recursoModel->actualizarCampos($id, ['url' => $url]);
                            $exito = 'Recurso actualizado correctamente.';
                        }
                    } elseif ($tipo == 'archivo') {
                        // Si se sube un nuevo archivo, procesarlo
                        if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
                            $archivo = $_FILES['archivo'];
                            $extensiones_permitidas = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'rar', 'jpg', 'jpeg', 'png', 'gif'];
                            $tamano_maximo = 10 * 1024 * 1024; // 10 MB

                            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
                            if (!in_array($extension, $extensiones_permitidas)) {
                                $error = 'Tipo de archivo no permitido.';
                            } elseif ($archivo['size'] > $tamano_maximo) {
                                $error = 'El archivo no puede ser mayor a 10 MB.';
                            } else {
                                // Crear nombre único
                                $nombre_archivo = uniqid() . '_' . time() . '.' . $extension;
                                $ruta_destino = __DIR__ . '/uploads/recursos/' . $nombre_archivo;

                                if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                                    // Eliminar archivo anterior si existe
                                    if ($recurso && $recurso['archivo_ruta']) {
                                        $ruta_anterior = __DIR__ . '/uploads/recursos/' . $recurso['archivo_ruta'];
                                        if (file_exists($ruta_anterior)) unlink($ruta_anterior);
                                    }
                                    // Actualizar BD
                                    $recursoModel->actualizarCampos($id, ['archivo_ruta' => $nombre_archivo]);
                                    $exito = 'Archivo actualizado correctamente.';
                                } else {
                                    $error = 'Error al subir el archivo.';
                                }
                            }
                        } else {
                            $exito = 'Recurso actualizado correctamente (sin cambios en el archivo).';
                        }
                    }

                    if (empty($error) && empty($exito)) {
                        $exito = 'Recurso actualizado correctamente.';
                    }
                } else {
                    $error = 'Error al actualizar el recurso.';
                }
            } else {
                // Crear nuevo recurso
                $url = trim($_POST['url']);
                $archivo_ruta = null;
                $resultado = false;

                if ($tipo == 'enlace' || $tipo == 'video') {
                    if (empty($url)) {
                        $error = 'La URL es obligatoria para enlaces y videos.';
                    } elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
                        $error = 'La URL no es válida.';
                    } else {
                        if ($tipo == 'enlace') {
                            $resultado = $recursoModel->crearEnlace($titulo, $descripcion, $url, $usuario_id, $proyecto_id);
                        } else {
                            $resultado = $recursoModel->crearVideo($titulo, $descripcion, $url, $usuario_id, $proyecto_id);
                        }
                    }
                } elseif ($tipo == 'archivo') {
                    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
                        $archivo = $_FILES['archivo'];
                        $extensiones_permitidas = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'rar', 'jpg', 'jpeg', 'png', 'gif'];
                        $tamano_maximo = 10 * 1024 * 1024;

                        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
                        if (!in_array($extension, $extensiones_permitidas)) {
                            $error = 'Tipo de archivo no permitido.';
                        } elseif ($archivo['size'] > $tamano_maximo) {
                            $error = 'El archivo no puede ser mayor a 10 MB.';
                        } else {
                            $nombre_archivo = uniqid() . '_' . time() . '.' . $extension;
                            $ruta_destino = __DIR__ . '/uploads/recursos/' . $nombre_archivo;
                            if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                                $resultado = $recursoModel->crearArchivo($titulo, $descripcion, $nombre_archivo, $usuario_id, $proyecto_id);
                                if (!$resultado) {
                                    unlink($ruta_destino);
                                    $error = 'Error al guardar en la base de datos.';
                                }
                            } else {
                                $error = 'Error al subir el archivo.';
                            }
                        }
                    } else {
                        $error = 'Debes seleccionar un archivo.';
                    }
                }

                if ($resultado) {
                    $exito = 'Recurso creado correctamente.';
                    header('Location: admin_recursos.php?mensaje=' . urlencode($exito) . '&tipo=success');
                    exit;
                } elseif (empty($error)) {
                    $error = 'Error al crear el recurso.';
                }
            }
        }
    }
}

// Generar token CSRF
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$extra_css = '
        .tipo-opcion {
            display: inline-block;
            margin-right: var(--space-md);
            margin-bottom: var(--space-sm);
        }
        .campo-condicional {
            display: none;
            margin-top: var(--space-md);
            padding: var(--space-md);
            background: #f9f9f9;
            border-radius: var(--radius-md);
        }
        .campo-condicional.activo {
            display: block;
        }
        .file-info {
            background: #e9ecef;
            padding: var(--space-sm);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-sm);
        }
    ';
$page_title = 'Gestión de Recursos - CIAUBA';
require_once __DIR__ . '/header.php';
?>

    <main>
        <div class="form-container" style="max-width: 700px;">
            <h2><?php echo $esEdicion ? 'Editar' : 'Nuevo'; ?> Recurso</h2>

            <?php if ($error): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($exito): ?>
                <div class="alert success"><?php echo $exito; ?></div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div class="form-group">
                    <label for="titulo">Título *</label>
                    <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($recurso['titulo'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="4"><?php echo htmlspecialchars($recurso['descripcion'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Tipo de recurso *</label>
                    <div>
                        <label class="tipo-opcion">
                            <input type="radio" name="tipo" value="enlace" <?php echo (!$esEdicion || ($recurso['tipo'] ?? '') == 'enlace') ? 'checked' : ''; ?> required> <i class="fas fa-link"></i> Enlace
                        </label>
                        <label class="tipo-opcion">
                            <input type="radio" name="tipo" value="archivo" <?php echo (!$esEdicion || ($recurso['tipo'] ?? '') == 'archivo') ? 'checked' : ''; ?>> <i class="fas fa-file"></i> Archivo
                        </label>
                        <label class="tipo-opcion">
                            <input type="radio" name="tipo" value="video" <?php echo (!$esEdicion || ($recurso['tipo'] ?? '') == 'video') ? 'checked' : ''; ?>> <i class="fas fa-video"></i> Video
                        </label>
                    </div>
                </div>

                <!-- Campo para enlace/video (aparece según selección) -->
                <div id="campo-url" class="campo-condicional <?php echo (!$esEdicion && !$recurso) ? '' : (in_array($recurso['tipo'] ?? '', ['enlace', 'video']) ? 'activo' : ''); ?>">
                    <div class="form-group">
                        <label for="url">URL *</label>
                        <input type="url" id="url" name="url" placeholder="https://..." value="<?php echo htmlspecialchars($recurso['url'] ?? ''); ?>">
                    </div>
                </div>

                <!-- Campo para archivo (aparece según selección) -->
                <div id="campo-archivo" class="campo-condicional <?php echo (!$esEdicion && !$recurso) ? '' : (($recurso['tipo'] ?? '') == 'archivo' ? 'activo' : ''); ?>">
                    <?php if ($esEdicion && $recurso && $recurso['tipo'] == 'archivo' && $recurso['archivo_ruta']): ?>
                        <div class="file-info">
                            <i class="fas fa-file"></i> Archivo actual: <?php echo htmlspecialchars($recurso['archivo_ruta']); ?>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="archivo"><?php echo $esEdicion ? 'Reemplazar archivo (opcional)' : 'Archivo *'; ?></label>
                        <input type="file" id="archivo" name="archivo" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar,.jpg,.jpeg,.png,.gif">
                        <small>Formatos permitidos: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, ZIP, RAR, JPG, PNG, GIF. Tamaño máximo: 10 MB.</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="proyecto_id">Asociar a proyecto (opcional)</label>
                    <select id="proyecto_id" name="proyecto_id">
                        <option value="">-- Ninguno --</option>
                        <?php foreach ($proyectos as $proy): ?>
                            <option value="<?php echo $proy['id']; ?>" <?php echo isset($recurso['proyecto_id']) && $recurso['proyecto_id'] == $proy['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($proy['titulo']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="usuario_id">Autor (usuario)</label>
                    <select id="usuario_id" name="usuario_id">
                        <?php foreach ($usuarios as $u): ?>
                            <option value="<?php echo $u['id']; ?>" <?php echo isset($recurso['usuario_id']) && $recurso['usuario_id'] == $u['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($u['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn-primary"><?php echo $esEdicion ? 'Actualizar' : 'Crear'; ?> recurso</button>
                    <a href="admin_recursos.php" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Mostrar/ocultar campos según tipo seleccionado
        const radios = document.querySelectorAll('input[name="tipo"]');
        const campoUrl = document.getElementById('campo-url');
        const campoArchivo = document.getElementById('campo-archivo');

        function actualizarCampos() {
            const tipoSeleccionado = document.querySelector('input[name="tipo"]:checked');
            if (!tipoSeleccionado) return;
            const valor = tipoSeleccionado.value;
            campoUrl.classList.remove('activo');
            campoArchivo.classList.remove('activo');
            if (valor === 'enlace' || valor === 'video') {
                campoUrl.classList.add('activo');
                document.getElementById('url').required = true;
                document.getElementById('archivo').required = false;
            } else if (valor === 'archivo') {
                campoArchivo.classList.add('activo');
                document.getElementById('archivo').required = <?php echo $esEdicion ? 'false' : 'true'; ?>;
                document.getElementById('url').required = false;
            }
        }

        radios.forEach(radio => radio.addEventListener('change', actualizarCampos));
        actualizarCampos(); // inicial
    </script>

<?php require_once __DIR__ . '/footer.php'; ?>
</body>
</html>
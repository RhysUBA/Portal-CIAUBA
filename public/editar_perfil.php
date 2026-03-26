<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::estaLogueado()) {
    header('Location: login.php');
    exit;
}

$userModel = new User();
$usuario_id = $_SESSION['usuario_id'];
$usuario = $userModel->obtenerPorId($usuario_id);

$error = '';
$exito = '';
$datos = [];

// Array de carreras
$carreras = [
    'Ingeniería en Sistemas',
    'Ingeniería Eléctrica', 
    'Ingeniería Mecánica',
    'Ingeniería Civil',
    'Ingeniería Industrial',
    'Ingeniería Química',
    'Ingeniería de Telecomunicaciones',
    'Otra'
];

// Array de intereses
$interesesDisponibles = [
    'robotics' => 'Robótica y automatización',
    'embedded' => 'Sistemas embebidos',
    'webdev' => 'Desarrollo web y móvil',
    '3dprinting' => 'Impresión 3D y prototipado',
    'iot' => 'IoT y dispositivos conectados',
    'renewable' => 'Energías renovables',
    'ai' => 'Inteligencia artificial y machine learning',
    'vr' => 'Realidad virtual y aumentada'
];

// Obtener intereses actuales del usuario
$intereses_actuales = !empty($usuario['intereses']) ? explode(',', $usuario['intereses']) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Error de validación del formulario.';
    } else {
        $datos = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'carrera' => $_POST['carrera'] ?? '',
            'intereses' => $_POST['intereses'] ?? [],
            'nivel_experiencia' => $_POST['nivel_experiencia'] ?? 'beginner'
        ];

        // Validaciones
        $errores = [];

        if (empty($datos['nombre'])) {
            $errores[] = "El nombre es obligatorio.";
        }

        if (empty($datos['email'])) {
            $errores[] = "El email es obligatorio.";
        } elseif (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = "El email no es válido.";
        }

        if (!empty($datos['telefono']) && !preg_match('/^(0412|0414|0424|0416|0426)\d{7}$/', $datos['telefono'])) {
            $errores[] = "El teléfono debe tener 11 dígitos con prefijo válido.";
        }

        if (empty($datos['carrera'])) {
            $errores[] = "La carrera es obligatoria.";
        }

        if (!empty($errores)) {
            $error = implode('<br>', $errores);
        } else {
            $resultado = $userModel->actualizarPerfil($usuario_id, $datos);
            
            if ($resultado === true) {
                $exito = 'Perfil actualizado correctamente.';
                $usuario = $userModel->obtenerPorId($usuario_id); // Recargar datos
                $intereses_actuales = !empty($usuario['intereses']) ? explode(',', $usuario['intereses']) : [];
            } else {
                $error = $resultado;
            }
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
$page_title = 'Perfil - CIAUBA';
require_once __DIR__ . '/header.php';
?>

    <main>
        <div class="form-container" style="max-width: 800px;">
            <h2>Editar Perfil</h2>
            
            <?php if ($error): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($exito): ?>
                <div class="alert success"><?php echo $exito; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <fieldset>
                    <legend>Información personal</legend>

                    <div class="form-group">
                        <label for="nombre">Nombre completo *</label>
                        <input type="text" id="nombre" name="nombre" 
                               value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input type="text" id="telefono" name="telefono" 
                               value="<?php echo htmlspecialchars($usuario['telefono']); ?>"
                               placeholder="04121234567">
                        <small>11 dígitos, prefijos válidos: 0412, 0414, 0424, 0416, 0426</small>
                    </div>

                    <div class="form-group">
                        <label for="carrera">Carrera *</label>
                        <select id="carrera" name="carrera" required>
                            <option value="">Selecciona tu carrera</option>
                            <?php foreach ($carreras as $carrera): ?>
                                <option value="<?php echo $carrera; ?>" 
                                    <?php echo ($usuario['carrera'] == $carrera) ? 'selected' : ''; ?>>
                                    <?php echo $carrera; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Intereses técnicos</legend>
                    <p>Selecciona las áreas de tu interés:</p>

                    <div class="form-group checkbox-group">
                        <?php foreach ($interesesDisponibles as $valor => $etiqueta): ?>
                            <label>
                                <input type="checkbox" name="intereses[]" value="<?php echo $valor; ?>" 
                                    <?php echo in_array($valor, $intereses_actuales) ? 'checked' : ''; ?>>
                                <?php echo $etiqueta; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="form-group">
                        <label for="nivel_experiencia">Nivel de experiencia</label>
                        <select id="nivel_experiencia" name="nivel_experiencia">
                            <option value="beginner" <?php echo ($usuario['nivel_experiencia'] == 'beginner') ? 'selected' : ''; ?>>
                                Básico (0-1 años)
                            </option>
                            <option value="intermediate" <?php echo ($usuario['nivel_experiencia'] == 'intermediate') ? 'selected' : ''; ?>>
                                Intermedio (1-3 años)
                            </option>
                            <option value="advanced" <?php echo ($usuario['nivel_experiencia'] == 'advanced') ? 'selected' : ''; ?>>
                                Avanzado (3+ años)
                            </option>
                        </select>
                    </div>
                </fieldset>

                <div class="button-group">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Guardar cambios
                    </button>
                    <a href="perfil.php" class="btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </main>

<?php require_once __DIR__ . '/footer.php'; ?>
</body>
</html>
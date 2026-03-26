<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (User::estaLogueado()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$exito = '';
$datos = [];

// Array de carreras para mantener consistencia
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

// Array de intereses disponible para reutilizar
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF (implementación básica)
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Error de validación del formulario. Por favor, intenta de nuevo.';
    } else {
        $datos = [
            'nombre' => trim($_POST['fullName'] ?? ''),
            'cedula' => trim($_POST['studentId'] ?? ''),
            'telefono' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'username' => trim($_POST['username'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'passwordConfirm' => $_POST['passwordConfirm'] ?? '',
            'carrera' => $_POST['major'] ?? '',
            'intereses' => $_POST['interests'] ?? [],
            'nivel_experiencia' => $_POST['experience'] ?? 'beginner'
        ];

        // --- Validaciones ---
        $errores = [];

        // 1. Campos obligatorios
        $camposObligatorios = ['nombre', 'cedula', 'telefono', 'email', 'username', 'password', 'passwordConfirm', 'carrera'];
        foreach ($camposObligatorios as $campo) {
            if (empty($datos[$campo])) {
                $errores[] = "El campo " . ucfirst($campo) . " es obligatorio.";
            }
        }

        // 2. Validar cédula (formato venezolano)
        if (!empty($datos['cedula']) && !preg_match('/^[VEJPG]-\d{5,9}$/', $datos['cedula'])) {
            $errores[] = "La cédula debe tener formato V-12345678 (entre 5 y 9 dígitos).";
        }

        // 3. Validar teléfono venezolano
        if (!empty($datos['telefono']) && !preg_match('/^(0412|0414|0424|0416|0426)\d{7}$/', $datos['telefono'])) {
            $errores[] = "El teléfono debe tener 11 dígitos (ej. 04121234567) con prefijo válido (0412, 0414, 0424, 0416, 0426).";
        }

        // 4. Validar email
        if (!empty($datos['email']) && !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = "El correo electrónico no es válido.";
        }

        // 5. Validar username (solo letras, números y guión bajo)
        if (!empty($datos['username']) && !preg_match('/^[a-zA-Z0-9_]{3,20}$/', $datos['username'])) {
            $errores[] = "El usuario debe tener entre 3 y 20 caracteres y solo puede contener letras, números y guión bajo.";
        }

        // 6. Validar contraseña
        if (strlen($datos['password']) < 8) {
            $errores[] = "La contraseña debe tener al menos 8 caracteres.";
        } elseif (!preg_match('/[A-Z]/', $datos['password'])) {
            $errores[] = "La contraseña debe contener al menos una letra mayúscula.";
        } elseif (!preg_match('/[0-9]/', $datos['password'])) {
            $errores[] = "La contraseña debe contener al menos un número.";
        } elseif (!preg_match('/[^a-zA-Z0-9]/', $datos['password'])) {
            $errores[] = "La contraseña debe contener al menos un carácter especial (ej. !@#$%).";
        }

        // 7. Confirmar contraseña
        if ($datos['password'] !== $datos['passwordConfirm']) {
            $errores[] = "Las contraseñas no coinciden.";
        }

        // 8. Validar términos
        if (!isset($_POST['terms'])) {
            $errores[] = "Debes aceptar los términos y condiciones.";
        }

        // Si hay errores, los mostramos
        if (!empty($errores)) {
            $error = implode('<br>', $errores);
        } else {
            $user = new User();
            $resultado = $user->registrar($datos);
            if ($resultado === true) {
                $exito = 'Registro exitoso. Tu cuenta está pendiente de aprobación por un administrador.';
                $datos = []; // Limpiar formulario
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
$page_title = 'Registro - CIAUBA';
require_once __DIR__ . '/header.php';
?>

    <main>
        <section class="registration-form">
            <h2>Únete al club</h2>
            
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($exito): ?>
                <div class="exito"><?php echo htmlspecialchars($exito); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <fieldset>
                    <legend>Información personal</legend>
                    
                    <div class="form-group">
                        <label for="fullName">Nombre completo *</label>
                        <input type="text" id="fullName" name="fullName" 
                               value="<?php echo htmlspecialchars($datos['nombre'] ?? ''); ?>" 
                               placeholder="Ej. Juan Pérez" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="studentId">Cédula * <small>(Formato: V-12345678)</small></label>
                        <input type="text" id="studentId" name="studentId" 
                               value="<?php echo htmlspecialchars($datos['cedula'] ?? ''); ?>" 
                               placeholder="V-12345678" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Teléfono * <small>(11 dígitos, ej. 04121234567)</small></label>
                        <input type="number" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($datos['telefono'] ?? ''); ?>" 
                               placeholder="04121234567" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Correo electrónico *</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($datos['email'] ?? ''); ?>" 
                               placeholder="correo@ejemplo.com" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="major">Carrera *</label>
                        <select id="major" name="major" required>
                            <option value="">Selecciona tu carrera</option>
                            <?php foreach ($carreras as $carrera): ?>
                                <option value="<?php echo $carrera; ?>" 
                                    <?php echo (isset($datos['carrera']) && $datos['carrera'] == $carrera) ? 'selected' : ''; ?>>
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
                                <input type="checkbox" name="interests[]" value="<?php echo $valor; ?>" 
                                    <?php echo (isset($datos['intereses']) && in_array($valor, (array)$datos['intereses'])) ? 'checked' : ''; ?>>
                                <?php echo $etiqueta; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="experience">Nivel de experiencia</label>
                        <select id="experience" name="experience">
                            <option value="beginner" <?php echo (isset($datos['nivel_experiencia']) && $datos['nivel_experiencia'] == 'beginner') ? 'selected' : ''; ?>>
                                Básico (0-1 años)
                            </option>
                            <option value="intermediate" <?php echo (isset($datos['nivel_experiencia']) && $datos['nivel_experiencia'] == 'intermediate') ? 'selected' : ''; ?>>
                                Intermedio (1-3 años)
                            </option>
                            <option value="advanced" <?php echo (isset($datos['nivel_experiencia']) && $datos['nivel_experiencia'] == 'advanced') ? 'selected' : ''; ?>>
                                Avanzado (3+ años)
                            </option>
                        </select>
                    </div>
                </fieldset>
                
                <fieldset>
                    <legend>Cuenta del Foro</legend>
                    
                    <div class="form-group">
                        <label for="username">Nombre de usuario * <small>(mínimo 3 caracteres)</small></label>
                        <input type="text" id="username" name="username" 
                               value="<?php echo htmlspecialchars($datos['username'] ?? ''); ?>" 
                               placeholder="usuario123" pattern="[a-zA-Z0-9_]{3,20}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Contraseña *</label>
                        <input type="password" id="password" name="password" 
                               placeholder="Mínimo 8 caracteres" required>
                        <small>Debe tener: mayúscula, número y carácter especial</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="passwordConfirm">Confirmar contraseña *</label>
                        <input type="password" id="passwordConfirm" name="passwordConfirm" required>
                    </div>
                </fieldset>
                
                <div class="form-group terms">
                    <label>
                        <input type="checkbox" name="terms" required>
                        Acepto el <a href="#" target="_blank">Código de Conducta</a> y los 
                        <a href="#" target="_blank">Términos de Permanencia</a> *
                    </label>
                </div>
                
                <div class="button-group">
                    <button type="submit">Registrarse</button>
                    <button type="reset" class="btn-secondary">Limpiar formulario</button>
                </div>
            </form>
            
            <p class="text-center">
                ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
            </p>
        </section>
    </main>

<?php require_once __DIR__ . '/footer.php'; ?>
</body>
</html>
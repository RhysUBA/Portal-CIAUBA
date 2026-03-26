<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/mail_helper.php';

$error = '';
$exito = '';

// Generar token CSRF
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Error de validación del formulario. Inténtalo de nuevo.';
    } else {
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $asunto = trim($_POST['asunto'] ?? '');
        $mensaje = trim($_POST['mensaje'] ?? '');

        // Validaciones básicas
        if (empty($nombre)) {
            $error = 'Por favor, ingresa tu nombre.';
        } elseif (empty($email)) {
            $error = 'Por favor, ingresa tu correo electrónico.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'El correo electrónico no es válido.';
        } elseif (empty($asunto)) {
            $error = 'Por favor, ingresa un asunto.';
        } elseif (empty($mensaje)) {
            $error = 'Por favor, escribe tu mensaje.';
        } else {
            // Construir cuerpo del email para el administrador
            $cuerpo_html = "
            <!DOCTYPE html>
            <html>
            <head><meta charset='UTF-8'><title>Mensaje de contacto</title></head>
            <body>
                <h2>Nuevo mensaje de contacto</h2>
                <p><strong>Nombre:</strong> $nombre</p>
                <p><strong>Email:</strong> $email</p>
                <p><strong>Asunto:</strong> $asunto</p>
                <p><strong>Mensaje:</strong></p>
                <p>" . nl2br(htmlspecialchars($mensaje)) . "</p>
                <hr>
                <p>Este mensaje fue enviado desde el formulario de contacto de CIAUBA.</p>
            </body>
            </html>
            ";
            $cuerpo_texto = "Nuevo mensaje de contacto\n\nNombre: $nombre\nEmail: $email\nAsunto: $asunto\n\nMensaje:\n$mensaje";

            // Enviar al correo del club
            $resultado = enviarCorreo(
                'rhysuba@gmail.com',     // destinatario (configurable)
                'CIAUBA - Contacto',
                "Contacto: $asunto",
                $cuerpo_html,
                $cuerpo_texto
            );

            if ($resultado['success']) {
                $exito = '¡Mensaje enviado correctamente! Te responderemos a la brevedad.';
                // Opcional: guardar en base de datos
            } else {
                $error = 'Hubo un problema al enviar el mensaje. Por favor, inténtalo más tarde.';
                // Registrar error
                error_log("Error enviando contacto: " . $resultado['message']);
            }
        }
    }
}

$extra_css = '
        .contact-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .contact-info {
            background: white;
            padding: var(--space-lg);
            border-radius: var(--radius-lg);
            margin-bottom: var(--space-lg);
            box-shadow: var(--shadow-sm);
        }
        .contact-info h3 {
            margin-bottom: var(--space-sm);
        }
        .contact-info i {
            color: var(--color-light-blue);
            width: 30px;
        }
        .info-row {
            display: flex;
            align-items: center;
            margin-bottom: var(--space-sm);
        }
        .map-container {
            margin-top: var(--space-md);
            border-radius: var(--radius-md);
            overflow: hidden;
        }
    ';
$page_title = 'Contacto - CIAUBA';
require_once __DIR__ . '/header.php';
?>

    <main>
        <div class="contact-container">
            <h2><i class="fas fa-envelope"></i> Contáctanos</h2>
            <p>¿Tienes preguntas, sugerencias o quieres colaborar con el club? Escríbenos y te responderemos lo antes posible.</p>

            <div class="contact-info">
                <h3>Información de contacto</h3>
                <div class="info-row"><i class="fas fa-map-marker-alt"></i> <span>Universidad Bicentenaria de Aragua, Edificio de Ingeniería, Salón de Realidad Virtual</span></div>
                <div class="info-row"><i class="fas fa-phone-alt"></i> <span>+58 424 8313052</span></div>
                <div class="info-row"><i class="fas fa-envelope"></i> <span>rhysuba@gmail.com</span></div>
                <div class="map-container">
                    <!-- Aquí puedes insertar un mapa de Google Maps si lo deseas -->
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d31568.60950455712!2d-67.137284!3d10.247568!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8c2b0c2b5c7e9a7f%3A0x2b7f5c0c1c8e9a7f!2sUniversidad%20Bicentenaria%20de%20Aragua!5e0!3m2!1ses!2sve!4v1647530000000!5m2!1ses!2sve" width="100%" height="200" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>

            <div class="form-container">
                <h3>Envíanos un mensaje</h3>
                <?php if ($error): ?>
                    <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if ($exito): ?>
                    <div class="alert success"><?php echo htmlspecialchars($exito); ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="form-group">
                        <label for="nombre">Nombre *</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="asunto">Asunto *</label>
                        <input type="text" id="asunto" name="asunto" required>
                    </div>

                    <div class="form-group">
                        <label for="mensaje">Mensaje *</label>
                        <textarea id="mensaje" name="mensaje" rows="6" required></textarea>
                    </div>

                    <button type="submit" class="btn-primary"><i class="fas fa-paper-plane"></i> Enviar mensaje</button>
                </form>
            </div>
        </div>
    </main>

<?php require_once __DIR__ . '/footer.php'; ?>
</body>
</html>
<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::estaLogueado()) {
    header('Location: login.php');
    exit;
}

$userModel = new User();
$temaModel = new Tema();
$postModel = new Post();
$proyectoModel = new MiembroProyecto(); // Para obtener proyectos del usuario
$eventoModel = new AsistenteEvento(); // Para obtener eventos del usuario

$usuario_id = $_GET['id'] ?? $_SESSION['usuario_id']; // Si no se pasa ID, ver perfil propio
$es_propio = ($usuario_id == $_SESSION['usuario_id']);

// Obtener datos del usuario
$usuario = $userModel->obtenerPorId($usuario_id);
if (!$usuario) {
    header('Location: members.php');
    exit;
}

// Obtener estadísticas
$estadisticas = $userModel->obtenerEstadisticas($usuario_id);

// Obtener temas del usuario
$temas = $temaModel->obtenerPorUsuario($usuario_id, 5);

// Obtener proyectos del usuario
$proyectos = $proyectoModel->obtenerProyectosDeUsuario($usuario_id);

// Obtener eventos próximos del usuario
$eventos = $eventoModel->obtenerEventosDeUsuario($usuario_id, true);

// Procesar intereses (convertir string a array)
$intereses = !empty($usuario['intereses']) ? explode(',', $usuario['intereses']) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?php echo htmlspecialchars($usuario['nombre']); ?> - CIAUBA</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-header {
            display: flex;
            gap: var(--space-xl);
            align-items: center;
            margin-bottom: var(--space-xl);
            padding: var(--space-xl);
            background: linear-gradient(135deg, var(--color-dark-blue), var(--color-medium-blue));
            border-radius: var(--radius-xl);
            color: white;
        }

        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: bold;
            color: var(--color-dark-blue);
            border: 4px solid white;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-info h1 {
            color: white;
            margin-bottom: var(--space-xs);
        }

        .profile-info .username {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: var(--space-sm);
        }

        .profile-meta {
            display: flex;
            gap: var(--space-md);
            flex-wrap: wrap;
        }

        .profile-meta-item {
            background: rgba(255,255,255,0.1);
            padding: var(--space-xs) var(--space-sm);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            gap: var(--space-xs);
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: var(--space-md);
            margin-bottom: var(--space-xl);
        }

        .stat-card {
            background: white;
            padding: var(--space-lg);
            border-radius: var(--radius-lg);
            text-align: center;
            box-shadow: var(--shadow-sm);
            transition: transform var(--transition-normal);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .stat-card i {
            font-size: 2rem;
            color: var(--color-light-blue);
            margin-bottom: var(--space-xs);
        }

        .stat-card .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--color-dark-blue);
        }

        .stat-card .stat-label {
            color: #666;
            font-size: 0.9rem;
        }

        .profile-details {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: var(--space-xl);
        }

        .detail-section {
            background: white;
            padding: var(--space-lg);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: var(--space-lg);
        }

        .detail-section h3 {
            display: flex;
            align-items: center;
            gap: var(--space-xs);
            margin-bottom: var(--space-md);
            padding-bottom: var(--space-xs);
            border-bottom: 2px solid rgba(12,43,78,0.1);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: var(--space-md);
        }

        .info-item {
            margin-bottom: var(--space-sm);
        }

        .info-item .label {
            font-weight: 600;
            color: var(--color-dark-blue);
            font-size: 0.9rem;
            text-transform: uppercase;
        }

        .info-item .value {
            color: #333;
            font-size: 1.1rem;
        }

        .interests-container {
            display: flex;
            flex-wrap: wrap;
            gap: var(--space-xs);
        }

        .interest-tag {
            background: rgba(29,84,108,0.1);
            color: var(--color-medium-blue);
            padding: var(--space-xs) var(--space-sm);
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .project-item, .event-item {
            padding: var(--space-sm);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .project-item:last-child, .event-item:last-child {
            border-bottom: none;
        }

        .project-status {
            font-size: 0.8rem;
            padding: var(--space-xs) var(--space-sm);
            border-radius: 20px;
            background: rgba(52,152,219,0.1);
            color: var(--color-info);
        }

        .profile-actions {
            display: flex;
            gap: var(--space-sm);
            margin-top: var(--space-lg);
        }

        .btn-edit {
            background: var(--color-warning);
        }

        .btn-password {
            background: var(--color-info);
        }

        .btn-message {
            background: var(--color-success);
        }

        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            .profile-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            .profile-details {
                grid-template-columns: 1fr;
            }
            .info-grid {
                grid-template-columns: 1fr;
            }
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
        <!-- Cabecera del perfil -->
        <div class="profile-header">
            <div class="profile-avatar">
                <?php if ($usuario['avatar']): ?>
                    <img src="uploads/avatars/<?php echo htmlspecialchars($usuario['avatar']); ?>" alt="Avatar">
                <?php else: ?>
                    <?php echo strtoupper(substr($usuario['nombre'], 0, 2)); ?>
                <?php endif; ?>
            </div>
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($usuario['nombre']); ?></h1>
                <div class="username">@<?php echo htmlspecialchars($usuario['username']); ?></div>
                <div class="profile-meta">
                    <span class="profile-meta-item">
                        <i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($usuario['carrera']); ?>
                    </span>
                    <span class="profile-meta-item">
                        <i class="fas fa-calendar-alt"></i> Miembro desde <?php echo date('M Y', strtotime($usuario['fecha_registro'])); ?>
                    </span>
                    <?php if ($usuario['ultimo_acceso']): ?>
                        <span class="profile-meta-item">
                            <i class="fas fa-clock"></i> Último acceso: <?php echo date('d/m/Y', strtotime($usuario['ultimo_acceso'])); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="profile-stats">
            <div class="stat-card">
                <i class="fas fa-comments"></i>
                <div class="stat-number"><?php echo $estadisticas['temas']; ?></div>
                <div class="stat-label">Temas creados</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-reply"></i>
                <div class="stat-number"><?php echo $estadisticas['respuestas']; ?></div>
                <div class="stat-label">Respuestas</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-project-diagram"></i>
                <div class="stat-number"><?php echo $estadisticas['proyectos']; ?></div>
                <div class="stat-label">Proyectos</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-calendar-check"></i>
                <div class="stat-number"><?php echo $estadisticas['eventos']; ?></div>
                <div class="stat-label">Eventos</div>
            </div>
        </div>

        <!-- Detalles del perfil -->
        <div class="profile-details">
            <!-- Columna izquierda - Información personal -->
            <div>
                <div class="detail-section">
                    <h3><i class="fas fa-user"></i> Información Personal</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="label">Nombre completo</div>
                            <div class="value"><?php echo htmlspecialchars($usuario['nombre']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="label">Cédula</div>
                            <div class="value"><?php echo htmlspecialchars($usuario['cedula']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="label">Email</div>
                            <div class="value"><?php echo htmlspecialchars($usuario['email']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="label">Teléfono</div>
                            <div class="value"><?php echo htmlspecialchars($usuario['telefono']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="label">Carrera</div>
                            <div class="value"><?php echo htmlspecialchars($usuario['carrera']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="label">Nivel de experiencia</div>
                            <div class="value">
                                <?php
                                switch($usuario['nivel_experiencia']) {
                                    case 'beginner': echo 'Básico'; break;
                                    case 'intermediate': echo 'Intermedio'; break;
                                    case 'advanced': echo 'Avanzado'; break;
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h3><i class="fas fa-microchip"></i> Intereses técnicos</h3>
                    <?php if (empty($intereses)): ?>
                        <p class="text-muted">Este usuario no ha especificado intereses.</p>
                    <?php else: ?>
                        <div class="interests-container">
                            <?php foreach ($intereses as $interes): ?>
                                <span class="interest-tag">
                                    <?php
                                    // Convertir código a nombre legible
                                    $nombres = [
                                        'robotics' => 'Robótica',
                                        'embedded' => 'Sistemas embebidos',
                                        'webdev' => 'Desarrollo web',
                                        '3dprinting' => 'Impresión 3D',
                                        'iot' => 'IoT',
                                        'renewable' => 'Energías renovables',
                                        'ai' => 'IA',
                                        'vr' => 'Realidad virtual'
                                    ];
                                    echo $nombres[trim($interes)] ?? trim($interes);
                                    ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Temas recientes -->
                <div class="detail-section">
                    <h3><i class="fas fa-comments"></i> Temas recientes en el foro</h3>
                    <?php if (empty($temas)): ?>
                        <p class="text-muted">Este usuario no ha creado temas aún.</p>
                    <?php else: ?>
                        <?php foreach ($temas as $tema): ?>
                            <div class="project-item">
                                <div>
                                    <a href="tema.php?id=<?php echo $tema['id']; ?>">
                                        <strong><?php echo htmlspecialchars($tema['titulo']); ?></strong>
                                    </a>
                                    <div style="font-size: 0.85rem; color: #666;">
                                        <?php echo $tema['num_respuestas']; ?> respuestas • <?php echo $tema['visitas']; ?> visitas
                                    </div>
                                </div>
                                <span class="project-status"><?php echo date('d/m/Y', strtotime($tema['creado_en'])); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Columna derecha - Proyectos y eventos -->
            <div>
                <!-- Proyectos -->
                <div class="detail-section">
                    <h3><i class="fas fa-project-diagram"></i> Proyectos actuales</h3>
                    <?php if (empty($proyectos)): ?>
                        <p class="text-muted">No participa en ningún proyecto actualmente.</p>
                    <?php else: ?>
                        <?php foreach ($proyectos as $proyecto): ?>
                            <div class="project-item">
                                <div>
                                    <strong><?php echo htmlspecialchars($proyecto['titulo']); ?></strong>
                                    <div style="font-size: 0.85rem; color: #666;">
                                        Rol: <?php echo htmlspecialchars($proyecto['rol_en_proyecto']); ?>
                                    </div>
                                </div>
                                <span class="project-status"><?php echo $proyecto['estado']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Eventos próximos -->
                <div class="detail-section">
                    <h3><i class="fas fa-calendar-alt"></i> Próximos eventos</h3>
                    <?php if (empty($eventos)): ?>
                        <p class="text-muted">No tiene eventos próximos.</p>
                    <?php else: ?>
                        <?php foreach ($eventos as $evento): ?>
                            <div class="event-item">
                                <div>
                                    <strong><?php echo htmlspecialchars($evento['titulo']); ?></strong>
                                    <div style="font-size: 0.85rem; color: #666;">
                                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($evento['lugar']); ?>
                                    </div>
                                </div>
                                <span class="project-status"><?php echo date('d/m/Y', strtotime($evento['fecha_inicio'])); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Acciones (solo si es el propio perfil) -->
                <?php if ($es_propio): ?>
                    <div class="detail-section">
                        <h3><i class="fas fa-cog"></i> Acciones</h3>
                        <div class="profile-actions">
                            <a href="editar_perfil.php" class="cta-button btn-edit">
                                <i class="fas fa-edit"></i> Editar perfil
                            </a>
                            <a href="cambiar_password.php" class="cta-button btn-password">
                                <i class="fas fa-key"></i> Cambiar contraseña
                            </a>
                            <a href="subir_avatar.php" class="cta-button">
                                <i class="fas fa-camera"></i> <?php echo $usuario['avatar'] ? 'Cambiar foto' : 'Subir foto'; ?>
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="detail-section">
                        <h3><i class="fas fa-envelope"></i> Contactar</h3>
                        <div class="profile-actions">
                            <a href="enviar_mensaje.php?to=<?php echo $usuario_id; ?>" class="cta-button btn-message">
                                <i class="fas fa-envelope"></i> Enviar mensaje
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        <p>Club de Ingeniería Aplicada UBA &copy; 2025</p>
        <p>Contacto: rhysuba@gmail.com</p>
    </footer>
</body>
</html>
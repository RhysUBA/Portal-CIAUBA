<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::esAdmin()) {
    header('Location: index.php');
    exit;
}

// Inicializar modelos
$userModel = new User();
$proyectoModel = new Proyecto(); // Asumo que existe, si no lo crearemos
$temaModel = new Tema();
$categoriaModel = new Categoria();
$eventoModel = new Evento(); // Asumo que existe, si no lo crearemos
$configModel = new Configuracion(); // Asumo que existe, si no lo crearemos

// Obtener datos para el dashboard
$pendientes = $userModel->obtenerMiembros(false); // pendientes
$activos = $userModel->obtenerMiembros(true);
$totalMiembros = count($activos) + count($pendientes);
$totalProyectos = count($proyectoModel->obtenerTodos());
$totalTemasForo = count($temaModel->obtenerTodos());
$proximosEventos = $eventoModel->obtenerProximos(5);

// Procesar acciones
$accion = $_GET['accion'] ?? '';
$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Error de validación. Intenta de nuevo.';
    } else {
        // Procesar según el formulario
        switch ($_POST['form_action'] ?? '') {
            case 'aprobar_miembro':
                if (isset($_POST['usuario_id'])) {
                    $resultado = $userModel->cambiarEstado($_POST['usuario_id'], 'activo');
                    $mensaje = $resultado ? 'Miembro aprobado correctamente.' : 'Error al aprobar miembro.';
                }
                break;
                
            case 'rechazar_miembro':
                if (isset($_POST['usuario_id'])) {
                    $resultado = $userModel->cambiarEstado($_POST['usuario_id'], 'inactivo');
                    $mensaje = $resultado ? 'Miembro rechazado correctamente.' : 'Error al rechazar miembro.';
                }
                break;
                
            case 'cambiar_rol':
                if (isset($_POST['usuario_id'], $_POST['nuevo_rol'])) {
                    $resultado = $userModel->cambiarRol($_POST['usuario_id'], $_POST['nuevo_rol']);
                    $mensaje = $resultado ? 'Rol actualizado correctamente.' : 'Error al actualizar rol.';
                }
                break;
                
            case 'crear_categoria':
                if (isset($_POST['nombre'], $_POST['descripcion'])) {
                    $resultado = $categoriaModel->crear($_POST['nombre'], $_POST['descripcion'], $_POST['posicion'] ?? 0);
                    $mensaje = $resultado ? 'Categoría creada correctamente.' : 'Error al crear categoría.';
                }
                break;
                
            case 'editar_categoria':
                if (isset($_POST['id'], $_POST['nombre'])) {
                    $resultado = $categoriaModel->actualizar($_POST['id'], $_POST['nombre'], $_POST['descripcion'], $_POST['posicion'] ?? 0, isset($_POST['activa']));
                    $mensaje = $resultado ? 'Categoría actualizada correctamente.' : 'Error al actualizar categoría.';
                }
                break;
                
            case 'guardar_configuracion':
                if (isset($_POST['config'])) {
                    foreach ($_POST['config'] as $clave => $valor) {
                        $configModel->guardar($clave, $valor);
                    }
                    $mensaje = 'Configuración guardada correctamente.';
                }
                break;
        }
    }
}

// Obtener datos actualizados después de acciones
$pendientes = $userModel->obtenerMiembros(false);
$activos = $userModel->obtenerMiembros(true);
$categorias = $categoriaModel->obtenerTodas();
$configuracion = $configModel->obtenerTodas();
$proyectos = $proyectoModel->obtenerTodos();
$temasRecientes = $temaModel->obtenerRecientes(10);

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
    <title>CIAUBA - Panel de Administración</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                <li><a href="admin.php" class="active">Admin</a></li>
                <li><a href="logout.php">Cerrar sesión <i class="fas fa-sign-out-alt"></i> (<?php echo $_SESSION['usuario_nombre']; ?>)</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <!-- Mensajes de éxito/error -->
        <?php if ($mensaje): ?>
            <div class="alert success"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <section class="admin-header">
            <h2>Panel de Administración</h2>
            <p>Bienvenido, <strong><?php echo $_SESSION['usuario_nombre']; ?></strong></p>
            
            <!-- Tarjetas de resumen -->
            <div class="stats-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--space-md); margin: var(--space-xl) 0;">
                <div class="stat-card">
                    <i class="fas fa-users" style="font-size: 2rem; color: var(--color-light-blue);"></i>
                    <h3><?php echo $totalMiembros; ?></h3>
                    <p>Total Miembros</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock" style="font-size: 2rem; color: var(--color-warning);"></i>
                    <h3><?php echo count($pendientes); ?></h3>
                    <p>Pendientes</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-project-diagram" style="font-size: 2rem; color: var(--color-success);"></i>
                    <h3><?php echo $totalProyectos; ?></h3>
                    <p>Proyectos</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-comments" style="font-size: 2rem; color: var(--color-info);"></i>
                    <h3><?php echo $totalTemasForo; ?></h3>
                    <p>Temas Foro</p>
                </div>
            </div>

            <?php if (count($pendientes) > 0): ?>
                <div class="admin-alert">
                    <p>⚠️ Tienes <strong><?php echo count($pendientes); ?></strong> miembro(s) pendiente(s) de aprobación.</p>
                </div>
            <?php endif; ?>
        </section>

        <div class="admin-layout">
            <aside class="admin-sidebar">
                <h3>Navegación</h3>
                <nav class="admin-menu">
                    <ul>
                        <li><a href="#dashboard" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a href="#members"><i class="fas fa-users"></i> Gestión de Miembros</a></li>
                        <li><a href="#projects"><i class="fas fa-project-diagram"></i> Proyectos</a></li>
                        <li><a href="#forum"><i class="fas fa-comments"></i> Foro</a></li>
                        <li><a href="#events"><i class="fas fa-calendar-alt"></i> Eventos</a></li>
                        <li><a href="#settings"><i class="fas fa-cog"></i> Configuración</a></li>
                    </ul>
                </nav>
            </aside>

            <div class="admin-content">
                <!-- Dashboard -->
                <section id="dashboard" class="admin-section active">
                    <h3>Dashboard</h3>
                    
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: var(--space-lg);">
                        <!-- Actividad reciente -->
                        <div class="card">
                            <h4>Actividad Reciente en el Foro</h4>
                            <?php if (empty($temasRecientes)): ?>
                                <p>No hay actividad reciente.</p>
                            <?php else: ?>
                                <ul class="activity-list">
                                    <?php foreach ($temasRecientes as $tema): ?>
                                        <li>
                                            <i class="fas fa-comment"></i>
                                            <strong><?php echo htmlspecialchars($tema['nombre']); ?></strong> creó 
                                            <a href="tema.php?id=<?php echo $tema['id']; ?>"><?php echo htmlspecialchars($tema['titulo']); ?></a>
                                            <small><?php echo date('d/m/Y H:i', strtotime($tema['creado_en'])); ?></small>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>

                        <!-- Próximos eventos -->
                        <div class="card">
                            <h4>Próximos Eventos</h4>
                            <?php if (empty($proximosEventos)): ?>
                                <p>No hay eventos programados.</p>
                                <a href="#events" class="btn-small" onclick="showSection('events')">Crear evento</a>
                            <?php else: ?>
                                <ul class="event-list">
                                    <?php foreach ($proximosEventos as $evento): ?>
                                        <li>
                                            <i class="fas fa-calendar-check"></i>
                                            <strong><?php echo htmlspecialchars($evento['titulo']); ?></strong>
                                            <small><?php echo date('d/m/Y H:i', strtotime($evento['fecha_inicio'])); ?></small>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>

                <!-- Gestión de Miembros -->
                <section id="members" class="admin-section">
                    <h3>Gestión de Miembros</h3>
                    
                    <!-- Pestañas -->
                    <div class="tabs">
                        <button class="tab-btn active" onclick="showTab('pendientes')">Pendientes (<?php echo count($pendientes); ?>)</button>
                        <button class="tab-btn" onclick="showTab('activos')">Activos (<?php echo count($activos); ?>)</button>
                        <button class="tab-btn" onclick="showTab('buscar')">Buscar</button>
                    </div>

                    <!-- Tab: Pendientes -->
                    <div id="tab-pendientes" class="tab-content active">
                        <?php if (empty($pendientes)): ?>
                            <p>No hay miembros pendientes de aprobación.</p>
                        <?php else: ?>
                            <div class="table-container">
                                <table class="members-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Email</th>
                                            <th>Cédula</th>
                                            <th>Teléfono</th>
                                            <th>Carrera</th>
                                            <th>Fecha Registro</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pendientes as $p): ?>
                                        <tr>
                                            <td><?php echo $p['id']; ?></td>
                                            <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                                            <td><?php echo htmlspecialchars($p['email']); ?></td>
                                            <td><?php echo htmlspecialchars($p['cedula'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($p['telefono'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($p['carrera']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($p['fecha_registro'] ?? $p['created_at'])); ?></td>
                                            <td>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                    <input type="hidden" name="form_action" value="aprobar_miembro">
                                                    <input type="hidden" name="usuario_id" value="<?php echo $p['id']; ?>">
                                                    <button type="submit" class="action-btn approve" title="Aprobar">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                    <input type="hidden" name="form_action" value="rechazar_miembro">
                                                    <input type="hidden" name="usuario_id" value="<?php echo $p['id']; ?>">
                                                    <button type="submit" class="action-btn reject" title="Rechazar" onclick="return confirm('¿Estás seguro de rechazar este miembro?')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tab: Activos -->
                    <div id="tab-activos" class="tab-content">
                        <div class="table-container">
                            <table class="members-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Carrera</th>
                                        <th>Rol</th>
                                        <th>Nivel</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($activos as $a): ?>
                                    <tr>
                                        <td><?php echo $a['id']; ?></td>
                                        <td>
                                            <a href="perfil.php?id=<?php echo $a['id']; ?>" style="color: var(--color-dark-blue);">
                                                <?php echo htmlspecialchars($a['nombre']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($a['email']); ?></td>
                                        <td><?php echo htmlspecialchars($a['carrera']); ?></td>
                                        <td>
                                            <span class="role-badge <?php echo $a['rol']; ?>">
                                                <?php echo $a['rol']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $a['nivel_experiencia'] ?? 'N/A'; ?></td>
                                        <td>
                                            <button class="action-btn edit" onclick="editarMiembro(<?php echo $a['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('¿Cambiar rol de este miembro?')">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <input type="hidden" name="form_action" value="cambiar_rol">
                                                <input type="hidden" name="usuario_id" value="<?php echo $a['id']; ?>">
                                                <select name="nuevo_rol" onchange="this.form.submit()">
                                                    <option value="user" <?php echo $a['rol'] == 'user' ? 'selected' : ''; ?>>Usuario</option>
                                                    <option value="admin" <?php echo $a['rol'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                </select>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab: Buscar -->
                    <div id="tab-buscar" class="tab-content">
                        <div class="search-box">
                            <input type="text" id="search-input" placeholder="Buscar por nombre, email o cédula...">
                            <button onclick="buscarMiembros()" class="action-btn">Buscar</button>
                        </div>
                        <div id="search-results"></div>
                    </div>
                </section>

                <!-- Gestión de Proyectos -->
                <section id="projects" class="admin-section">
                    <h3>Gestión de Proyectos</h3>
                    
                    <div class="section-actions">
                        <button class="cta-button" onclick="mostrarFormProyecto()">
                            <i class="fas fa-plus"></i> Nuevo Proyecto
                        </button>
                    </div>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Estado</th>
                                    <th>Líder</th>
                                    <th>Fecha Inicio</th>
                                    <th>Miembros</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($proyectos as $proyecto): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($proyecto['titulo']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $proyecto['estado']; ?>">
                                            <?php echo $proyecto['estado']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($proyecto['lider_nombre'] ?? 'Sin líder'); ?></td>
                                    <td><?php echo $proyecto['fecha_inicio'] ? date('d/m/Y', strtotime($proyecto['fecha_inicio'])) : 'N/A'; ?></td>
                                    <td><?php echo $proyecto['num_miembros'] ?? 0; ?></td>
                                    <td>
                                        <a href="proyecto.php?id=<?php echo $proyecto['id']; ?>" class="action-btn view">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="action-btn edit" onclick="editarProyecto(<?php echo $proyecto['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Gestión del Foro -->
                <section id="forum" class="admin-section">
                    <h3>Moderación del Foro</h3>
                    
                    <!-- Subpestañas del foro -->
                    <div class="tabs">
                        <button class="tab-btn active" onclick="showForumTab('categorias')">Categorías</button>
                        <button class="tab-btn" onclick="showForumTab('temas')">Temas Recientes</button>
                        <button class="tab-btn" onclick="showForumTab('reportes')">Reportes</button>
                    </div>

                    <!-- Categorías -->
                    <div id="forum-categorias" class="tab-content active">
                        <div class="section-actions">
                            <button class="cta-button" onclick="mostrarFormCategoria()">
                                <i class="fas fa-plus"></i> Nueva Categoría
                            </button>
                        </div>

                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Posición</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categorias as $cat): ?>
                                    <tr>
                                        <td><?php echo $cat['id']; ?></td>
                                        <td><?php echo htmlspecialchars($cat['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($cat['descripcion']); ?></td>
                                        <td><?php echo $cat['posicion']; ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $cat['activa'] ? 'status-active' : 'status-inactive'; ?>">
                                                <?php echo $cat['activa'] ? 'Activa' : 'Inactiva'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="action-btn edit" onclick="editarCategoria(<?php echo $cat['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Formulario nueva categoría (oculto) -->
                        <div id="form-categoria" style="display: none; margin-top: var(--space-xl);">
                            <h4>Nueva Categoría</h4>
                            <form method="POST" class="form-container" style="max-width: 600px;">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="form_action" value="crear_categoria">
                                
                                <div class="form-group">
                                    <label>Nombre *</label>
                                    <input type="text" name="nombre" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Descripción</label>
                                    <textarea name="descripcion" rows="3"></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label>Posición</label>
                                    <input type="number" name="posicion" value="0" min="0">
                                </div>
                                
                                <div class="button-group">
                                    <button type="submit" class="action-btn approve">Crear Categoría</button>
                                    <button type="button" class="action-btn reject" onclick="ocultarFormCategoria()">Cancelar</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Temas Recientes -->
                    <div id="forum-temas" class="tab-content">
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Autor</th>
                                        <th>Categoría</th>
                                        <th>Respuestas</th>
                                        <th>Visitas</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($temasRecientes as $tema): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($tema['titulo']); ?></td>
                                        <td><?php echo htmlspecialchars($tema['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($tema['categoria_nombre']); ?></td>
                                        <td><?php echo $tema['num_respuestas']; ?></td>
                                        <td><?php echo $tema['visitas']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($tema['creado_en'])); ?></td>
                                        <td>
                                            <a href="tema.php?id=<?php echo $tema['id']; ?>" class="action-btn view">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button class="action-btn edit" onclick="editarTema(<?php echo $tema['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Reportes -->
                    <div id="forum-reportes" class="tab-content">
                        <p>Próximamente: Sistema de reportes de contenido inapropiado.</p>
                    </div>
                </section>

                <!-- Gestión de Eventos -->
                <section id="events" class="admin-section">
                    <h3>Gestión de Eventos</h3>
                    
                    <div class="section-actions">
                        <button class="cta-button" onclick="mostrarFormEvento()">
                            <i class="fas fa-plus"></i> Nuevo Evento
                        </button>
                    </div>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Tipo</th>
                                    <th>Fecha Inicio</th>
                                    <th>Lugar</th>
                                    <th>Asistentes</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($proximosEventos as $evento): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($evento['titulo']); ?></td>
                                    <td><?php echo $evento['tipo']; ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($evento['fecha_inicio'])); ?></td>
                                    <td><?php echo htmlspecialchars($evento['lugar']); ?></td>
                                    <td><?php echo $evento['asistentes_count'] ?? 0; ?></td>
                                    <td>
                                        <button class="action-btn edit" onclick="editarEvento(<?php echo $evento['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="action-btn view" onclick="verAsistentes(<?php echo $evento['id']; ?>)">
                                            <i class="fas fa-users"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Configuración -->
                <section id="settings" class="admin-section">
                    <h3>Configuración del Club</h3>
                    
                    <form method="POST" class="form-container">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="form_action" value="guardar_configuracion">
                        
                        <div class="form-group">
                            <label>Nombre del Club</label>
                            <input type="text" name="config[club_nombre]" value="<?php echo htmlspecialchars($configuracion['club_nombre'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Email de Contacto</label>
                            <input type="email" name="config[club_email]" value="<?php echo htmlspecialchars($configuracion['club_email'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="text" name="config[club_telefono]" value="<?php echo htmlspecialchars($configuracion['club_telefono'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Máximo de Miembros</label>
                            <input type="number" name="config[max_miembros]" value="<?php echo htmlspecialchars($configuracion['max_miembros'] ?? '100'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="config[require_aprobacion]" value="1" 
                                    <?php echo ($configuracion['require_aprobacion'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                Requerir aprobación de administrador para nuevos miembros
                            </label>
                        </div>
                        
                        <button type="submit" class="action-btn approve">Guardar Configuración</button>
                    </form>
                </section>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script>
        // Navegación entre secciones
        document.querySelectorAll('.admin-menu a').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                document.querySelectorAll('.admin-menu a').forEach(a => a.classList.remove('active'));
                link.classList.add('active');
                const target = link.getAttribute('href').substring(1);
                document.querySelectorAll('.admin-section').forEach(section => {
                    section.classList.remove('active');
                });
                document.getElementById(target).classList.add('active');
            });
        });

        // Función para mostrar sección específica
        function showSection(sectionId) {
            document.querySelectorAll('.admin-menu a').forEach(a => a.classList.remove('active'));
            document.querySelector(`.admin-menu a[href="#${sectionId}"]`).classList.add('active');
            document.querySelectorAll('.admin-section').forEach(section => {
                section.classList.remove('active');
            });
            document.getElementById(sectionId).classList.add('active');
        }

        // Pestañas de miembros
        function showTab(tabName) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            event.target.classList.add('active');
            document.getElementById(`tab-${tabName}`).classList.add('active');
        }

        // Pestañas del foro
        function showForumTab(tabName) {
            document.querySelectorAll('#forum .tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('#forum .tab-content').forEach(content => content.classList.remove('active'));
            
            event.target.classList.add('active');
            document.getElementById(`forum-${tabName}`).classList.add('active');
        }

        // Formularios
        function mostrarFormCategoria() {
            document.getElementById('form-categoria').style.display = 'block';
        }

        function ocultarFormCategoria() {
            document.getElementById('form-categoria').style.display = 'none';
        }

        function mostrarFormProyecto() {
            alert('Formulario de nuevo proyecto - Próximamente');
        }

        function mostrarFormEvento() {
            alert('Formulario de nuevo evento - Próximamente');
        }

        // Búsqueda de miembros
        function buscarMiembros() {
            const query = document.getElementById('search-input').value;
            if (query.length < 3) {
                alert('Ingresa al menos 3 caracteres para buscar');
                return;
            }

            // Aquí iría la petición AJAX para buscar
            fetch(`buscar_miembros.php?q=${encodeURIComponent(query)}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('search-results').innerHTML = html;
                });
        }

        // Editar funciones
        function editarMiembro(id) {
            alert(`Editar miembro ${id} - Próximamente`);
        }

        function editarProyecto(id) {
            alert(`Editar proyecto ${id} - Próximamente`);
        }

        function editarCategoria(id) {
            alert(`Editar categoría ${id} - Próximamente`);
        }

        function editarTema(id) {
            alert(`Editar tema ${id} - Próximamente`);
        }

        function editarEvento(id) {
            alert(`Editar evento ${id} - Próximamente`);
        }

        function verAsistentes(id) {
            alert(`Ver asistentes del evento ${id} - Próximamente`);
        }
    </script>

    <style>
        /* Estilos adicionales para admin */
        .admin-section {
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .stats-grid .stat-card {
            background: white;
            padding: var(--space-lg);
            border-radius: var(--radius-lg);
            text-align: center;
            box-shadow: var(--shadow-sm);
        }

        .stats-grid .stat-card h3 {
            font-size: 2rem;
            margin: var(--space-xs) 0;
        }

        .tabs {
            display: flex;
            gap: var(--space-xs);
            margin-bottom: var(--space-lg);
            border-bottom: 2px solid rgba(12,43,78,0.1);
            padding-bottom: var(--space-xs);
        }

        .tab-btn {
            background: none;
            border: none;
            padding: var(--space-sm) var(--space-lg);
            cursor: pointer;
            color: var(--color-medium-blue);
            font-weight: 600;
            position: relative;
        }

        .tab-btn.active {
            color: var(--color-light-blue);
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--color-light-blue);
            border-radius: 3px 3px 0 0;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .role-badge {
            padding: var(--space-xs) var(--space-sm);
            border-radius: var(--radius-sm);
            font-size: 0.85rem;
            font-weight: 600;
        }

        .role-badge.admin {
            background: rgba(231, 76, 60, 0.1);
            color: var(--color-accent);
        }

        .role-badge.user {
            background: rgba(52, 152, 219, 0.1);
            color: var(--color-info);
        }

        .alert {
            padding: var(--space-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-lg);
        }

        .alert.success {
            background: rgba(39, 174, 96, 0.1);
            color: var(--color-success);
            border: 1px solid rgba(39, 174, 96, 0.3);
        }

        .alert.error {
            background: rgba(231, 76, 60, 0.1);
            color: var(--color-accent);
            border: 1px solid rgba(231, 76, 60, 0.3);
        }

        .section-actions {
            margin-bottom: var(--space-lg);
        }

        .activity-list, .event-list {
            list-style: none;
        }

        .activity-list li, .event-list li {
            padding: var(--space-sm) 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }

        .activity-list li i, .event-list li i {
            color: var(--color-light-blue);
            width: 20px;
        }

        .activity-list li small, .event-list li small {
            color: #999;
            margin-left: auto;
        }
    </style>
</body>
</html>
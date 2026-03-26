<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::estaLogueado()) {
    header('Location: login.php');
    exit;
}

$recursoModel = new Recurso();

$tipo = $_GET['tipo'] ?? '';
$busqueda = trim($_GET['q'] ?? '');

// Obtener recursos según filtros
if (!empty($tipo) && !empty($busqueda)) {
    $recursos = $recursoModel->obtenerTodos();
    $recursos = array_filter($recursos, function($r) use ($tipo, $busqueda) {
        return $r['tipo'] == $tipo && (stripos($r['titulo'], $busqueda) !== false || stripos($r['descripcion'], $busqueda) !== false);
    });
} elseif (!empty($tipo)) {
    $recursos = $recursoModel->obtenerPorTipo($tipo, 100);
} elseif (!empty($busqueda)) {
    $recursos = $recursoModel->obtenerTodos();
    $recursos = array_filter($recursos, function($r) use ($busqueda) {
        return stripos($r['titulo'], $busqueda) !== false || stripos($r['descripcion'], $busqueda) !== false;
    });
} else {
    $recursos = $recursoModel->obtenerTodos(null, null, null, 100);
}

$conteo = $recursoModel->contarPorTipo();
$conteoPorTipo = [];
foreach ($conteo as $c) {
    $conteoPorTipo[$c['tipo']] = $c['total'];
}
$totalRecursos = array_sum($conteoPorTipo);

$esAdmin = User::esAdmin();
$usuarioId = $_SESSION['usuario_id'];

$extra_css = '
        .recursos-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space-xl);
            flex-wrap: wrap;
            gap: var(--space-md);
        }
        .filtros {
            display: flex;
            gap: var(--space-sm);
            flex-wrap: wrap;
            margin-bottom: var(--space-lg);
            background: white;
            padding: var(--space-md);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }
        .filtro-btn {
            padding: var(--space-xs) var(--space-md);
            border-radius: var(--radius-md);
            background: #f0f0f0;
            color: #333;
            text-decoration: none;
            transition: all var(--transition-fast);
            font-size: 0.9rem;
        }
        .filtro-btn:hover, .filtro-btn.active {
            background: var(--color-light-blue);
            color: white;
        }
        .search-box {
            display: flex;
            gap: var(--space-sm);
            margin-bottom: var(--space-lg);
        }
        .search-box input {
            flex: 1;
        }
        .recursos-grid {
            display: grid;
            gap: var(--space-md);
        }
        .clickable-recurso {
            display: block;
            text-decoration: none;
            color: inherit;
            transition: transform var(--transition-fast), box-shadow var(--transition-fast);
        }
        .clickable-recurso:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        .recurso-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: var(--space-lg);
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid transparent;
            transition: none;
        }
        .clickable-recurso:hover .recurso-card {
            transform: none;
            box-shadow: none;
        }
        .recurso-card.tipo-enlace { border-left-color: #3498db; }
        .recurso-card.tipo-archivo { border-left-color: #27ae60; }
        .recurso-card.tipo-video { border-left-color: #9b59b6; }
        .recurso-card.tipo-otro { border-left-color: #95a5a6; }
        
        .recurso-info {
            flex: 1;
        }
        .recurso-info h3 {
            margin-bottom: var(--space-xs);
            font-size: 1.2rem;
        }
        .recurso-info h3 a {
            color: var(--color-dark-blue);
            text-decoration: none;
        }
        .recurso-descripcion {
            color: #666;
            margin-bottom: var(--space-xs);
            font-size: 0.95rem;
        }
        .recurso-meta {
            display: flex;
            gap: var(--space-md);
            font-size: 0.85rem;
            color: #888;
        }
        .recurso-meta i {
            margin-right: 3px;
        }
        .recurso-tipo {
            display: inline-block;
            padding: var(--space-xs) var(--space-sm);
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            background: #f0f0f0;
        }
        .recurso-acciones {
            display: flex;
            gap: var(--space-sm);
            margin-left: var(--space-md);
            position: relative;
            z-index: 2;
        }
        .btn-accion {
            padding: var(--space-xs) var(--space-sm);
            border-radius: var(--radius-md);
            font-size: 0.9rem;
            text-decoration: none;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-descargar { background: var(--color-success); }
        .btn-ver { background: var(--color-info); }
        .btn-editar { background: var(--color-warning); }
        .btn-eliminar { background: var(--color-accent); }
        .empty-state {
            text-align: center;
            padding: var(--space-xxl);
            background: white;
            border-radius: var(--radius-lg);
            color: #999;
        }
        /* Para evitar que el clic en los botones active el enlace padre */
        .btn-accion {
            pointer-events: auto;
            position: relative;
            z-index: 3;
        }
        .clickable-recurso {
            position: relative;
        }
    ';
$page_title = 'Recursos - CIAUBA';
require_once __DIR__ . '/header.php';
?>

    <main>
        <div class="recursos-header">
            <h2><i class="fas fa-book-open"></i> Biblioteca de Recursos</h2>
            <a href="subir_recurso.php" class="cta-button">
                <i class="fas fa-upload"></i> Subir recurso
            </a>
        </div>

        <form method="GET" class="search-box">
            <input type="text" name="q" placeholder="Buscar por título o descripción..." value="<?php echo htmlspecialchars($busqueda); ?>">
            <?php if ($tipo): ?>
                <input type="hidden" name="tipo" value="<?php echo htmlspecialchars($tipo); ?>">
            <?php endif; ?>
            <button type="submit" class="action-btn"><i class="fas fa-search"></i> Buscar</button>
            <?php if ($busqueda || $tipo): ?>
                <a href="recursos.php" class="action-btn reject"><i class="fas fa-times"></i> Limpiar</a>
            <?php endif; ?>
        </form>

        <div class="filtros">
            <a href="recursos.php" class="filtro-btn <?php echo !$tipo ? 'active' : ''; ?>">Todos (<?php echo $totalRecursos; ?>)</a>
            <a href="recursos.php?tipo=enlace" class="filtro-btn <?php echo $tipo == 'enlace' ? 'active' : ''; ?>"><i class="fas fa-link"></i> Enlaces (<?php echo $conteoPorTipo['enlace'] ?? 0; ?>)</a>
            <a href="recursos.php?tipo=archivo" class="filtro-btn <?php echo $tipo == 'archivo' ? 'active' : ''; ?>"><i class="fas fa-file"></i> Archivos (<?php echo $conteoPorTipo['archivo'] ?? 0; ?>)</a>
            <a href="recursos.php?tipo=video" class="filtro-btn <?php echo $tipo == 'video' ? 'active' : ''; ?>"><i class="fas fa-video"></i> Videos (<?php echo $conteoPorTipo['video'] ?? 0; ?>)</a>
            <a href="recursos.php?tipo=otro" class="filtro-btn <?php echo $tipo == 'otro' ? 'active' : ''; ?>"><i class="fas fa-question"></i> Otros (<?php echo $conteoPorTipo['otro'] ?? 0; ?>)</a>
        </div>

        <div class="recursos-grid">
            <?php if (empty($recursos)): ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open" style="font-size: 4rem; color: #ddd; margin-bottom: var(--space-md);"></i>
                    <h3>No hay recursos para mostrar</h3>
                    <p>¡Sé el primero en <a href="subir_recurso.php">subir un recurso</a>!</p>
                </div>
            <?php else: ?>
                <?php foreach ($recursos as $recurso): ?>
                    <?php
                    $tipoClase = 'tipo-' . $recurso['tipo'];
                    $icono = '';
                    $accion = '';
                    $url_destino = '';
                    switch ($recurso['tipo']) {
                        case 'enlace':
                            $icono = 'fa-link';
                            $url_destino = $recurso['url'];
                            $accion = '<a href="' . htmlspecialchars($recurso['url']) . '" target="_blank" class="btn-accion btn-ver"><i class="fas fa-external-link-alt"></i> Visitar</a>';
                            break;
                        case 'archivo':
                            $icono = 'fa-file';
                            $url_destino = 'uploads/recursos/' . $recurso['archivo_ruta'];
                            $accion = '<a href="uploads/recursos/' . htmlspecialchars($recurso['archivo_ruta']) . '" download class="btn-accion btn-descargar"><i class="fas fa-download"></i> Descargar</a>';
                            break;
                        case 'video':
                            $icono = 'fa-video';
                            $url_destino = $recurso['url'];
                            $accion = '<a href="' . htmlspecialchars($recurso['url']) . '" target="_blank" class="btn-accion btn-ver"><i class="fas fa-play"></i> Ver video</a>';
                            break;
                        default:
                            $icono = 'fa-question';
                            $url_destino = $recurso['url'] ?? '#';
                            $accion = $recurso['url'] ? '<a href="' . htmlspecialchars($recurso['url']) . '" target="_blank" class="btn-accion btn-ver"><i class="fas fa-external-link-alt"></i> Ver</a>' : '';
                    }
                    ?>
                    <a href="<?php echo $url_destino; ?>" target="_blank" class="clickable-recurso">
                        <div class="recurso-card <?php echo $tipoClase; ?>">
                            <div class="recurso-info">
                                <h3>
                                    <?php if ($recurso['proyecto_id']): ?>
                                        <span class="proyecto-tag" style="font-size:0.8rem; background:#eee; padding:2px 8px; border-radius:12px; margin-right:8px;">
                                            <i class="fas fa-project-diagram"></i> Proyecto
                                        </span>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($recurso['titulo']); ?>
                                </h3>
                                <div class="recurso-descripcion"><?php echo nl2br(htmlspecialchars($recurso['descripcion'])); ?></div>
                                <div class="recurso-meta">
                                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($recurso['usuario_nombre']); ?></span>
                                    <span><i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($recurso['fecha_subida'])); ?></span>
                                    <span class="recurso-tipo"><i class="fas <?php echo $icono; ?>"></i> <?php echo $recurso['tipo']; ?></span>
                                </div>
                            </div>
                            <div class="recurso-acciones">
                                <?php echo $accion; ?>
                                <?php if ($esAdmin || $_SESSION['usuario_id'] == $recurso['usuario_id']): ?>
                                    <a href="editar_recurso.php?id=<?php echo $recurso['id']; ?>" class="btn-accion btn-editar"><i class="fas fa-edit"></i> Editar</a>
                                    <a href="eliminar_recurso.php?id=<?php echo $recurso['id']; ?>" class="btn-accion btn-eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar este recurso? Esta acción no se puede deshacer.')"><i class="fas fa-trash"></i> Eliminar</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

<?php require_once __DIR__ . '/footer.php'; ?>

    <script>
        // Evitar que los clics en los botones activen el enlace padre
        document.querySelectorAll('.recurso-acciones a').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    </script>
</body>
</html>
<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::estaLogueado()) {
    header('Location: login.php');
    exit;
}

// Usamos las clases específicas en lugar de Forum
$temaModel = new Tema();
$categoriaModel = new Categoria();

$categorias = $categoriaModel->obtenerTodas();

// Recibir parámetros de búsqueda
$busqueda = trim($_GET['q'] ?? '');
$categoria_id = $_GET['cat'] ?? null;

// Obtener temas filtrados
if ($categoria_id) {
    $temas = $temaModel->obtenerTodos($categoria_id);
} else {
    $temas = $temaModel->obtenerTodos();
}

// Filtrar por búsqueda si es necesario
if (!empty($busqueda)) {
    $temas = array_filter($temas, function($tema) use ($busqueda) {
        $busquedaLower = strtolower($busqueda);
        return strpos(strtolower($tema['titulo']), $busquedaLower) !== false || 
               strpos(strtolower($tema['contenido']), $busquedaLower) !== false;
    });
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIAUBA - Work Together</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <img src="img/logo-uba-horizontal1.png" alt="uba_logo">
        <div class="logo">
            <h1>Club de Ingeniería Aplicada de la Universidad Bicentenaria de Aragua</h1>
            <p>Aprende • Construye • Mejora</p>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Inicio</a></li>
                <li><a href="information.php">Información</a></li>
                <li><a href="members.php">Miembros</a></li>
                <li><a href="perfil.php"><i class="fas fa-user"></i> Mi Perfil</a></li>
                <?php if (User::esAdmin()): ?>
                    <li><a href="admin.php">Admin</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Cerrar sesión (<?php echo $_SESSION['usuario_nombre']; ?>)</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="forum-header text-center" style="background: transparent; box-shadow: none; border: none; padding-bottom: 0;">
            <h2>Foro CIAUBA: Work Together</h2>
            <p>Comparte ideas, resuelve dudas y colabora con otros miembros.</p>

            <!-- Buscador -->
            <form method="GET" action="work_together.php" style="max-width: 600px; margin: var(--space-lg) auto; display: flex; gap: var(--space-sm);">
                <input type="text" name="q" placeholder="Buscar palabras clave..." value="<?php echo htmlspecialchars($busqueda); ?>" style="flex: 1;">
                <?php if ($categoria_id): ?>
                    <input type="hidden" name="cat" value="<?php echo htmlspecialchars($categoria_id); ?>">
                <?php endif; ?>
                <button type="submit" class="action-btn">Buscar</button>
                
                <?php if ($busqueda || $categoria_id): ?>
                    <a href="work_together.php" class="action-btn reject" style="text-decoration: none;">Limpiar</a>
                <?php endif; ?>
            </form>

            <!-- Filtro por categorías -->
            <div style="display: flex; justify-content: center; gap: var(--space-sm); flex-wrap: wrap; margin-bottom: var(--space-xl);">
                <a href="work_together.php<?php echo $busqueda ? '?q='.urlencode($busqueda) : ''; ?>" 
                   class="filter-btn <?php echo !$categoria_id ? 'active' : ''; ?>">
                   Todos
                </a>
                
                <?php foreach ($categorias as $cat): ?>
                    <a href="work_together.php?cat=<?php echo $cat['id']; ?><?php echo $busqueda ? '&q='.urlencode($busqueda) : ''; ?>" 
                       class="filter-btn <?php echo $categoria_id == $cat['id'] ? 'active' : ''; ?>">
                       <?php echo htmlspecialchars($cat['nombre']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <!-- Botón nuevo tema (CORREGIDO: nuevo_tema.php) -->
            <div class="text-right mb-md">
                <a href="nuevo_tema.php" class="cta-button" style="padding: var(--space-sm) var(--space-md); font-size: 1rem;">+ Nuevo Tema</a>
            </div>
        </section>

        <section class="forum-content">
            <div class="grid-system grid-1">
                <?php if (empty($temas)): ?>
                    <div class="text-center p-xl">
                        <h3>No se encontraron resultados</h3>
                        <p>Intenta con otras palabras clave o cambia de categoría.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($temas as $tema): ?>
                        <article class="discussion">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-xs);">
                                <h3>
                                    <?php if ($tema['fijo']): ?>📌 <?php endif; ?>
                                    <!-- CORREGIDO: tema.php en lugar de ver_tema.php -->
                                    <a href="tema.php?id=<?php echo $tema['id']; ?>"><?php echo htmlspecialchars($tema['titulo']); ?></a>
                                </h3>
                                <span class="status-badge status-inprogress"><?php echo htmlspecialchars($tema['categoria_nombre']); ?></span>
                            </div>
                            <p style="margin-bottom: var(--space-xs); color: #555;">
                                <?php 
                                    echo htmlspecialchars(substr($tema['contenido'], 0, 150)) . (strlen($tema['contenido']) > 150 ? '...' : ''); 
                                ?>
                            </p>
                            
                            <div style="font-size: 0.85rem; color: #888; margin-bottom: 0.25rem;">
                                👤 Publicado por <strong><?php echo htmlspecialchars($tema['nombre']); ?></strong> 
                                • 📅 <?php echo date('d M Y, H:i', strtotime($tema['creado_en'])); ?> 
                                • 💬 <?php echo $tema['num_respuestas']; ?> respuestas
                                • 👁️ <?php echo $tema['visitas']; ?> vistas
                            </div>
                            
                            <?php if (!empty($tema['actualizado_en']) && $tema['actualizado_en'] != $tema['creado_en']): ?>
                                <div style="font-size: 0.8rem; color: #999;">
                                    Última actividad: <?php echo date('d/m/Y H:i', strtotime($tema['actualizado_en'])); ?>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <p>Club de Ingeniería Aplicada UBA &copy; 2025</p>
        <p>Contacto: rhysuba@gmail.com | Campus Edificio de Ingeniería, Salón de Realidad Virtual</p>
    </footer>
</body>
</html>
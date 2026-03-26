<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::esAdmin()) {
    header('Location: index.php');
    exit;
}

$proyectoModel = new Proyecto();

// Procesar eliminación
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    if ($proyectoModel->eliminar($id)) {
        $mensaje = 'Proyecto eliminado correctamente.';
        $tipo = 'success';
    } else {
        $mensaje = 'Error al eliminar el proyecto.';
        $tipo = 'error';
    }
    header('Location: admin_proyectos.php?mensaje=' . urlencode($mensaje) . '&tipo=' . $tipo);
    exit;
}

$proyectos = $proyectoModel->obtenerTodos();
$mensaje = $_GET['mensaje'] ?? '';
$tipo = $_GET['tipo'] ?? '';
$page_title = 'Gestión de Proyectos - CIAUBA';
require_once __DIR__ . '/header.php';
?>

    <main>
        <div class="admin-header">
            <h2>Gestión de Proyectos</h2>
            <a href="admin_proyecto_form.php" class="cta-button"><i class="fas fa-plus"></i> Nuevo Proyecto</a>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert <?php echo $tipo; ?>"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
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
                            <td><?php echo $proyecto['id']; ?></td>
                            <td><a href="proyecto.php?id=<?php echo $proyecto['id']; ?>"><?php echo htmlspecialchars($proyecto['titulo']); ?></a></td>
                            <td><span class="status-badge status-<?php echo $proyecto['estado']; ?>"><?php echo $proyecto['estado']; ?></span></td>
                            <td><?php echo htmlspecialchars($proyecto['lider_nombre'] ?? 'Sin líder'); ?></td>
                            <td><?php echo $proyecto['fecha_inicio'] ? date('d/m/Y', strtotime($proyecto['fecha_inicio'])) : 'N/A'; ?></td>
                            <td><?php echo $proyecto['num_miembros'] ?? 0; ?></td>
                            <td class="actions">
                                <a href="admin_proyecto_form.php?id=<?php echo $proyecto['id']; ?>" class="action-btn edit" title="Editar"><i class="fas fa-edit"></i></a>
                                <a href="admin_proyectos.php?eliminar=<?php echo $proyecto['id']; ?>" class="action-btn reject" title="Eliminar" onclick="return confirm('¿Eliminar este proyecto? Se perderán todos los datos asociados.');"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p><a href="admin.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Volver al panel</a></p>
    </main>

<?php require_once __DIR__ . '/footer.php'; ?>
</body>
</html>
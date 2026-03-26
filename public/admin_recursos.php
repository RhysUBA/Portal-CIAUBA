<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::esAdmin()) {
    header('Location: index.php');
    exit;
}

$recursoModel = new Recurso();

// Procesar eliminación
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    if ($recursoModel->eliminar($id)) {
        $mensaje = 'Recurso eliminado correctamente.';
        $tipo = 'success';
    } else {
        $mensaje = 'Error al eliminar el recurso.';
        $tipo = 'error';
    }
    header('Location: admin_recursos.php?mensaje=' . urlencode($mensaje) . '&tipo=' . $tipo);
    exit;
}

$recursos = $recursoModel->obtenerTodos(null, null, null, 100);
$mensaje = $_GET['mensaje'] ?? '';
$tipo = $_GET['tipo'] ?? '';
$page_title = 'Gestión de Recursos - CIAUBA';
require_once __DIR__ . '/header.php';
?>

    <main>
        <div class="admin-header">
            <h2>Gestión de Recursos</h2>
            <a href="admin_recurso_form.php" class="cta-button"><i class="fas fa-plus"></i> Nuevo Recurso</a>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert <?php echo $tipo; ?>"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    退休
                        <th>ID</th>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Autor</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recursos as $recurso): ?>
                        <tr>
                            <td><?php echo $recurso['id']; ?></td>
                            <td><a href="recursos.php?id=<?php echo $recurso['id']; ?>"><?php echo htmlspecialchars($recurso['titulo']); ?></a></td>
                            <td><?php echo $recurso['tipo']; ?></td>
                            <td><?php echo htmlspecialchars($recurso['usuario_nombre']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($recurso['fecha_subida'])); ?></td>
                            <td class="actions">
                                <a href="admin_recurso_form.php?id=<?php echo $recurso['id']; ?>" class="action-btn edit" title="Editar"><i class="fas fa-edit"></i></a>
                                <a href="admin_recursos.php?eliminar=<?php echo $recurso['id']; ?>" class="action-btn reject" title="Eliminar" onclick="return confirm('¿Eliminar este recurso? Se perderá el archivo si es de tipo archivo.');"><i class="fas fa-trash"></i></a>
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
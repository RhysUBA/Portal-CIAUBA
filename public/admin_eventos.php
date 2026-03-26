<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::esAdmin()) {
    header('Location: index.php');
    exit;
}

$eventoModel = new Evento();

// Procesar eliminación
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    if ($eventoModel->eliminar($id)) {
        $mensaje = 'Evento eliminado correctamente.';
        $tipo = 'success';
    } else {
        $mensaje = 'Error al eliminar el evento.';
        $tipo = 'error';
    }
    header('Location: admin_eventos.php?mensaje=' . urlencode($mensaje) . '&tipo=' . $tipo);
    exit;
}

$eventos = $eventoModel->obtenerTodos();
$mensaje = $_GET['mensaje'] ?? '';
$tipo = $_GET['tipo'] ?? '';
$page_title = 'Gestión de Eventos - CIAUBA';
require_once __DIR__ . '/header.php';
?>

    <main>
        <div class="admin-header">
            <h2>Gestión de Eventos</h2>
            <a href="admin_evento_form.php" class="cta-button"><i class="fas fa-plus"></i> Nuevo Evento</a>
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
                        <th>Tipo</th>
                        <th>Fecha Inicio</th>
                        <th>Lugar</th>
                        <th>Asistentes</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($eventos as $evento): ?>
                        <tr>
                            <td><?php echo $evento['id']; ?></td>
                            <td><a href="evento.php?id=<?php echo $evento['id']; ?>"><?php echo htmlspecialchars($evento['titulo']); ?></a></td>
                            <td><?php echo $evento['tipo']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($evento['fecha_inicio'])); ?></td>
                            <td><?php echo htmlspecialchars($evento['lugar']); ?></td>
                            <td><?php echo $evento['asistentes_count'] ?? 0; ?></td>
                            <td class="actions">
                                <a href="admin_evento_form.php?id=<?php echo $evento['id']; ?>" class="action-btn edit" title="Editar"><i class="fas fa-edit"></i></a>
                                <a href="admin_eventos.php?eliminar=<?php echo $evento['id']; ?>" class="action-btn reject" title="Eliminar" onclick="return confirm('¿Eliminar este evento? Se perderán todos los datos de asistencia.');"><i class="fas fa-trash"></i></a>
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
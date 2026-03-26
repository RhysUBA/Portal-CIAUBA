<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::esAdmin()) {
    http_response_code(403);
    exit;
}

$termino = $_GET['q'] ?? '';
if (strlen($termino) < 3) {
    echo '<p>Ingresa al menos 3 caracteres para buscar.</p>';
    exit;
}

$userModel = new User();
$resultados = $userModel->buscar($termino);

if (empty($resultados)) {
    echo '<p>No se encontraron resultados para "' . htmlspecialchars($termino) . '".</p>';
    exit;
}
?>
<div class="table-container">
    <table class="members-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Cédula</th>
                <th>Carrera</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($resultados as $usuario): ?>
            <tr>
                <td><?php echo $usuario['id']; ?></td>
                <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                <td><?php echo htmlspecialchars($usuario['cedula']); ?></td>
                <td><?php echo htmlspecialchars($usuario['carrera']); ?></td>
                <td>
                    <span class="role-badge <?php echo $usuario['rol']; ?>">
                        <?php echo $usuario['rol']; ?>
                    </span>
                </td>
                <td>
                    <span class="status-badge <?php echo 'status-' . $usuario['estado']; ?>">
                        <?php echo $usuario['estado']; ?>
                    </span>
                </td>
                <td>
                    <button class="action-btn view" onclick="verMiembro(<?php echo $usuario['id']; ?>)">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::esAdmin()) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'] ?? 0;
if ($id) {
    $db = Database::obtenerConexion();
    $stmt = $db->prepare("UPDATE usuarios SET estado = 'inactivo' WHERE id = :id AND estado = 'pendiente'");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
}
header('Location: admin.php#members');
exit;
<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::estaLogueado()) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? 0;
if (!$id) {
    die('Recurso no especificado.');
}

$recursoModel = new Recurso();
$recurso = $recursoModel->obtenerPorId($id);

if (!$recurso || $recurso['tipo'] != 'archivo') {
    die('Recurso no válido.');
}

$ruta_archivo = __DIR__ . '/uploads/recursos/' . $recurso['archivo_ruta'];
if (!file_exists($ruta_archivo)) {
    die('El archivo no existe.');
}

// Forzar descarga
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($recurso['titulo'] . '.' . pathinfo($recurso['archivo_ruta'], PATHINFO_EXTENSION)) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($ruta_archivo));
readfile($ruta_archivo);
exit;
<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!User::estaLogueado()) {
    header('Location: login.php');
    exit;
}

$post_id = $_GET['id'] ?? 0;
$tema_id = $_GET['tema'] ?? 0;

if (!$post_id || !$tema_id) {
    header('Location: work_together.php');
    exit;
}

$postModel = new Post();
$resultado = $postModel->eliminar($post_id, $_SESSION['usuario_id']);

if ($resultado === true) {
    header('Location: tema.php?id=' . $tema_id);
} else {
    echo "<div class='error'>" . $resultado . "</div>";
    echo "<a href='tema.php?id=$tema_id'>Volver al tema</a>";
}
exit;
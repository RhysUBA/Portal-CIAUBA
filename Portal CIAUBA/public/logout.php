<?php
require_once __DIR__ . '/../vendor/autoload.php';
User::logout();
header('Location: index.php');
exit;
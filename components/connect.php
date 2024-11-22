<?php
require_once __DIR__ . '/../config/security.php';
$dbConfig = require __DIR__ . '/../config/database.php';

try {
    $conn = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']}",
        $dbConfig['user'],
        $dbConfig['pass']
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    error_log("Erro de conexão: " . $e->getMessage());
    die('Erro de conexão com o banco de dados');
}

?>
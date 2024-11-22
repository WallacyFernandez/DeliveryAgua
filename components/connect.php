<?php

$db_name = 'mysql:host=db-agua.cjbewbs6d6lt.us-east-1.rds.amazonaws.com;dbname=NewRDS';
$user_name = 'root';
$user_password = 'wallacylendario';

try {
    $conn = new PDO($db_name, $user_name, $user_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Erro de conexão: " . $e->getMessage();
    die();
}

?>
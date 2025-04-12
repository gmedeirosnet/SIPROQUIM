<?php
// config/db.php

// Use environment variables if available, otherwise use defaults
$host = getenv('DB_HOST') ?: 'db'; // Changed from 'localhost' to 'db' (Docker service name)
$dbname = getenv('DB_NAME') ?: 'estoque';
$user = getenv('DB_USER') ?: 'admin';
$pass = getenv('DB_PASSWORD') ?: 'password';
$port = getenv('DB_PORT') ?: '5432'; // Added port configuration

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
try {
    $pdo = new PDO($dsn, $user, $pass);
    // Definindo o modo de erro para exceções
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    echo "Falha na conexão: " . $e->getMessage();
    exit;
}
?>

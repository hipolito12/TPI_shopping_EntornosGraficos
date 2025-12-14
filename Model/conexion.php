<?php
/**
 * Retorna una conexión PDO a la base de datos 'shopping'.
 */
function getConnection() {
    $host = '127.0.0.1';
    $db   = 'shopping';
    $user = 'root';
    $pass = 'root';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    try {
        $pdo = new PDO($dsn, $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (\PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}

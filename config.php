<?php
session_start();

$host = $_SESSION['host'] ?? 'localhost';
$user = $_SESSION['user'] ?? 'root';
$password = $_SESSION['password'] ?? 'root';
$database = 'fitness_salina';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}

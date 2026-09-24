<?php

require_once __DIR__ . "/vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$host = $_ENV["DB_HOST"] ?? "localhost";
$port = $_ENV["DB_PORT"] ?? "3306";
$dbname = $_ENV["DB_NAME"] ?? "hackathon";
$user = $_ENV["DB_USER"] ?? "root";
$password = $_ENV["DB_PASSWORD"] ?? "";

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die("Erro ao conectar com o banco de dados: " . $e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) {
    $secureCookie = !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off";

    session_set_cookie_params([
        "httponly" => true,
        "secure" => $secureCookie,
        "samesite" => "Lax"
    ]);

    session_start();
}

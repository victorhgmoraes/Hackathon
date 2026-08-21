<?php

session_start();

header("Content-Type: application/json; charset=UTF-8");

$host = "localhost";
$dbname = "hackathon";
$user = "root";
$password = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro ao conectar com o banco de dados."
    ]);

    exit;
}

$dados = json_decode(file_get_contents("php://input"), true);

$email = trim($dados["email"] ?? "");
$senha = $dados["senha"] ?? "";

if ($email === "" || $senha === "") {

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Preencha o e-mail e a senha."
    ]);

    exit;
}

try {

    $sql = "SELECT id, nome, email, matricula, senha, tipo
            FROM usuarios
            WHERE email = :email
            LIMIT 1";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":email" => $email
    ]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario || !password_verify($senha, $usuario["senha"])) {

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "E-mail ou senha incorretos."
        ]);

        exit;
    }

    $_SESSION["usuario_id"] = $usuario["id"];
    $_SESSION["nome"] = $usuario["nome"];
    $_SESSION["email"] = $usuario["email"];
    $_SESSION["matricula"] = $usuario["matricula"];
    $_SESSION["tipo"] = $usuario["tipo"];

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Login realizado com sucesso!",
        "tipo" => $usuario["tipo"]
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Não foi possível realizar o login."
    ]);
}
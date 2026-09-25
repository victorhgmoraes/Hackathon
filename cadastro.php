<?php
require_once __DIR__ . "/config.php";
header("Content-Type: application/json; charset=UTF-8");

$dados = json_decode(file_get_contents("php://input"), true);

$nome = trim($dados["nome"] ?? "");
$email = strtolower(trim($dados["email"] ?? ""));
$matricula = trim($dados["matricula"] ?? "");
$senha = $dados["senha"] ?? "";

if ($nome === "" || $email === "" || $matricula === "" || $senha === "") {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Preencha todos os campos."
    ]);
    exit;
}

try {
    $sql = "SELECT email, matricula
            FROM usuarios
            WHERE email = :email OR matricula = :matricula
            LIMIT 1";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":email" => $email,
        ":matricula" => $matricula
    ]);

    $existente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existente) {
        if ($existente["email"] === $email) {
            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Este e-mail já está cadastrado."
            ]);
            exit;
        }

        if ($existente["matricula"] === $matricula) {
            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Esta matrícula já está cadastrada."
            ]);
            exit;
        }
    }

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (nome, email, matricula, senha, tipo)
            VALUES (:nome, :email, :matricula, :senha, 'participante')";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":nome" => $nome,
        ":email" => $email,
        ":matricula" => $matricula,
        ":senha" => $senhaHash
    ]);

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Cadastro realizado com sucesso!"
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Não foi possível realizar o cadastro."
    ]);
}
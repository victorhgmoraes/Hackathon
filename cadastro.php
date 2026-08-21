<?php

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
$nome = trim($dados["nome"] ?? "");
$email = trim($dados["email"] ?? "");
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

    if ($e->getCode() == 23000) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Este e-mail ou matrícula já está cadastrado."
        ]);
    } else {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Não foi possível realizar o cadastro."
        ]);
    }
}
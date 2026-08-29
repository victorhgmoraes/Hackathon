<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
  header("Location: ../login.html");
  exit;
}

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
  die("Erro ao conectar com o banco de dados.");
}

$usuarioId = $_SESSION["usuario_id"];

$titulo = trim($_POST["titulo"] ?? "");
$descricao = trim($_POST["descricao"] ?? "");
$categoria = trim($_POST["categoria"] ?? "");
$repositorio = trim($_POST["repositorio"] ?? "");

$categoriasValidas = [
  "Tecnologia",
  "Educação",
  "Sustentabilidade",
  "Saúde",
  "Mobilidade",
  "Outros"
];

if ($titulo === "" || $descricao === "" || $categoria === "" || $repositorio === "") {
  die("Preencha todos os campos obrigatórios.");
}

if (strlen($titulo) > 100) {
  die("O título do projeto deve ter no máximo 100 caracteres.");
}

if (strlen($descricao) > 1000) {
  die("A descrição deve ter no máximo 1000 caracteres.");
}

if (!in_array($categoria, $categoriasValidas, true)) {
  die("Categoria inválida.");
}

if (!filter_var($repositorio, FILTER_VALIDATE_URL)) {
  die("Informe uma URL válida para o repositório.");
}

$protocolo = parse_url($repositorio, PHP_URL_SCHEME);

if (!in_array(strtolower($protocolo), ["http", "https"], true)) {
  die("Informe uma URL válida para o repositório.");
}

$sqlEquipe = "
  SELECT equipe_id
  FROM participantes
  WHERE usuario_id = :usuario_id
  LIMIT 1
";

$stmtEquipe = $pdo->prepare($sqlEquipe);
$stmtEquipe->execute([
  ":usuario_id" => $usuarioId
]);

$participante = $stmtEquipe->fetch(PDO::FETCH_ASSOC);

if (!$participante || !$participante["equipe_id"]) {
  die("Você precisa estar em uma equipe para submeter um projeto.");
}

$equipeId = $participante["equipe_id"];

$sqlProjeto = "
  SELECT id
  FROM projetos
  WHERE equipe_id = :equipe_id
  LIMIT 1
";

$stmtProjeto = $pdo->prepare($sqlProjeto);
$stmtProjeto->execute([
  ":equipe_id" => $equipeId
]);

if ($stmtProjeto->fetch()) {
  die("Sua equipe já possui um projeto submetido.");
}

$sql = "
  INSERT INTO projetos (equipe_id, nome, descricao, repositorio, categoria)
  VALUES (:equipe_id, :nome, :descricao, :repositorio, :categoria)
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
  ":equipe_id" => $equipeId,
  ":nome" => $titulo,
  ":descricao" => $descricao,
  ":repositorio" => $repositorio,
  ":categoria" => $categoria
]);

header("Location: dashboard.php");
exit;
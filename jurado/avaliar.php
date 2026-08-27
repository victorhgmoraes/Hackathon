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
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Erro ao conectar com o banco de dados.");
}

$juradoId = $_SESSION["usuario_id"];

$sqlUsuario = "SELECT nome, tipo FROM usuarios WHERE id = :id LIMIT 1";
$stmtUsuario = $pdo->prepare($sqlUsuario);
$stmtUsuario->execute([":id" => $juradoId]);
$usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

if (!$usuario || $usuario["tipo"] !== "jurado") {
  die("Acesso negado.");
}

$projetoId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$projetoId) {
  die("Projeto inválido.");
}

$sqlProjeto = "
  SELECT
    projetos.id,
    projetos.nome,
    projetos.descricao,
    projetos.categoria,
    projetos.repositorio,
    equipes.nome AS equipe_nome
  FROM projetos
  INNER JOIN equipes ON projetos.equipe_id = equipes.id
  WHERE projetos.id = :projeto_id
  LIMIT 1
";
$stmtProjeto = $pdo->prepare($sqlProjeto);
$stmtProjeto->execute([":projeto_id" => $projetoId]);
$projeto = $stmtProjeto->fetch(PDO::FETCH_ASSOC);

if (!$projeto) {
  die("Projeto não encontrado.");
}

$erro = "";
$sucesso = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $nota = filter_input(INPUT_POST, "nota", FILTER_VALIDATE_FLOAT);
  $comentario = trim($_POST["comentario"] ?? "");

  if ($nota === false || $nota === null) {
    $erro = "Informe uma nota válida.";
  } elseif ($nota < 0 || $nota > 10) {
    $erro = "A nota deve estar entre 0 e 10.";
  } elseif ($comentario === "") {
    $erro = "Informe um comentário.";
  } else {
    $sqlExiste = "SELECT id FROM avaliacoes WHERE projeto_id = :projeto_id AND jurado_id = :jurado_id LIMIT 1";
    $stmtExiste = $pdo->prepare($sqlExiste);
    $stmtExiste->execute([
      ":projeto_id" => $projetoId,
      ":jurado_id" => $juradoId
    ]);
    $avaliacaoExistente = $stmtExiste->fetch(PDO::FETCH_ASSOC);

    if ($avaliacaoExistente) {
      $sql = "UPDATE avaliacoes SET nota = :nota, comentario = :comentario WHERE id = :id";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([
        ":nota" => $nota,
        ":comentario" => $comentario,
        ":id" => $avaliacaoExistente["id"]
      ]);
      $sucesso = "Avaliação atualizada com sucesso.";
    } else {
      $sql = "INSERT INTO avaliacoes (projeto_id, jurado_id, nota, comentario) VALUES (:projeto_id, :jurado_id, :nota, :comentario)";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([
        ":projeto_id" => $projetoId,
        ":jurado_id" => $juradoId,
        ":nota" => $nota,
        ":comentario" => $comentario
      ]);
      $sucesso = "Avaliação registrada com sucesso.";
    }
  }
}

$sqlAvaliacao = "SELECT nota, comentario FROM avaliacoes WHERE projeto_id = :projeto_id AND jurado_id = :jurado_id LIMIT 1";
$stmtAvaliacao = $pdo->prepare($sqlAvaliacao);
$stmtAvaliacao->execute([
  ":projeto_id" => $projetoId,
  ":jurado_id" => $juradoId
]);
$avaliacao = $stmtAvaliacao->fetch(PDO::FETCH_ASSOC);

$notaAtual = $avaliacao["nota"] ?? "";
$comentarioAtual = $avaliacao["comentario"] ?? "";
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Avaliar Projeto | 1º Hackathon do Curso</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet" />
</head>
<body class="login-page">
  <header class="navbar">
    <div class="container navbar-content">
      <a href="../index.html" class="logo">
        <span class="logo-symbol">&lt;/&gt;</span>
        <span>HACKA<span>THON</span></span>
      </a>
      <a href="dashboard.php" class="back-home">← Voltar para o painel</a>
    </div>
  </header>
  <main class="login-main">
    <div class="login-container">
      <section class="login-intro">
        <span class="section-label">AVALIAÇÃO</span>
        <h1>Avalie o <span>projeto.</span></h1>
        <p>Analise as informações apresentadas pela equipe e registre sua avaliação.</p>
        <div class="login-features">
          <div class="login-feature">
            <span class="feature-number">PROJETO</span>
            <div>
              <strong><?= htmlspecialchars($projeto["nome"]) ?></strong>
              <p><?= htmlspecialchars($projeto["descricao"]) ?></p>
            </div>
          </div>
          <div class="login-feature">
            <span class="feature-number">EQUIPE</span>
            <div>
              <strong><?= htmlspecialchars($projeto["equipe_nome"]) ?></strong>
              <p>Categoria: <?= htmlspecialchars($projeto["categoria"]) ?></p>
            </div>
          </div>
          <?php if (!empty($projeto["repositorio"])): ?>
            <div class="login-feature">
              <span class="feature-number">GITHUB</span>
              <div>
                <a href="<?= htmlspecialchars($projeto["repositorio"]) ?>" target="_blank" rel="noopener noreferrer" class="dashboard-link">Abrir repositório →</a>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </section>
      <section class="login-card">
        <div class="login-card-header">
          <span class="login-icon">★</span>
          <div>
            <h2>Avaliação</h2>
            <p>Registre sua nota e comentário</p>
          </div>
        </div>
        <?php if ($erro !== ""): ?>
          <div class="login-message show"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
        <?php if ($sucesso !== ""): ?>
          <div class="login-message show" style="border-color: rgba(134, 239, 172, 0.25); background: rgba(134, 239, 172, 0.05); color: #86efac;">
            <?= htmlspecialchars($sucesso) ?>
          </div>
        <?php endif; ?>
        <form method="POST" action="avaliar.php?id=<?= $projetoId ?>">
          <div class="form-group">
            <label for="nota">Nota</label>
            <input type="number" id="nota" name="nota" min="0" max="10" step="0.1" value="<?= htmlspecialchars($notaAtual) ?>" placeholder="Ex.: 8.5" required />
          </div>
          <div class="form-group">
            <label for="comentario">Comentário</label>
            <textarea id="comentario" name="comentario" placeholder="Escreva seus comentários sobre o projeto..." required><?= htmlspecialchars($comentarioAtual) ?></textarea>
          </div>
          <button type="submit" class="btn btn-primary login-button">
            <?= $avaliacao ? "Atualizar avaliação" : "Registrar avaliação" ?>
            <span>→</span>
          </button>
        </form>
      </section>
    </div>
  </main>
  <footer class="login-footer">
    <span>© 2026 — 1º Hackathon do Curso</span>
  </footer>
  <script src="../js/script.js"></script>
</body>
</html>
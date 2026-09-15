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
  } elseif (round($nota, 1) != $nota) {
    $erro = "A nota deve ter no máximo uma casa decimal.";
  } elseif ($comentario === "") {
    $erro = "Informe um comentário.";
  } elseif (strlen($comentario) > 1000) {
    $erro = "O comentário deve ter no máximo 1000 caracteres.";
  } else {
    $sqlExiste = "
      SELECT id
      FROM avaliacoes
      WHERE projeto_id = :projeto_id
      AND jurado_id = :jurado_id
      LIMIT 1
    ";

    $stmtExiste = $pdo->prepare($sqlExiste);
    $stmtExiste->execute([
      ":projeto_id" => $projetoId,
      ":jurado_id" => $juradoId
    ]);

    $avaliacaoExistente = $stmtExiste->fetch(PDO::FETCH_ASSOC);

    if ($avaliacaoExistente) {
      $sql = "
        UPDATE avaliacoes
        SET nota = :nota, comentario = :comentario
        WHERE id = :id
      ";

      $stmt = $pdo->prepare($sql);
      $stmt->execute([
        ":nota" => $nota,
        ":comentario" => $comentario,
        ":id" => $avaliacaoExistente["id"]
      ]);

      $sucesso = "Avaliação atualizada com sucesso.";
    } else {
      $sql = "
        INSERT INTO avaliacoes (projeto_id, jurado_id, nota, comentario)
        VALUES (:projeto_id, :jurado_id, :nota, :comentario)
      ";

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

$sqlAvaliacao = "
  SELECT nota, comentario
  FROM avaliacoes
  WHERE projeto_id = :projeto_id
  AND jurado_id = :jurado_id
  LIMIT 1
";

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
  <link rel="stylesheet" href="../css/avaliar.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet" />
</head>
<body class="evaluation-page">
  <header class="navbar">
    <div class="container navbar-content">
      <a href="../index.php" class="logo">
        <span class="logo-symbol">&lt;/&gt;</span>
        <span>HACKA<span>THON</span></span>
      </a>
      <div class="navbar-user">
        <span>Jurado: <?= htmlspecialchars($usuario["nome"]) ?></span>
        <a href="../logout.php" class="logout-button">Sair</a>
      </div>
    </div>
  </header>
  <main class="evaluation-main">
    <div class="container evaluation-container">
      <header class="evaluation-header">
        <div>
          <span class="section-label">AVALIAÇÃO</span>
          <h1>Avalie o <span>projeto.</span></h1>
          <p>Analise as informações apresentadas pela equipe e registre sua avaliação.</p>
        </div>
        <a href="dashboard.php" class="back-button">← Voltar para o painel</a>
      </header>
      <div class="evaluation-grid">
        <section class="project-details">
          <div class="details-header">
            <div>
              <span class="card-label">PROJETO</span>
              <h2><?= htmlspecialchars($projeto["nome"]) ?></h2>
            </div>
            <span class="project-category">
              <?= htmlspecialchars($projeto["categoria"]) ?>
            </span>
          </div>
          <div class="details-content">
            <div class="detail-block">
              <span class="detail-label">DESCRIÇÃO</span>
              <p><?= nl2br(htmlspecialchars($projeto["descricao"])) ?></p>
            </div>
            <div class="detail-block">
              <span class="detail-label">EQUIPE</span>
              <p><?= htmlspecialchars($projeto["equipe_nome"]) ?></p>
            </div>
            <?php if (!empty($projeto["repositorio"])): ?>
              <div class="detail-block">
                <span class="detail-label">REPOSITÓRIO</span>
                <a
                  href="<?= htmlspecialchars($projeto["repositorio"]) ?>"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="repository-link"
                >
                  Abrir repositório no GitHub →
                </a>
              </div>
            <?php endif; ?>
          </div>
        </section>
        <section class="evaluation-card">
          <div class="evaluation-card-header">
            <div class="evaluation-icon">★</div>
            <div>
              <span class="card-label">JURADO</span>
              <h2>Sua avaliação</h2>
              <p>Registre sua nota e comentário.</p>
            </div>
          </div>
          <?php if ($erro !== ""): ?>
            <div class="form-message error show">
              <?= htmlspecialchars($erro) ?>
            </div>
          <?php endif; ?>
          <?php if ($sucesso !== ""): ?>
            <div class="form-message success show">
              <?= htmlspecialchars($sucesso) ?>
            </div>
          <?php endif; ?>
          <form method="POST" action="avaliar.php?id=<?= (int) $projetoId ?>">
            <div class="form-group">
              <label for="nota">Nota</label>
              <input
                type="number"
                id="nota"
                name="nota"
                min="0"
                max="10"
                step="0.1"
                value="<?= htmlspecialchars($notaAtual) ?>"
                placeholder="Ex.: 8.5"
                required
              />
              <small>Informe uma nota entre 0 e 10.</small>
            </div>
            <div class="form-group">
              <label for="comentario">Comentário</label>
              <textarea
                id="comentario"
                name="comentario"
                placeholder="Escreva seus comentários sobre o projeto..."
                maxlength="1000"
                required
              ><?= htmlspecialchars($comentarioAtual) ?></textarea>
              <small>Máximo de 1000 caracteres.</small>
            </div>
            <button type="submit" class="btn btn-primary">
              <?= $avaliacao ? "Atualizar avaliação" : "Registrar avaliação" ?>
              <span>→</span>
            </button>
          </form>
        </section>
      </div>
    </div>
  </main>
  <footer class="footer">
    <div class="container footer-content">
      <span>© 2026 — 1º Hackathon do Curso</span>
      <span>Engenharia de Software</span>
    </div>
  </footer>
  <script src="../js/script.js"></script>
</body>
</html>

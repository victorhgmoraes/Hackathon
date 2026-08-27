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

$usuarioId = $_SESSION["usuario_id"];

$sqlUsuario = "SELECT nome, tipo FROM usuarios WHERE id = :id LIMIT 1";
$stmtUsuario = $pdo->prepare($sqlUsuario);
$stmtUsuario->execute([":id" => $usuarioId]);
$usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

if (!$usuario || $usuario["tipo"] !== "jurado") {
  die("Acesso negado. Esta área é exclusiva para jurados.");
}

$sqlProjetos = "
  SELECT
    projetos.id,
    projetos.nome,
    projetos.descricao,
    projetos.categoria,
    projetos.repositorio,
    equipes.nome AS equipe_nome,
    avaliacoes.id AS avaliacao_id,
    avaliacoes.nota AS avaliacao_nota
  FROM projetos
  INNER JOIN equipes ON projetos.equipe_id = equipes.id
  LEFT JOIN avaliacoes ON avaliacoes.projeto_id = projetos.id AND avaliacoes.jurado_id = :jurado_id
  ORDER BY projetos.id ASC
";
$stmtProjetos = $pdo->prepare($sqlProjetos);
$stmtProjetos->execute([":jurado_id" => $usuarioId]);
$projetos = $stmtProjetos->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Painel do Jurado | 1º Hackathon do Curso</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet" />
</head>
<body class="dashboard-page">
  <header class="navbar">
    <div class="container navbar-content">
      <a href="../index.html" class="logo">
        <span class="logo-symbol">&lt;/&gt;</span>
        <span>HACKA<span>THON</span></span>
      </a>
      <div class="dashboard-user">
        <span>Jurado: <?= htmlspecialchars($usuario["nome"]) ?></span>
        <a href="../logout.php" class="logout-button">Sair</a>
      </div>
    </div>
  </header>
  <main class="dashboard-main">
    <div class="dashboard-container">
      <header class="dashboard-header">
        <span class="section-label">PAINEL DO JURADO</span>
        <h1>Avalie os <span>projetos.</span></h1>
        <p>Analise os projetos submetidos pelas equipes e registre sua avaliação.</p>
      </header>
      <section class="dashboard-section">
        <div class="dashboard-section-header">
          <div>
            <span class="section-label">PROJETOS</span>
            <h2>Projetos submetidos</h2>
          </div>
          <span class="ranking-count"><?= count($projetos) ?> projeto(s)</span>
        </div>
        <div class="jury-project-list">
          <?php if (empty($projetos)): ?>
            <div class="project-card">
              <div class="project-content">
                <h3>Nenhum projeto submetido</h3>
                <p>Ainda não existem projetos disponíveis para avaliação.</p>
              </div>
            </div>
          <?php else: ?>
            <?php foreach ($projetos as $index => $projeto): ?>
              <article class="jury-project-card">
                <div class="jury-project-number">
                  <?= str_pad($index + 1, 2, "0", STR_PAD_LEFT) ?>
                </div>
                <div class="jury-project-info">
                  <h3><?= htmlspecialchars($projeto["nome"]) ?></h3>
                  <p><?= htmlspecialchars($projeto["descricao"]) ?></p>
                  <p style="margin-top: 8px;">
                    <strong>Equipe:</strong> <?= htmlspecialchars($projeto["equipe_nome"]) ?>
                  </p>
                  <p style="margin-top: 4px;">
                    <strong>Categoria:</strong> <?= htmlspecialchars($projeto["categoria"]) ?>
                  </p>
                  <?php if (!empty($projeto["repositorio"])): ?>
                    <p style="margin-top: 8px;">
                      <a href="<?= htmlspecialchars($projeto["repositorio"]) ?>" target="_blank" rel="noopener noreferrer" class="dashboard-link">Ver repositório →</a>
                    </p>
                  <?php endif; ?>
                </div>
                <div class="jury-project-action">
                  <?php if ($projeto["avaliacao_id"]): ?>
                    <a href="avaliar.php?id=<?= $projeto["id"] ?>" class="btn btn-outline evaluate-button">
                      Avaliado — <?= htmlspecialchars($projeto["avaliacao_nota"]) ?>
                    </a>
                  <?php else: ?>
                    <a href="avaliar.php?id=<?= $projeto["id"] ?>" class="btn btn-primary evaluate-button">
                      Avaliar →
                    </a>
                  <?php endif; ?>
                </div>
              </article>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </section>
    </div>
  </main>
  <footer class="login-footer">
    <span>© 2026 — 1º Hackathon do Curso</span>
  </footer>
  <script src="../js/script.js"></script>
</body>
</html>
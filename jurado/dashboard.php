<?php
require_once __DIR__ . "/../config.php";
if (!isset($_SESSION["usuario_id"])) {
  header("Location: ../login.html");
  exit;
}

$usuarioId = $_SESSION["usuario_id"];

$sqlUsuario = "
  SELECT nome, tipo
  FROM usuarios
  WHERE id = :id
  LIMIT 1
";

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
  INNER JOIN equipes
    ON projetos.equipe_id = equipes.id
  LEFT JOIN avaliacoes
    ON avaliacoes.projeto_id = projetos.id
    AND avaliacoes.jurado_id = :jurado_id
  ORDER BY projetos.id ASC
";

$stmtProjetos = $pdo->prepare($sqlProjetos);
$stmtProjetos->execute([":jurado_id" => $usuarioId]);
$projetos = $stmtProjetos->fetchAll(PDO::FETCH_ASSOC);

$totalProjetos = count($projetos);
$totalAvaliados = 0;

foreach ($projetos as $projeto) {
  if (!empty($projeto["avaliacao_id"])) {
    $totalAvaliados++;
  }
}
$totalPendentes = $totalProjetos - $totalAvaliados;
?>

<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Painel do Jurado | 1º Hackathon do Curso</title>
  <link rel="stylesheet" href="../css/jurado.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet" />
</head>
<body class="jury-page">
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
<main class="jury-main">
  <div class="container jury-container">
    <section class="jury-header">
      <div>
        <span class="section-label">PAINEL DO JURADO</span>
        <h1>Avalie os <span>projetos.</span></h1>
        <p>
          Analise os projetos submetidos pelas equipes e registre sua avaliação.
        </p>
      </div>
    </section>
    <section class="jury-summary">
      <div class="summary-item">
        <span class="summary-number"><?= $totalProjetos ?></span>
        <div>
          <strong>Projetos</strong>
          <p>Submetidos</p>
        </div>
      </div>
      <div class="summary-item">
        <span class="summary-number"><?= $totalAvaliados ?></span>
        <div>
          <strong>Avaliados</strong>
          <p>Por você</p>
        </div>
      </div>
      <div class="summary-item">
        <span class="summary-number"><?= $totalPendentes ?></span>
        <div>
          <strong>Pendentes</strong>
          <p>Aguardando avaliação</p>
        </div>
      </div>
    </section>
    <section class="jury-section">
      <div class="jury-section-header">
        <div>
          <span class="section-label">SUBMISSÕES</span>
          <h2>Projetos participantes</h2>
        </div>
        <span class="project-count">
          <?= $totalProjetos ?> projeto(s)
        </span>
      </div>
      <div class="jury-project-list">
        <?php if (empty($projetos)): ?>
          <div class="empty-projects">
            <span class="empty-icon">&lt;/&gt;</span>
            <h3>Nenhum projeto submetido</h3>
            <p>
              Ainda não existem projetos disponíveis para avaliação.
            </p>
          </div>
        <?php else: ?>
          <?php foreach ($projetos as $index => $projeto): ?>
            <article class="jury-project-card">
              <div class="project-number">
                <?= str_pad($index + 1, 2, "0", STR_PAD_LEFT) ?>
              </div>
              <div class="project-info">
                <div class="project-top">
                  <div>
                    <span class="project-category">
                      <?= htmlspecialchars($projeto["categoria"]) ?>
                    </span>
                    <h3>
                      <?= htmlspecialchars($projeto["nome"]) ?>
                    </h3>
                  </div>
                  <?php if (!empty($projeto["avaliacao_id"])): ?>
                    <span class="evaluation-status evaluated">
                      Avaliado
                    </span>
                  <?php else: ?>
                    <span class="evaluation-status pending">
                      Pendente
                    </span>
                  <?php endif; ?>
                </div>
                <p class="project-description">
                  <?= htmlspecialchars($projeto["descricao"]) ?>
                </p>
                <div class="project-meta">
                  <span>
                    <strong>Equipe:</strong>
                    <?= htmlspecialchars($projeto["equipe_nome"]) ?>
                  </span>
                  <?php if (!empty($projeto["repositorio"])): ?>
                    <a
                      href="<?= htmlspecialchars($projeto["repositorio"]) ?>"
                      target="_blank"
                      rel="noopener noreferrer"
                    >
                      Ver repositório →
                    </a>
                  <?php endif; ?>
                </div>
              </div>
              <div class="project-action">
                <?php if (!empty($projeto["avaliacao_id"])): ?>
                  <a
                    href="avaliar.php?id=<?= (int) $projeto["id"] ?>"
                    class="btn btn-evaluated"
                  >
                    Nota <?= htmlspecialchars($projeto["avaliacao_nota"]) ?>
                    <span>→</span>
                  </a>
                <?php else: ?>
                  <a
                    href="avaliar.php?id=<?= (int) $projeto["id"] ?>"
                    class="btn btn-primary"
                  >
                    Avaliar
                    <span>→</span>
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
<footer class="footer">
  <div class="container footer-content">
    <span>© 2026 — 1º Hackathon do Curso</span>
    <span>Engenharia de Software</span>
  </div>
</footer>
<script src="../js/script.js"></script>
</body>
</html>
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
  die("Erro ao conectar com o banco de dados.");
}

$sql = "
  SELECT
    p.id,
    p.nome AS projeto,
    p.categoria,
    e.nome AS equipe,
    AVG(a.nota) AS nota_media,
    COUNT(a.id) AS total_avaliacoes
  FROM projetos p
  INNER JOIN equipes e
    ON e.id = p.equipe_id
  LEFT JOIN avaliacoes a
    ON a.projeto_id = p.id
  GROUP BY
    p.id,
    p.nome,
    p.categoria,
    e.nome
  HAVING COUNT(a.id) > 0
  ORDER BY nota_media DESC, p.nome ASC
";
$stmt = $pdo->query($sql);
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalProjetos = count($resultados);
?>
<!doctype html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Resultados | 1º Hackathon do Curso</title>
    <link rel="stylesheet" href="css/resultados.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet" />
  </head>
  <body>
    <!-- NAVBAR -->
    <header class="navbar">
      <div class="container navbar-content">
        <a href="index.php" class="logo">
          <span class="logo-symbol">&lt;/&gt;</span>
          <span>HACKA<span>THON</span></span>
        </a>
        <nav class="nav-links">
          <a href="index.php">Início</a>
          <a href="index.php#sobre">Sobre</a>
          <a href="index.php#como-funciona">Como funciona</a>
          <a href="agenda.html">Agenda</a>
          <a href="resultados.php" class="active">Resultados</a>
        </nav>
        <a href="login.html" class="btn btn-outline">Entrar</a>
        <button class="menu-toggle" id="menuToggle">☰</button>
      </div>
    </header>
    <main>
      <!-- CABEÇALHO -->
      <section class="results-header">
        <div class="container">
          <span class="section-label">02 — RESULTADOS</span>
          <h1>Quem fez história <span>no Hackathon.</span></h1>
          <p>Confira a classificação final e conheça os projetos que se destacaram no desafio.</p>
        </div>
      </section>
      <!-- RESULTADOS -->
      <section class="results-section">
        <div class="container">
          <div class="results-status">
            <span class="status-indicator"></span>
            Resultado final
          </div>
          <!-- PÓDIO -->
          <div class="podium">
            <?php if ($totalProjetos === 0): ?>
              <div class="empty-results">
                <div class="empty-icon">&lt;/&gt;</div>
                <h2>Ainda não há resultados</h2>
                <p>A classificação será exibida após os projetos receberem avaliações dos jurados.</p>
              </div>
            <?php elseif ($totalProjetos === 1): ?>
              <?php $resultado = $resultados[0]; ?>
              <div class="podium-card first">
                <div class="podium-medal">🏆</div>
                <span class="podium-position">1º</span>
                <h3><?= htmlspecialchars($resultado["projeto"]) ?></h3>
                <span class="podium-team"><?= htmlspecialchars($resultado["equipe"]) ?></span>
                <div class="podium-score">
                  <?= number_format($resultado["nota_media"], 2, ".", "") ?>
                  <small>/ 10</small>
                </div>
              </div>
            <?php else: ?>
              <?php
              $primeiros = array_slice($resultados, 0, 3);
              $ordem = [];
              if (isset($primeiros[1])) {
                $ordem[] = [
                  "resultado" => $primeiros[1],
                  "posicao" => 2,
                  "classe" => "second",
                  "medalha" => "🥈"
                ];
              }
              if (isset($primeiros[0])) {
                $ordem[] = [
                  "resultado" => $primeiros[0],
                  "posicao" => 1,
                  "classe" => "first",
                  "medalha" => "🏆"
                ];
              }
              if (isset($primeiros[2])) {
                $ordem[] = [
                  "resultado" => $primeiros[2],
                  "posicao" => 3,
                  "classe" => "third",
                  "medalha" => "🥉"
                ];
              }
              ?>
              <?php foreach ($ordem as $item): ?>
                <?php $resultado = $item["resultado"]; ?>
                <div class="podium-card <?= $item["classe"] ?>">
                  <div class="podium-medal"><?= $item["medalha"] ?></div>
                  <span class="podium-position"><?= $item["posicao"] ?>º</span>
                  <h3><?= htmlspecialchars($resultado["projeto"]) ?></h3>
                  <span class="podium-team"><?= htmlspecialchars($resultado["equipe"]) ?></span>
                  <div class="podium-score">
                    <?= number_format($resultado["nota_media"], 2, ".", "") ?>
                    <small>/ 10</small>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <!-- RANKING -->
          <div class="ranking-header">
            <div>
              <span class="section-label">CLASSIFICAÇÃO</span>
              <h2>Ranking final</h2>
            </div>
            <span class="ranking-count">
              <?= $totalProjetos ?>
              <?= $totalProjetos === 1 ? "projeto" : "projetos" ?>
            </span>
          </div>
          <div class="ranking-list">
            <?php if ($totalProjetos === 0): ?>
              <div class="ranking-empty">
                Nenhum projeto possui avaliação registrada.
              </div>
            <?php else: ?>
              <?php foreach ($resultados as $index => $resultado): ?>
                <article class="ranking-item">
                  <div class="ranking-position">
                    <?= str_pad($index + 1, 2, "0", STR_PAD_LEFT) ?>
                  </div>
                  <div class="ranking-project">
                    <strong><?= htmlspecialchars($resultado["projeto"]) ?></strong>
                    <span><?= htmlspecialchars($resultado["equipe"]) ?></span>
                  </div>
                  <div class="ranking-category">
                    <?= htmlspecialchars($resultado["categoria"]) ?>
                  </div>
                  <div class="ranking-score">
                    <?= number_format($resultado["nota_media"], 2, ".", "") ?>
                    <small>/ 10</small>
                  </div>
                </article>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </section>
      <!-- AVISO -->
      <section class="results-notice">
        <div class="container">
          <div class="notice-card">
            <div class="notice-icon">✓</div>
            <div>
              <h3>Resultados oficiais</h3>
              <p>A classificação apresentada será publicada pela comissão organizadora após a conclusão das avaliações dos jurados.</p>
            </div>
          </div>
        </div>
      </section>
    </main>
    <!-- FOOTER -->
    <footer class="footer">
      <div class="container footer-content">
        <div class="footer-brand">
          <a href="index.php" class="logo">
            <span class="logo-symbol">&lt;/&gt;</span>
            <span>HACKA<span>THON</span></span>
          </a>
          <p>Sistema de apoio ao 1º Hackathon do curso.</p>
        </div>
        <div class="footer-links">
          <div>
            <strong>Evento</strong>
            <a href="index.php#sobre">Sobre</a>
            <a href="index.php#como-funciona">Como funciona</a>
            <a href="agenda.html">Agenda</a>
          </div>
          <div>
            <strong>Sistema</strong>
            <a href="login.html">Entrar</a>
            <a href="resultados.php">Resultados</a>
          </div>
        </div>
      </div>
      <div class="container footer-bottom">
        <span>© 2026 — 1º Hackathon do Curso</span>
        <span>Engenharia de Software</span>
      </div>
    </footer>
    <script src="js/script.js"></script>
  </body>
</html>
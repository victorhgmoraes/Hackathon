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

$sql = "
    SELECT
        u.id AS usuario_id,
        u.nome AS usuario_nome,
        u.email,
        u.matricula,
        e.id AS equipe_id,
        e.nome AS equipe_nome
    FROM usuarios u
    LEFT JOIN participantes p
        ON p.usuario_id = u.id
    LEFT JOIN equipes e
        ON e.id = p.equipe_id
    WHERE u.id = :usuario_id
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
  ":usuario_id" => $usuarioId
]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
  session_destroy();
  header("Location: ../login.html");
  exit;
}

$nomeUsuario = $usuario["usuario_nome"];
$equipeId = $usuario["equipe_id"];
$nomeEquipe = $usuario["equipe_nome"];

$integrantes = [];
if ($equipeId) {
  $sqlIntegrantes = "
        SELECT
            u.id,
            u.nome
        FROM participantes p
        INNER JOIN usuarios u
            ON u.id = p.usuario_id
        WHERE p.equipe_id = :equipe_id
        ORDER BY u.nome
    ";
  $stmtIntegrantes = $pdo->prepare($sqlIntegrantes);
  $stmtIntegrantes->execute([
    ":equipe_id" => $equipeId
  ]);
  $integrantes = $stmtIntegrantes->fetchAll(PDO::FETCH_ASSOC);
}

$projeto = null;
if ($equipeId) {
  $sqlProjeto = "
        SELECT
            id,
            nome,
            descricao,
            categoria
        FROM projetos
        WHERE equipe_id = :equipe_id
        LIMIT 1
    ";
  $stmtProjeto = $pdo->prepare($sqlProjeto);
  $stmtProjeto->execute([
    ":equipe_id" => $equipeId
  ]);
  $projeto = $stmtProjeto->fetch(PDO::FETCH_ASSOC);
}

?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard | 1º Hackathon do Curso</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap"
    rel="stylesheet" />
</head>
<body class="dashboard-page">
  <header class="navbar">
    <div class="container navbar-content">
      <a href="../index.html" class="logo">
        <span class="logo-symbol">
          &lt;/&gt;
        </span>
        <span>
          HACKA<span>THON</span>
        </span>
      </a>
      <div class="dashboard-user">
        <span>
          <?= htmlspecialchars($nomeUsuario) ?>
        </span>
        <a href="../logout.php" class="logout-button">
          Sair
        </a>
      </div>
    </div>
  </header>
  <main class="dashboard-main">
    <div class="dashboard-container">
      <section class="dashboard-header">
        <span class="section-label">
          ÁREA DO PARTICIPANTE
        </span>
        <h1>
          Olá,
          <span>
            <?= htmlspecialchars($nomeUsuario) ?>.
          </span>
        </h1>
        <p>
          Acompanhe sua equipe e o projeto do Hackathon.
        </p>
      </section>
      <section class="dashboard-section">
        <div class="dashboard-section-header">
          <div>
            <span class="section-label">
              EQUIPE
            </span>
            <h2>
              Minha equipe
            </h2>
          </div>
          <a href="equipe.php" class="btn dashboard-small-button">
            <?= $nomeEquipe ? "Gerenciar equipe" : "Criar ou entrar em equipe" ?>
          </a>
        </div>
        <div class="team-card">
          <div class="team-info">
            <span class="card-label">
              NOME DA EQUIPE
            </span>
            <?php if ($nomeEquipe): ?>
              <h3>
                <?= htmlspecialchars($nomeEquipe) ?>
              </h3>
              <span class="team-status">
                INSCRITA
              </span>
            <?php else: ?>
              <h3>
                Você ainda não possui uma equipe.
              </h3>
              <span class="team-status">
                SEM EQUIPE
              </span>
            <?php endif; ?>
          </div>
          <div class="team-members">
            <span class="card-label">
              INTEGRANTES
            </span>
            <div class="member-list">
              <?php if (count($integrantes) > 0): ?>
                <?php foreach ($integrantes as $indice => $integrante): ?>
                  <div class="member">
                    <span class="member-number">
                      <?= str_pad(
                        $indice + 1,
                        2,
                        "0",
                        STR_PAD_LEFT
                      ) ?>
                    </span>
                    <span>
                      <?= htmlspecialchars($integrante["nome"]) ?>
                    </span>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="member">
                  <span class="member-number">
                    01
                  </span>
                  <span>
                    Nenhum integrante cadastrado.
                  </span>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </section>
      <section class="dashboard-section">
        <div class="dashboard-section-header">
          <div>
            <span class="section-label">
              PROJETO
            </span>
            <h2>
              Meu projeto
            </h2>
          </div>
        </div>
        <div class="project-card">
          <?php if ($projeto): ?>
            <div class="project-status">
              <span class="status-dot"></span>
              PROJETO SUBMETIDO
            </div>
            <div class="project-content">
              <h3>
                <?= htmlspecialchars($projeto["nome"]) ?>
              </h3>
              <p>
                <?= htmlspecialchars($projeto["descricao"]) ?>
              </p>
              <p>
                <strong>
                  Categoria:
                </strong>
                <?= htmlspecialchars($projeto["categoria"]) ?>
              </p>
            </div>
          <?php else: ?>
            <div class="project-status">
              <span class="status-dot"></span>
              NÃO SUBMETIDO
            </div>
            <div class="project-content">
              <h3>
                Seu projeto ainda não foi submetido.
              </h3>
              <p>
                Cadastre as informações do projeto da sua
                equipe para participar do Hackathon.
              </p>
              <?php if ($equipeId): ?>
                <a href="submissao.html" class="btn project-button">
                  Submeter projeto
                  <span>
                    →
                  </span>
                </a>
              <?php else: ?>
                <p>
                  Você precisa estar em uma equipe
                  antes de submeter um projeto.
                </p>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </section>
      <section class="dashboard-section">
        <div class="dashboard-section-header">
          <div>
            <span class="section-label">
              EVENTO
            </span>
            <h2>
              Informações
            </h2>
          </div>
          <a href="../agenda.html" class="dashboard-link">
            Ver agenda →
          </a>
        </div>
        <div class="event-info">
          <div>
            <span class="card-label">
              PRÓXIMA ATIVIDADE
            </span>
            <strong>
              Desenvolvimento dos projetos
            </strong>
          </div>
          <div>
            <span class="card-label">
              DATA
            </span>
            <strong>
              Consulte a agenda
            </strong>
          </div>
        </div>
      </section>
    </div>
  </main>
  <footer class="login-footer">
    <span>
      © 2026 — 1º Hackathon do Curso
    </span>
  </footer>
  <script src="../js/script.js"></script>
</body>
</html>
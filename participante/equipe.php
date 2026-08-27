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
$mensagem = "";
$tipoMensagem = "";

$sqlUsuario = "
  SELECT u.nome, p.equipe_id
  FROM usuarios u
  LEFT JOIN participantes p ON p.usuario_id = u.id
  WHERE u.id = :usuario_id
  LIMIT 1
";

$stmtUsuario = $pdo->prepare($sqlUsuario);
$stmtUsuario->execute([
  ":usuario_id" => $usuarioId
]);

$usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
  session_destroy();
  header("Location: ../login.html");
  exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $acao = $_POST["acao"] ?? "";

  if ($acao === "sair") {
    if (!$usuario["equipe_id"]) {
      $mensagem = "Você não está em nenhuma equipe.";
      $tipoMensagem = "erro";
    } else {
      $sqlSair = "
        DELETE FROM participantes
        WHERE usuario_id = :usuario_id
      ";

      $stmtSair = $pdo->prepare($sqlSair);
      $stmtSair->execute([
        ":usuario_id" => $usuarioId
      ]);

      header("Location: dashboard.php");
      exit;
    }
  } elseif ($usuario["equipe_id"]) {
    $mensagem = "Você já está participando de uma equipe.";
    $tipoMensagem = "erro";
  } elseif ($acao === "criar") {
    $nomeEquipe = trim($_POST["nome_equipe"] ?? "");

    if ($nomeEquipe === "") {
      $mensagem = "Informe o nome da equipe.";
      $tipoMensagem = "erro";
    } else {
      $sqlEquipe = "INSERT INTO equipes (nome) VALUES (:nome)";
      $stmtEquipe = $pdo->prepare($sqlEquipe);
      $stmtEquipe->execute([
        ":nome" => $nomeEquipe
      ]);

      $equipeId = $pdo->lastInsertId();

      $sqlParticipante = "
        INSERT INTO participantes (usuario_id, equipe_id)
        VALUES (:usuario_id, :equipe_id)
      ";

      $stmtParticipante = $pdo->prepare($sqlParticipante);
      $stmtParticipante->execute([
        ":usuario_id" => $usuarioId,
        ":equipe_id" => $equipeId
      ]);

      header("Location: dashboard.php");
      exit;
    }
  } elseif ($acao === "entrar") {
    $equipeId = (int) ($_POST["equipe_id"] ?? 0);

    if ($equipeId <= 0) {
      $mensagem = "Selecione uma equipe.";
      $tipoMensagem = "erro";
    } else {
      $sqlEquipe = "
        SELECT id
        FROM equipes
        WHERE id = :equipe_id
        LIMIT 1
      ";

      $stmtEquipe = $pdo->prepare($sqlEquipe);
      $stmtEquipe->execute([
        ":equipe_id" => $equipeId
      ]);

      if (!$stmtEquipe->fetch()) {
        $mensagem = "A equipe selecionada não existe.";
        $tipoMensagem = "erro";
      } else {
        $sqlParticipante = "
          INSERT INTO participantes (usuario_id, equipe_id)
          VALUES (:usuario_id, :equipe_id)
        ";

        $stmtParticipante = $pdo->prepare($sqlParticipante);
        $stmtParticipante->execute([
          ":usuario_id" => $usuarioId,
          ":equipe_id" => $equipeId
        ]);

        header("Location: dashboard.php");
        exit;
      }
    }
  }
}

$sqlEquipes = "
  SELECT
    e.id,
    e.nome,
    COUNT(p.id) AS integrantes
  FROM equipes e
  LEFT JOIN participantes p ON p.equipe_id = e.id
  GROUP BY e.id, e.nome
  ORDER BY e.nome
";
$stmtEquipes = $pdo->query($sqlEquipes);
$equipes = $stmtEquipes->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Equipe | 1º Hackathon do Curso</title>
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
      <a href="dashboard.php" class="back-home">
        ← Voltar para o dashboard
      </a>
    </div>
  </header>
  <main class="login-main">
    <div class="login-container">
      <section class="login-intro">
        <span class="section-label">EQUIPE</span>
        <h1>
          Monte sua
          <span>equipe.</span>
        </h1>
        <p>
          Crie uma nova equipe ou entre em uma equipe existente para participar do Hackathon.
        </p>
        <div class="login-features">
          <div class="login-feature">
            <span class="feature-number">01</span>
            <div>
              <strong>Criar equipe</strong>
              <p>Crie uma equipe e torne-se automaticamente seu primeiro integrante.</p>
            </div>
          </div>
          <div class="login-feature">
            <span class="feature-number">02</span>
            <div>
              <strong>Entrar</strong>
              <p>Escolha uma equipe existente e participe dela.</p>
            </div>
          </div>
          <div class="login-feature">
            <span class="feature-number">03</span>
            <div>
              <strong>Projeto</strong>
              <p>Depois de formar sua equipe, vocês poderão submeter o projeto.</p>
            </div>
          </div>
        </div>
      </section>
      <section class="login-card">
        <div class="login-card-header">
          <span class="login-icon">&lt;/&gt;</span>
          <div>
            <h2>Minha equipe</h2>
            <p>Crie ou entre em uma equipe</p>
          </div>
        </div>
        <?php if ($mensagem): ?>
          <div class="login-message show">
            <?= htmlspecialchars($mensagem) ?>
          </div>
        <?php endif; ?>
        <?php if (!$usuario["equipe_id"]): ?>
          <form method="POST">
            <input type="hidden" name="acao" value="criar" />
            <div class="form-group">
              <label for="nome_equipe">Nome da equipe</label>
              <input type="text" id="nome_equipe" name="nome_equipe" placeholder="Digite o nome da equipe" required />
            </div>
            <button type="submit" class="btn login-button">
              Criar equipe
              <span>→</span>
            </button>
          </form>
          <div class="login-divider">
            <span>ou</span>
          </div>
          <form method="POST">
            <input type="hidden" name="acao" value="entrar" />
            <div class="form-group">
              <label for="equipe_id">Entrar em uma equipe</label>
              <select id="equipe_id" name="equipe_id" required>
                <option value="">Selecione uma equipe</option>
                <?php foreach ($equipes as $equipe): ?>
                  <option value="<?= $equipe["id"] ?>">
                    <?= htmlspecialchars($equipe["nome"]) ?> — <?= $equipe["integrantes"] ?> integrante(s)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <button type="submit" class="btn login-button">
              Entrar na equipe
              <span>→</span>
            </button>
          </form>
        <?php else: ?>
          <div class="login-message show">
            Você já está em uma equipe. Volte ao dashboard para continuar.
          </div>
          <a href="dashboard.php" class="btn login-button">
            Voltar para o dashboard
            <span>→</span>
          </a>
          <form method="POST">
            <input type="hidden" name="acao" value="sair" />
            <button type="submit" class="btn login-button">
              Sair da equipe
              <span>×</span>
            </button>
          </form>
        <?php endif; ?>
      </section>
    </div>
  </main>
  <footer class="login-footer">
    <span>© 2026 — 1º Hackathon do Curso</span>
  </footer>
  <script src="../js/script.js"></script>
</body>
</html>
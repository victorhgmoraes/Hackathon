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
  $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Erro ao conectar com o banco de dados.");
}

$usuarioId = $_SESSION["usuario_id"];
$mensagem = "";
$tipoMensagem = "";

$sqlUsuario = "
  SELECT u.id, u.nome, u.email, u.matricula, p.equipe_id
  FROM usuarios u
  LEFT JOIN participantes p ON p.usuario_id = u.id
  WHERE u.id = :usuario_id
  LIMIT 1
";

$stmtUsuario = $pdo->prepare($sqlUsuario);
$stmtUsuario->execute([":usuario_id" => $usuarioId]);
$usuario = $stmtUsuario->fetch();

if (!$usuario) {
  session_destroy();
  header("Location: ../login.html");
  exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $acao = $_POST["acao"] ?? "";

  if ($acao === "excluir_projeto") {
    if (!$usuario["equipe_id"]) {
      $mensagem = "Você não está participando de nenhuma equipe.";
      $tipoMensagem = "erro";
    } else {
      try {
        $pdo->beginTransaction();

        $sqlProjeto = "
          SELECT id
          FROM projetos
          WHERE equipe_id = :equipe_id
          LIMIT 1
        ";

        $stmtProjeto = $pdo->prepare($sqlProjeto);
        $stmtProjeto->execute([":equipe_id" => $usuario["equipe_id"]]);
        $projeto = $stmtProjeto->fetch();

        if (!$projeto) {
          $pdo->rollBack();
          $mensagem = "Sua equipe não possui um projeto cadastrado.";
          $tipoMensagem = "erro";
        } else {
          $sqlAvaliacoes = "DELETE FROM avaliacoes WHERE projeto_id = :projeto_id";
          $stmtAvaliacoes = $pdo->prepare($sqlAvaliacoes);
          $stmtAvaliacoes->execute([":projeto_id" => $projeto["id"]]);

          $sqlExcluir = "
            DELETE FROM projetos
            WHERE id = :projeto_id AND equipe_id = :equipe_id
          ";
          $stmtExcluir = $pdo->prepare($sqlExcluir);
          $stmtExcluir->execute([
            ":projeto_id" => $projeto["id"],
            ":equipe_id" => $usuario["equipe_id"]
          ]);

          $pdo->commit();
          $mensagem = "Projeto excluído com sucesso.";
          $tipoMensagem = "sucesso";
        }
      } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
          $pdo->rollBack();
        }
        $mensagem = "Não foi possível excluir o projeto.";
        $tipoMensagem = "erro";
      }
    }
  } elseif ($acao === "sair") {
    if (!$usuario["equipe_id"]) {
      $mensagem = "Você não está participando de nenhuma equipe.";
      $tipoMensagem = "erro";
    } else {
      $sqlSair = "DELETE FROM participantes WHERE usuario_id = :usuario_id";
      $stmtSair = $pdo->prepare($sqlSair);
      $stmtSair->execute([":usuario_id" => $usuarioId]);
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
    } elseif (mb_strlen($nomeEquipe) > 100) {
      $mensagem = "O nome da equipe deve ter no máximo 100 caracteres.";
      $tipoMensagem = "erro";
    } else {
      try {
        $pdo->beginTransaction();

        $sqlEquipe = "INSERT INTO equipes (nome) VALUES (:nome)";
        $stmtEquipe = $pdo->prepare($sqlEquipe);
        $stmtEquipe->execute([":nome" => $nomeEquipe]);
        $equipeId = $pdo->lastInsertId();

        $sqlParticipante = "INSERT INTO participantes (usuario_id, equipe_id) VALUES (:usuario_id, :equipe_id)";
        $stmtParticipante = $pdo->prepare($sqlParticipante);
        $stmtParticipante->execute([
          ":usuario_id" => $usuarioId,
          ":equipe_id" => $equipeId
        ]);

        $pdo->commit();
        header("Location: dashboard.php");
        exit;
      } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
          $pdo->rollBack();
        }
        $mensagem = "Não foi possível criar a equipe.";
        $tipoMensagem = "erro";
      }
    }
  } elseif ($acao === "entrar") {
    $equipeId = (int) ($_POST["equipe_id"] ?? 0);

    if ($equipeId <= 0) {
      $mensagem = "Selecione uma equipe.";
      $tipoMensagem = "erro";
    } else {
      $sqlEquipe = "SELECT id FROM equipes WHERE id = :equipe_id LIMIT 1";
      $stmtEquipe = $pdo->prepare($sqlEquipe);
      $stmtEquipe->execute([":equipe_id" => $equipeId]);
      $equipeExiste = $stmtEquipe->fetch();

      if (!$equipeExiste) {
        $mensagem = "A equipe selecionada não existe.";
        $tipoMensagem = "erro";
      } else {
        try {
          $sqlParticipante = "INSERT INTO participantes (usuario_id, equipe_id) VALUES (:usuario_id, :equipe_id)";
          $stmtParticipante = $pdo->prepare($sqlParticipante);
          $stmtParticipante->execute([
            ":usuario_id" => $usuarioId,
            ":equipe_id" => $equipeId
          ]);
          header("Location: dashboard.php");
          exit;
        } catch (PDOException $e) {
          if ($e->getCode() === "23000") {
            $mensagem = "Você já está participando de uma equipe.";
          } else {
            $mensagem = "Não foi possível entrar na equipe.";
          }
          $tipoMensagem = "erro";
        }
      }
    }
  }
}

$projetoAtual = null;

if ($usuario["equipe_id"]) {
  $sqlProjetoAtual = "
    SELECT id, nome, descricao, categoria, repositorio
    FROM projetos
    WHERE equipe_id = :equipe_id
    LIMIT 1
  ";
  $stmtProjetoAtual = $pdo->prepare($sqlProjetoAtual);
  $stmtProjetoAtual->execute([":equipe_id" => $usuario["equipe_id"]]);
  $projetoAtual = $stmtProjetoAtual->fetch();
}

$sqlEquipes = "
  SELECT e.id, e.nome, COUNT(p.id) AS integrantes
  FROM equipes e
  LEFT JOIN participantes p ON p.equipe_id = e.id
  GROUP BY e.id, e.nome
  ORDER BY e.nome ASC
";

$stmtEquipes = $pdo->query($sqlEquipes);
$equipes = $stmtEquipes->fetchAll();
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Equipe | 1º Hackathon do Curso</title>
  <link rel="stylesheet" href="../css/equipe.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet" />
</head>
<body class="team-page">
  <header class="navbar">
    <div class="container navbar-content">
      <a href="../index.html" class="logo">
        <span class="logo-symbol">&lt;/&gt;</span>
        <span>HACKA<span>THON</span></span>
      </a>
      <div class="navbar-user">
        <span><?= htmlspecialchars($usuario["nome"]) ?></span>
        <a href="../logout.php" class="logout-button">Sair</a>
      </div>
    </div>
  </header>
  <main class="team-main">
    <div class="container team-container">
      <section class="team-header">
        <div class="team-header-content">
          <span class="section-label">ÁREA DO PARTICIPANTE</span>
          <h1>Monte sua <span>equipe.</span></h1>
          <p>Crie uma nova equipe ou entre em uma equipe existente para participar do Hackathon.</p>
        </div>
        <a href="dashboard.php" class="back-button">← Voltar para o dashboard</a>
      </section>
      <?php if ($mensagem): ?>
        <div class="form-message show <?= htmlspecialchars($tipoMensagem) ?>">
          <span class="message-icon">!</span>
          <span><?= htmlspecialchars($mensagem) ?></span>
        </div>
      <?php endif; ?>
      <?php if (!$usuario["equipe_id"]): ?>
        <section class="team-options">
          <article class="team-card">
            <div class="card-number">01</div>
            <div class="card-content">
              <span class="card-label">NOVA EQUIPE</span>
              <h2>Crie sua equipe</h2>
              <p>Dê um nome para sua equipe e torne-se automaticamente seu primeiro integrante.</p>
              <form method="POST">
                <input type="hidden" name="acao" value="criar" />
                <div class="form-group">
                  <label for="nome_equipe">Nome da equipe</label>
                  <input type="text" id="nome_equipe" name="nome_equipe" placeholder="Ex.: Code Masters" maxlength="100" required />
                </div>
                <button type="submit" class="btn btn-primary">
                  Criar equipe
                  <span>→</span>
                </button>
              </form>
            </div>
          </article>
          <div class="team-divider">
            <span>ou</span>
          </div>
          <article class="team-card">
            <div class="card-number">02</div>
            <div class="card-content">
              <span class="card-label">EQUIPE EXISTENTE</span>
              <h2>Entre em uma equipe</h2>
              <p>Escolha uma equipe existente e participe dela para desenvolver o projeto.</p>
              <form method="POST">
                <input type="hidden" name="acao" value="entrar" />
                <div class="form-group">
                  <label for="equipe_id">Equipe</label>
                  <select id="equipe_id" name="equipe_id" required>
                    <option value="">Selecione uma equipe</option>
                    <?php foreach ($equipes as $equipe): ?>
                      <option value="<?= (int) $equipe["id"] ?>">
                        <?= htmlspecialchars($equipe["nome"]) ?> — <?= (int) $equipe["integrantes"] ?> integrante(s)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <button type="submit" class="btn btn-primary">
                  Entrar na equipe
                  <span>→</span>
                </button>
              </form>
            </div>
          </article>
        </section>
        <section class="team-info">
          <div class="info-item">
            <span class="info-number">01</span>
            <div>
              <strong>Forme sua equipe</strong>
              <p>Reúna os participantes que irão trabalhar juntos durante o Hackathon.</p>
            </div>
          </div>
          <div class="info-item">
            <span class="info-number">02</span>
            <div>
              <strong>Desenvolva o projeto</strong>
              <p>Depois de formar a equipe, vocês poderão cadastrar e submeter o projeto.</p>
            </div>
          </div>
          <div class="info-item">
            <span class="info-number">03</span>
            <div>
              <strong>Participe do Hackathon</strong>
              <p>Acompanhe as informações e atividades disponíveis para sua equipe.</p>
            </div>
          </div>
        </section>
      <?php else: ?>
        <section class="current-team">
          <div class="current-team-top">
            <span class="card-label">MINHA EQUIPE</span>
            <span class="team-status">PARTICIPANDO</span>
          </div>
          <div class="current-team-icon">&lt;/&gt;</div>
          <h2>Você já está em uma equipe.</h2>
          <p>Para continuar, volte ao dashboard e acompanhe as informações da sua equipe e do projeto.</p>
          <?php if ($projetoAtual): ?>
            <div class="current-project">
              <span class="card-label">PROJETO DA EQUIPE</span>
              <h3><?= htmlspecialchars($projetoAtual["nome"]) ?></h3>
              <p><?= htmlspecialchars($projetoAtual["descricao"]) ?></p>
              <div class="project-details">
                <span>
                  <strong>Categoria:</strong>
                  <?= htmlspecialchars($projetoAtual["categoria"]) ?>
                </span>
                <?php if (!empty($projetoAtual["repositorio"])): ?>
                  <a href="<?= htmlspecialchars($projetoAtual["repositorio"]) ?>" target="_blank" rel="noopener noreferrer">
                    Ver repositório →
                  </a>
                <?php endif; ?>
              </div>
            </div>
          <?php else: ?>
            <div class="current-project-empty">
              <span class="card-label">PROJETO</span>
              <p>Sua equipe ainda não possui um projeto cadastrado.</p>
              <a href="submissao.html" class="btn btn-primary">
                Submeter projeto
                <span>→</span>
              </a>
            </div>
          <?php endif; ?>
          <div class="current-team-actions">
            <a href="dashboard.php" class="btn btn-primary">
              Voltar para o dashboard
              <span>→</span>
            </a>
            <?php if ($projetoAtual): ?>
              <form method="POST">
                <input type="hidden" name="acao" value="excluir_projeto" />
                <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir este projeto?\n\nTodas as avaliações relacionadas a ele também serão excluídas.\n\nEssa ação não pode ser desfeita.');">
                  Excluir projeto
                  <span>×</span>
                </button>
              </form>
            <?php endif; ?>

            <form method="POST">
              <input type="hidden" name="acao" value="sair" />
              <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja sair da equipe?');">
                Sair da equipe
                <span>×</span>
              </button>
            </form>
          </div>
        </section>
      <?php endif; ?>
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
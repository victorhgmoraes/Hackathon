<?php
require_once __DIR__ . "/config.php";
$token = $_GET["token"] ?? "";

if ($token === "") {
    die("Link de recuperação inválido.");
}

$sql = "
    SELECT
        recuperacao_senha.id,
        recuperacao_senha.usuario_id,
        recuperacao_senha.expira_em,
        usuarios.email
    FROM recuperacao_senha
    INNER JOIN usuarios
        ON usuarios.id = recuperacao_senha.usuario_id
    WHERE recuperacao_senha.token = :token
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":token" => $token
]);

$recuperacao = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$recuperacao) {
    die("Link de recuperação inválido ou já utilizado.");
}

if (strtotime($recuperacao["expira_em"]) < time()) {
    $sql = "DELETE FROM recuperacao_senha WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":id" => $recuperacao["id"]
    ]);

    die("Este link de recuperação expirou.");
}

$mensagem = "";
$tipoMensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    echo "POST RECEBIDO";
    exit;

    $senha = $_POST["senha"] ?? "";
    $confirmarSenha = $_POST["confirmar_senha"] ?? "";

    if ($senha === "" || $confirmarSenha === "") {

        $mensagem = "Preencha os dois campos de senha.";
        $tipoMensagem = "erro";

    } elseif (strlen($senha) < 6) {

        $mensagem = "A senha deve ter pelo menos 6 caracteres.";
        $tipoMensagem = "erro";

    } elseif ($senha !== $confirmarSenha) {

        $mensagem = "As senhas não são iguais.";
        $tipoMensagem = "erro";

    } else {

        $novaSenha = password_hash($senha, PASSWORD_DEFAULT);

        $sql = "
            UPDATE usuarios
            SET senha = :senha
            WHERE id = :usuario_id
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":senha" => $novaSenha,
            ":usuario_id" => $recuperacao["usuario_id"]
        ]);

        if ($stmt->rowCount() === 0) {

            $mensagem = "Não foi possível alterar a senha.";
            $tipoMensagem = "erro";

        } else {

            $sql = "DELETE FROM recuperacao_senha WHERE id = :id";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ":id" => $recuperacao["id"]
            ]);

            $mensagem = "Senha alterada com sucesso!";
            $tipoMensagem = "sucesso";
        }
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta
        name="description"
        content="Redefina sua senha do 1º Hackathon do Curso."
    >
    <title>Redefinir senha | 1º Hackathon do Curso</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap"
        rel="stylesheet"
    >
</head>
<body>
<header class="navbar">
    <div class="container navbar-content">
        <a href="index.php" class="logo">
            <span class="logo-symbol">&lt;/&gt;</span>
            <span>HACKA<span>THON</span></span>
        </a>
        <a href="login.html" class="back-link">
            ← Voltar para o login
        </a>
    </div>
</header>
<main class="login-main">
    <div class="container login-container">
        <section class="login-intro">
            <span class="section-label">
                RECUPERAÇÃO DE ACESSO
            </span>
            <h1>
                Crie uma nova <span>senha.</span>
            </h1>
            <p>
                Defina uma nova senha para continuar acessando
                sua conta no Hackathon.
            </p>
            <div class="login-info">
                <div class="info-item">
                    <span class="info-number">01</span>
                    <div>
                        <h3>Nova senha</h3>
                        <p>
                            Crie uma senha com pelo menos 6 caracteres.
                        </p>
                    </div>
                </div>
                <div class="info-item">
                    <span class="info-number">02</span>
                    <div>
                        <h3>Confirme a senha</h3>
                        <p>
                            Digite a mesma senha novamente.
                        </p>
                    </div>
                </div>
                <div class="info-item">
                    <span class="info-number">03</span>
                    <div>
                        <h3>Acesse sua conta</h3>
                        <p>
                            Depois da alteração, faça login normalmente.
                        </p>
                    </div>
                </div>
            </div>
        </section>
        <section class="login-card">
            <div class="card-header">
                <div class="card-icon">
                    &lt;/&gt;
                </div>
                <div>
                    <span class="section-label">
                        NOVA SENHA
                    </span>
                    <h2>
                        Redefinir senha
                    </h2>
                    <p>
                        Digite sua nova senha abaixo.
                    </p>
                </div>
            </div>
            <?php if ($mensagem !== ""): ?>
                <div class="form-message <?= $tipoMensagem === "sucesso" ? "success" : "error" ?>">
                    <?= htmlspecialchars($mensagem) ?>
                </div>
            <?php endif; ?>
            <?php if ($tipoMensagem !== "sucesso"): ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="senha">
                            Nova senha
                        </label>
                        <div class="password-input">
                            <input
                                type="password"
                                id="senha"
                                name="senha"
                                placeholder="Digite sua nova senha"
                                autocomplete="new-password"
                                required
                            >
                            <button
                                type="button"
                                class="password-toggle"
                                onclick="togglePassword('senha', this)"
                            >
                                Mostrar
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="confirmar_senha">
                            Confirmar nova senha
                        </label>
                        <div class="password-input">
                            <input
                                type="password"
                                id="confirmar_senha"
                                name="confirmar_senha"
                                placeholder="Digite a senha novamente"
                                autocomplete="new-password"
                                required
                            >
                            <button
                                type="button"
                                class="password-toggle"
                                onclick="togglePassword('confirmar_senha', this)"
                            >
                                Mostrar
                            </button>
                        </div>
                    </div>
                    <button
                        type="submit"
                        class="btn btn-primary login-button"
                    >
                        Alterar senha <span>→</span>
                    </button>

                </form>
            <?php else: ?>
                <a
                    href="login.html"
                    class="btn btn-primary login-button"
                >
                    Ir para o login <span>→</span>
                </a>
            <?php endif; ?>
        </section>
    </div>
</main>
<footer class="footer">
    <div class="container footer-content">
        <span>
            © 2026 — 1º Hackathon do Curso
        </span>
        <span>
            Engenharia de Software
        </span>
    </div>
</footer>
<script>
function togglePassword(id, button) {

    const input = document.getElementById(id);

    if (input.type === "password") {
        input.type = "text";
        button.textContent = "Ocultar";
    } else {
        input.type = "password";
        button.textContent = "Mostrar";
    }
}
</script>
</body>
</html>
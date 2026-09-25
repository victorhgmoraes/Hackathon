<?php

require_once __DIR__ . "/config.php";

header("Content-Type: application/json; charset=UTF-8");

$dados = json_decode(file_get_contents("php://input"), true);

$email = strtolower(trim($dados["email"] ?? ""));

if ($email === "") {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Informe o seu e-mail."
    ]);
    exit;
}

try {

    $sql = "SELECT id, nome, email
            FROM usuarios
            WHERE email = :email
            LIMIT 1";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":email" => $email
    ]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Não encontramos uma conta com esse e-mail."
        ]);
        exit;
    }

    $token = bin2hex(random_bytes(32));

    $expiraEm = date("Y-m-d H:i:s", time() + 1800);

    $sql = "DELETE FROM recuperacao_senha
            WHERE usuario_id = :usuario_id";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":usuario_id" => $usuario["id"]
    ]);

    $sql = "INSERT INTO recuperacao_senha
            (usuario_id, token, expira_em)
            VALUES
            (:usuario_id, :token, :expira_em)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":usuario_id" => $usuario["id"],
        ":token" => $token,
        ":expira_em" => $expiraEm
    ]);

    $appUrl = rtrim(
        getenv("APP_URL") ?: ($_ENV["APP_URL"] ?? ""),
        "/"
    );

    if ($appUrl === "") {
        $appUrl = "https://" . $_SERVER["HTTP_HOST"];
    }

    $link = $appUrl . "/redefinir-senha.php?token=" . urlencode($token);

    $apiKey = getenv("RESEND_API_KEY");

    if (!$apiKey) {
        throw new Exception("RESEND_API_KEY não configurada.");
    }

    $dadosEmail = [
        "from" => "1º Hackathon do Curso <onboarding@resend.dev>",
        "to" => [$usuario["email"]],
        "subject" => "Recuperação de senha - 1º Hackathon do Curso",
        "html" => "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto;'>
                <h2>Olá, {$usuario["nome"]}!</h2>

                <p>
                    Recebemos uma solicitação para redefinir a senha
                    da sua conta no 1º Hackathon do Curso.
                </p>

                <p>
                    Clique no botão abaixo para criar uma nova senha:
                </p>

                <p>
                    <a
                        href='{$link}'
                        style='
                            display: inline-block;
                            padding: 12px 24px;
                            background: #019f48;
                            color: white;
                            text-decoration: none;
                            border-radius: 6px;
                            font-weight: bold;
                        '
                    >
                        Redefinir minha senha
                    </a>
                </p>

                <p>
                    Este link será válido por <strong>30 minutos</strong>.
                </p>

                <p>
                    Se você não solicitou a recuperação da senha,
                    pode ignorar este e-mail.
                </p>

                <p>
                    1º Hackathon do Curso
                </p>
            </div>
        ",
        "text" =>
            "Olá, {$usuario["nome"]}!\n\n" .
            "Acesse o link abaixo para redefinir sua senha:\n\n" .
            $link . "\n\n" .
            "Este link será válido por 30 minutos."
    ];

    $ch = curl_init("https://api.resend.com/emails");

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . $apiKey,
            "Content-Type: application/json"
        ],
        CURLOPT_POSTFIELDS => json_encode($dadosEmail),
        CURLOPT_TIMEOUT => 20
    ]);

    $resposta = curl_exec($ch);

    if ($resposta === false) {
        $erro = curl_error($ch);
        curl_close($ch);

        throw new Exception("Erro de conexão com o Resend: " . $erro);
    }

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($status < 200 || $status >= 300) {
        throw new Exception("Resend retornou HTTP " . $status . ": " . $resposta);
    }

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Enviamos um link de recuperação para o seu e-mail."
    ]);

} catch (Exception $e) {

    echo json_encode([
        "sucesso" => false,
        "mensagem" => $e->getMessage()
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro no banco de dados."
    ]);
}
<?php

require_once __DIR__ . "/config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Banco e recuperação funcionaram até antes do envio do e-mail."
    ]);
    exit;
    
    $mail = new PHPMailer(true);

    $mail->isSMTP();

    $mail->Host = getenv("MAIL_HOST") ?: ($_ENV["MAIL_HOST"] ?? "");
    $mail->SMTPAuth = true;

    $mail->Username = getenv("MAIL_USERNAME") ?: ($_ENV["MAIL_USERNAME"] ?? "");
    $mail->Password = getenv("MAIL_PASSWORD") ?: ($_ENV["MAIL_PASSWORD"] ?? "");

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

    $mail->Port = getenv("MAIL_PORT") ?: ($_ENV["MAIL_PORT"] ?? "587");

    $mail->CharSet = "UTF-8";

    $mail->setFrom(
        $mail->Username,
        getenv("MAIL_FROM_NAME") ?: ($_ENV["MAIL_FROM_NAME"] ?? "1º Hackathon do Curso")
    );

    $mail->addAddress(
        $usuario["email"],
        $usuario["nome"]
    );

    $mail->isHTML(true);

    $mail->Subject = "Recuperação de senha - 1º Hackathon do Curso";

    $mail->Body = "
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
    ";

    $mail->AltBody =
        "Olá, {$usuario["nome"]}!\n\n" .
        "Acesse o link abaixo para redefinir sua senha:\n\n" .
        $link . "\n\n" .
        "Este link será válido por 30 minutos.";

    $mail->send();

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Enviamos um link de recuperação para o seu e-mail."
    ]);

} catch (Exception $e) {

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro SMTP: " . $e->getMessage()
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro banco: " . $e->getMessage()
    ]);
}
const forgotPasswordForm = document.getElementById("forgotPasswordForm");
const forgotPasswordMessage = document.getElementById("forgotPasswordMessage");

if (forgotPasswordForm) {
  forgotPasswordForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    const email = document.getElementById("email").value.trim();

    forgotPasswordMessage.className = "form-message";

    if (!email) {
      forgotPasswordMessage.textContent = "Informe seu e-mail.";
      forgotPasswordMessage.classList.add("show", "error");
      return;
    }

    forgotPasswordMessage.textContent = "Enviando...";
    forgotPasswordMessage.classList.add("show");

    try {
      const resposta = await fetch("esqueci-senha.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          email: email,
        }),
      });

      const texto = await resposta.text();

      let resultado;

      try {
        resultado = JSON.parse(texto);
      } catch (erro) {
        console.error("Resposta recebida do servidor:", texto);

        throw new Error(
          "O servidor não retornou uma resposta JSON válida."
        );
      }

      forgotPasswordMessage.textContent = resultado.mensagem;

      if (resultado.sucesso) {
        forgotPasswordMessage.classList.add("show", "success");
        forgotPasswordForm.reset();
      } else {
        forgotPasswordMessage.classList.add("show", "error");
      }

    } catch (erro) {
      console.error("Erro:", erro);

      forgotPasswordMessage.textContent =
        "Não foi possível processar a solicitação.";

      forgotPasswordMessage.classList.add("show", "error");
    }
  });
}
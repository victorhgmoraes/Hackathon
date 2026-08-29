const togglePassword = document.getElementById("togglePassword");
const passwordInput = document.getElementById("password");

if (togglePassword && passwordInput) {
  togglePassword.addEventListener("click", () => {
    if (passwordInput.type === "password") {
      passwordInput.type = "text";
      togglePassword.textContent = "Ocultar";
    } else {
      passwordInput.type = "password";
      togglePassword.textContent = "Mostrar";
    }
  });
}

const loginForm = document.getElementById("loginForm");
const loginMessage = document.getElementById("loginMessage");

if (loginForm) {
  loginForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    const email = document.getElementById("email").value.trim();
    const senha = document.getElementById("password").value;

    loginMessage.classList.remove("show");

    if (!email || !senha) {
      loginMessage.textContent = "Preencha seu e-mail e sua senha.";
      loginMessage.classList.add("show");
      return;
    }

    try {
      const resposta = await fetch("login.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          email: email,
          senha: senha,
        }),
      });

      const resultado = await resposta.json();

      if (!resultado.sucesso) {
        loginMessage.textContent = resultado.mensagem;
        loginMessage.classList.add("show");
        return;
      }

      loginMessage.textContent = resultado.mensagem;
      loginMessage.classList.add("show");

      setTimeout(() => {
        if (resultado.tipo === "jurado") {
          window.location.href = "jurado/dashboard.php";
        } else if (resultado.tipo === "participante") {
          window.location.href = "participante/dashboard.php";
        }
      }, 1000);
    } catch (erro) {
      console.error(erro);

      loginMessage.textContent = "Erro ao conectar com o servidor.";
      loginMessage.classList.add("show");
    }
  });
}
const cadastroForm = document.getElementById("cadastroForm");
const cadastroMessage = document.getElementById("cadastroMessage");

const togglePassword = document.getElementById("togglePassword");
const passwordInput = document.getElementById("password");

const toggleConfirmPassword = document.getElementById("toggleConfirmPassword");
const confirmPasswordInput = document.getElementById("confirmPassword");

// MOSTRAR / OCULTAR SENHA
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

// MOSTRAR / OCULTAR CONFIRMAÇÃO DE SENHA
if (toggleConfirmPassword && confirmPasswordInput) {
  toggleConfirmPassword.addEventListener("click", () => {
    if (confirmPasswordInput.type === "password") {
      confirmPasswordInput.type = "text";
      toggleConfirmPassword.textContent = "Ocultar";
    } else {
      confirmPasswordInput.type = "password";
      toggleConfirmPassword.textContent = "Mostrar";
    }
  });
}

// CADASTRO
if (cadastroForm) {
  cadastroForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    const nome = document.getElementById("nome").value.trim();
    const email = document.getElementById("email").value.trim();
    const matricula = document.getElementById("matricula").value.trim();
    const senha = document.getElementById("password").value;
    const confirmPassword =
      document.getElementById("confirmPassword").value;

    cadastroMessage.classList.remove("show");

    // VERIFICA CAMPOS
    if (!nome || !email || !matricula || !senha || !confirmPassword) {
      cadastroMessage.textContent = "Preencha todos os campos.";
      cadastroMessage.classList.add("show");
      return;
    }

    // VERIFICA SENHAS
    if (senha !== confirmPassword) {
      cadastroMessage.textContent = "As senhas não coincidem.";
      cadastroMessage.classList.add("show");
      return;
    }

    try {
      const resposta = await fetch("cadastro.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          nome: nome,
          email: email,
          matricula: matricula,
          senha: senha,
        }),
      });

      const resultado = await resposta.json();

      cadastroMessage.textContent = resultado.mensagem;
      cadastroMessage.classList.add("show");

      // CADASTRO REALIZADO
      if (resultado.sucesso) {
        cadastroForm.reset();

        setTimeout(() => {
          window.location.href = "login.html";
        }, 1500);
      }
    } catch (erro) {
      console.error(erro);

      cadastroMessage.textContent =
        "Erro ao conectar com o servidor.";
      cadastroMessage.classList.add("show");
    }
  });
}

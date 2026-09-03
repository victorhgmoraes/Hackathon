const cadastroForm = document.getElementById("cadastroForm");
const cadastroMessage = document.getElementById("cadastroMessage");

const togglePassword = document.getElementById("togglePassword");
const passwordInput = document.getElementById("password");

const toggleConfirmPassword = document.getElementById("toggleConfirmPassword");
const confirmPasswordInput = document.getElementById("confirmPassword");

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

if (cadastroForm) {
  cadastroForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    const nome = document.getElementById("nome").value.trim();
    const email = document.getElementById("email").value.trim();
    const matricula = document.getElementById("matricula").value.trim();
    const senha = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirmPassword").value;

    cadastroMessage.className = "form-message";

    if (!nome || !email || !matricula || !senha || !confirmPassword) {
      cadastroMessage.textContent = "Preencha todos os campos.";
      cadastroMessage.classList.add("show", "error");
      return;
    }

    if (senha !== confirmPassword) {
      cadastroMessage.textContent = "As senhas não coincidem.";
      cadastroMessage.classList.add("show", "error");
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

      if (resultado.sucesso) {
        cadastroMessage.classList.add("show", "success");
        cadastroForm.reset();

        setTimeout(() => {
          window.location.href = "login.html";
        }, 1500);
      } else {
        cadastroMessage.classList.add("show", "error");
      }
    } catch (erro) {
      console.error(erro);

      cadastroMessage.textContent = "Erro ao conectar com o servidor.";
      cadastroMessage.classList.add("show", "error");
    }
  });
}
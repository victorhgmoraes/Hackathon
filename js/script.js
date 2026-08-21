const menuToggle = document.getElementById("menuToggle");
const navLinks = document.querySelector(".nav-links");
if (menuToggle) {
  menuToggle.addEventListener("click", () => {
    navLinks.classList.toggle("mobile-active");
  });
}
const links = document.querySelectorAll(".nav-links a");
links.forEach((link) => {
  link.addEventListener("click", () => {
    navLinks.classList.remove("mobile-active");
  });
});
const animatedElements = document.querySelectorAll(
  ".about-card, .timeline-item",
);
const observer = new IntersectionObserver(
  (entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = "1";
        entry.target.style.transform = "translateY(0)";
      }
    });
  },
  {
    threshold: 0.1,
  },
);
animatedElements.forEach((element) => {
  element.style.opacity = "0";
  element.style.transform = "translateY(25px)";
  element.style.transition = "opacity 0.6s ease, transform 0.6s ease";
  observer.observe(element);
});

const eventos = [
  {
    id: 1,
    dia: "sexta",
    diaNome: "Sexta-feira",
    horario: "09:00",
    titulo: "Credenciamento",
    descricao: "Recepção das equipes e confirmação das inscrições.",
    categoria: "Organização",
  },

  {
    id: 2,
    dia: "sexta",
    diaNome: "Sexta-feira",
    horario: "10:00",
    titulo: "Abertura do Hackathon",
    descricao: "Apresentação do evento, regras e orientações gerais.",
    categoria: "Evento",
  },

  {
    id: 3,
    dia: "sexta",
    diaNome: "Sexta-feira",
    horario: "11:00",
    titulo: "Apresentação do desafio",
    descricao: "Apresentação do problema e início oficialmente do desafio.",
    categoria: "Desafio",
  },

  {
    id: 4,
    dia: "sabado",
    diaNome: "Sábado",
    horario: "09:00",
    titulo: "Desenvolvimento dos projetos",
    descricao:
      "Período dedicado ao desenvolvimento das soluções pelas equipes.",
    categoria: "Desenvolvimento",
  },

  {
    id: 5,
    dia: "sabado",
    diaNome: "Sábado",
    horario: "18:00",
    titulo: "Acompanhamento das equipes",
    descricao: "Momento destinado ao acompanhamento do andamento dos projetos.",
    categoria: "Desenvolvimento",
  },

  {
    id: 6,
    dia: "domingo",
    diaNome: "Domingo",
    horario: "09:00",
    titulo: "Finalização dos projetos",
    descricao: "Últimos ajustes e preparação das apresentações.",
    categoria: "Desenvolvimento",
  },

  {
    id: 7,
    dia: "domingo",
    diaNome: "Domingo",
    horario: "14:00",
    titulo: "Apresentação dos projetos",
    descricao: "As equipes apresentam suas soluções para os jurados.",
    categoria: "Apresentação",
  },

  {
    id: 8,
    dia: "domingo",
    diaNome: "Domingo",
    horario: "16:00",
    titulo: "Avaliação dos jurados",
    descricao: "Período destinado à avaliação dos projetos participantes.",
    categoria: "Avaliação",
  },

  {
    id: 9,
    dia: "domingo",
    diaNome: "Domingo",
    horario: "17:00",
    titulo: "Resultado final",
    descricao: "Divulgação pública dos resultados do Hackathon.",
    categoria: "Resultado",
  },
];

function renderAgenda(filtro = "todos") {
  const agendaList = document.getElementById("agendaList");
  if (!agendaList) {
    return;
  }
  agendaList.innerHTML = "";
  const eventosFiltrados =
    filtro === "todos"
      ? eventos
      : eventos.filter((evento) => evento.dia === filtro);
  eventosFiltrados.forEach((evento) => {
    const elemento = document.createElement("article");
    elemento.classList.add("agenda-event");
    elemento.innerHTML = `
            <div class="event-time">
                ${evento.horario}
            </div>
            <div class="event-content">
                <span class="event-day">
                    ${evento.diaNome}
                </span>
                <h3>
                    ${evento.titulo}
                </h3>
                <p>
                    ${evento.descricao}
                </p>
                <span class="event-tag">
                    ${evento.categoria}
                </span>
            </div>
        `;
    agendaList.appendChild(elemento);
  });
}
const dayButtons = document.querySelectorAll(".day-button");
dayButtons.forEach((button) => {
  button.addEventListener("click", () => {
    dayButtons.forEach((btn) => {
      btn.classList.remove("active");
    });
    button.classList.add("active");
    const diaSelecionado = button.dataset.day;
    renderAgenda(diaSelecionado);
  });
});
renderAgenda();

const resultados = [
  {
    posicao: 1,
    projeto: "EcoTech",
    equipe: "Green Solutions",
    categoria: "Sustentabilidade",
    nota: 9.42,
  },

  {
    posicao: 2,
    projeto: "Campus Connect",
    equipe: "Connect Team",
    categoria: "Tecnologia",
    nota: 9.18,
  },

  {
    posicao: 3,
    projeto: "Smart Campus",
    equipe: "NextGen",
    categoria: "Educação",
    nota: 8.91,
  },

  {
    posicao: 4,
    projeto: "SafeRoute",
    equipe: "Pathfinders",
    categoria: "Mobilidade",
    nota: 8.67,
  },

  {
    posicao: 5,
    projeto: "Green Food",
    equipe: "EcoCoders",
    categoria: "Sustentabilidade",
    nota: 8.42,
  },

  {
    posicao: 6,
    projeto: "HealthHub",
    equipe: "DevHealth",
    categoria: "Saúde",
    nota: 8.21,
  },
];

function renderPodium() {
  const podium = document.getElementById("podium");
  if (!podium) {
    return;
  }
  const primeiros = resultados.slice(0, 3);
  const ordem = [primeiros[1], primeiros[0], primeiros[2]];
  const classes = ["second", "first", "third"];
  const medalhas = ["🥈", "🏆", "🥉"];
  podium.innerHTML = "";
  ordem.forEach((resultado, index) => {
    if (!resultado) {
      return;
    }
    const card = document.createElement("div");
    card.classList.add("podium-card", classes[index]);
    card.innerHTML = `
            <div class="podium-medal">
                ${medalhas[index]}
            </div>
            <div class="podium-position">
                ${resultado.posicao}º
            </div>
            <h3>
                ${resultado.projeto}
            </h3>
            <div class="podium-team">
                ${resultado.equipe}
            </div>
            <div class="podium-score">
                ${resultado.nota.toFixed(2)}
                <small>/ 10</small>
            </div>
        `;
    podium.appendChild(card);
  });
}

function renderRanking() {
  const rankingList = document.getElementById("rankingList");
  const rankingCount = document.getElementById("rankingCount");
  if (!rankingList) {
    return;
  }
  rankingList.innerHTML = "";
  resultados.forEach((resultado) => {
    const item = document.createElement("div");
    item.classList.add("ranking-item");
    item.innerHTML = `
            <div class="ranking-position">
                ${String(resultado.posicao).padStart(2, "0")}
            </div>
            <div class="ranking-project">
                <strong>
                    ${resultado.projeto}
                </strong>
                <span>
                    ${resultado.equipe}
                </span>
            </div>
            <div class="ranking-category">
                ${resultado.categoria}
            </div>
            <div class="ranking-score">
                ${resultado.nota.toFixed(2)}
                <small>
                    / 10
                </small>
            </div>
        `;
    rankingList.appendChild(item);
  });

  if (rankingCount) {
    rankingCount.textContent = `${resultados.length} projetos`;
  }
}
renderPodium();
renderRanking();

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
          window.location.href = "jurado/dashboard.html";
        } else {
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

const cadastroForm = document.getElementById("cadastroForm");
const cadastroMessage = document.getElementById("cadastroMessage");
const toggleConfirmPassword = document.getElementById("toggleConfirmPassword");
const confirmPasswordInput = document.getElementById("confirmPassword");
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

    cadastroMessage.classList.remove("show");

    if (!nome || !email || !matricula || !senha || !confirmPassword) {
      cadastroMessage.textContent = "Preencha todos os campos.";
      cadastroMessage.classList.add("show");
      return;
    }

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

      if (resultado.sucesso) {
        cadastroForm.reset();

        setTimeout(() => {
          window.location.href = "login.html";
        }, 1500);
      }
    } catch (erro) {
      cadastroMessage.textContent = "Erro ao conectar com o servidor.";
      cadastroMessage.classList.add("show");

      console.error(erro);
    }
  });
}

// PAINEL DO JURADO

const evaluateButtons = document.querySelectorAll(".evaluate-button");
const evaluationSection = document.getElementById("evaluationSection");
const selectedProject = document.getElementById("selectedProject");
evaluateButtons.forEach((button) => {
  button.addEventListener("click", () => {
    const projectName = button.dataset.project;
    if (selectedProject) {
      selectedProject.textContent = projectName;
    }
    if (evaluationSection) {
      evaluationSection.scrollIntoView({
        behavior: "smooth",
      });
    }
  });
});

// FORMULÁRIO DE AVALIAÇÃO

const evaluationForm = document.getElementById("evaluationForm");
const evaluationMessage = document.getElementById("evaluationMessage");
if (evaluationForm) {
  evaluationForm.addEventListener("submit", (event) => {
    event.preventDefault();
    const project = selectedProject.textContent;
    const nota = document.getElementById("nota").value;
    const comentario = document.getElementById("comentario").value.trim();
    if (!project || project === "Selecione um projeto acima") {
      evaluationMessage.textContent = "Selecione um projeto para avaliar.";
      evaluationMessage.classList.add("show");
      return;
    }
    if (!nota || nota < 0 || nota > 10) {
      evaluationMessage.textContent = "Informe uma nota entre 0 e 10.";
      evaluationMessage.classList.add("show");
      return;
    }
    if (!comentario) {
      evaluationMessage.textContent = "Digite um comentário sobre o projeto.";
      evaluationMessage.classList.add("show");
      return;
    }
    evaluationMessage.textContent =
      "Avaliação registrada. O salvamento no banco será implementado posteriormente.";
    evaluationMessage.classList.add("show");
    evaluationForm.reset();
  });
}

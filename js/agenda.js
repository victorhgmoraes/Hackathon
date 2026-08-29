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

const agendaList = document.getElementById("agendaList");
const dayButtons = document.querySelectorAll(".day-button");

function renderAgenda(filtro = "todos") {
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

dayButtons.forEach((button) => {
  button.addEventListener("click", () => {
    dayButtons.forEach((btn) => {
      btn.classList.remove("active");
    });

    button.classList.add("active");

    renderAgenda(button.dataset.day);
  });
});

renderAgenda();

const menuToggle = document.getElementById("menuToggle");
const navLinks = document.querySelector(".nav-links");

if (menuToggle && navLinks) {
  menuToggle.addEventListener("click", () => {
    const aberto = navLinks.classList.toggle("mobile-active");

    menuToggle.setAttribute("aria-expanded", aberto);
  });

  navLinks.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", () => {
      navLinks.classList.remove("mobile-active");
      menuToggle.setAttribute("aria-expanded", "false");
    });
  });
}

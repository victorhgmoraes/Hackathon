const menuToggle = document.getElementById("menuToggle");
const navLinks = document.getElementById("navLinks");

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

const animatedElements = document.querySelectorAll(".feature-card, .stage");

if ("IntersectionObserver" in window) {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("visible");
          observer.unobserve(entry.target);
        }
      });
    },
    {
      threshold: 0.1,
    },
  );

  animatedElements.forEach((element) => {
    observer.observe(element);
  });
}

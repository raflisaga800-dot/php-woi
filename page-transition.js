document.querySelectorAll("a, button").forEach((el) => {
  el.addEventListener("click", function (e) {
    const target = el.getAttribute("href");

    // kalau bukan link pindah halaman, skip
    if (!target || target.startsWith("#")) return;

    e.preventDefault();

    const page = document.querySelector(".page");
    page.classList.add("fade-out");

    setTimeout(() => {
      window.location.href = target;
    }, 350);
  });
});

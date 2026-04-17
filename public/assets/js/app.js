(() => {
  document.documentElement.setAttribute("data-lumis", "1");

  const body = document.body;
  const sidebar = document.getElementById("appSidebar");
  const backdrop = document.getElementById("appSidebarBackdrop");
  const btnOpen = document.getElementById("appSidebarOpen");
  const btnCollapse = document.getElementById("appSidebarCollapse");

  const mqMobile = window.matchMedia("(max-width: 991.98px)");
  const storageKey = "lumis.sidebar.collapsed";

  function isCollapsedPreferred() {
    return localStorage.getItem(storageKey) === "1";
  }

  function setCollapsedPreferred(value) {
    localStorage.setItem(storageKey, value ? "1" : "0");
  }

  function closeMobileDrawer() {
    body.classList.remove("app-sidebar-open");
    if (backdrop) {
      backdrop.classList.remove("is-visible");
      backdrop.setAttribute("aria-hidden", "true");
    }
  }

  function openMobileDrawer() {
    body.classList.add("app-sidebar-open");
    if (backdrop) {
      backdrop.classList.add("is-visible");
      backdrop.setAttribute("aria-hidden", "false");
    }
  }

  function applyDesktopCollapsed() {
    if (mqMobile.matches) {
      body.classList.remove("app-sidebar-collapsed");
      return;
    }
    if (isCollapsedPreferred()) {
      body.classList.add("app-sidebar-collapsed");
    } else {
      body.classList.remove("app-sidebar-collapsed");
    }
  }

  function toggleDesktopCollapsed() {
    if (mqMobile.matches) {
      return;
    }
    body.classList.toggle("app-sidebar-collapsed");
    setCollapsedPreferred(body.classList.contains("app-sidebar-collapsed"));
  }

  btnCollapse?.addEventListener("click", () => {
    toggleDesktopCollapsed();
  });

  btnOpen?.addEventListener("click", () => {
    openMobileDrawer();
  });

  backdrop?.addEventListener("click", () => {
    closeMobileDrawer();
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      closeMobileDrawer();
    }
  });

  window.addEventListener("resize", () => {
    if (!mqMobile.matches) {
      closeMobileDrawer();
    }
    applyDesktopCollapsed();
  });

  // Inicial
  applyDesktopCollapsed();

  // Atalho "/" para foco na busca (placeholder)
  document.addEventListener("keydown", (e) => {
    const t = e.target;
    const tag = t && t.tagName ? t.tagName.toLowerCase() : "";
    if (tag === "input" || tag === "textarea" || tag === "select") {
      return;
    }
    if (e.key === "/" && !e.ctrlKey && !e.metaKey && !e.altKey) {
      e.preventDefault();
      const search = document.querySelector(".app-search-placeholder");
      search?.scrollIntoView({ block: "nearest", behavior: "smooth" });
    }
  });
})();

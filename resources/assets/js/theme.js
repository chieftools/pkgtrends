const STORAGE_KEY = "theme";
const VALID = ["light", "dark", "system"];

function getStoredTheme() {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        return VALID.indexOf(stored) !== -1 ? stored : "system";
    } catch (e) {
        return "system";
    }
}

function setStoredTheme(value) {
    try {
        localStorage.setItem(STORAGE_KEY, value);
    } catch (e) {}
}

function resolveTheme(theme) {
    if (theme === "system") {
        return window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
    }
    return theme;
}

function applyTheme(theme) {
    const resolved = resolveTheme(theme);
    document.documentElement.setAttribute("data-bs-theme", resolved);
    document.dispatchEvent(
        new CustomEvent("themechange", {
            detail: { theme: theme, resolved: resolved },
        })
    );
}

function syncSwitcher(theme) {
    const buttons = document.querySelectorAll("[data-theme-value]");
    buttons.forEach((btn) => {
        const isActive = btn.getAttribute("data-theme-value") === theme;
        btn.classList.toggle("active", isActive);
        btn.setAttribute("aria-pressed", isActive ? "true" : "false");
    });
}

function init() {
    const initial = getStoredTheme();
    applyTheme(initial);
    syncSwitcher(initial);

    document.querySelectorAll("[data-theme-value]").forEach((btn) => {
        btn.addEventListener("click", () => {
            const value = btn.getAttribute("data-theme-value");
            if (VALID.indexOf(value) === -1) return;
            setStoredTheme(value);
            applyTheme(value);
            syncSwitcher(value);
        });
    });

    const media = window.matchMedia("(prefers-color-scheme: dark)");
    const onChange = () => {
        if (getStoredTheme() === "system") {
            applyTheme("system");
        }
    };
    if (media.addEventListener) {
        media.addEventListener("change", onChange);
    } else if (media.addListener) {
        media.addListener(onChange);
    }

    window.pkgtrends = window.pkgtrends || {};
    window.pkgtrends.getResolvedTheme = () => resolveTheme(getStoredTheme());
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
} else {
    init();
}

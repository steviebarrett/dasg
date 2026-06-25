function ready(fn) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fn);
    } else {
        fn();
    }
}

if (window.jstiming && window.jstiming.load) {
    window.jstiming.load.tick('render');
}

ready(async function () {
    await import('./pagefind/pagefind-highlight.js');

    new PagefindHighlight({
        highlightParam: "highlight",
        markContext: "[data-pagefind-body]"
    });

    function scrollToFirstHighlight() {
        const firstHighlight = document.querySelector('.pagefind-highlight');

        if (firstHighlight) {
            firstHighlight.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            return true;
        }

        return false;
    }

    if (!scrollToFirstHighlight()) {
        const observer = new MutationObserver(() => {
            if (scrollToFirstHighlight()) {
                observer.disconnect();
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        setTimeout(() => observer.disconnect(), 3000);
    }

    if (document.getElementById('search') && window.PagefindUI) {
        new PagefindUI({
            element: "#search",
            highlightParam: "highlight"
        });
    }

    const toggle = document.getElementById('poem-view-toggle');
    const content = document.getElementById('sites-canvas-main-content');
    const sidebar = document.getElementById('sites-chrome-sidebar-left');

    if (toggle && content) {
        toggle.addEventListener('change', function () {
            content.classList.toggle('poem-side-by-side', toggle.checked);

            if (sidebar) {
                sidebar.style.display = toggle.checked ? 'none' : '';
            }
        });
    }
});
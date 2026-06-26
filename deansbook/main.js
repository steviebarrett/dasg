function ready(fn) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fn);
    } else {
        fn();
    }
}

function scrollToFirstHighlight() {
    const firstHighlight = document.querySelector('.pagefind-highlight');

    if (!firstHighlight) {
        return false;
    }

    firstHighlight.scrollIntoView({
        behavior: 'smooth',
        block: 'center'
    });

    return true;
}

function waitForHighlightAndScroll() {
    if (scrollToFirstHighlight()) {
        return;
    }

    const observer = new MutationObserver(function () {
        if (scrollToFirstHighlight()) {
            observer.disconnect();
        }
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    setTimeout(function () {
        observer.disconnect();
    }, 4000);
}

ready(async function () {
    try {
        await import('./pagefind/pagefind-highlight.js');

        new PagefindHighlight({
            highlightParam: "highlight",
            markContext: "[data-pagefind-body]"
        });

        waitForHighlightAndScroll();

    } catch (error) {
        console.warn('Pagefind highlighting could not be loaded:', error);
    }

    if (document.getElementById('search') && window.PagefindUI) {
        new PagefindUI({
            element: "#search",
            highlightParam: "highlight"
        });
    }

    const toggle = document.getElementById('poem-view-toggle');
    const ms = document.getElementById('ms');
    const restored = document.getElementById('restored-text');
    const translation = document.getElementById('translation');

    if (toggle && ms && restored && translation) {
        let wrapper = document.querySelector('.poem-columns');

        if (!wrapper) {
            wrapper = document.createElement('div');
            wrapper.className = 'poem-columns';

            ms.parentNode.insertBefore(wrapper, ms);

            wrapper.appendChild(ms);
            wrapper.appendChild(restored);
            wrapper.appendChild(translation);
        }

        const params = new URLSearchParams(window.location.search);
        const sideBySideActive = params.get('view') === 'side-by-side';

        document.body.classList.toggle('side-by-side-active', sideBySideActive);

        if (sideBySideActive) {
            toggle.textContent = 'Back to standard view';
        } else {
            toggle.textContent = 'Side-by-side view';
        }

        toggle.addEventListener('click', function () {
            const url = new URL(window.location.href);

            if (sideBySideActive) {
                url.searchParams.delete('view');
            } else {
                url.searchParams.set('view', 'side-by-side');
            }

            window.location.href = url.toString();
        });
    }
});
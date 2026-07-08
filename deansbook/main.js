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


function columnLabelForId(id) {
    if (id === 'ms') return 'MS';
    if (id === 'restored-text') return 'Restored text';
    if (id === 'translation') return 'Translation';
    return '';
}

function splitColumnIntoStanzas(column, type) {
    const html = column.innerHTML;
    const starts = [];
    let match;

    const markerPattern = type === 'ms'
        ? /(\[\s*(\d{1,3})\.\s*\])/g
        : /(^|[\s>]|&nbsp;)(\d{1,3})\.(?=(?:\s|&nbsp;|<span|<br|<\/|$))/g;

    while ((match = markerPattern.exec(html)) !== null) {
        const number = parseInt(type === 'ms' ? match[2] : match[2], 10);
        const markerStart = type === 'ms'
            ? match.index
            : match.index + match[1].length;

        starts.push({ number, index: markerStart });
    }

    if (!starts.length) {
        return {
            prelude: html.trim(),
            stanzas: new Map()
        };
    }

    const prelude = html.slice(0, starts[0].index).trim();
    const stanzas = new Map();

    starts.forEach(function (start, i) {
        const end = i + 1 < starts.length ? starts[i + 1].index : html.length;
        const stanzaHtml = html.slice(start.index, end).trim();

        if (stanzaHtml) {
            stanzas.set(start.number, stanzaHtml);
        }
    });

    return { prelude, stanzas };
}

function buildAlignedStanzaView(ms, restored, translation, wrapper) {
    if (wrapper.querySelector('.poem-aligned')) {
        return;
    }

    const columns = [
        { id: 'ms', type: 'ms', element: ms, parts: splitColumnIntoStanzas(ms, 'ms') },
        { id: 'restored-text', type: 'numbered', element: restored, parts: splitColumnIntoStanzas(restored, 'numbered') },
        { id: 'translation', type: 'numbered', element: translation, parts: splitColumnIntoStanzas(translation, 'numbered') }
    ];

    const stanzaNumbers = Array.from(new Set(
        columns.flatMap(function (column) {
            return Array.from(column.parts.stanzas.keys());
        })
    )).sort(function (a, b) {
        return a - b;
    });

    // If there is nothing useful to align, keep the original column layout.
    if (!stanzaNumbers.length) {
        return;
    }

    const aligned = document.createElement('div');
    aligned.className = 'poem-aligned';

    const hasPrelude = columns.some(function (column) {
        return column.parts.prelude && column.parts.prelude.replace(/<[^>]*>/g, '').trim();
    });

    if (hasPrelude) {
        const row = document.createElement('div');
        row.className = 'stanza-row stanza-row--prelude';

        columns.forEach(function (column) {
            const cell = document.createElement('div');
            cell.className = 'stanza-cell stanza-cell--' + column.id;
            cell.setAttribute('data-column-label', columnLabelForId(column.id));
            cell.innerHTML = column.parts.prelude || '';
            row.appendChild(cell);
        });

        aligned.appendChild(row);
    }

    stanzaNumbers.forEach(function (number) {
        const row = document.createElement('div');
        row.className = 'stanza-row';
        row.setAttribute('data-stanza', String(number));

        columns.forEach(function (column) {
            const cell = document.createElement('div');
            cell.className = 'stanza-cell stanza-cell--' + column.id;
            cell.setAttribute('data-column-label', columnLabelForId(column.id));
            cell.innerHTML = column.parts.stanzas.get(number) || '';
            row.appendChild(cell);
        });

        aligned.appendChild(row);
    });

    wrapper.insertBefore(aligned, wrapper.firstChild);
    wrapper.classList.add('has-aligned-stanzas');
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

        buildAlignedStanzaView(ms, restored, translation, wrapper);

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
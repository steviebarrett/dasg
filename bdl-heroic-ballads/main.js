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

function adjustedMarkerStart(html, markerStart, type) {
    const before = html.slice(0, markerStart);

    /*
     * Poem XXIII uses a complete .variant block for manuscript stanzas 3–5.
     * The stanza number sits inside that block, so starting the slice at the
     * number would discard the opening grid markup and break the brace.
     * If the marker is currently inside an unclosed .variant div, begin at
     * the opening div instead.
     */
    if (type === 'ms') {
        const variantOpenPattern = /<div\b[^>]*class=(?:"[^"]*\bvariant\b[^"]*"|'[^']*\bvariant\b[^']*')[^>]*>/gi;
        let variantMatch;
        let lastVariantStart = -1;

        while ((variantMatch = variantOpenPattern.exec(before)) !== null) {
            lastVariantStart = variantMatch.index;
        }

        if (lastVariantStart !== -1) {
            const afterOpening = before.slice(lastVariantStart);
            const openedDivs = (afterOpening.match(/<div\b/gi) || []).length;
            const closedDivs = (afterOpening.match(/<\/div\s*>/gi) || []).length;

            if (openedDivs > closedDivs) {
                return lastVariantStart;
            }
        }

        return markerStart;
    }

    /*
     * If a standalone >>> paragraph occurs immediately before a stanza number,
     * attach it to the following stanza rather than the previous one.
     *
     * This preserves the visible >>> in the output, but stops it breaking
     * stanza alignment.
     */
    const standaloneParagraphMarker =
        /(<p\b[^>]*>\s*(?:<[^>]+>\s*)*(?:&gt;|>){3}\s*(?:<\/[^>]+>\s*)*<\/p>\s*)$/i;

    const standaloneBareMarker =
        /((?:\s|&nbsp;|<br\s*\/?>)*(?:&gt;|>){3}(?:\s|&nbsp;|<br\s*\/?>)*)$/i;

    let match = before.match(standaloneParagraphMarker);

    if (match) {
        return markerStart - match[1].length;
    }

    match = before.match(standaloneBareMarker);

    if (match) {
        return markerStart - match[1].length;
    }

    return markerStart;
}

function splitColumnIntoStanzas(column, type) {
    const html = column.innerHTML;
    const starts = [];
    let match;

    const markerPattern = type === 'ms'
    ? /(\[\s*(?:(?:&gt;|>){3}(?:\s|&nbsp;|\u00a0|<[^>]+>)*)?\.?(?:\s|&nbsp;|\u00a0|<[^>]+>)*(\d{1,3})\.\s*\])/g
    : /(^|[\s>]|&nbsp;)(?:(?:&gt;|>){3}(?:\s|&nbsp;|\u00a0|<[^>]+>)*)?\.?(?:\s|&nbsp;|\u00a0|<[^>]+>)*(\d{1,3})\.(?=(?:\s|&nbsp;|\u00a0|<span|<br|<\/|$))/g;

    while ((match = markerPattern.exec(html)) !== null) {
        const number = parseInt(match[2], 10);

        const rawMarkerStart = type === 'ms'
            ? match.index
            : match.index + match[1].length;

        const markerStart = adjustedMarkerStart(html, rawMarkerStart, type);

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
    let stanzaHtml = html.slice(start.index, end);

    /*
     * Remove leading separator markup that Google Sites often leaves
     * before stanzas 2, 3, 4... Otherwise the stanza starts lower than
     * the corresponding stanza in the other columns.
     */
    const holder = document.createElement('div');
    holder.innerHTML = stanzaHtml;

    while (holder.firstChild) {
        const first = holder.firstChild;

        if (
            first.nodeType === Node.TEXT_NODE &&
            !first.textContent.trim()
        ) {
            holder.removeChild(first);
            continue;
        }

        if (
            first.nodeType === Node.ELEMENT_NODE &&
            first.tagName === 'BR'
        ) {
            holder.removeChild(first);
            continue;
        }

        if (
            first.nodeType === Node.ELEMENT_NODE &&
            first.tagName === 'SPAN' &&
            !first.textContent.trim() &&
            !first.querySelector('img,svg,br')
        ) {
            holder.removeChild(first);
            continue;
        }

        break;
    }

    stanzaHtml = holder.innerHTML.trim();

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
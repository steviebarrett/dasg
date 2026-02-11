function escapeRegExp(s) {
    return String(s).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function escHtml(s){
    return String(s).replace(/[&<>"']/g, function(m){
        return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]);
    });
}

function escAttr(s){
    return escHtml(s).replace(/`/g, '&#96;');
}

function escapeRegExp(s) {
    return String(s).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function buildSearchRegex(pattern, regexMode) {
    let p = String(pattern || '');

    // Your existing “class translation” tweaks:
    p = p.replace('[[:<:]]', String.raw`\b`)
        .replace('[[:>:]]', String.raw`\b`)
        .replace('[[:alpha:]]', '[a-z]')
        .replace('[[:digit:]]', '[0-9]')
        .replace('[[:space:]]', String.raw`\s`);

    // Literal mode: treat as plain text
    if (!regexMode) {
        p = escapeRegExp(p);
    }

    // Guardrails (prevents “broken regex” from killing the table)
    if (p.length > 200) return null;

    try {
        return new RegExp(p, 'giu');
    } catch (e) {
        return null; // invalid regex
    }
}
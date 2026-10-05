<?php
declare(strict_types=1);


require_once '../includes/include.php';


if (!defined('DASG_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Forbidden');
}

/**
 * Faclair Word -> Forms/Senses HTML converter (proof of concept)
 *
 * Usage:
 *   php faclair-word-to-html.php entryWeb.docx output-dir
 *
 * Produces:
 *   output-dir/forms.html
 *   output-dir/senses.html
 *   output-dir/warnings.txt
 *
 * No third-party library required: reads OOXML directly with ZipArchive + DOM.
 */

if ($argc < 2) {
    fwrite(STDERR, "Usage: php {$argv[0]} input.docx [output-dir]\n");
    exit(2);
}
$input = $argv[1];
$outDir = $argv[2] ?? dirname($input);

$dbh = new PDO(
    'mysql:host=localhost;dbname=' . FACLAIR_DB_NAME . ';charset=utf8mb4',
    FACLAIR_DB_USER,
    FACLAIR_DB_PASSWORD,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

if (!is_dir($outDir) && !mkdir($outDir, 0775, true) && !is_dir($outDir)) {
    throw new RuntimeException("Cannot create $outDir");
}

final class Run {
    public function __construct(
        public string $text,
        public bool $bold = false,
        public bool $italic = false,
        public bool $smallCaps = false,
        public bool $highlight = false,
        public ?string $href = null,
    ) {}
}
final class Para {
    /** @param Run[] $runs */
    public function __construct(public array $runs) {}
    public function text(): string { return trim(implode('', array_map(fn($r) => $r->text, $this->runs))); }
    public function isBlank(): bool { return $this->text() === ''; }
}

function esc(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8'); }
function norm(string $s): string { return preg_replace('/\s+/u', ' ', trim($s)) ?? trim($s); }
function hasEl(DOMXPath $xp, DOMNode $ctx, string $q): bool { return $xp->query($q, $ctx)->length > 0; }

/** @return Para[] */
function readDocx(string $path): array {
    $zip = new ZipArchive();
    if ($zip->open($path) !== true) throw new RuntimeException("Cannot open $path");
    $xml = $zip->getFromName('word/document.xml');
    $relsXml = $zip->getFromName('word/_rels/document.xml.rels');
    if ($xml === false) throw new RuntimeException('word/document.xml missing');

    $rels = [];
    if ($relsXml !== false) {
        $rd = new DOMDocument(); $rd->loadXML($relsXml);
        foreach ($rd->getElementsByTagName('Relationship') as $r) {
            $rels[$r->getAttribute('Id')] = $r->getAttribute('Target');
        }
    }
    $d = new DOMDocument(); $d->preserveWhiteSpace = true; $d->loadXML($xml);
    $xp = new DOMXPath($d);
    $xp->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
    $xp->registerNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');

    $paras = [];
    foreach ($xp->query('//w:body/w:p') as $p) {
        $runs = [];
        // Runs can be direct children or inside hyperlinks.
        foreach ($xp->query('.//w:r', $p) as $r) {
            $txt = '';
            foreach ($xp->query('.//w:t|.//w:tab|.//w:br', $r) as $n) {
                $txt .= match ($n->localName) { 'tab' => "\t", 'br' => "\n", default => $n->textContent };
            }
            if ($txt === '') continue;
            $parent = $r->parentNode;
            $href = null;
            if ($parent instanceof DOMElement && $parent->localName === 'hyperlink') {
                $rid = $parent->getAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'id');
                if ($rid && isset($rels[$rid])) $href = $rels[$rid];
            }
            $runs[] = new Run(
                $txt,
                hasEl($xp, $r, './w:rPr/w:b[not(@w:val="0")]'),
                hasEl($xp, $r, './w:rPr/w:i[not(@w:val="0")]'),
                hasEl($xp, $r, './w:rPr/w:smallCaps[not(@w:val="0")]'),
                hasEl($xp, $r, './w:rPr/w:highlight[not(@w:val="none")]'),
                $href
            );
        }
        $paras[] = new Para($runs);
    }
    $zip->close();
    return $paras;
}

function stripMarker(string $s): array {
    if (preg_match('/\s*\[((?:#?\d+)|#?Not in Corpus|https?:\/\/[^\]]+)\]\s*$/iu', $s, $m, PREG_OFFSET_CAPTURE)) {
        $marker = $m[1][0]; $off = $m[0][1];
        return [rtrim(substr($s, 0, $off)), $marker];
    }
    return [$s, null];
}
function corpusHref(?string $marker): ?string {
    if (!$marker) return null;
    $id = ltrim(trim($marker), '#');
    if (!ctype_digit($id)) return null;
    return 'https://dasg.ac.uk/corpus/textmeta.php?text=' . rawurlencode($id);
}
function dateHtml(string $date): string {
    // a1707, c1695, ?c1695 etc.: italicise the prefix letter as in target HTML.
    if (preg_match('/^([?]?)([ac])(.+)$/u', $date, $m)) return esc($m[1]).'<i>'.esc($m[2]).'</i>'.esc($m[3]);
    return esc($date);
}
function runHtml(Run $r, string $class = '', bool $markHighlight = false): string {
    $t = esc($r->text);
    $classes = trim($class.($r->smallCaps ? ' small-caps' : ''));
    $s = $classes !== '' ? '<span class="'.esc($classes).'">'.$t.'</span>' : $t;
    if ($r->italic) $s = '<i>'.$s.'</i>';
    if ($r->bold) $s = '<strong>'.$s.'</strong>';
    if ($r->href) $s = '<a href="'.esc($r->href).'">'.$s.'</a>';
    if ($markHighlight && $r->highlight) $s = '<mark>'.$s.'</mark>';
    return $s;
}

/** Remove a trailing corpus/URL marker even when Word has split it across several runs. */
function removeMarkerFromRuns(array $runs): array {
    $copy = array_map(fn($r) => clone $r, $runs);
    $full = '';
    $ends = [];
    foreach ($copy as $i => $r) {
        $full .= $r->text;
        $ends[$i] = strlen($full);
    }
    if (!preg_match('/\s*\[((?:#?\d+)|#?Not in Corpus|https?:\/\/[^\]]+)\]\s*$/iu', $full, $m, PREG_OFFSET_CAPTURE)) {
        return $copy;
    }
    $cut = $m[0][1];
    $pos = 0;
    foreach ($copy as $i => $r) {
        $len = strlen($r->text);
        if ($pos >= $cut) {
            $copy[$i]->text = '';
        } elseif ($pos + $len > $cut) {
            $copy[$i]->text = rtrim(substr($r->text, 0, $cut - $pos));
        }
        $pos += $len;
    }
    return array_values(array_filter($copy, fn($r) => $r->text !== ''));
}

function splitCitationRuns(array $runs): array {
    // Date is normally the leading bold run(s), sometimes with italic a/c prefix.
    $date = ''; $i = 0;
    while ($i < count($runs)) {
        $r = $runs[$i];
        if ($r->bold || trim($r->text)==='' || preg_match('/^[?ac0-9.\-]+$/u', trim($r->text))) {
            $date .= $r->text; $i++;
            if (preg_match('/\d/u', $date) && ($i>=count($runs) || !$runs[$i]->bold)) break;
        } else break;
    }
    $date = trim($date);
    return [$date, array_slice($runs, $i)];
}

function corpusId(?string $marker): ?string {
    if (!$marker) return null;
    $id = ltrim(trim($marker), '#');
    return ctype_digit($id) ? $id : null;
}

function getCorpusText(PDO $dbh, string $id): ?array {
    static $cache = [];
    if (array_key_exists($id, $cache)) return $cache[$id];
    $stmt = $dbh->prepare('SELECT short_title, reference_editor AS ed, reference_author AS au, reference_volume AS vol FROM corpus_text WHERE reference_number = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    return $cache[$id] = ($row ?: null);
}

function renderDbReference(PDO $dbh, string $id, string $page, string $issue = ''): string {
    $text = getCorpusText($dbh, $id);
    if (!$text) throw new RuntimeException("Corpus text {$id} not found in corpus_text");
    $title = (string)($text['short_title'] ?? '');
    $vol = trim((string)($text['vol'] ?? ''));
    $pre = '';
    if (!empty($text['au'])) {
        $pre = '<span class="reference small-caps">'.esc(ucfirst(strtolower((string)$text['au']))).'</span> ';
    } elseif (!empty($text['ed'])) {
        $pre = '<span class="reference">'.esc((string)$text['ed']).'</span> ';
    }
    $inside = $pre.'<i><span class="reference">'.esc($title).'</span></i>';
    if ($vol !== '') $inside .= '<span class="reference"> '.esc($vol).'</span>';
    if ($issue !== '') $inside .= '<span class="reference"> '.esc($issue).'</span>';
    $html = '<a target="_blank" rel="noopener noreferrer" href="https://dasg.ac.uk/corpus/textmeta.php?text='.rawurlencode($id).'" title="'.esc($title).'">'.$inside.'</a>';
    if ($page !== '') $html .= '&nbsp;<span class="reference pageref">'.esc($page).'</span>';
    return $html;
}

/** Split Word reference runs into a page value and optional issue. Database supplies title/author/volume. */
function referenceDetails(array $runs): array {
    $plain = norm(implode('', array_map(fn($r) => $r->text, $runs)));

    // The database supplies the canonical title and volume, but issue information
    // (e.g. "No. 40.") belongs to the individual citation in the Word document.
    // Extract the final page first, then look immediately before it for a numbered issue.
    $page = '';
    $issue = '';

    if (preg_match('/(?:^|\s)(\[no page number\]|\d+[A-Za-z]?(?:[.:-]\d+)?)(?:\s*)$/u', $plain, $m, PREG_OFFSET_CAPTURE)) {
        $page = $m[1][0];
        $pagePos = $m[1][1];
        $beforePage = rtrim(substr($plain, 0, $pagePos));

        // Mac-Talla, An Deo-gréine, etc.: "II No. 40. 306".
        // Keep the terminal full stop as part of the issue label so the rendered
        // reference becomes, for example, "Mac-Talla II No. 40. 306".
        if (preg_match('/(?:^|\s)(No\.\s*\d+\.)\s*$/ui', $beforePage, $im)) {
            $issue = preg_replace('/\s+/u', ' ', trim($im[1]));
        }
    }

    return [$page, $issue];
}

/** Split styled Word runs at a byte offset in their concatenated UTF-8 text. */
function splitRunsAtOffset(array $runs, int $offset): array {
    $left=[]; $right=[]; $pos=0;
    foreach ($runs as $r) {
        $len = strlen($r->text);
        if ($pos + $len <= $offset) {
            $left[] = clone $r;
        } elseif ($pos >= $offset) {
            $right[] = clone $r;
        } else {
            $cut = $offset - $pos;
            $a = clone $r; $b = clone $r;
            $a->text = substr($r->text, 0, $cut);
            $b->text = substr($r->text, $cut);
            if ($a->text !== '') $left[] = $a;
            if ($b->text !== '') $right[] = $b;
        }
        $pos += $len;
    }
    // The separator belongs to neither semantic field.
    if ($left) $left[count($left)-1]->text = rtrim($left[count($left)-1]->text);
    if ($right) $right[0]->text = ltrim($right[0]->text);
    return [$left,$right];
}

function citationBodyHtml(PDO $dbh, array $runs, ?string $marker, string $quoteClass, bool $forms, array &$warnings): string {
    $id = corpusId($marker);
    $externalHref = ($marker && preg_match('/^https?:\/\//i', $marker)) ? $marker : null;

    $quoteStart = null;
    if ($forms) foreach ($runs as $k=>$r) if ($r->highlight) { $quoteStart=$k; break; }
    if ($quoteStart === null) $quoteStart = count($runs);
    $refRuns = array_slice($runs, 0, $quoteStart);
    $quoteRuns = array_slice($runs, $quoteStart);

    // In Forms citations the quotation can begin BEFORE the highlighted form.
    // Word normally separates the bibliographic reference from the quotation
    // with a run of two or more spaces.  Previously everything before <mark>
    // was treated as reference data, so text such as "Bithidh do " vanished
    // when the DB-generated reference replaced those Word runs.
    if ($forms && $refRuns) {
        $pre = implode('', array_map(fn($r) => $r->text, $refRuns));
        if (preg_match_all('/\s{2,}/u', $pre, $mm, PREG_OFFSET_CAPTURE) && !empty($mm[0])) {
            $last = end($mm[0]);
            $boundary = $last[1] + strlen($last[0]);
            [$bibRuns, $preQuoteRuns] = splitRunsAtOffset($refRuns, $boundary);
            if ($preQuoteRuns) {
                $refRuns = $bibRuns;
                $quoteRuns = array_merge($preQuoteRuns, $quoteRuns);
            }
        }
    }

    [$page,$issue] = referenceDetails($refRuns);
    $refHtml = '';
    if ($id !== null) {
        try { $refHtml = renderDbReference($dbh, $id, $page, $issue); }
        catch (RuntimeException $e) { $warnings[] = $e->getMessage(); }
    }
    if ($refHtml === '') {
        // Not in corpus / explicit URL / DB miss: preserve the Word reference formatting as fallback.
        foreach ($refRuns as $r) $refHtml .= runHtml($r, 'reference');
        if ($externalHref && $refHtml !== '') $refHtml = '<a target="_blank" rel="noopener noreferrer" href="'.esc($externalHref).'">'.$refHtml.'</a>';
    }

    $q=''; foreach ($quoteRuns as $r) $q .= runHtml($r, $quoteClass, $forms);
    return $refHtml . ($refHtml && $q ? ' ' : '') . $q;
}

function isCitationStart(string $t): bool {
    return (bool)preg_match('/^(?:\?|[ac])?(?:\d{3,4}|\d{2}\.\.|\d{2,4}-\d{2,4})\b/u', $t);
}
function senseLabel(string $t): ?array {
    if (preg_match('/^((?:[IVX]+(?:\.\d+)?(?:\.[a-z])?|\d+(?:\.[a-z])?))\.\s*(.*)$/us', $t, $m)) return [$m[1], $m[2]];
    return null;
}
function senseId(string $label): string { return preg_replace('/[^A-Za-z0-9]/', '', $label); }
function labelDepth(string $label): int { return substr_count($label,'.')+1; }

function richRuns(array $runs, string $class='definition'): string {
    $out=''; foreach ($runs as $r) $out .= runHtml($r,$class,false); return $out;
}

/**
 * Render a sense definition while restoring word boundaries lost when Word
 * splits adjacent words into differently-formatted runs.  We only insert a
 * space when the source has no whitespace and both sides look like word text;
 * punctuation therefore remains attached to the preceding word.
 */
function senseDefinitionHtml(array $runs): string {
    // Word already records the intended spaces explicitly (usually in
    // w:t elements with xml:space="preserve").  Do not infer spaces at run
    // boundaries: Word may split a single word across runs (e.g. "carcas" +
    // "s" or "T" + "he") for editing-history reasons.
    return richRuns($runs, 'definition');
}
function paraAfterLabel(Para $p, string $label): array {
    $need = strlen($label)+1; // label plus final dot
    $runs=[]; $left=$need;
    foreach ($p->runs as $r) {
        $c=clone $r;
        if ($left>0) {
            $len=strlen($c->text);
            if ($len <= $left) { $left-=$len; continue; }
            $c->text=substr($c->text,$left); $left=0;
        }
        // Strip only the leading whitespace immediately after the sense
        // label.  Do not strip leading whitespace from every subsequent run:
        // those spaces are significant and are explicitly stored by Word.
        if (empty($runs)) {
            $c->text=preg_replace('/^\s+/u','',$c->text,1) ?? $c->text;
        }
        if ($c->text!=='') $runs[]=$c;
    }
    return $runs;
}

function buildForms(PDO $dbh, array $paras, array &$warnings): string {
    $start=null;$end=null;
    foreach($paras as $i=>$p){ if(strcasecmp($p->text(),'Forms')===0)$start=$i+1; if(strcasecmp($p->text(),'Senses')===0){$end=$i;break;} }
    if($start===null||$end===null)return '';
    $html=''; $openSections=0; $ulOpen=false;
    for($i=$start;$i<$end;$i++){
        $p=$paras[$i];$t=$p->text(); if($t==='')continue;
        if(isCitationStart($t)){
            if(!$ulOpen){$html.='<ul class="forms-citations">';$ulOpen=true;}
            [$raw,$marker]=stripMarker($t); $runs=removeMarkerFromRuns($p->runs); [$date,$rest]=splitCitationRuns($runs);
            $html.='<li><div class="date">'.dateHtml($date).'</div><div class="citation">'.citationBodyHtml($dbh,$rest,$marker,'quote',true,$warnings).'</div></li>';
            continue;
        }
        if($ulOpen){$html.='</ul>';$ulOpen=false;}
        $low=mb_strtolower(rtrim($t,':'),'UTF-8');
        if(in_array($low,['singular','plural'],true)){
            // close case section, and number section when moving to a new top-level number heading
            if($openSections>0){$html.=str_repeat('</section>', $openSections);$openSections=0;}
            $html.='<section><h3>'.richRuns($p->runs,'').'</h3>'; $openSections=1;
        } elseif(preg_match('/^(nominative|genitive|dative|vocative)$/u',$low)){
            if($openSections>1){$html.='</section>';$openSections=1;}
            $html.='<section><h4>'.esc($low).'</h4>'; $openSections=2;
        } else {
            // A grammatical grouping such as "Attributive following nm", Predicative, Adverbial.
            if($openSections>0){$html.=str_repeat('</section>', $openSections);$openSections=0;}
            $html.='<section><h3>'.richRuns($p->runs,'').'</h3>'; $openSections=1;
        }
    }
    if($ulOpen)$html.='</ul>'; if($openSections)$html.=str_repeat('</section>',$openSections);
    return $html;
}

function buildSenseCitation(PDO $dbh, Para $meta, Para $quote, array &$warnings): string {
    [$qt,$marker]=stripMarker($quote->text());
    [$date,$rest]=splitCitationRuns($meta->runs);
    $ref=citationBodyHtml($dbh,$rest,$marker,'reference',false,$warnings);
    // Quote formatting comes from quote paragraph; remove marker and render as quote.
    $qr=removeMarkerFromRuns($quote->runs); $qh=''; foreach($qr as $r)$qh.=runHtml($r,'quote');
    return '<li><div class="date">'.dateHtml($date).'</div><div class="citation">'.$ref.'<br>'.$qh.'</div></li>';
}

function buildSenses(PDO $dbh, array $paras, array &$warnings): string {
    $start=null; foreach($paras as $i=>$p)if(strcasecmp($p->text(),'Senses')===0){$start=$i+1;break;}
    if($start===null)return '';
    $html='<ul>'; $i=$start; $senseOpen=false; $citOpen=false; $currentId=null;
    while($i<count($paras)){
        $p=$paras[$i];$t=$p->text(); if($t===''){ $i++; continue; }
        $lab=senseLabel($t);
        if($lab){
            if($citOpen){$html.='</ul></div>';$citOpen=false;}
            if($senseOpen){$html.='</li>';$senseOpen=false;}
            [$label,$def]=$lab; $id=senseId($label); $currentId=$id;
            $after=paraAfterLabel($p,$label); $depth=labelDepth($label);
            // A pure structural heading (e.g. II. With a modifier.) has no following citation.
            $j=$i+1; while($j<count($paras)&&$paras[$j]->isBlank())$j++;
            $next=$j<count($paras)?$paras[$j]->text():'';
            $hasCitation=isCitationStart($next);
            // Also allow an explanatory continuation/subheading before citations.
            if(!$hasCitation && $j<count($paras) && !senseLabel($next)) {
                $k=$j+1; while($k<count($paras)&&$paras[$k]->isBlank())$k++;
                $hasCitation=$k<count($paras)&&isCitationStart($paras[$k]->text());
            }
            if(!$hasCitation && $depth===1){
                $html.='<li><strong>'.esc($label).'.</strong> '.senseDefinitionHtml($after).'</li>'; $i++; continue;
            }
            $html.='<li><p><strong>'.esc($label).'.</strong> '.senseDefinitionHtml($after).'&nbsp;';
            $html.='<a class="fnag-toggle" href="#fnag-sense-'.$id.'" data-bs-toggle="collapse" role="button" aria-expanded="true" aria-controls="fnag-sense-'.$id.'"><span class="fas fa-sort">&nbsp;</span></a></p>';
            $html.='<div class="collapse show sense-list" id="fnag-sense-'.$id.'">'; $senseOpen=true;
            $i++; continue;
        }
        if($senseOpen && isCitationStart($t)){
            if(!$citOpen){$html.='<ul>'; $citOpen=true;}
            $j=$i+1; while($j<count($paras)&&$paras[$j]->isBlank())$j++;
            if($j<count($paras) && !senseLabel($paras[$j]->text()) && !isCitationStart($paras[$j]->text())){
                $html.=buildSenseCitation($dbh,$p,$paras[$j],$warnings); $i=$j+1; continue;
            }
            $warnings[]="Sense citation metadata without following quote: $t"; $i++; continue;
        }
        if($senseOpen){
            // Intermediate prose/subheading inside a sense (e.g. possessive/prepositional).
            if($citOpen){$html.='</ul>';$citOpen=false;}
            $html.='<p>'.richRuns($p->runs).'</p>'; $i++; continue;
        }
        $warnings[]="Unclassified Senses paragraph: $t"; $i++;
    }
    if($citOpen)$html.='</ul></div>'; else if($senseOpen)$html.='</div>';
    if($senseOpen)$html.='</li>'; $html.='</ul>';
    return $html;
}

$paras=readDocx($input); $warnings=[];
$forms=buildForms($dbh,$paras,$warnings); $senses=buildSenses($dbh,$paras,$warnings);
file_put_contents($outDir.'/forms.html',$forms."\n");
file_put_contents($outDir.'/senses.html',$senses."\n");
file_put_contents($outDir.'/warnings.txt',implode("\n",$warnings).($warnings?"\n":""));
fwrite(STDOUT,"Wrote forms.html, senses.html and warnings.txt to $outDir\n");

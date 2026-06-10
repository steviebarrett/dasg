<?php

require_once '../includes/include.php';

// require a logged in user with admin privileges for all access
Functions::requireAdmin();

$dbh = DB::getDatabaseHandle();

//get all records
$sth = $dbh->prepare("SELECT * FROM macinnes_record ORDER BY CAST(id AS unsigned) ASC");
$sth->execute();
$records = $sth->fetchAll();
$recordHtml = '<option>-- select a record --</option>';

foreach ($records as $record) {
    $recordHtml .= '<option value="' . $record["id"] . '">' . $record["standardised_headword"] . '</option>';
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>MacInnes Records</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/super-build/ckeditor.js"></script>

    <style>
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.35rem;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.04em;
        }
    </style>
</head>

<body class="bg-light">

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">MacInnes Records</h1>
        <button class="btn btn-primary" id="newRecordBtn">New record</button>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form id="searchForm" class="row g-2">
                <div class="col-md-5">
                    <input type="search" class="form-control" id="q" placeholder="Search headword, context, meaning, translation...">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" id="location" placeholder="Location">
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control" id="box_number_search" placeholder="Box number">
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-dark" type="submit">Search</button>
                </div>
            </form>
        </div>
    </div>

    <div id="alertBox"></div>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    Records <span class="badge text-bg-secondary">0</span>
                </div>
                <div class="card-body">
                    <select class="form-select" id="recordSelect">
                        <?= $recordHtml ?>
                    </select>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    Results <span class="badge text-bg-secondary" id="resultCount">0</span>
                </div>
                <div class="list-group list-group-flush" id="resultsList"></div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">View / edit record</div>
                <div class="card-body">
                    <form id="recordForm">
                        <input type="hidden" id="id" name="id">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Standardised headword</label>
                                <input class="form-control" id="standardised_headword" name="standardised_headword" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Headword as given</label>
                                <input class="form-control" id="headword_given" name="headword_given">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Context</label>
                                <textarea class="form-control" id="context" name="context" rows="4"></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Meaning</label>
                                <textarea class="form-control" id="meaning" name="meaning" rows="3"></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Translation given</label>
                                <textarea class="form-control" id="translation_given" name="translation_given" rows="3"></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Geographical location</label>
                                <input class="form-control" id="geographical_location" name="geographical_location">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Box number</label>
                                <input class="form-control" id="box_number_edit" name="box_number">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Additional information</label>
                                <textarea class="form-control" id="additional_information" name="additional_information" rows="4"></textarea>
                            </div>
                        </div>

                        <div class="mt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-success">Save</button>
                            <button type="button" class="btn btn-outline-secondary" id="clearFormBtn">Clear</button>
                            <button type="button" class="btn btn-outline-danger ms-auto" id="deleteBtn">Delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    // Create the editors for main record fields
    const editorFields = [
        'context',
        'meaning',
        'translation_given',
        'additional_information'
    ];

    editorFields.forEach(function(field) {
        createEditor(field);
    });

    const ajaxUrl = 'ajax.php';

    function showAlert(message, type = 'success') {
        document.getElementById('alertBox').innerHTML =
            '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
            message +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
            '</div>';
    }

    function escapeHtml(value) {
        if (value === null || value === undefined) {
            return '';
        }

        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function clearForm() {
        document.getElementById('recordForm').reset();
        document.getElementById('id').value = '';

        editorFields.forEach(function(field) {
            if (window[field]) {
                window[field].setData('');
            }
        });
    }

    async function loadRecords() {
        const params = new URLSearchParams({
            action: 'search',
            q: document.getElementById('q').value,
            location: document.getElementById('location').value,
            box_number: document.getElementById('box_number_search').value
        });

        const response = await fetch(ajaxUrl + '?' + params.toString());
        const data = await response.json();

        if (data.error) {
            showAlert(data.error, 'danger');
            return;
        }

        const list = document.getElementById('resultsList');
        list.innerHTML = '';
        document.getElementById('resultCount').textContent = data.length;

        if (data.length === 0) {
            list.innerHTML = '<div class="list-group-item text-muted">No records found.</div>';
            return;
        }

        data.forEach(function(record) {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'list-group-item list-group-item-action';
            item.innerHTML =
                '<div class="fw-bold">' + escapeHtml(record.standardised_headword) + '</div>' +
                '<div>' + escapeHtml(record.headword_given || '') + '</div>' +
                '<small class="text-muted">' +
                escapeHtml(record.geographical_location || '') +
                (record.box_number ? ' · Box ' + escapeHtml(record.box_number) : '') +
                '</small>';

            item.addEventListener('click', function() {
                loadRecord(record.id);
            });

            list.appendChild(item);
        });
    }

    async function loadRecord(id) {
        const params = new URLSearchParams({
            action: 'get',
            id: id
        });

        const response = await fetch(ajaxUrl + '?' + params.toString());
        const record = await response.json();

        if (record.error) {
            showAlert(record.error, 'danger');
            return;
        }

        Object.keys(record).forEach(function(key) {
            if (editorFields.includes(key) && window[key]) {
                window[key].setData(record[key] || '');
                return;
            }

            const field = document.getElementById(key);
            if (field) {
                field.value = record[key] || '';
            }
        });

        document.getElementById('box_number_edit').value = record.box_number || '';
    }

    async function saveRecord(event) {
        event.preventDefault();

        editorFields.forEach(function(field) {
            const textarea = document.getElementById(field);
            if (textarea && window[field]) {
                textarea.value = window[field].getData();
            }
        });

        const formData = new FormData(document.getElementById('recordForm'));
        formData.append('action', 'save');

        const response = await fetch(ajaxUrl, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.error) {
            showAlert(data.error, 'danger');
            return;
        }

        showAlert(data.msg || 'Record saved.');
        document.getElementById('id').value = data.id;
        loadRecords();
    }

    async function deleteRecord() {
        const id = document.getElementById('id').value;

        if (!id) {
            showAlert('No record selected.', 'warning');
            return;
        }

        if (!confirm('Delete this record?')) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);

        const response = await fetch(ajaxUrl, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.error) {
            showAlert(data.error, 'danger');
            return;
        }

        showAlert(data.msg || 'Record deleted.');
        clearForm();
        loadRecords();
    }

    document.getElementById('recordSelect').addEventListener('change', function() {
        if (this.value) {
            loadRecord(this.value);
        }
    });

    document.getElementById('searchForm').addEventListener('submit', function(event) {
        event.preventDefault();
        loadRecords();
    });

    document.getElementById('recordForm').addEventListener('submit', saveRecord);
    document.getElementById('newRecordBtn').addEventListener('click', clearForm);
    document.getElementById('clearFormBtn').addEventListener('click', clearForm);
    document.getElementById('deleteBtn').addEventListener('click', deleteRecord);

    loadRecords();

    function SpecialCharactersSimple( editor ) {
        editor.plugins.get('SpecialCharacters').addItems('Simple', [

            {title: 'Close front unrounded vowel', character: 'i'},
            {title: 'Close front rounded vowel', character: 'y'},
            {title: 'Close central unrounded vowel', character: 'ɨ'},
            {title: 'Close central rounded vowel', character: 'ʉ'},
            {title: 'Close back unrounded vowel', character: 'ɯ'},
            {title: 'Close back rounded vowel', character: 'u'},

            {title: 'Near-close near-front unrounded vowel', character: 'ɪ'},
            {title: 'Near-close near-front rounded vowel', character: 'ʏ'},
            {title: 'Near-close near-back rounded vowel', character: 'ʊ'},

            {title: 'Close-mid front unrounded vowel', character: 'e'},
            {title: 'Close-mid front rounded vowel', character: 'ø'},
            {title: 'Close-mid central unrounded vowel', character: 'ɘ'},
            {title: 'Close-mid central rounded vowel', character: 'ɵ'},
            {title: 'Close-mid back unrounded vowel', character: 'ɤ'},
            {title: 'Close-mid back rounded vowel', character: 'o'},

            {title: 'Mid central vowel', character: 'ə'},

            {title: 'Open-mid front unrounded vowel', character: 'ɛ'},
            {title: 'Open-mid front rounded vowel', character: 'œ'},
            {title: 'Open-mid central unrounded vowel', character: 'ɜ'},
            {title: 'Open-mid central rounded vowel', character: 'ɞ'},
            {title: 'Open-mid back unrounded vowel', character: 'ʌ'},
            {title: 'Open-mid back rounded vowel', character: 'ɔ'},

            {title: 'Near-open front unrounded vowel', character: 'æ'},
            {title: 'Near-open central vowel', character: 'ɐ'},

            {title: 'Open front unrounded vowel', character: 'a'},
            {title: 'Open front rounded vowel', character: 'ɶ'},
            {title: 'Open back unrounded vowel', character: 'ɑ'},
            {title: 'Open back rounded vowel', character: 'ɒ'},

            {title: 'Voiceless bilabial plosive', character: 'p'},
            {title: 'Voiced bilabial plosive', character: 'b'},
            {title: 'Voiceless alveolar plosive', character: 't'},
            {title: 'Voiced alveolar plosive', character: 'd'},
            {title: 'Voiceless retroflex plosive', character: 'ʈ'},
            {title: 'Voiced retroflex plosive', character: 'ɖ'},
            {title: 'Voiceless palatal plosive', character: 'c'},
            {title: 'Voiced palatal plosive', character: 'ɟ'},
            {title: 'Voiceless velar plosive', character: 'k'},
            {title: 'Voiced velar plosive', character: 'ɡ'},
            {title: 'Voiceless uvular plosive', character: 'q'},
            {title: 'Voiced uvular plosive', character: 'ɢ'},
            {title: 'Glottal stop', character: 'ʔ'},

            {title: 'Voiceless labiodental fricative', character: 'f'},
            {title: 'Voiced labiodental fricative', character: 'v'},
            {title: 'Voiceless dental fricative', character: 'θ'},
            {title: 'Voiced dental fricative', character: 'ð'},
            {title: 'Voiceless alveolar fricative', character: 's'},
            {title: 'Voiced alveolar fricative', character: 'z'},
            {title: 'Voiceless postalveolar fricative', character: 'ʃ'},
            {title: 'Voiced postalveolar fricative', character: 'ʒ'},
            {title: 'Voiceless retroflex fricative', character: 'ʂ'},
            {title: 'Voiced retroflex fricative', character: 'ʐ'},
            {title: 'Voiceless palatal fricative', character: 'ç'},
            {title: 'Voiced palatal fricative', character: 'ʝ'},
            {title: 'Voiceless velar fricative', character: 'x'},
            {title: 'Voiced velar fricative', character: 'ɣ'},
            {title: 'Voiceless uvular fricative', character: 'χ'},
            {title: 'Voiced uvular fricative', character: 'ʁ'},
            {title: 'Voiceless pharyngeal fricative', character: 'ħ'},
            {title: 'Voiced pharyngeal fricative', character: 'ʕ'},
            {title: 'Voiceless glottal fricative', character: 'h'},
            {title: 'Voiced glottal fricative', character: 'ɦ'},

            {title: 'Bilabial nasal', character: 'm'},
            {title: 'Labiodental nasal', character: 'ɱ'},
            {title: 'Alveolar nasal', character: 'n'},
            {title: 'Retroflex nasal', character: 'ɳ'},
            {title: 'Palatal nasal', character: 'ɲ'},
            {title: 'Velar nasal', character: 'ŋ'},
            {title: 'Uvular nasal', character: 'ɴ'},

            {title: 'Alveolar lateral approximant', character: 'l'},
            {title: 'Retroflex lateral approximant', character: 'ɭ'},
            {title: 'Palatal lateral approximant', character: 'ʎ'},
            {title: 'Velar lateral approximant', character: 'ʟ'},

            {title: 'Alveolar approximant', character: 'ɹ'},
            {title: 'Retroflex approximant', character: 'ɻ'},
            {title: 'Palatal approximant', character: 'j'},
            {title: 'Labial-velar approximant', character: 'w'},

            {title: 'Bilabial trill', character: 'ʙ'},
            {title: 'Alveolar trill', character: 'r'},
            {title: 'Uvular trill', character: 'ʀ'},

            {title: 'Alveolar tap', character: 'ɾ'},
            {title: 'Retroflex flap', character: 'ɽ'},

            {title: 'Primary stress', character: 'ˈ'},
            {title: 'Secondary stress', character: 'ˌ'},
            {title: 'Long', character: 'ː'},
            {title: 'Half long', character: 'ˑ'},
            {title: 'Extra short', character: '̆'},
            {title: 'Syllabic', character: '̩'},
            {title: 'Non-syllabic', character: '̯'},
            {title: 'Aspirated', character: 'ʰ'},
            {title: 'Palatalized', character: 'ʲ'},
            {title: 'Velarized', character: 'ˠ'},
            {title: 'Pharyngealized', character: 'ˤ'},
            {title: 'Nasalized', character: '̃'},
            {title: 'Rhoticity', character: '˞'},

            {title: 'Rising tone', character: '↗'},
            {title: 'Falling tone', character: '↘'},

            {title: 'Click dental', character: 'ǀ'},
            {title: 'Click lateral', character: 'ǁ'},
            {title: 'Click alveolar', character: 'ǃ'},
            {title: 'Click palatal', character: 'ǂ'},
            {title: 'Click bilabial', character: 'ʘ'}


        ]);
    }

    /*
        Creates and stores an instance of CKEditor
     */
    function createEditor(id) {
        // Visit https://ckeditor.com/docs/ckeditor5/latest/features/index.html to browse all the features.
        CKEDITOR.ClassicEditor.create(document.getElementById(id), {
            extraPlugins: [SpecialCharactersSimple],
            // https://ckeditor.com/docs/ckeditor5/latest/features/toolbar/toolbar.html#extended-toolbar-configuration-format
            toolbar: {
                items: [
                    '|',
                    'bold', 'italic', 'strikethrough', 'underline', 'subscript', 'superscript', 'removeFormat', '|',
                    'fontColor','|',
                    'undo', 'redo',
                    'specialCharacters',  'sourceEditing'
                ],
                shouldNotGroupWhenFull: true
            },
            // Changing the language of the interface requires loading the language file using the <script> tag.
            // language: 'es',
            list: {
                properties: {
                    styles: true,
                    startIndex: true,
                    reversed: true
                }
            },

            // Be careful with the setting below. It instructs CKEditor to accept ALL HTML markup.
            // https://ckeditor.com/docs/ckeditor5/latest/features/general-html-support.html#enabling-all-html-features
            htmlSupport: {
                allow: [
                    {
                        name: /.*/,
                        attributes: false,
                        classes: true,
                        styles: false
                    }
                ]
            },
            // Be careful with enabling previews
            // https://ckeditor.com/docs/ckeditor5/latest/features/html-embed.html#content-previews
            htmlEmbed: {
                showPreviews: true
            },

            // The "superbuild" contains more premium features that require additional configuration, disable them below.
            // Do not turn them on unless you read the documentation and know how to configure them and setup the editor.
            removePlugins: [
                // These two are commercial, but you can try them out without registering to a trial.
                'ExportPdf',
                'ExportWord',
                'AIAssistant',
                'CKBox',
                'CKFinder',
                'EasyImage',
                'Base64UploadAdapter',
                'RealTimeCollaborativeComments',
                'RealTimeCollaborativeTrackChanges',
                'RealTimeCollaborativeRevisionHistory',
                'PresenceList',
                'Comments',
                'TrackChanges',
                'TrackChangesData',
                'RevisionHistory',
                'Pagination',
                'WProofreader',
                // Careful, with the Mathtype plugin CKEditor will not load when loading this sample
                // from a local file system (file://) - load this site via HTTP server if you enable MathType.
                'MathType',
                // The following features are part of the Productivity Pack and require additional license.
                'SlashCommand',
                'Template',
                'DocumentOutline',
                'FormatPainter',
                'TableOfContents',
                'PasteFromOfficeEnhanced',
                'CaseChange'
            ]
        })
            .then(editor => {
                window[id] = editor;    // save the instance to the window for reuse
            })
            .catch(error => {
                console.error('CKEditor failed to initialise for #' + id, error);
            });
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
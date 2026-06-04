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
                    <input type="text" class="form-control" id="box_number" placeholder="Box number">
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
    }

    async function loadRecords() {
        const params = new URLSearchParams({
            action: 'search',
            q: document.getElementById('q').value,
            location: document.getElementById('location').value,
            box_number: document.getElementById('box_number').value
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
            const field = document.getElementById(key);
            if (field) {
                field.value = record[key] || '';
            }
        });

        document.getElementById('box_number_edit').value = record.box_number || '';
    }

    async function saveRecord(event) {
        event.preventDefault();

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
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
<?php

require_once '../includes/include.php';

// require a logged in user with admin privileges for all access
Functions::requireAdmin();

$dbh = DB::getDatabaseHandle();

header('Content-Type: application/json; charset=utf-8');

function respond(array $data): void
{
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function fail(string $message, int $status = 400): void
{
    http_response_code($status);
    respond([
        'success' => false,
        'message' => $message
    ]);
}

function clean(?string $value): ?string
{
    if ($value === null) {
        return null;
    }

    $value = trim($value);

    return $value === '' ? null : $value;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    if ($action === 'list' || $action === 'search') {
        $q = clean($_GET['q'] ?? null);
        $location = clean($_GET['location'] ?? null);
        $boxNumber = clean($_GET['box_number'] ?? null);

        $where = [];
        $params = [];

        if ($q !== null) {
            $where[] = "(
                standardised_headword LIKE :q
                OR headword_given LIKE :q
                OR context LIKE :q
                OR meaning LIKE :q
                OR translation_given LIKE :q
                OR additional_information LIKE :q
            )";
            $params[':q'] = '%' . $q . '%';
        }

        if ($location !== null) {
            $where[] = "geographical_location LIKE :location";
            $params[':location'] = '%' . $location . '%';
        }

        if ($boxNumber !== null) {
            $where[] = "box_number LIKE :box_number";
            $params[':box_number'] = '%' . $boxNumber . '%';
        }

        $sql = "
            SELECT
                id,
                standardised_headword,
                headword_given,
                meaning,
                geographical_location,
                box_number,
                updated_at
            FROM macinnes_record
        ";

        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY standardised_headword, headword_given, id LIMIT 500";

        $stmt = $dbh->prepare($sql);
        $stmt->execute($params);

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($action === 'search') {
            respond($records);
        }

        respond([
            'success' => true,
            'records' => $records
        ]);
    }

    if ($action === 'get') {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            fail('Missing or invalid record ID.');
        }

        $stmt = $dbh->prepare("SELECT * FROM macinnes_record WHERE id = :id");
        $stmt->execute([':id' => $id]);

        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$record) {
            fail('Record not found.', 404);
        }

        respond($record);
    }

    if ($action === 'save') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        $standardisedHeadword = clean($_POST['standardised_headword'] ?? null);

        if ($standardisedHeadword === null) {
            fail('Standardised headword is required.');
        }

        $params = [
            ':standardised_headword' => $standardisedHeadword,
            ':headword_given' => clean($_POST['headword_given'] ?? null),
            ':context' => clean($_POST['context'] ?? null),
            ':meaning' => clean($_POST['meaning'] ?? null),
            ':translation_given' => clean($_POST['translation_given'] ?? null),
            ':geographical_location' => clean($_POST['geographical_location'] ?? null),
            ':box_number' => clean($_POST['box_number'] ?? null),
            ':additional_information' => clean($_POST['additional_information'] ?? null)
        ];

        if ($id) {
            $params[':id'] = $id;

            $sql = "
                UPDATE macinnes_record
                SET
                    standardised_headword = :standardised_headword,
                    headword_given = :headword_given,
                    context = :context,
                    meaning = :meaning,
                    translation_given = :translation_given,
                    geographical_location = :geographical_location,
                    box_number = :box_number,
                    additional_information = :additional_information
                WHERE id = :id
            ";

            $stmt = $dbh->prepare($sql);
            $stmt->execute($params);

            respond([
                'success' => true,
                'id' => $id,
                'message' => 'Record updated.'
            ]);
        }

        $sql = "
            INSERT INTO macinnes_record (
                standardised_headword,
                headword_given,
                context,
                meaning,
                translation_given,
                geographical_location,
                box_number,
                additional_information
            ) VALUES (
                :standardised_headword,
                :headword_given,
                :context,
                :meaning,
                :translation_given,
                :geographical_location,
                :box_number,
                :additional_information
            )
        ";

        $stmt = $dbh->prepare($sql);
        $stmt->execute($params);

        respond([
            'success' => true,
            'id' => (int)$dbh->lastInsertId(),
            'message' => 'Record created.'
        ]);
    }

    if ($action === 'delete') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            fail('Missing or invalid record ID.');
        }

        $stmt = $dbh->prepare("DELETE FROM macinnes_record WHERE id = :id");
        $stmt->execute([':id' => $id]);

        respond([
            'success' => true,
            'message' => 'Record deleted.'
        ]);
    }

    fail('Unknown action.', 404);

} catch (Throwable $e) {
    fail('Database error: ' . $e->getMessage(), 500);
}
<?php
declare(strict_types=1);

namespace models;

if (!defined('DASG_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Forbidden');
}
final class record
{
    private string $ai;
    private database $db;
    private array $props = [];        // props pulled from DB
    private ?string $transcription = null;

    /** @var array<string,string> */
    private static array $gaelicFieldMap = [
        "ai" => "Àireamh-aithneachaidh",
        "title" => "Tiotal",
        "alternative_title" => "Tiotal eile",
        "air" => "Fonn",
        "first_line_chorus" => "A’ chiad sreath (séist)",
        "first_line_verse" => "A’ chiad sreath (rann)",
        "classifications" => "Seòrsachan",
        "subjects" => "Cuspairean",
        "structure" => "Structar",
        "place_of_origin" => "Tùs-àite",
        "composer_first_name" => "Ainm a’ bhàird",
        "composer_last_name" => "Cinneadh a’ bhàird",
        "composer_patronymic" => "Sloinneadh / ainmean eile a' bhàird",
        "composer_dates" => "Bliadhnachan a’ bhàird",
        "composer_gender" => "Gné a' bhàird",
        "community" => "Coimhearsnachd",
        "county" => "Siorramachd",
        "era_of_poetry" => "Linn na bàrdachd",
        "original_format" => "Cruth tùsail",
        "singer" => "Seinneadair",
        "singer_location" => "Àite an t-seinneadair",
        "date_recorded" => "Ceann-latha clàraidh",
        "collector" => "Neach-cruinneachaidh",
        "collection_title" => "Tiotal a’ chruinneachaidh",
        "collection_location" => "Àite a' chruinneachaidh",
        "collection_number" => "Àireamh-bhratha",
        "publication_title" => "Tiotal an fhoillseachain",
        "editor" => "Neach-deasachaidh",
        "publisher" => "Foillsichear",
        "publication_date" => "Ceann-là foillseachaidh",
        "page_number" => "Àireamh na duilleige",
        "online_access" => "Air loidhne",
        "notes_1" => "Nòtaichean 1",
        "notes_2" => "Nòtaichean 2",
        "notes_3" => "Nòtaichean 3",
        "notes_4" => "Nòtaichean 4"
    ];

    public function __construct(string $ai)
    {
        $this->ai = $ai;
        $this->db = new database();
    }

    /**
     * Queries the database for record properties and sets them appropriately
     * @return $this
     */
    public function load(): self
    {
        if ($this->ai === '-1') { // ai flag for creating a new record
            $this->_loadTemplate();
            return $this;
        }

        $sql = "SELECT * FROM record WHERE ai = :ai";
        $rows = $this->db->fetch($sql, [":ai" => $this->getAI()]);

        if (empty($rows) || !isset($rows[0]) || !is_array($rows[0])) {
            // empty props if not found (avoid notices)
            $this->props = [];
            $this->transcription = null;
            return $this;
        }

        foreach ($rows[0] as $propName => $value) {
            $this->setPropValue((string)$propName, $value);
        }

        // legacy behaviour retained
        $this->getTranscriptionLink();

        return $this;
    }

    /**
     * Returns an HTML link if transcription exists; otherwise null.
     * (Same behaviour as before: nothing returned if none exists.)
     */
    public function getTranscriptionLink(): ?string
    {
        $sql = "SELECT text FROM transcription WHERE record_ai = :ai";
        $result = $this->db->fetch($sql, [":ai" => $this->getAI()]);

        if (!empty($result)) {
            $this->transcription = (string)($result[0]["text"] ?? '');

            // safe encode ai for the URL and for HTML context
            $aiUrl = functions::urlEncode($this->getAI());

            return '<a target="_blank" rel="noopener noreferrer" href="transcription.php?ai=' . $aiUrl . '">link</a>';
        }

        return null;
    }

    public function getTranscription(): ?string
    {
        return $this->transcription;
    }

    /**
     * Initialises required properties for a new blank record
     * @return $this
     */
    private function _loadTemplate(): self
    {
        $model = new records();
        $fields = $model->getAllFieldNames();

        foreach ($fields as $field) {
            $this->setPropValue((string)$field, "");
        }

        return $this;
    }

    /**
     * Persist record data (REPLACE INTO) using a whitelist of real DB columns.
     * Values are parameterised; column names are validated against schema.
     */
    public function save(array $data): void
    {
        // Allow only real DB columns (prevents mass assignment)
        $recordsModel = new records();
        $allowed = array_flip($recordsModel->getAllFieldNames());

        // Keep only allowed fields
        $clean = [];
        foreach ($data as $field => $value) {
            $field = (string)$field;
            if (!isset($allowed[$field])) {
                continue;
            }

            // handle multiples (array): e.g. classifications
            if (is_array($value)) {
                $value = implode(" , ", $value);
            }

            $clean[$field] = $value;
        }

        if (empty($clean)) {
            return;
        }

        // Require ai for replace/update semantics
        if (!isset($clean['ai']) || (string)$clean['ai'] === '') {
            throw new \InvalidArgumentException("Missing ai");
        }
        if (!$this->isValidAi((string)$clean['ai'])) {
            throw new \InvalidArgumentException("Invalid ai");
        }

        $fields = array_keys($clean);
        $fieldList = implode(', ', $fields);

        $placeholders = array_fill(0, count($fields), '?');
        $placeholderList = implode(', ', $placeholders);

        $values = array_values($clean);

        $sql = "REPLACE INTO record ({$fieldList}) VALUES({$placeholderList})";
        $this->db->exec($sql, $values);

        $this->_updateTracking((string)$clean["ai"]);
    }

    private function isValidAi(string $ai): bool
    {
        return (bool)preg_match('/^[A-Za-z0-9_-]{1,64}$/', $ai);
    }

    private function _updateTracking(string $ai): void
    {
        $sql = "REPLACE INTO recordTracking VALUES(?, now())";
        $this->db->exec($sql, [$ai]);
    }

    public function getAI(): string
    {
        return $this->ai;
    }

    public function getPropValue(string $propName): mixed
    {
        return $this->props[$propName] ?? null;
    }

    public function getAllProps(): array
    {
        return $this->props;
    }

    public function setPropValue(string $propName, mixed $value): void
    {
        $this->props[$propName] = $value;
    }

    public static function getGaelicFieldMap(): array
    {
        return self::$gaelicFieldMap;
    }
}

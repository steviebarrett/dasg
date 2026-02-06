<?php

namespace views;

use models;

class record
{
    private $_record;
    private $_origin;

    // an array of fields with controlled vocabularies
    private $_controlOptions = array(
        "classifications" => array(
            "Ballad", "Bawdy", "Clapping", "Complaint", "Dialogue", "Drinking", "Elegy", "Exile", "Flyting", "Historical",
            "Homeland", "Humorous", "Instructive", "Lament", "Local events and characters", "Love", "Lullaby", "Macaronic",
            "Milling", "Nature", "Pibroch", "Political", "Port-a-beul", "Praise", "Rann / Duan", "Religious", "Sailing",
            "Satire", "Spiritual", "Supernatural", "Work"
        ),
        "structure" => array(
            "One line verse", "One line verse / Three line Chorus", "One line verse / split chorus", "Two line verse",
            "Two line verse / Two line chorus", "Two line verse / Three line chorus", "Two line verse / Four line chorus",
            "Two line verse / Woven", "Three line verse", "Three line verse / Two line chorus",
            "Three line verse / Three line chorus", "Three line verse / Four line chorus", "Three line verse / Woven",
            "Four line verse", "Four line verse / Two line chorus", "Four line verse / Three line chorus",
            "Four line verse / Four line chorus", "Four line verse / Five line chorus", "Five line verse", "Six line verse",
            "Six line verse / Two line chorus", "Six line verse / Three line chorus", "Six line verse / Four line chorus",
            "Seven line verse", "Eight line verse", "Eight line verse / Four line chorus", "Eight line verse / Eight line chorus",
            "Nine line verse", "Ten line verse", "Twelve line verse", "Sixteen line verse", "Split chorus", "Woven",
            "Woven / Split chorus", "Irregular"
        ),
        "place_of_origin" => array(
            "Scotland", "Nova Scotia", "Prince Edward Island", "Ontario", "United States", "Other", "Unknown"
        ),
        "gender_voice" => array(
            "Male", "Female", "Male-Female", "Unknown", "Not Applicable"
        ),
        "composer_gender" => array(
            "Male", "Female", "Other", "Unknown"
        ),
        "general_material_description" => array(
            "Sound Recording", "Manuscript", "Publication"
        )
    );

    // an array of database fields and the form type required for each field
    private $_formTypes = array(
        "classifications" => "multiple",
        "structure" => "select",
        "place_of_origin" => "select",
        "gender_voice" => "select",
        "composer_gender" => "select",
        "general_material_description" => "select",
        "notes_1" => "text",
        "notes_2" => "text",
        "notes_3" => "text",
        "notes_4" => "text"
    );

    public function __construct($record, $origin = "")
    {
        $this->_record = $record;
        $this->_origin = $origin;
    }

    public function show()
    {
        $id = (string)$this->_record->getAI();
        $idEsc = models\functions::e($id);

        $html = '<div class="container py-5">';

        if (!empty($this->_origin)) {
            // origin appears to be app-generated, but escape it anyway when outputting
            $origin = models\functions::decodeOrigin($this->_origin);
            $originEsc = models\functions::e($origin);

            $html .= <<<HTML
                <div><a href="index.php?{$originEsc}" title="back">&lt;&lt;&lt; back</a></div>
HTML;
        }

        $html .= <<<HTML
            <table class="table">
                <tbody>
HTML;

        $this->_record->load();   // loads the info from the database into the record

        foreach ($this->_record->getAllProps() as $name => $value) {
            $friendlyName = models\functions::getFriendlyName((string)$name);
            $nameEsc = models\functions::e($friendlyName);

            $valueStr = (string)$value;
            $valueOut = models\functions::e($valueStr);

            // If it looks like a URL, render a safe link (instead of outputting raw URL text)
            if (preg_match('~^https?://~i', $valueStr)) {
                $href = models\functions::e($valueStr);
                $valueOut = '<a href="' . $href . '" target="_blank" rel="noopener noreferrer">link</a>';
            }

            $html .= "<tr><td>{$nameEsc}</td><td>{$valueOut}</td></tr>";
        }

        $closeButtonUrl = !empty($_SESSION["loggedIn"])
            ? "index.php?m=admin"
            : "index.php?m=records&a=list";

        $closeButtonUrlEsc = models\functions::e($closeButtonUrl);

        $html .= <<<HTML
            </tbody></table>
            <a class="btn btn-secondary" href="{$closeButtonUrlEsc}" title="close">close</a>
            <!--a class="btn btn-primary" href="index.php?m=record&a=edit&id={$idEsc}&o={$this->_origin}" title="Edit {$idEsc}">edit</a-->
HTML;

        $html .= '</div>';  // end container div
        echo $html;
    }

    public function edit()
    {
        // check for admin status
        if (empty($_SESSION["loggedIn"])) {
            echo <<<HTML
            <h2>Not authorised</h2>
HTML;
            return;
        }

        $displayOnlyFields = array("ai");

        $ai = (string)$this->_record->getAI();
        $aiEsc = models\functions::e($ai);

        $hiddenFields = '<input type="hidden" name="ai" value="' . $aiEsc . '">';

        $id = (int)($_GET["id"] ?? 0);
        if ($id === -1) {    // if we are creating a new record
            $displayOnlyFields = array();
            $hiddenFields = "";
        }

        $html = "";
        if (!empty($this->_origin)) {
            // origin is used inside a URL query string; escape for HTML
            $originEsc = models\functions::e($this->_origin);
            $html .= <<<HTML
                <div><a href="index.php?{$originEsc}" title="back">&lt;&lt;&lt; back</a></div>
HTML;
        }

        $actionUrl = 'index.php?m=record&a=save&id=' . models\functions::e($this->_record->getAI());

        $html .= <<<HTML
            <div class='container py-5'>
            <div class='row'>
            <div class='col-12'>
            <form action="{$actionUrl}" class='edit-record-form' method="post">
HTML;

        $this->_record->load();   // loads the info from the database into the record

        foreach ($this->_record->getAllProps() as $name => $value) {
            $nameStr = (string)$name;
            $friendlyName = models\functions::getFriendlyName($nameStr);

            // headings
            if ($nameStr === 'ai' && (string)$value !== '') {
                $html .= <<<HTML
                    <div class="col-12 mb-3"><h2 class='page-title'>Edit Record</h2></div>
HTML;
            } elseif ($nameStr === 'ai' && (string)$value === '') {
                $html .= <<<HTML
                    <div class="col-12 mb-3"><h2 class='page-title'>Add A Record</h2></div>
HTML;
            }

            $nameEsc = models\functions::e($nameStr);
            $friendlyEsc = models\functions::e($friendlyName);
            $valueStr = (string)$value;

            if (in_array($nameStr, $displayOnlyFields, true)) {
                // display only (escape output)
                $formFieldHtml = '<p class="read-only">' . models\functions::e($valueStr) . '</p>';
            } elseif (isset($this->_formTypes[$nameStr])) {
                $formFieldHtml = $this->_getFormFieldHtml($nameStr, $valueStr);
            } else {
                // default: input text field (escape for attribute)
                $valueEsc = models\functions::e($valueStr);
                $formFieldHtml = <<<HTML
                    <input class="form-control" type="text" id="{$nameEsc}" name="{$nameEsc}" placeholder="{$friendlyEsc}" value="{$valueEsc}">
HTML;
            }

            $html .= <<<HTML
                <div class="form-group">
                    <label for="{$nameEsc}" class="form-label">{$friendlyEsc}</label>
                    {$formFieldHtml}
                </div>
HTML;
        }

        $html .= <<<HTML
                    {$hiddenFields}
                    <a class="btn btn-secondary" href="index.php" title="close">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
                </div>
                </div>
                </div>
HTML;

        echo $html;
        $this->_writeJavascript();
    }

    /**
     * Generates the HTML code required for a particular form element
     * @param string $name : the name of the database field
     * @param string $value : the value of the database field
     * @return string : the HTML for the form element
     */
    private function _getFormFieldHtml($name, $value)
    {
        $nameEsc = models\functions::e($name);

        switch ($this->_formTypes[$name]) {
            case "text":
                $valueEsc = models\functions::e($value);
                $formFieldHtml = <<<HTML
                            <textarea class="form-control" id="{$nameEsc}" name="{$nameEsc}" rows="4">{$valueEsc}</textarea>
HTML;
                break;

            case "select":
                $formFieldHtml = <<<HTML
                            <select name="{$nameEsc}" id="{$nameEsc}" class="form-select">
                                <option value="">--- select ---</option>
HTML;

                foreach ($this->_controlOptions[$name] as $option) {
                    $selected = ((string)$option === (string)$value) ? "selected" : "";
                    $optEsc = models\functions::e($option);

                    $formFieldHtml .= <<<HTML
                                <option value="{$optEsc}" {$selected}>{$optEsc}</option>
HTML;
                }

                $formFieldHtml .= "</select>";
                break;

            case "multiple":
                $formFieldHtml = <<<HTML
                            <select name="{$nameEsc}[]" id="{$nameEsc}" multiple class="form-select">
HTML;

                $selectedOptions = explode(" , ", (string)$value);

                foreach ($this->_controlOptions[$name] as $option) {
                    $selected = in_array($option, $selectedOptions, true) ? "selected" : "";
                    $optEsc = models\functions::e($option);

                    $formFieldHtml .= <<<HTML
                                <option value="{$optEsc}" {$selected}>{$optEsc}</option>
HTML;
                }

                $formFieldHtml .= "</select>";
                $formFieldHtml .= "<div class='form-text'>Ctrl/Command click to select multiple.</div>";
                break;

            default:
                // Fallback safe text input (should not be hit)
                $valueEsc = models\functions::e($value);
                $formFieldHtml = <<<HTML
                    <input class="form-control" type="text" id="{$nameEsc}" name="{$nameEsc}" value="{$valueEsc}">
HTML;
                break;
        }

        return $formFieldHtml;
    }

    private function _writeJavascript()
    {
        echo <<<HTML
            <script>
                $(function() {
                  $('#ai').change(function () {
                    $.getJSON('ajax.php?action=getRecordExists&ai=' + encodeURIComponent($(this).val()), function (data) {
                      if (data.exists == true) {
                        alert("That identifier is already in use");
                        $('#ai').val('').focus();
                      }
                    });
                  });
                });
            </script>
HTML;
    }
}
<?php


if (!defined('DASG_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Forbidden');
}

final class Functions
{
    /**
     * Escapes for HTML output
     * @param mixed $value
     * @return string
     */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Builds application-style URLs
     * @param mixed $value
     * @return string
     */
    public static function urlEncode(mixed $value): string
    {
        $text = str_ireplace(' ', '.', $value);
        return rawurlencode((string)$text);
    }

    public static function getFriendlyFieldName($fieldname, $delimiter)
    {
        $elements = explode($delimiter, (string)$fieldname);
        return ucwords(implode(' ', $elements));
    }

    public static function urlDecode($text)
    {
        $text = str_ireplace('.', ' ', (string)$text);
        return rawurldecode($text);
    }

    public static function showLoginForm($referer = null, $lang = "en")
    {
        // Safe reads
        $logout = !empty($_POST['logout']);
        $email  = isset($_POST['email']) ? trim((string)$_POST['email']) : '';
        $pass   = isset($_POST['password']) ? (string)$_POST['password'] : '';

        if ($logout) {
            unset($_SESSION["user"]);
        } else if (!empty($_SESSION["user"])) {
            self::showLogoutForm();
            return true;
        } else if ($email !== '') {

            $user = Users::getUser($email);

            if (empty($user)) {
                echo "<h3 class=\"error\">Email/Password combination not recognised</h3>";
            } else if ($user->checkPassword($pass) === true) {
                $_SESSION["user"] = $user->getEmail();
                Users::markLoggedIn($user->getEmail());  // updates last logged-in
                self::showLogoutForm();
                return true;
            } else {
                echo "<h3 class=\"error\">Email/Password combination not recognised</h3>";
            }
        }

        $emailText = ["en" => "Email", "gd" => "Seòladh puist-d."];
        $passwordText = ["en" => "Password", "gd" => "Facal-faire"];
        $forgotText = ["en" => "Forgot my password", "gd" => "Dhìochuimhnich mi am facal-faire"];
        $loginText = ["en" => "login", "gd" => "cuir a-staigh"];

        // Guard language
        $lang = in_array($lang, ['en', 'gd'], true) ? $lang : 'en';

        $refererEsc = self::e($referer ?? '');

        $csrfField = Csrf::field();
        echo <<<HTML
            <div id="login">
                <form id="loginForm" method="POST">
                    {$csrfField}
                    <div>
                        <label for="email">{$emailText[$lang]}:</label>
                        <input type="text" id="email" name="email"/>
                    </div>

                    <div>
                        <label for="password">{$passwordText[$lang]}:</label>
                        <input type="password" id="password" name="password"/>
                    </div>

                    <div id="forgotPassLink">
                        <a href="/forgotPassword.php" title="Forgotten password link">{$forgotText[$lang]}</a>
                    </div>

                    <input type="hidden" name="referer" value="{$refererEsc}"/>

                    <input type="submit" value="{$loginText[$lang]}" class="loginSecondCol dasg_bigButton"/>
                </form>
            </div>
HTML;
        return true;
    }

    public static function showLogoutForm()
    {
        $email = $_SESSION["user"] ?? null;
        if (!$email) {
            return;
        }

        $user = Users::getUser($email);
        if (!$user) {
            return;
        }

        $first = self::e($user->getFirstName());
        $last  = self::e($user->getLastName());

        $csrfField = Csrf::field();
        echo <<<HTML
            <div id="logout">
                <p><strong>Logged-in as {$first} {$last}</strong></p>
                <form method="POST">
                    {$csrfField}
                    <input type="hidden" name="logout" value="true"/>
                    <input type="submit" value="logout" class="dasg_smlButton">
                </form>
            </div>
HTML;
    }

    public static function getTextLink($textId)
    {
        $dbh = DB::getDatabaseHandle(DB2_NAME);

        $query = "SELECT link FROM corpus_text WHERE reference_number = :textId";
        $sth = $dbh->prepare($query);
        $sth->bindParam(":textId", $textId);
        $sth->execute();

        // safest + simplest:
        $link = $sth->fetchColumn();
        return $link === false ? null : (string)$link;
    }

    public static function writeError($message)
    {
        $msg = self::e($message);
        echo "<h3 class=\"error\">{$msg}</h3>";
        require_once '../includes/htmlFooter.php';
        die();
    }

    public static function canBeLenited($word)
    {
        $word = (string)$word;
        if (strlen($word) < 2) return false;
        if (substr($word, 1, 1) === 'h') return false;

        $excludeChars = ['h', 'l', 'n', 'r', '?', '*', '~', '[', ']'];
        return !in_array(substr($word, 0, 1), $excludeChars, true);
    }

    public static function getLenited($word)
    {
        $word = (string)$word;
        if (!self::canBeLenited($word)) return $word;
        return self::e(substr_replace($word, "h=", 1, 0));
    }

    public static function getAccentInsensitive($text, $caseSens = true)
    {
        $regExp = "";
        $accentMappedChars = $caseSens
            ? ["aàá", "eèé", "iìí", "oòó", "uùú"]
            : ["aàáAÀÁ", "eèéEÈÉ", "iìíIÌÍ", "oòóOÒÓ", "uùúUÙÚ"];

        foreach (self::str_split_unicode((string)$text) as $char) {
            $replaced = false;
            foreach ($accentMappedChars as $accentMap) {
                if (stristr($accentMap, $char)) {
                    $regExp .= "[{$accentMap}]+";
                    $replaced = true;
                    break;
                }
            }
            if (!$replaced) $regExp .= preg_quote($char, '/');
        }
        return self::e($regExp);
    }

    public static function str_split_unicode($str, $l = 0)
    {
        $str = (string)$str;
        if ($l > 0) {
            $ret = [];
            $len = mb_strlen($str, "UTF-8");
            for ($i = 0; $i < $len; $i += $l) {
                $ret[] = mb_substr($str, $i, $l, "UTF-8");
            }
            return $ret;
        }
        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    public static function requireAdmin()
    {
        if (!isset($_SESSION["user"])) {
            echo "<h2>not logged in</h2>";
            exit();
        } else {
            $user = Users::getUser($_SESSION["user"]);
            if (!$user->getIsBlogAdmin()) {
                echo "<h2>not authorised</h2>";
                exit();
            }
        }
    }

    public static function buildFieldworkWildcardRegex(string $raw): string
    {
        // Limit raw input length
        $raw = trim($raw);
        if ($raw === '') return '';

        if (mb_strlen($raw, 'UTF-8') > 80) {
            throw new RuntimeException('Query too long');
        }

        // Limit wildcard counts (prevents regex blowups)
        $wcCount = substr_count($raw, '*') + substr_count($raw, '?') + substr_count($raw, '~');
        if ($wcCount > 10) {
            throw new RuntimeException('Query too complex');
        }

        // Replace straight apostrophe with your XML apostrophe
        $raw = str_replace("'", "’", $raw);

        // Build regex by walking characters:
        // - normal chars are preg_quoted
        // - wildcards expand to your charclasses
        $out = '';
        $chars = preg_split('//u', $raw, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($chars as $ch) {
            if ($ch === '*') {
                $out .= '[' . ACCENT_CHARSET . ']*';
            } elseif ($ch === '?') {
                $out .= '[' . ACCENT_CHARSET . ']';
            } elseif ($ch === '~') {
                $out .= '[' . ACCENT_VOWELS . ']+';
            } else {
                $out .= preg_quote($ch, '/');
            }
        }

        // Optional extra tweak you had:
        $out = str_replace('h=', 'h?', $out);

        // Final size cap (belt-and-braces)
        if (strlen($out) > 5000) {
            throw new RuntimeException('Query too complex');
        }

        return $out;
    }
}
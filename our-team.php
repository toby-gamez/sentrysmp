<?php
// Načtení knihovny pro zpracování Markdownu, pokud je dostupná
@include_once "vendor/autoload.php";

// Definice souborů pro ukládání dat o týmu
$team_html_file = "our-team.html";
$team_content_file = "team_content.html";
$team_db_file = "team_content.sqlite";

// Prioritně používáme SQLite databázi místo souborů (kvůli potížím s oprávněními)
$use_sqlite_primary = true;

// Pokud team_content.html neexistuje, zkusíme ho vytvořit
if (!file_exists($team_content_file)) {
    @touch($team_content_file);
    @chmod($team_content_file, 0644);
}

$use_html_file = file_exists($team_html_file);
$use_content_file =
    file_exists($team_content_file) && filesize($team_content_file) > 0;
$use_db_file = file_exists($team_db_file);

// Funkce pro převod Markdown obsahu na HTML
function processMarkdown($text)
{
    // Kontrola, zda je k dispozici knihovna ParseDown nebo jiná knihovna pro Markdown
    if (class_exists("Parsedown")) {
        // Použití knihovny ParseDown
        $parsedown = new Parsedown();
        return $parsedown->text($text);
    } else {
        // Jednoduchá náhradní implementace základních Markdown značek
        $patterns = [
            "/\*\*(.*?)\*\*/" => '<strong>$1</strong>', // **tučný text**
            "/\*(.*?)\*/" => '<em>$1</em>', // *kurzíva*
            '/^- (.*)$/m' => '<li>$1</li>', // - položka seznamu
        ];

        $text = preg_replace(
            array_keys($patterns),
            array_values($patterns),
            $text
        );

        // Zabalení <li> elementů do <ul>
        $text = preg_replace(
            "/<li>.*?<\/li>(\s*<li>.*?<\/li>)*/s",
            '<ul>$0</ul>',
            $text
        );

        return $text;
    }
}

// Získání šablony stránky
$template_file = "template.html";
$template_exists = file_exists($template_file);
$template_content = $template_exists ? file_get_contents($template_file) : "";

// Funkce pro načtení obsahu z SQLite databáze
function loadContentFromDb($db_file)
{
    try {
        if (!file_exists($db_file)) {
            return null;
        }
        $db = new SQLite3($db_file);
        $query = $db->query(
            "SELECT content FROM team_content WHERE id = 1 ORDER BY updated_at DESC LIMIT 1"
        );
        $result = $query ? $query->fetchArray(SQLITE3_ASSOC) : null;
        $db->close();
        return $result ? $result["content"] : null;
    } catch (Exception $e) {
        error_log("SQLite read error: " . $e->getMessage());
        return null;
    }
}

// Načtení obsahu týmu
$team_content = "";

// Změnili jsme pořadí načítání - nejprve databáze (pokud je nastavena jako primární)
if ($use_sqlite_primary && $use_db_file) {
    // Nejprve zkusíme načíst z databáze
    $db_content = loadContentFromDb($team_db_file);
    if ($db_content !== null) {
        $team_content = $db_content;

        // Pokusíme se uložit obsah z databáze do souboru pro budoucí použití
        @file_put_contents($team_content_file, $team_content);
    }
} elseif ($use_content_file) {
    // Pokud nepoužíváme primárně databázi nebo nebyla nalezena, zkusíme soubor
    $team_content = file_get_contents($team_content_file);
} elseif ($use_db_file) {
    // Pokud soubor neexistuje, zkusíme načíst z databáze (záložní řešení)
    $db_content = loadContentFromDb($team_db_file);
    if ($db_content !== null) {
        $team_content = $db_content;

        // Pokusíme se uložit obsah z databáze do souboru pro budoucí použití
        @file_put_contents($team_content_file, $team_content);
    }
} elseif ($use_html_file) {
    // Pokud obsah neexistuje, pokusíme se ho extrahovat z našeho HTML souboru
    $html_content = file_get_contents($team_html_file);

    // Extrahovat část s obsahem týmu (mezi <div class="container"> a </div> před <footer>)
    $pattern = '/<div class="container">(.*?)<\/div>\s*<footer/s';
    if (preg_match($pattern, $html_content, $matches)) {
        $team_content = $matches[1];

        // Uložíme extrahovaný obsah do team_content.html pro budoucí použití
        @file_put_contents($team_content_file, $team_content);

        // Také uložíme do databáze pro případ, že by soubor nebyl zapisovatelný
        if (class_exists("SQLite3")) {
            try {
                $db = new SQLite3($team_db_file);
                $db->exec(
                    "CREATE TABLE IF NOT EXISTS team_content (id INTEGER PRIMARY KEY, content TEXT, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)"
                );
                $stmt = $db->prepare(
                    "INSERT OR REPLACE INTO team_content (id, content, updated_at) VALUES (1, :content, CURRENT_TIMESTAMP)"
                );
                $stmt->bindParam(":content", $team_content, SQLITE3_TEXT);
                $stmt->execute();
                $db->close();
            } catch (Exception $e) {
                error_log("SQLite error: " . $e->getMessage());
            }
        }
    }
} else {
    // Výchozí obsah, pokud nic z výše uvedeného není k dispozici
    $team_content = '<div class="main-wrapper"><h1 class="main">Our Team</h1></div>
    <h2 style="color: red" class="team-member">
        <img
            src="https://minotar.net/helm/sskerixx19/100"
            class="skin-preview"
            alt=""
        /><b>sskerixx19</b>
    </h2>
    <ul>
        <li>
            🌟 Visionary founder and dedicated owner of both the website
            and server
        </li>
        <li>
            💝 Friendly and welcoming personality that brings people
            together
        </li>
    </ul>';

    // Uložíme výchozí obsah do team_content.html - zkusíme více metod
    $file_saved = false;

    // Metoda 1: Přímý zápis
    if (@file_put_contents($team_content_file, $team_content) !== false) {
        $file_saved = true;
    } else {
        // Metoda 2: Pomocí fopen/fwrite
        $fp = @fopen($team_content_file, "w");
        if ($fp) {
            if (@fwrite($fp, $team_content)) {
                $file_saved = true;
            }
            @fclose($fp);
        }

        // Metoda 3: Pomocí dočasného souboru
        if (!$file_saved) {
            $temp_file = tempnam(sys_get_temp_dir(), "team");
            if ($temp_file !== false) {
                if (@file_put_contents($temp_file, $team_content) !== false) {
                    if (@copy($temp_file, $team_content_file)) {
                        $file_saved = true;
                    }
                    @unlink($temp_file);
                }
            }
        }
    }

    // Také uložíme do databáze pro případ, že by soubor nebyl zapisovatelný
    if (class_exists("SQLite3")) {
        try {
            $db = new SQLite3($team_db_file);
            $db->exec(
                "CREATE TABLE IF NOT EXISTS team_content (id INTEGER PRIMARY KEY, content TEXT, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)"
            );
            $stmt = $db->prepare(
                "INSERT OR REPLACE INTO team_content (id, content, updated_at) VALUES (1, :content, CURRENT_TIMESTAMP)"
            );
            $stmt->bindParam(":content", $team_content, SQLITE3_TEXT);
            $stmt->execute();
            $db->close();
        } catch (Exception $e) {
            error_log("SQLite error: " . $e->getMessage());
        }
    }
}

// Přidání <h1> nadpisu na začátek obsahu, pokud tam ještě není
if (
    strpos(
        $team_content,
        '<div class="main-wrapper"><h1 class="main">Our Team</h1></div>'
    ) === false
) {
    $team_content =
        '<div class="main-wrapper"><h1 class="main">Our Team</h1></div>' .
        $team_content;
}

// Načtení kompletní šablony
if ($template_exists) {
    // Změníme titulek stránky v šabloně
    $template_content = preg_replace(
        "/<title>.*?<\/title>/i",
        "<title>Our Team - Sentry SMP</title>",
        $template_content
    );

    // Načtení šablony a vložení obsahu týmu
    if (strpos($template_content, '<div class="container"></div>') !== false) {
        $full_content = str_replace(
            '<div class="container"></div>',
            '<div class="container">' . $team_content . "</div>",
            $template_content
        );
    } else {
        // Hledáme jiné varianty značky kontejneru
        $container_patterns = [
            '/<div class="container">.*?<\/div>/s',
            "/<main.*?>.*?<\/main>/s",
            "/<body>.*<footer/s",
        ];

        $replacement_patterns = [
            '<div class="container">' . $team_content . "</div>",
            "<main>" . $team_content . "</main>",
            '<body><div class="container">' . $team_content . "</div><footer",
        ];

        $full_content = preg_replace(
            $container_patterns,
            $replacement_patterns,
            $template_content,
            1
        );

        // Pokud se nepodařilo najít ani jednu z variant, připojíme obsah před </body>
        if ($full_content === $template_content) {
            $full_content = str_replace(
                "</body>",
                '<div class="container">' . $team_content . "</div></body>",
                $template_content
            );
        }
    }

    // Odeslání správných HTTP hlaviček
    header("Content-Type: text/html; charset=UTF-8");

    // Vypsání HTML obsahu
    echo $full_content;
} elseif ($use_html_file) {
    // Pokud není šablona, ale máme HTML soubor, použijeme ho
    $html_file_content = file_get_contents($team_html_file);
    // Změníme titulek stránky
    $html_file_content = preg_replace(
        "/<title>.*?<\/title>/i",
        "<title>Our Team - Sentry SMP</title>",
        $html_file_content
    );
    echo $html_file_content;
} else {
    // Záložní řešení - jednoduchá HTML struktura
    echo "<!DOCTYPE html><html><head><title>Our Team - Sentry SMP</title>";
    echo '<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<link rel="stylesheet" href="css/style.css"></head><body>';
    echo '<nav class="navbar" id="navbar-main"></nav>';
    echo '<div class="container">' . $team_content . "</div>";
    echo '<footer id="footer-main"></footer>';
    echo '<script src="js/script.js"></script>';
    echo "</body></html>";
}
?>

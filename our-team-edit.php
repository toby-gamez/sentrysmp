<?php
session_start();

// Kontrola přihlášení uživatele
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login.php");
    exit();
}

$username = htmlspecialchars($_SESSION["username"]);

// Cesta k souborům s týmem
$team_html_file = "our-team.html";
$team_content_file = "team_content.html";
$team_db_file = "team_content.sqlite";

// Prioritně používáme SQLite databázi místo souborů (kvůli potížím s oprávněními)
$use_sqlite_primary = true;

// Funkce pro uložení obsahu do SQLite databáze (nyní jako primární řešení)
function saveContentToDb($content)
{
    global $team_db_file;
    try {
        $db = new SQLite3($team_db_file);
        $db->exec(
            "CREATE TABLE IF NOT EXISTS team_content (id INTEGER PRIMARY KEY, content TEXT, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)"
        );

        // Připravit statement pro vložení nebo aktualizaci
        $stmt = $db->prepare(
            "INSERT OR REPLACE INTO team_content (id, content, updated_at) VALUES (1, :content, CURRENT_TIMESTAMP)"
        );
        $stmt->bindParam(":content", $content, SQLITE3_TEXT);
        $result = $stmt->execute();
        $db->close();
        return $result !== false;
    } catch (Exception $e) {
        error_log("SQLite error: " . $e->getMessage());
        return false;
    }
}

// Funkce pro načtení obsahu z SQLite databáze
function loadContentFromDb()
{
    global $team_db_file;
    try {
        if (!file_exists($team_db_file)) {
            return null;
        }
        $db = new SQLite3($team_db_file);
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

// Zpracování požadavku na uložení
$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save_team"])) {
    // Získání dat z formuláře
    $team_content = $_POST["team_content"];
    $team_markdown = isset($_POST["team_markdown"])
        ? $_POST["team_markdown"]
        : "";

    try {
        // Nejprve uložíme do SQLite databáze (kvůli potížím s oprávněními souboru)
        $db_saved = saveContentToDb($team_content);

        if ($db_saved) {
            $success_message = "Changes have been saved successfully.";

            // Pokud je SQLite primární, nemusíme zkoušet soubory
            if (!$use_sqlite_primary) {
                // Zkusíme uložit i do souboru jako zálohu

                // Nejprve zkusíme uložit obsahy do dočasného souboru
                $temp_file = tempnam(sys_get_temp_dir(), "team_content");
                if ($temp_file !== false) {
                    // Zkusíme zapsat do dočasného souboru
                    $temp_result = @file_put_contents(
                        $temp_file,
                        $team_content
                    );
                    if ($temp_result !== false) {
                        // Pokud se zápis do dočasného souboru povedl, pokusíme se ho přesunout do cílového umístění
                        // Nejprve zkontrolujeme adresář
                        $dir = dirname($team_content_file);
                        if (!is_writable($dir)) {
                            @chmod($dir, 0755);
                        }

                        // Zkusíme různé způsoby pro uložení obsahu
                        $saved = false;
                        // 1. Pokus: přímé přejmenování dočasného souboru
                        if (@rename($temp_file, $team_content_file)) {
                            $saved = true;
                        } else {
                            // 2. Pokus: Zkusíme zkopírovat dočasný soubor a nastavit oprávnění
                            if (@copy($temp_file, $team_content_file)) {
                                @chmod($team_content_file, 0644);
                                @unlink($temp_file);
                                $saved = true;
                            } else {
                                // 3. Pokus: Přímý zápis
                                $result = @file_put_contents(
                                    $team_content_file,
                                    $team_content
                                );
                                if ($result !== false) {
                                    @chmod($team_content_file, 0644);
                                    @unlink($temp_file);
                                    $saved = true;
                                } else {
                                    // 4. Pokus: použít file_exists + is_writable + zápis
                                    if (!file_exists($team_content_file)) {
                                        @touch($team_content_file);
                                    }
                                    if (
                                        file_exists($team_content_file) &&
                                        is_writable($team_content_file)
                                    ) {
                                        $fp = @fopen($team_content_file, "w");
                                        if ($fp) {
                                            fwrite($fp, $team_content);
                                            fclose($fp);
                                            @chmod($team_content_file, 0644);
                                            $saved = true;
                                        }
                                    }
                                    // Pokud nic z výše uvedeného nefunguje, zkusíme fopen+fwrite
                                    if (!$saved) {
                                        $fp = @fopen($team_content_file, "w");
                                        if ($fp) {
                                            fwrite($fp, $team_content);
                                            fclose($fp);
                                            @chmod($team_content_file, 0644);
                                            $saved = true;
                                        }
                                        @unlink($temp_file);
                                    }
                                }
                            }
                        }
                    } else {
                        @unlink($temp_file); // Odstranění dočasného souboru
                    }
                }
            } // konec podmínky if(!$use_sqlite_primary)
        } else {
            $error_message =
                "Changes were not saved. Please check the server permissions.";
        }

        // Pokud existuje původní HTML soubor a je zapisovatelný, aktualizujeme i ten (volitelně)
        if (file_exists($team_html_file) && is_writable($team_html_file)) {
            try {
                // Načtení původního souboru
                $original_content = file_get_contents($team_html_file);

                // Rozdělit soubor na části - hlavička, tělo a patička
                $header_pattern = '/<body>(.*?)<div class="container">/s';
                if (
                    preg_match(
                        $header_pattern,
                        $original_content,
                        $header_matches
                    )
                ) {
                    $header = $header_matches[0];

                    $footer_pattern = "/<\/div>\s*<footer/s";
                    if (
                        preg_match(
                            $footer_pattern,
                            $original_content,
                            $footer_matches
                        )
                    ) {
                        $footer = $footer_matches[0];
                        $footer = str_replace("</div>", "", $footer);

                        // Sestavení nového obsahu s původní hlavičkou a patičkou
                        $new_content = preg_replace(
                            "/<body>.*<\/footer>/s",
                            $header . $team_content . $footer,
                            $original_content
                        );

                        // Pokus o uložení HTML souboru (není kritické)
                        @file_put_contents($team_html_file, $new_content);
                    }
                }
            } catch (Exception $e) {
                // Pokud se nepodaří aktualizovat HTML, není to kritická chyba
                // Obsah už je uložen v team_content.html nebo v databázi
            }
        }
    } catch (Exception $e) {
        $error_message = "Error while saving changes: " . $e->getMessage();
        error_log("Our-Team Edit Error: " . $e->getMessage());
    }
}

// Načtení aktuálního obsahu týmu
$team_content = "";

// Pokus o vytvoření souboru, pokud neexistuje
if (!file_exists($team_content_file)) {
    @touch($team_content_file);
    @chmod($team_content_file, 0644);
}

// Změna pořadí načítání obsahu kvůli potížím s oprávněními:
// 1. Ze SQLite databáze (primární)
// 2. Z team_content.html souboru (pokud není použito SQLite jako primární)
// 3. Z our-team.html souboru
// 4. Výchozí obsah

// 1. Nejprve zkusíme načíst z databáze
if (($db_content = loadContentFromDb()) !== null) {
    $team_content = $db_content;
    // Zkusíme také uložit do souboru pro případné offline zpracování
    if (
        !$use_sqlite_primary &&
        file_exists($team_content_file) &&
        is_writable($team_content_file)
    ) {
        @file_put_contents($team_content_file, $db_content);
    }
}
// 2. Pokud se nepodařilo načíst z DB, zkusíme načíst ze souboru
elseif (file_exists($team_content_file) && filesize($team_content_file) > 0) {
    $team_content = file_get_contents($team_content_file);
    // Zkusíme uložit obsah do souboru, pokud je dostupný
    if (file_exists($team_content_file) && is_writable($team_content_file)) {
        @file_put_contents($team_content_file, $team_content);
    }
}
// 3. Zkusíme načíst z HTML souboru
elseif (file_exists($team_html_file)) {
    // Pokud samostatný soubor neexistuje nebo je prázdný, extrahujeme obsah z HTML souboru
    $html_content = file_get_contents($team_html_file);

    // Extrahovat část s obsahem týmu (mezi <div class="container"> a </div> před <footer>)
    $pattern = '/<div class="container">(.*?)<\/div>\s*<footer/s';
    if (preg_match($pattern, $html_content, $matches)) {
        $team_content = $matches[1];

        // Zkusíme uložit extrahovaný obsah do souboru
        if (
            file_exists($team_content_file) &&
            is_writable($team_content_file)
        ) {
            @file_put_contents($team_content_file, $team_content);
        }
    }
} else {
    // Výchozí obsah, pokud žádný soubor není k dispozici
    $team_content = <<<HTML
    <div class="main-wrapper">
<h1 class="main">Our Team</h1>
    </div>
<h2 style="color: red" class="team-member">
    <img
        src="https://minotar.net/helm/sskerixx19/100"
        class="skin-preview"
        alt=""
    /><b>sskerixx19</b>
</h2>
<ul>
    <li>🌟 Founder and owner</li>
</ul>
HTML;
}

// Načtení externí knihovny pro práci s Markdown (s ošetřením chyby)
@require_once "vendor/autoload.php";
?>

<!doctype html>
<html>
    <head>
        <!-- Google Consent Mode -->
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag() {
                dataLayer.push(arguments);
            }

            // Výchozí stav: souhlas odepřen, pokud není uložena volba
            if (!localStorage.getItem("cookies-accepted")) {
                gtag("consent", "default", {
                    analytics_storage: "denied",
                });
            } else {
                gtag("consent", "update", {
                    analytics_storage:
                        localStorage.getItem("cookies-accepted") === "granted"
                            ? "granted"
                            : "denied",
                });
            }
        </script>
        <!-- Google Analytics (nenačte se automaticky) -->
        <script
            async
            src="https://www.googletagmanager.com/gtag/js?id=G-SGG2CLM06D"
        ></script>
        <script>
            function loadGoogleAnalytics() {
                gtag("js", new Date());
                gtag("config", "G-SGG2CLM06D");
            }

            // Funkce pro zobrazení/skrytí cookie banneru
            function showCookieBanner() {
                if (!localStorage.getItem("cookies-accepted")) {
                    document.getElementById("cookie-banner").style.display =
                        "flex";
                } else {
                    document.getElementById("cookie-banner").style.display =
                        "none";
                    if (
                        localStorage.getItem("cookies-accepted") === "granted"
                    ) {
                        loadGoogleAnalytics();
                    }
                }
            }

            // Funkce pro přijetí cookies
            function acceptCookies() {
                localStorage.setItem("cookies-accepted", "granted");
                document.getElementById("cookie-banner").style.display = "none";
                gtag("consent", "update", {
                    analytics_storage: "granted",
                });
                loadGoogleAnalytics();
            }

            // Funkce pro odmítnutí cookies
            function declineCookies() {
                localStorage.setItem("cookies-accepted", "denied");
                document.getElementById("cookie-banner").style.display = "none";
                gtag("consent", "update", {
                    analytics_storage: "denied",
                });
            }

            // Zobrazit cookie banner po načtení stránky
            window.addEventListener("DOMContentLoaded", showCookieBanner);
        </script>
        <script>
            // Notification system
            let notificationContainer = null;

            // Create the notification container when first needed
            function createNotificationContainer() {
                if (!notificationContainer) {
                    notificationContainer = document.createElement('div');
                    notificationContainer.className = 'notification-container';
                    document.body.appendChild(notificationContainer);
                }
                return notificationContainer;
            }

            // Show notification with auto-dismiss timer
            function showNotification(
                message,
                type = "success",
                duration = 5000
            ) {
                createNotificationContainer();

                // Create notification element
                const notification = document.createElement("div");
                notification.className = "notification " + type;

                // Create message content
                const messageEl = document.createElement("div");
                messageEl.className = "message";
                messageEl.innerHTML = message.replace(/\n/g, "<br>");
                notification.appendChild(messageEl);

                // Add close button
                const closeBtn = document.createElement("span");
                closeBtn.className = "close-btn";
                closeBtn.innerHTML = "&times;";
                closeBtn.onclick = () => removeNotification(notification);
                notification.appendChild(closeBtn);

                // Add timer bar
                const timerBar = document.createElement("div");
                timerBar.className = "timer-bar";
                notification.appendChild(timerBar);

                // Add to container
                notificationContainer.appendChild(notification);

                // Animate timer
                const startTime = Date.now();
                const timerInterval = setInterval(() => {
                    const elapsedTime = Date.now() - startTime;
                    const remainingPercent = 100 - (elapsedTime / duration) * 100;

                    if (remainingPercent <= 0) {
                        clearInterval(timerInterval);
                        removeNotification(notification);
                    } else {
                        timerBar.style.width = remainingPercent + "%";
                    }
                }, 50);

                // Store interval ID for cleanup
                notification.dataset.intervalId = timerInterval;

                return notification;
            }

            // Remove notification with animation
            function removeNotification(notification) {
                // Clear the timer interval
                clearInterval(notification.dataset.intervalId);

                // Animate out
                notification.style.animation = "fadeOut 0.3s forwards";

                // Remove after animation
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }

                    // Remove container if empty
                    if (
                        notificationContainer &&
                        notificationContainer.children.length === 0
                    ) {
                        document.body.removeChild(notificationContainer);
                        notificationContainer = null;
                    }
                }, 300);
            }
        </script>
        <!-- SEO meta tagy pro Google -->
        <meta
            name="description"
            content="SentrySMP is a Minecraft server focused on providing a safe and enjoyable experience for players of all ages. It is SMP with addons."
        />
        <meta
            name="keywords"
            content="Minecraft, SMP, English, Czech, server, safe, enjoyable, experience, players, vip, premium, exclusive"
        />
        <meta name="author" content="Sentry SMP" />

        <!-- Open Graph pro Facebook, Discord, aj. -->
        <meta property="og:title" content="Sentry SMP" />
        <meta
            property="og:description"
            content="Sentry SMP server is a Minecraft server focused on providing a safe and enjoyable experience for players of all ages. It is SMP with addons."
        />
        <meta
            property="og:image"
            content="https://sentrysmp.eu/images/logo.png"
        />
        <meta property="og:url" content="https://sentrysmp.eu/" />
        <meta property="og:type" content="website" />

        <!-- Twitter Cards (volitelné) -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="Sentry SMP" />
        <meta
            name="twitter:description"
            content="Sentry SMP server is a Minecraft server focused on providing a safe and enjoyable experience for players of all ages. It is SMP with addons."
        />
        <meta
            name="twitter:image"
            content="https://sentrysmp.eu/images/logo.png"
        />
        <!-- Language settings -->
        <meta http-equiv="content-language" content="en" />
        <meta name="language" content="English" />

        <title>Team Edit - Admin Panel - Sentry SMP</title>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="css/style.css" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link rel="icon" href="images/favicon.png" />
        <style>
            body.dark .header-background {
                background-image: url("images/background-image-dark.png");
            }

            body:not(.dark) .header-background {
                background-image: url("images/background-image.png");
            }

            #team_content, #team_markdown {
                width: 100%;
                min-height: 500px;
                font-family: monospace;
                padding: 10px;
                margin-bottom: 20px;
                white-space: pre-wrap;
                overflow-y: auto;
                resize: vertical;
                height: auto;
                min-height: 400px;
            }

            .tab-container {
                display: flex;
                margin-bottom: 10px;
            }

            .tab {
                padding: 10px 20px;
                cursor: pointer;
                border: 1px solid #ccc;
                border-bottom: none;
                margin-right: 5px;
                border-radius: 5px 5px 0 0;
            }

            .tab.active {
                background-color: #f4f4f4;
                color: black;
                font-weight: bold;
            }

            .tab-content {
                display: none;
                border: 1px solid #ccc;
                padding: 10px;
                margin-bottom: 20px;
            }

            .tab-content.active {
                display: block;
            }

            .success-message {
                color: green;
                background-color: #dff0d8;
                padding: 10px;
                border-radius: 5px;
                margin-bottom: 20px;
            }

            .error-message {
                color: red;
                background-color: #f2dede;
                padding: 10px;
                border-radius: 5px;
                margin-bottom: 20px;
            }

            .editor-container {
                display: flex;
                flex-direction: column;
                gap: 20px;
            }

            .editor-section {
                margin-bottom: 30px;
            }

            .preview {
                border: 1px solid #ccc;
                padding: 20px;
                border-radius: 5px;
                background-color: rgba(255, 255, 255, 0.05);
                min-height: 100px;
            }



            .team-member-template {
                display: none;
                margin: 20px 0;
                padding: 15px;
                border: 1px solid #ccc;
                border-radius: 5px;
                background-color: rgba(255, 255, 255, 0.1);
            }

            .team-member-form input,
            .team-member-form textarea {
                width: 100%;
                margin-bottom: 10px;
                padding: 8px;
            }
        </style>
    </head>
    <body>
        <nav class="navbar" id="navbar-main"></nav>

        <div class="container">
            <div class="main-wrapper">
                <h1 class="main">Our Team Edit</h1>
            </div>

            <?php if (!empty($success_message)): ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?>
                    <div style="margin-top: 10px; font-size: 0.9em;">
                        <strong>Debug info:</strong><br>
                        PHP Version: <?php echo phpversion(); ?><br>
                        File Permissions: <?php echo file_exists(
                            $team_content_file
                        )
                            ? substr(
                                sprintf("%o", fileperms($team_content_file)),
                                -4
                            )
                            : "File does not exist"; ?><br>
                        Directory Permissions: <?php echo substr(
                            sprintf(
                                "%o",
                                fileperms(dirname($team_content_file))
                            ),
                            -4
                        ); ?><br>
                        Server OS: <?php echo PHP_OS; ?><br>
                        SQLite Enabled: <?php echo class_exists("SQLite3")
                            ? "Yes"
                            : "No"; ?><br>
                        SQLite File Exists: <?php echo file_exists(
                            $team_db_file
                        )
                            ? "Yes"
                            : "No"; ?><br>
                        SQLite Primary: <?php echo $use_sqlite_primary
                            ? "Yes"
                            : "No"; ?><br>
                        <?php if (function_exists("posix_getuid")): ?>
                        UID: <?php echo posix_getuid(); ?>, GID: <?php echo posix_getgid(); ?><br>
                        <?php endif; ?>
                        Is temp dir writable: <?php echo is_writable(
                            sys_get_temp_dir()
                        )
                            ? "Yes"
                            : "No"; ?><br>
                        <a href="#" onclick="tryDbRestore(); return false;" style="color: blue;">Try restore from DB</a> |
                        <a href="#" onclick="createContentFile(); return false;" style="color: green;">Create team_content.html file</a>
                    </div>
                </div>
            <?php endif; ?>

            <div class="editor-container">
                <div class="editor-section">
                    <h2>Instructions</h2>
                    <p>To edit the page, we recommend using the Markdown editor (the same format as Discord). After making changes, always convert:</p>
                    <ol>
                        <li>Your updated Markdown to HTML using the button</li>
                        <li>Your updated HTML to Markdown using the button</li>
                    </ol>
                    <rewrite_this>
                                        <p>After converting, save your changes with the button and you can view the preview below. To customize the name color, use a HEX value (e.g. #ffffff). For the skin, use the Minecraft Java Edition username.</p>
                    </rewrite_this>
                    <h2>Team Content Editor</h2>
                    <p>Here you can edit the content of the Our Team page:</p>

                    <div class="tab-container">
                        <div class="tab active" id="html-tab-btn">HTML Editor</div>
                        <div class="tab" id="markdown-tab-btn">Markdown Editor</div>
                    </div>

                    <form method="post" action="">
                        <div id="html-tab" class="tab-content active">
                            <textarea id="team_content" name="team_content"><?php echo htmlspecialchars(
                                $team_content
                            ); ?></textarea>
                        </div>

                        <div id="markdown-tab" class="tab-content">
                            <p>When finished, <b>always convert to HTML</b>!</p>

                            <textarea id="team_markdown" name="team_markdown"></textarea>
                        </div>

                        <div class="button-row">
                            <button type="button" style="display: none" class="back-button" onclick="window.location.href='admin.php'">Back</button>
                            <button type="button" id="preview-btn" style="display: none;">Update Preview</button>
                            <button type="button" class="secondary" id="md-to-html-btn">Convert Markdown to HTML</button>
                            <button type="button" class="secondary" id="html-to-md-btn">Convert HTML to Markdown</button>
                            <button type="submit" name="save_team" class="save-button">Save Changes</button>
                        </div>
                    </form>
                </div>

                <div class="editor-section">
                    <h2>Preview</h2>
                    <p>Always shows HTML version, that means you should transfer Markdown to HTML– always convert Markdown to HTML before saving, as the preview and saved content use the HTML version.</p>
                    <div id="preview" class="preview"></div>
                </div>

                <!-- Sekce průvodce byla odstraněna, ale zachována neviditelná -->
                <div style="display: none;" id="memberTemplate">
                    <!-- Skrytý obsah šablony pro zachování funkčnosti náhledu -->
                </div>
            </div>
        </div>
        <footer id="footer-main"></footer>
        <script src="js/script.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/marked@4.0.0/marked.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/turndown@7.1.1/dist/turndown.min.js"></script>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Kontrola načtení knihoven
            if (typeof marked === 'undefined') {
                console.error('Marked.js library is not available');
                alert('Warning: Markdown processing library is not available. Some features may not work properly.');
            }

            // Automatické zvětšování textových polí podle obsahu
            // Function to auto-resize textarea elements
            function autoResizeTextarea(textarea) {
                textarea.style.height = "auto";
                textarea.style.height = textarea.scrollHeight + "px";
            }

            // Function to show notification on save success
            function showSuccessMessage(message) {
                document.querySelector('.success-message').textContent = message;
                document.querySelector('.success-message').style.display = 'block';
                showNotification(message, "success");

                setTimeout(function() {
                    document.querySelector('.success-message').style.display = 'none';
                }, 3000);
            }

            // Function to show notification on save error
            function showErrorMessage(message) {
                document.querySelector('.error-message').textContent = message;
                document.querySelector('.error-message').style.display = 'block';
                showNotification(message, "error");

                setTimeout(function() {
                    document.querySelector('.error-message').style.display = 'none';
                }, 3000);
            }

            // Inicializace výšky textových polí
            document.querySelectorAll('textarea').forEach(function(textarea) {
                autoResizeTextarea(textarea);
                textarea.addEventListener('input', function() {
                    autoResizeTextarea(this);
                });
            });

            // Zobrazení náhledu - pouze z HTML verze
            function showPreview() {
                try {
                    // Vždy použijeme HTML verzi pro náhled, bez ohledu na aktivní záložku
                    const content = document.getElementById('team_content').value;
                    document.getElementById('preview').innerHTML = content || '<p>Empty content</p>';
                } catch (error) {
                    console.error('Error displaying preview:', error);
                    document.getElementById('preview').innerHTML = '<p>Error displaying preview: ' + error.message + '</p>';
                }
            }

            // Přepínání mezi HTML a Markdown editorem
            function switchTab(tabName) {
                try {
                    // Deaktivace všech záložek
                    document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
                    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

                    // Aktivace vybrané záložky
                    if (tabName === 'html') {
                        document.getElementById('html-tab-btn').classList.add('active');
                        document.getElementById('html-tab').classList.add('active');
                    } else if (tabName === 'markdown') {
                        document.getElementById('markdown-tab-btn').classList.add('active');
                        document.getElementById('markdown-tab').classList.add('active');
                    }

                    // Aktualizace náhledu po přepnutí záložky
                    showPreview();
                } catch (error) {
                    console.error('Error switching tabs:', error);
                }
            }

            // Převod Markdown na HTML
            function convertMarkdownToHtml() {
                try {
                    const markdown = document.getElementById('team_markdown').value;
                    if (!markdown.trim()) {
                        alert('Markdown editor is empty');
                        return;
                    }

                    if (typeof marked === 'undefined') {
                        alert('Marked.js library is not available');
                        return;
                    }

                    // Rozdělit text podle hlaviček
                    const sections = markdown.split(/(?=## )/);
                    let htmlOutput = '<h1 class="main">Our Team</h1>';

                    sections.forEach(section => {
                        if (!section.trim()) return;

                        // Zkontrolovat zda sekce obsahuje informace o členovi
                        const memberMatch = section.match(/^## (.*?)(?:\n|$)/);
                        if (memberMatch) {
                            const memberName = memberMatch[1].trim();
                            let memberColor = 'gray';
                            let memberSkin = memberName;

                            // Hledat barvu v komentáři
                            const colorMatch = section.match(/<!--\s*color:\s*(.*?)\s*-->/);
                            if (colorMatch) memberColor = colorMatch[1];

                            // Hledat skin v komentáři
                            const skinMatch = section.match(/<!--\s*skin:\s*(.*?)\s*-->/);
                            if (skinMatch) memberSkin = skinMatch[1];

                            // Odstranit komentáře a hlavičku z obsahu
                            let content = section
                                .replace(/^## .*?(?:\n|$)/, '')
                                .replace(/<!--.*?-->/g, '')
                                .trim();

                            // Převést Markdown obsah na HTML
                            const contentHtml = marked.parse(content);

                            // Vytvořit HTML pro člena týmu
                            htmlOutput += `
<h2 style="color: ${memberColor}" class="team-member">
    <img
        src="https://minotar.net/helm/${memberSkin}/100"
        class="skin-preview"
        alt=""
    />${memberName}
</h2>
${contentHtml}`;
                        } else {
                            // Pokud sekce není členem týmu, převést ji jako běžný Markdown
                            htmlOutput += marked.parse(section);
                        }
                    });

                    // Vložit výsledný HTML do editoru
                    document.getElementById('team_content').value = htmlOutput;

                    // Přepnout na HTML záložku
                    switchTab('html');

                    // Zobrazit náhled
                    showPreview();

                    // Aktualizovat výšku textarea
                    autoResizeTextarea(document.getElementById('team_content'));
                } catch (error) {
                    console.error('Error converting Markdown to HTML:', error);
                    alert('Conversion error: ' + error.message);
                }
            }

            // Převod HTML na Markdown
            function convertHtmlToMarkdown() {
                try {
                    const htmlContent = document.getElementById('team_content').value;
                    if (!htmlContent.trim()) {
                        alert('HTML editor is empty');
                        return;
                    }

                    if (typeof TurndownService === 'undefined') {
                        alert('TurndownService library is not available');
                        return;
                    }

                    // Vytvořit dočasný element pro parsování HTML
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = htmlContent;

                    // Generovat výsledný Markdown
                    let markdownContent = '';

                    // Extrahovat nadpis stránky
                    const title = tempDiv.querySelector('h1.main');
                    if (title) {
                        markdownContent += '# ' + title.textContent.trim() + '\n\n';
                    }

                    // Extrahovat členy týmu
                    const members = tempDiv.querySelectorAll('h2.team-member');
                    members.forEach(member => {
                        const name = member.textContent.trim();

                        // Získat barvu z atributu style
                        const colorMatch = member.getAttribute('style')?.match(/color:\s*([^;]+)/);
                        const color = colorMatch ? colorMatch[1].trim() : 'gray';

                        // Získat URL skinu
                        const img = member.querySelector('img.skin-preview');
                        const skinUrl = img ? img.getAttribute('src') : '';
                        const skinMatch = skinUrl.match(/\/helm\/([^\/]+)\//);
                        const skin = skinMatch ? skinMatch[1] : '';

                        markdownContent += `## ${name}\n`;
                        markdownContent += `<!-- color: ${color} -->\n`;
                        if (skin) {
                            markdownContent += `<!-- skin: ${skin} -->\n`;
                        }
                        markdownContent += '\n';

                        // Zpracovat popis
                        const ulElement = member.nextElementSibling;
                        if (ulElement && ulElement.tagName === 'UL') {
                            const items = ulElement.querySelectorAll('li');
                            items.forEach(item => {
                                markdownContent += '- ' + item.textContent.trim() + '\n';
                            });
                        }

                        markdownContent += '\n';
                    });

                    // Nastavit výsledný Markdown do editoru
                    document.getElementById('team_markdown').value = markdownContent;

                    // Přepnout na Markdown záložku
                    switchTab('markdown');

                    // Automaticky upravit výšku textarea
                    autoResizeTextarea(document.getElementById('team_markdown'));
                } catch (error) {
                    console.error('Error converting HTML to Markdown:', error);
                    alert('Conversion error: ' + error.message);
                }
            }

            // Funkce pro zobrazení šablony nového člena
            function addMemberTemplate() {
                document.getElementById('memberTemplate').style.display = 'block';
            }

            // Funkce pro vygenerování HTML kódu pro nového člena
            function generateMemberHTML() {
                const name = document.getElementById('memberName').value.trim();
                const color = document.getElementById('memberColor').value.trim();
                const skin = document.getElementById('memberSkin').value.trim();
                const description = document.getElementById('memberDescription').value.trim();

                if (!name) {
                    alert('Please enter the team member name');
                    return;
                }

                try {
                    let memberHTML = `
<h2 style="color: ${color || 'gray'}" class="team-member">
    <img
        src="https://minotar.net/helm/${skin || name}/100"
        class="skin-preview"
        alt=""
    />${name}
</h2>`;

                    // Přidání položek seznamu
                    if (description) {
                        if (typeof marked !== 'undefined') {
                            memberHTML += marked.parse(description);
                        } else {
                            // Manuální konverze jednoduchého Markdown seznamu na HTML
                            let html = '<ul>\n';
                            description.split('\n').forEach(line => {
                                line = line.trim();
                                if (line.startsWith('- ')) {
                                    html += `    <li>${line.substring(2)}</li>\n`;
                                } else if (line) {
                                    html += `    <li>${line}</li>\n`;
                                }
                            });
                            html += '</ul>';
                            memberHTML += html;
                        }
                    } else {
                        memberHTML += `<ul>\n    <li>Team member</li>\n</ul>`;
                    }

                    // Přidání do textového pole
                    const editor = document.getElementById('team_content');
                    editor.value += '\n' + memberHTML;

                    // Ukázat náhled
                    showPreview();

                    // Skrytí šablony
                    document.getElementById('memberTemplate').style.display = 'none';

                    // Resetování formuláře
                    document.getElementById('memberName').value = '';
                    document.getElementById('memberColor').value = '';
                    document.getElementById('memberSkin').value = '';
                    document.getElementById('memberDescription').value = '';
                } catch (error) {
                    console.error('Error generating HTML:', error);
                    alert('Error: ' + error.message);
                }
            }

            // Funkce pro vygenerování Markdown kódu pro nového člena
            function generateMemberMarkdown() {
                const name = document.getElementById('memberName').value.trim();
                const color = document.getElementById('memberColor').value.trim();
                const skin = document.getElementById('memberSkin').value.trim();
                const description = document.getElementById('memberDescription').value.trim();

                if (!name) {
                    alert('Please enter the team member name');
                    return;
                }

                try {
                    let memberMarkdown = `## ${name}\n`;

                    // Přidání metadat jako komentářů
                    if (color) memberMarkdown += `<!-- color: ${color} -->\n`;
                    if (skin) memberMarkdown += `<!-- skin: ${skin} -->\n`;

                    // Přidání popisu
                    if (description) {
                        memberMarkdown += `\n${description}\n`;
                    } else {
                        memberMarkdown += `\n- Team member\n`;
                    }

                    // Přidání do textového pole Markdown editoru
                    const editor = document.getElementById('team_markdown');
                    editor.value += (editor.value ? '\n\n' : '') + memberMarkdown;

                    // Přepnutí na Markdown záložku
                    switchTab('markdown');

                    // Skrytí šablony
                    document.getElementById('memberTemplate').style.display = 'none';

                    // Resetování formuláře
                    document.getElementById('memberName').value = '';
                    document.getElementById('memberColor').value = '';
                    document.getElementById('memberSkin').value = '';
                    document.getElementById('memberDescription').value = '';
                } catch (error) {
                    console.error('Error generating Markdown:', error);
                    alert('Error: ' + error.message);
                }
            }

            // Funkce pro export členů týmu do JSON
            function exportTeamToJson() {
                try {
                    const content = document.getElementById('team_content').value;
                    const teamMembers = [];

                    // Vytvoříme dočasný element pro parsování HTML
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = content;

                    // Najdeme všechny elementy h2 s třídou team-member
                    const memberHeaders = tempDiv.querySelectorAll('h2.team-member');

                    memberHeaders.forEach(header => {
                        // Získáme jméno člena týmu
                        const name = header.textContent.trim();

                        // Získáme barvu
                        const color = header.getAttribute('style')?.match(/color:\s*([^;]+)/)?.[1] || 'gray';

                        // Získáme URL obrázku skinu
                        const imgElement = header.querySelector('img.skin-preview');
                        const skinUrl = imgElement?.getAttribute('src') || '';
                        const skinName = skinUrl.match(/\/helm\/([^\/]+)\//) ? skinUrl.match(/\/helm\/([^\/]+)\//)[1] : name;

                        // Získáme popis (položky seznamu)
                        const nextElement = header.nextElementSibling;
                        let description = '';

                        if (nextElement && nextElement.tagName === 'UL') {
                            const items = nextElement.querySelectorAll('li');
                            const descItems = [];
                            items.forEach(item => {
                                descItems.push(item.textContent.trim());
                            });
                            description = descItems.join('\n');
                        }

                        // Přidáme člena do pole
                        teamMembers.push({
                            name,
                            color,
                            skin: skinName,
                            description
                        });
                    });

                    // Vytvoříme JSON string a stáhneme ho jako soubor
                    const jsonString = JSON.stringify(teamMembers, null, 2);
                    const blob = new Blob([jsonString], { type: 'application/json' });
                    const url = URL.createObjectURL(blob);

                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'team-members.json';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                } catch (error) {
                    console.error('Error exporting to JSON:', error);
                    showNotification('Export error: ' + error.message, 'error');
                }
            }

            // Funkce pro import členů týmu z JSON
            function importTeamFromJson(file) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    try {
                        const teamMembers = JSON.parse(e.target.result);

                        if (!Array.isArray(teamMembers)) {
                            throw new Error('Invalid JSON format - expected array of objects.');
                        }

                        // Vytvoříme HTML nebo Markdown podle aktuálního režimu
                        const activeTab = document.getElementById('html-tab').classList.contains('active') ? 'html' : 'markdown';

                        if (activeTab === 'markdown') {
                            let markdownContent = '';

                            teamMembers.forEach(member => {
                                markdownContent += `## ${member.name}\n`;
                                markdownContent += `<!-- color: ${member.color || 'gray'} -->\n`;

                                if (member.skin) {
                                    markdownContent += `<!-- skin: ${member.skin} -->\n`;
                                }

                                markdownContent += '\n';

                                // Přidáme popis jako seznam s odrážkami
                                if (member.description) {
                                    const descLines = member.description.split('\n');
                                    descLines.forEach(line => {
                                        if (line.trim()) {
                                            // Pokud řádek nezačíná odrážkou, přidáme ji
                                            if (!line.trim().startsWith('-')) {
                                                markdownContent += `- ${line.trim()}\n`;
                                            } else {
                                                markdownContent += `${line.trim()}\n`;
                                            }
                                        }
                                    });
                                } else {
                                    markdownContent += '- Team member\n';
                                }

                                markdownContent += '\n';
                            });

                            // Vložíme Markdown do editoru
                            document.getElementById('team_markdown').value = markdownContent;

                            // Přepneme na Markdown záložku
                            switchTab('markdown');

                        } else {
                            let htmlContent = '<h1 class="main">Our Team</h1>\n';

                            teamMembers.forEach(member => {
                                htmlContent += `
<h2 style="color: ${member.color || 'gray'}" class="team-member">
    <img
        src="https://minotar.net/helm/${member.skin || member.name}/100"
        class="skin-preview"
        alt=""
    />${member.name}
</h2>
<ul>`;

                                // Přidáme popis jako položky seznamu
                                if (member.description) {
                                    const descLines = member.description.split('\n');
                                    descLines.forEach(line => {
                                        if (line.trim()) {
                                            htmlContent += `\n    <li>${line.trim()}</li>`;
                                        }
                                    });
                                } else {
                                    htmlContent += `\n    <li>Team member</li>`;
                                }

                                htmlContent += `\n</ul>\n`;
                            });

                            // Vložíme HTML do editoru
                            document.getElementById('team_content').value = htmlContent;
                        }

                        // Zobrazíme náhled
                        showPreview();

                        alert('Team members import was successful.');
                    } catch (error) {
                        console.error('Error importing JSON:', error);
                        alert('Import error: ' + error.message);
                    }
                };

                reader.readAsText(file);
            }

            // Funkce pro obnovení obsahu z databáze
            function tryDbRestore() {
                if (confirm('Are you sure you want to restore content from the database? Current changes in the editor will be lost.')) {
                    showNotification("Restoring content from database...", "info");
                    fetch('restore_team_content.php')
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification('Content was successfully restored from the database', 'success');
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1500);
                            } else {
                                showNotification('Error restoring content: ' + data.error, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error restoring from DB:', error);
                            showNotification('Could not contact server: ' + error, 'error');
                        });
                }
            }

            // Funkce pro vytvoření nebo opravu souboru team_content.html
            function createContentFile() {
                if (confirm('Do you want to create the team_content.html file with the current content?')) {
                    const content = document.getElementById('team_content').value;
                    showNotification("Creating team_content.html file...", "info");

                    fetch('create_team_file.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ content: content })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('File was successfully created', 'success');
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            showNotification('Error creating file: ' + data.error, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error creating file:', error);
                        showNotification('Error creating file: ' + error, 'error');
                        alert('Could not contact server: ' + error);
                    });
                }
            }

            // Přidání událostí tlačítkům
            document.getElementById('html-tab-btn').addEventListener('click', function() {
                switchTab('html');
                showPreview(); // Aktualizovat náhled při přepnutí záložky
            });

            document.getElementById('markdown-tab-btn').addEventListener('click', function() {
                switchTab('markdown');
                // Při přepnutí na markdown záložku neaktualizujeme náhled, ten zůstává z HTML verze
            });

            document.getElementById('preview-btn').addEventListener('click', showPreview);
            document.getElementById('md-to-html-btn').addEventListener('click', function() {
                convertMarkdownToHtml();
                showPreview(); // Aktualizovat náhled po převodu
            });
            document.getElementById('html-to-md-btn').addEventListener('click', convertHtmlToMarkdown);

            // Odstraněny event listenery pro průvodce a import/export

            // Inicializace stránky
            autoResizeTextarea(document.getElementById('team_content'));
            showPreview();

            // Automaticky převést HTML na Markdown při načtení stránky
            if (document.getElementById('team_content').value.trim()) {
                setTimeout(function() {
                    try {
                        convertHtmlToMarkdown();
                    } catch (e) {
                        console.error('Error during automatic HTML to Markdown conversion:', e);
                    }
                }, 500);
            }
        });
        </script>
    </body>
</html>

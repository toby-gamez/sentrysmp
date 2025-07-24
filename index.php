<?php
// Include the RCON functionality
require_once "vip-rcon.php";
require_once "eternal-rcon.php";

// Function to automatically clean up expired VIP users
function cleanupExpiredVipUsers()
{
    try {
        // Check if database exists
        if (!file_exists("vip.sqlite")) {
            return 0;
        }

        $db = new SQLite3("vip.sqlite");

        // Helper function to calculate days left
        function getDaysLeft($created_at)
        {
            $created_ts = strtotime($created_at);
            $expire_ts = $created_ts + 30 * 24 * 60 * 60;
            $now = time();
            $diff = $expire_ts - $now;
            $days_left = ceil($diff / (60 * 60 * 24));
            if ($days_left < 0) {
                $days_left = 0;
            }
            return $days_left;
        }

        // Find and delete expired users
        $result = $db->query("SELECT id, username, created_at FROM vip_users");
        $deleted_count = 0;
        $failed_count = 0;

        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $days_left = getDaysLeft($row["created_at"]);
            if ($days_left <= 0) {
                $remove_name = $row["username"];

                // First remove the VIP permissions via RCON (non-blocking)
                $rcon_result = false;
                try {
                    $rcon_result = removeVipPermissions($remove_name);
                } catch (Exception $rcon_e) {
                    // Log RCON error but continue with database cleanup
                    $log_message =
                        date("Y-m-d H:i:s") .
                        " - RCON error for $remove_name: " .
                        $rcon_e->getMessage() .
                        "\n";
                    file_put_contents(
                        "vip_cleanup_log.txt",
                        $log_message,
                        FILE_APPEND,
                    );
                }

                // Always try to delete from database regardless of RCON result
                try {
                    $stmt = $db->prepare(
                        "DELETE FROM vip_users WHERE username = :username",
                    );
                    $stmt->bindValue(":username", $remove_name, SQLITE3_TEXT);
                    $result_execute = $stmt->execute();

                    if ($result_execute) {
                        $deleted_count++;

                        // Log successful removal
                        $log_message =
                            date("Y-m-d H:i:s") .
                            " - Removed VIP user: $remove_name - RCON: " .
                            ($rcon_result ? "Success" : "Failed") .
                            " - Database: Success\n";
                        file_put_contents(
                            "vip_cleanup_log.txt",
                            $log_message,
                            FILE_APPEND,
                        );
                    } else {
                        $failed_count++;
                        $log_message =
                            date("Y-m-d H:i:s") .
                            " - Failed to delete from database: $remove_name\n";
                        file_put_contents(
                            "vip_cleanup_log.txt",
                            $log_message,
                            FILE_APPEND,
                        );
                    }
                } catch (Exception $e) {
                    $failed_count++;
                    $log_message =
                        date("Y-m-d H:i:s") .
                        " - Database deletion error for $remove_name: " .
                        $e->getMessage() .
                        "\n";
                    file_put_contents(
                        "vip_cleanup_log.txt",
                        $log_message,
                        FILE_APPEND,
                    );
                }
            }
        }

        // Close database connection
        $db->close();

        // Log cleanup summary
        if ($deleted_count > 0 || $failed_count > 0) {
            $log_message =
                date("Y-m-d H:i:s") .
                " - Cleanup summary: Removed $deleted_count expired VIP users, $failed_count failures\n";
            file_put_contents("vip_cleanup_log.txt", $log_message, FILE_APPEND);
        }

        return $deleted_count;
    } catch (Exception $e) {
        // Log error
        $log_message =
            date("Y-m-d H:i:s") .
            " - Critical error in VIP cleanup process: " .
            $e->getMessage() .
            "\n";
        file_put_contents("vip_cleanup_log.txt", $log_message, FILE_APPEND);
        return 0;
    }
}

// Function to automatically clean up expired Eternal users
function cleanupExpiredEternalUsers()
{
    try {
        // Check if database exists
        if (!file_exists("eternal.sqlite")) {
            return 0;
        }

        $db = new SQLite3("eternal.sqlite");

        // Helper function to calculate days left for eternal users
        function getEternalDaysLeft($created_at)
        {
            $created_ts = strtotime($created_at);
            $expire_ts = $created_ts + 30 * 24 * 60 * 60;
            $now = time();
            $diff = $expire_ts - $now;
            $days_left = ceil($diff / (60 * 60 * 24));
            if ($days_left < 0) {
                $days_left = 0;
            }
            return $days_left;
        }

        // Find and delete expired users
        $result = $db->query(
            "SELECT id, username, created_at FROM eternal_users",
        );
        $deleted_count = 0;
        $failed_count = 0;

        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $days_left = getEternalDaysLeft($row["created_at"]);
            if ($days_left <= 0) {
                $remove_name = $row["username"];

                // First remove the Eternal permissions via RCON (non-blocking)
                $rcon_result = false;
                try {
                    $rcon_result = removeEternalPermissions($remove_name);
                } catch (Exception $rcon_e) {
                    // Log RCON error but continue with database cleanup
                    $log_message =
                        date("Y-m-d H:i:s") .
                        " - RCON error for $remove_name: " .
                        $rcon_e->getMessage() .
                        "\n";
                    file_put_contents(
                        "eternal_cleanup_log.txt",
                        $log_message,
                        FILE_APPEND,
                    );
                }

                // Always try to delete from database regardless of RCON result
                try {
                    $stmt = $db->prepare(
                        "DELETE FROM eternal_users WHERE username = :username",
                    );
                    $stmt->bindValue(":username", $remove_name, SQLITE3_TEXT);
                    $result_execute = $stmt->execute();

                    if ($result_execute) {
                        $deleted_count++;

                        // Log successful removal
                        $log_message =
                            date("Y-m-d H:i:s") .
                            " - Removed Eternal user: $remove_name - RCON: " .
                            ($rcon_result ? "Success" : "Failed") .
                            " - Database: Success\n";
                        file_put_contents(
                            "eternal_cleanup_log.txt",
                            $log_message,
                            FILE_APPEND,
                        );
                    } else {
                        $failed_count++;
                        $log_message =
                            date("Y-m-d H:i:s") .
                            " - Failed to delete from database: $remove_name\n";
                        file_put_contents(
                            "eternal_cleanup_log.txt",
                            $log_message,
                            FILE_APPEND,
                        );
                    }
                } catch (Exception $e) {
                    $failed_count++;
                    $log_message =
                        date("Y-m-d H:i:s") .
                        " - Database deletion error for $remove_name: " .
                        $e->getMessage() .
                        "\n";
                    file_put_contents(
                        "eternal_cleanup_log.txt",
                        $log_message,
                        FILE_APPEND,
                    );
                }
            }
        }

        // Close database connection
        $db->close();

        // Log cleanup summary
        if ($deleted_count > 0 || $failed_count > 0) {
            $log_message =
                date("Y-m-d H:i:s") .
                " - Cleanup summary: Removed $deleted_count expired Eternal users, $failed_count failures\n";
            file_put_contents(
                "eternal_cleanup_log.txt",
                $log_message,
                FILE_APPEND,
            );
        }

        return $deleted_count;
    } catch (Exception $e) {
        // Log error
        $log_message =
            date("Y-m-d H:i:s") .
            " - Critical error in Eternal cleanup process: " .
            $e->getMessage() .
            "\n";
        file_put_contents("eternal_cleanup_log.txt", $log_message, FILE_APPEND);
        return 0;
    }
}

// Run cleanup on page loads (with throttling to prevent excessive execution)
$cleanup_file = "last_cleanup.txt";
$should_run_cleanup = true;

if (file_exists($cleanup_file)) {
    $last_cleanup = (int) file_get_contents($cleanup_file);
    $time_since_cleanup = time() - $last_cleanup;

    // Only run cleanup if it's been at least 1 hour since last cleanup
    if ($time_since_cleanup < 3600) {
        $should_run_cleanup = false;
    }
}

if ($should_run_cleanup) {
    $vip_deleted_count = cleanupExpiredVipUsers();
    $eternal_deleted_count = cleanupExpiredEternalUsers();
    file_put_contents($cleanup_file, time());

    if ($vip_deleted_count > 0) {
        error_log("VIP Cleanup: Removed $vip_deleted_count expired users");
    }

    if ($eternal_deleted_count > 0) {
        error_log(
            "Eternal Cleanup: Removed $eternal_deleted_count expired users",
        );
    }
}

// Load Discord announcements using bot token (same as news.php but limit to 3)
function getDiscordAnnouncements($limit = 3)
{
    // Load environment variables manually to handle special characters
    $envFile = __DIR__ . "/.env";
    if (!file_exists($envFile)) {
        return [];
    }

    $env = [];
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), "#") === 0) {
            continue; // Skip comments
        }
        if (strpos($line, "=") !== false) {
            [$key, $value] = explode("=", $line, 2);
            $env[trim($key)] = trim($value);
        }
    }

    if (
        !isset($env["DISCORD_BOT_TOKEN"]) ||
        !isset($env["DISCORD_CHANNEL_ID"])
    ) {
        error_log(
            "Missing DISCORD_BOT_TOKEN or DISCORD_CHANNEL_ID in .env file",
        );
        return [];
    }

    $botToken = $env["DISCORD_BOT_TOKEN"];
    $channelId = $env["DISCORD_CHANNEL_ID"];

    // Use Discord API to get recent messages from the channel
    $apiUrl = "https://discord.com/api/v10/channels/{$channelId}/messages?limit={$limit}";

    $context = stream_context_create([
        "http" => [
            "method" => "GET",
            "header" => [
                "Authorization: Bot " . $botToken,
                "User-Agent: SentrySMP-Website/1.0",
                "Content-Type: application/json",
            ],
        ],
    ]);

    $response = @file_get_contents($apiUrl, false, $context);
    if ($response === false) {
        error_log("Discord API call failed for channel: " . $channelId);
        return [];
    }

    $messages = json_decode($response, true);
    if (!$messages || !is_array($messages)) {
        error_log("Discord API response invalid: " . substr($response, 0, 200));
        return [];
    }

    $announcements = [];
    foreach ($messages as $message) {
        // Skip messages from bots
        if (isset($message["author"]["bot"]) && $message["author"]["bot"]) {
            continue;
        }

        // Extract content from message (text, embeds, or attachments)
        $content = $message["content"] ?? "";

        // If no text content, try to get content from embeds
        if (empty($content) && !empty($message["embeds"])) {
            $embed = $message["embeds"][0];
            $content =
                ($embed["title"] ?? "") . "\n" . ($embed["description"] ?? "");
            $content = trim($content);
        }

        // If still no content, create content from attachments
        if (empty($content) && !empty($message["attachments"])) {
            $attachmentNames = array_map(function ($att) {
                return $att["filename"] ?? "attachment";
            }, $message["attachments"]);
            $content = "📎 " . implode(", ", $attachmentNames);
        }

        // If still empty, create a placeholder message with timestamp info
        if (empty($content)) {
            $messageDate = date("Y-m-d H:i", strtotime($message["timestamp"]));
            $content = "*[Message from {$messageDate} - no content available]*";
        }

        $lines = explode("\n", $content);
        $title = "Announcement";

        // If first line looks like a title (short and potentially bold/header)
        if (count($lines) > 1) {
            $firstLine = trim($lines[0]);
            // Check if it's a potential title (short line, maybe with markdown formatting)
            if (
                strlen($firstLine) < 120 &&
                strlen($firstLine) > 3 &&
                (strpos($firstLine, "**") !== false || // Bold text
                    strpos($firstLine, "__") !== false || // Underline
                    strpos($firstLine, "#") === 0 || // Header
                    preg_match('/^[A-Z][^.!?]*$/', $firstLine) || // Starts with capital, no sentence ending
                    count($lines) > 2) // Multiple lines suggest first is title
            ) {
                $title = strip_tags(
                    str_replace(["**", "__", "#", "*"], "", $firstLine),
                );
                // Remove title from content to avoid duplication
                $content = implode("\n", array_slice($lines, 1));
            }
        }

        // If no good title found, try to extract from content
        if ($title === "Announcement" && strlen($content) > 50) {
            $words = explode(" ", $content);
            if (count($words) >= 3) {
                $title = implode(" ", array_slice($words, 0, 6)) . "...";
            }
        }

        // Convert Discord markdown to standard markdown for better compatibility
        $content = convertDiscordMarkdown($content);

        $announcements[] = [
            "title" => trim($title),
            "content" => trim($content),
            "author" => $message["author"]["username"] ?? "Unknown",
            "created_at" => $message["timestamp"],
        ];
    }

    return $announcements;
}

// Convert Discord markdown to standard markdown
function convertDiscordMarkdown($text)
{
    // Remove @everyone and @here mentions completely
    $text = str_replace("@everyone", "", $text);
    $text = str_replace("@here", "", $text);

    // Discord spoilers ||text|| to <spoiler> tags
    $text = preg_replace(
        "/\|\|(.*?)\|\|/s",
        '<span class="spoiler">$1</span>',
        $text,
    );

    // Discord mentions <@userid> - just remove the <> brackets
    $text = preg_replace("/<@!?(\d+)>/", "@user", $text);

    // Discord role mentions <@&roleid>
    $text = preg_replace("/<@&(\d+)>/", "@role", $text);

    // Discord channels <#channelid>
    $text = preg_replace("/<#(\d+)>/", "#channel", $text);

    // Discord custom emojis <:name:id> - just show the name
    $text = preg_replace("/<a?:(.*?):\d+>/", ':$1:', $text);

    // Discord timestamps <t:timestamp> - convert to readable format
    $text = preg_replace_callback(
        "/<t:(\d+)(?::[tTdDfFR])?>/",
        function ($matches) {
            return date("Y-m-d H:i:s", $matches[1]);
        },
        $text,
    );

    // Convert Discord underline __text__ to HTML (since marked.js might not handle it well)
    $text = preg_replace("/__(.*?)__/", '<u>$1</u>', $text);

    // Support Discord blockquotes > text
    // Convert single-line Discord quotes to markdown quotes
    $text = preg_replace('/^> (.+)$/m', '> $1', $text);

    // Preserve Discord line breaks by converting to double line breaks for markdown
    $text = str_replace("\n", "\n\n", $text);

    // Clean up triple+ line breaks to just double
    $text = preg_replace("/\n{3,}/", "\n\n", $text);

    // Clean up extra spaces but preserve line structure
    $text = preg_replace("/[ \t]+/", " ", $text);
    $text = trim($text);

    return $text;
}

$announcements = getDiscordAnnouncements(3);

// Debug announcements loading
error_log("Index.php: Loaded " . count($announcements) . " announcements");
if (count($announcements) > 0) {
    error_log("Index.php: First announcement: " . $announcements[0]["title"]);
} else {
    error_log("Index.php: No announcements loaded - check Discord API");
}
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

        <title>Home - Sentry SMP</title>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="css/style.css" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link rel="icon" href="images/favicon.png" />
        <script src="https://cdn.jsdelivr.net/npm/marked@4.0.12/marked.min.js"></script>
        <script src="js/script.js"></script>
        <style>
            body.dark .header-background {
                background-image: url("images/background-image-dark.png");
            }

            body:not(.dark) .header-background {
                background-image: url("images/background-image.png");
            }

            /* Latest Announcements Section */
            .latest-announcements {
                max-width: 1200px;
                margin: 40px auto;
                padding: 0 20px;
            }

            .latest-announcements h2 {
                text-align: center;
                margin-bottom: 30px;
                color: #333;
            }

            body.dark .latest-announcements h2 {
                color: #fff;
            }

            .announcements-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
                gap: 20px;
            }

            /* Discord-specific styling */
            .spoiler {
                background-color: #202225;
                color: #202225;
                border-radius: 3px;
                padding: 0 2px;
                cursor: pointer;
                transition: color 0.1s ease;
                user-select: none;
            }

            .spoiler:hover {
                color: #dcddde;
            }

            body.dark .spoiler {
                background-color: #36393f;
                color: #36393f;
            }

            body.dark .spoiler:hover {
                color: #dcddde;
            }

            /* Discord-style underline */
            .announcement u {
                text-decoration: underline;
                text-decoration-color: #7289da;
            }

            @media (max-width: 768px) {
                .announcements-grid {
                    grid-template-columns: 1fr;
                }

                .latest-announcements {
                    margin: 20px auto;
                    padding: 0 15px;
                }
            }
            .announcement a {
                color: #dc3545;
                transition: all ease 0.3s;
                font-weight: bold;
            }

            .announcement a:hover {
                color: #c82333;
            }

            body.dark .announcement a {
                color: #dc0000;
            }

            body.dark .announcement a:hover {
                color: #b90000;
            }
        </style>
    </head>
    <body>
        <nav id="navbar-main" class="navbar">
            <!-- Obsah bude doplněn pomocí JavaScriptu -->
        </nav>

        <!-- udělat hovery na grid-itemy -->
        <header id="header-main"></header>

        <div class="grid-div">
            <ul class="thing-grid">
                <!--
                <a href="vip.html"
                    ><li class="thing-grid-item item-blue">VIP</li></a
                >
                 -->
                <a href="keys.php"
                    ><li class="thing-grid-item item-green">KEYS</li></a
                >
                <a href="shards.php"
                    ><li class="thing-grid-item item-pink">SHARDS</li></a
                >
                <a href="ranks.php"
                    ><li class="thing-grid-item item-gold">RANKS</li></a
                >
                <a href="battlepasses.php"
                    ><li class="thing-grid-item item-red">BATTLE PASS</li></a
                >
                <!--
                <a href="community.html"
                    ><li class="thing-grid-item item-turquoise">COMMUNITY</li></a
                >
                -->
            </ul>
        </div>

        <!-- Latest Announcements Section -->
        <div class="latest-announcements">
            <h2>Latest Announcements</h2>
            <div class="announcements-grid" id="latestAnnouncements"></div>
            <div style="margin-left: auto; margin-right: auto; width: min-content;">
                <button onclick="window.location.href='news.php'" style="height: 40px; width: 100px">Show more</button>
            </div>

        </div>

        <footer id="footer-main"></footer>

        <script>
            // Load announcements from PHP (server-side Discord data)
            const announcements = <?php echo json_encode($announcements); ?>;
            console.log('Announcements loaded:', announcements);
            console.log('Announcements count:', announcements.length);

            function loadLatestAnnouncements() {
                console.log('Loading latest announcements...');
                const container = document.getElementById("latestAnnouncements");

                if (!container) {
                    console.error('Container latestAnnouncements not found!');
                    return;
                }

                container.innerHTML = "";

                if (announcements.length === 0) {
                    console.log('No announcements available');
                    container.innerHTML = '<div class="announcement"><p>No announcements available at the moment.</p></div>';
                    return;
                }

                console.log('Processing', announcements.length, 'announcements');

                announcements.forEach((blog, index) => {
                    console.log('Processing announcement', index, ':', blog.title);
                    const item = document.createElement("div");
                    item.className = "announcement";

                    // Adjust time based on timezone offset
                    const date = new Date(blog.created_at);
                    const localOffset = date.getTimezoneOffset() * 60000;
                    const localDate = new Date(date.getTime() - localOffset);

                    // Check if marked.js is available
                    if (typeof marked === 'undefined') {
                        console.error('marked.js is not loaded!');
                        item.innerHTML = `
                            <div class="info"><h3>${blog.title}</h3>
                            <small>Autor: ${blog.author} | ${localDate.toLocaleString()}</small></div>
                            <div>${blog.content}</div>
                        `;
                    } else {
                        item.innerHTML = `
                            <div class="info"><h3>${blog.title}</h3>
                            <small>Autor: ${blog.author} | ${localDate.toLocaleString()}</small></div>
                            <div>${marked.parse(blog.content)}</div>
                        `;
                    }
                    container.appendChild(item);
                });

                console.log('Announcements loaded successfully');
                // Initialize spoilers after content is loaded
                initializeSpoilers();
            }

            // Add spoiler click functionality
            function initializeSpoilers() {
                document.querySelectorAll('.spoiler').forEach(spoiler => {
                    spoiler.addEventListener('click', function() {
                        this.style.color = this.style.color === 'rgb(220, 221, 222)' ? '' : '#dcddde';
                    });
                });
            }



            // Load announcements and setup theme toggle when window is fully loaded
            window.addEventListener('load', function() {
                console.log('Window fully loaded, initializing everything...');

                // Setup theme toggle
                const toggle = document.getElementById("modeToggle");
                const body = document.body;

                // Only setup theme toggle if element exists
                if (toggle) {
                    // Při načtení stránky zkontroluj uložený režim
                    const savedMode = localStorage.getItem("theme");
                    if (savedMode === "dark") {
                        body.classList.add("dark");
                        toggle.checked = true;
                    }

                    toggle.addEventListener("change", () => {
                        if (toggle.checked) {
                            body.classList.add("dark");
                            localStorage.setItem("theme", "dark");
                        } else {
                            body.classList.remove("dark");
                            localStorage.setItem("theme", "light");
                        }
                    });
                } else {
                    // If no toggle exists, just apply saved theme
                    const savedMode = localStorage.getItem("theme");
                    if (savedMode === "dark") {
                        body.classList.add("dark");
                    }
                }

                // Load announcements
                loadLatestAnnouncements();
            });
        </script>
    </body>
</html>

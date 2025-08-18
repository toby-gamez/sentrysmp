<?php
// Load Discord announcements using bot token
function getDiscordAnnouncements()
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

    error_log("Using Discord channel ID: " . $channelId);

    // Use Discord API to get recent messages from the channel
    $apiUrl = "https://discord.com/api/v10/channels/{$channelId}/messages?limit=20";

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

    error_log(
        "Successfully fetched " . count($messages) . " messages from Discord",
    );

    $announcements = [];
    foreach ($messages as $message) {
        // Skip messages from bots
        if (isset($message["author"]["bot"]) && $message["author"]["bot"]) {
            error_log(
                "Skipping bot message from: " .
                    ($message["author"]["username"] ?? "unknown"),
            );
            continue;
        }

        // Debug message structure
        error_log(
            "Processing message from: " .
                ($message["author"]["username"] ?? "unknown"),
        );
        error_log("Content length: " . strlen($message["content"] ?? ""));
        error_log("Embeds count: " . count($message["embeds"] ?? []));
        error_log("Attachments count: " . count($message["attachments"] ?? []));

        // For debugging, don't skip empty messages - show them all

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
            error_log("Using placeholder content for empty message");
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

    error_log(
        "Processed " . count($announcements) . " announcements from Discord",
    );
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

    // Discord bold **text** and italic *text* are supported by marked.js
    // But ensure nested formatting works properly

    // Convert Discord underline __text__ to HTML (since marked.js might not handle it well)
    $text = preg_replace("/__(.*?)__/", '<u>$1</u>', $text);

    // Convert Discord strikethrough ~~text~~ (already supported by marked.js)

    // Support Discord code blocks ```code``` and inline code `code`
    // These are already supported by marked.js, no conversion needed

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

$announcements = getDiscordAnnouncements();
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

        <title>News - Sentry SMP</title>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="css/style.css" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link rel="icon" href="images/favicon.png" />
        <script src="https://cdn.jsdelivr.net/npm/marked@4.0.12/marked.min.js"></script>
        <style>
            body.dark .header-background {
                background-image: url("images/background-image-dark.png");
            }

            body:not(.dark) .header-background {
                background-image: url("images/background-image.png");
            }
            #blogList {
                color: black;
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



        </style>
    </head>
    <body>
        <nav class="navbar" id="navbar-main"></nav>

        <!-- udělat hovery na grid-itemy -->
        <header id="header-main"></header>
        <div class="container">
            <div class="main-wrapper">
                <h1 class="main">News</h1>
            </div>
            <div id="blogList"></div>
            <p><i>*only the first 20 announcements are loaded here, if you want to see all, go to Discord.</i></p>
        </div>
        <footer id="footer-main"></footer>
        <script src="js/script.js"></script>
        <body>
            <script>
                // Load announcements from PHP (server-side Discord webhook data)
                const announcements = <?php echo json_encode(
                    $announcements,
                ); ?>;

                function loadBlogs() {
                    const list = document.getElementById("blogList");
                    list.innerHTML = "";

                    if (announcements.length === 0) {
                        list.innerHTML = '<div class="announcement"><p>No announcements available at the moment.</p></div>';
                        return;
                    }

                    announcements.forEach((blog) => {
                        const item = document.createElement("div");
                        item.className = "announcement"; // Třída pro styling

                        // Adjust time based on timezone offset
                        const date = new Date(blog.created_at);
                        const localOffset = date.getTimezoneOffset() * 60000;
                        const localDate = new Date(date.getTime() - localOffset);

                        item.innerHTML = `
                            <div class="info"><h3>${blog.title}</h3>
                            <small>Author: ${blog.author} | ${localDate.toLocaleString()}</small></div>
                            <div>${marked.parse(blog.content)}</div>
                        `;
                        list.appendChild(item);

                        // Re-initialize spoilers for this new content
                        initializeSpoilers();
                    });
                }

                // Add spoiler click functionality
                function initializeSpoilers() {
                    document.querySelectorAll('.spoiler').forEach(spoiler => {
                        spoiler.addEventListener('click', function() {
                            this.style.color = this.style.color === 'rgb(220, 221, 222)' ? '' : '#dcddde';
                        });
                    });
                }

                // Load announcements when page loads
                document.addEventListener('DOMContentLoaded', function() {
                    loadBlogs();
                    // Initialize spoilers after content is loaded
                    setTimeout(initializeSpoilers, 100);
                });
            </script>
        </body>
    </body>
</html>

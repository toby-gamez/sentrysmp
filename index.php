<?php
// Include the RCON functionality
require_once "vip-rcon.php";

// Function to automatically clean up expired VIP users
function cleanupExpiredVipUsers()
{
    try {
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

        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $days_left = getDaysLeft($row["created_at"]);
            if ($days_left <= 0) {
                // First remove the VIP permissions via RCON
                $remove_name = $row["username"];
                $rcon_result = removeVipPermissions($remove_name);

                // Then delete the expired user from database
                try {
                    $stmt = $db->prepare(
                        "DELETE FROM vip_users WHERE username = :username"
                    );
                    $stmt->bindValue(":username", $remove_name, SQLITE3_TEXT);
                    $stmt->execute();
                    $deleted_count++;

                    // Log the complete removal
                    $log_message =
                        date("Y-m-d H:i:s") .
                        " - Removed VIP user: $remove_name - RCON: " .
                        ($rcon_result ? "Success" : "Failed") .
                        "\n";
                    file_put_contents(
                        "vip_cleanup_log.txt",
                        $log_message,
                        FILE_APPEND
                    );
                } catch (Exception $e) {
                    // Silent error handling
                    $log_message =
                        date("Y-m-d H:i:s") .
                        " - Failed to remove VIP user from database: $remove_name - Error: " .
                        $e->getMessage() .
                        "\n";
                    file_put_contents(
                        "vip_cleanup_log.txt",
                        $log_message,
                        FILE_APPEND
                    );
                }
            }
        }

        // Optional: log cleanup activity summary
        if ($deleted_count > 0) {
            $log_message =
                date("Y-m-d H:i:s") .
                " - Summary: Removed $deleted_count expired VIP users\n";
            file_put_contents("vip_cleanup_log.txt", $log_message, FILE_APPEND);
        }

        return $deleted_count;
    } catch (Exception $e) {
        // Log error
        $log_message =
            date("Y-m-d H:i:s") .
            " - Error in VIP cleanup process: " .
            $e->getMessage() .
            "\n";
        file_put_contents("vip_cleanup_log.txt", $log_message, FILE_APPEND);
        return 0;
    }
}

// Run cleanup on 100% of page loads
cleanupExpiredVipUsers();
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
        <script src="js/script.js"></script>
        <style>
            body.dark .header-background {
                background-image: url("images/background-image-dark.png");
            }

            body:not(.dark) .header-background {
                background-image: url("images/background-image.png");
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
                <a href="spawners.php"
                    ><li class="thing-grid-item item-red">SPAWNERS</li></a
                >
                <a href="ranks.php"
                    ><li class="thing-grid-item item-gold">RANKS</li></a
                >
                <a href="news.html"
                    ><li class="thing-grid-item item-pink">NEWS</li></a
                >
                <a href="commutity.html"
                    ><li class="thing-grid-item item-turquoise">
                        COMMUNITY
                    </li></a
                >
            </ul>
        </div>

        <footer id="footer-main"></footer>

        <script>
            const toggle = document.getElementById("modeToggle");
            const body = document.body;

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
        </script>
    </body>
</html>

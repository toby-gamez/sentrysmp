<?php
// Include the RCON functionality
require_once "vip-rcon.php";

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
                        FILE_APPEND
                    );
                }

                // Always try to delete from database regardless of RCON result
                try {
                    $stmt = $db->prepare(
                        "DELETE FROM vip_users WHERE username = :username"
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
                            FILE_APPEND
                        );
                    } else {
                        $failed_count++;
                        $log_message =
                            date("Y-m-d H:i:s") .
                            " - Failed to delete from database: $remove_name\n";
                        file_put_contents(
                            "vip_cleanup_log.txt",
                            $log_message,
                            FILE_APPEND
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
                        FILE_APPEND
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
    $deleted_count = cleanupExpiredVipUsers();
    file_put_contents($cleanup_file, time());

    if ($deleted_count > 0) {
        error_log("VIP Cleanup: Removed $deleted_count expired users");
    }
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

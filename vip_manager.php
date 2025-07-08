<?php
session_start();

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login.php");
    exit();
}

$username = htmlspecialchars($_SESSION["username"]);
// User deletion processing
$delete_result = "";
if (
    isset($_POST["action"]) &&
    $_POST["action"] === "delete" &&
    isset($_POST["username"])
) {
    try {
        $db = new SQLite3("vip.sqlite");
        $stmt = $db->prepare(
            "DELETE FROM vip_users WHERE username = :username"
        );
        $stmt->bindValue(":username", $_POST["username"], SQLITE3_TEXT);
        $result = $stmt->execute();
        $delete_result = "User '{$_POST["username"]}' was successfully deleted.";
    } catch (Exception $e) {
        $delete_result = "Error deleting user: " . $e->getMessage();
    }
}

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

// Get list of users and auto-delete expired
$users = [];
try {
    $db = new SQLite3("vip.sqlite");
    $result = $db->query(
        "SELECT id, username, created_at FROM vip_users ORDER BY created_at DESC"
    );
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $days_left = getDaysLeft($row["created_at"]);
        if ($days_left <= 0) {
            // Delete expired user
            try {
                $stmt = $db->prepare(
                    "DELETE FROM vip_users WHERE username = :username"
                );
                $stmt->bindValue(":username", $row["username"], SQLITE3_TEXT);
                $stmt->execute();
                // Optionally, you could add a message for auto-deleted users
            } catch (Exception $e) {
                // Optionally, handle error
            }
            continue; // Skip adding to $users
        }
        $users[] = $row;
    }
} catch (Exception $e) {
    $error = "Error loading users: " . $e->getMessage();
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

            // Default state: consent denied if no choice is stored
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
        <!-- Google Analytics (does not load automatically) -->
        <script
            async
            src="https://www.googletagmanager.com/gtag/js?id=G-SGG2CLM06D"
        ></script>
        <script>
            function loadGoogleAnalytics() {
                gtag("js", new Date());
                gtag("config", "G-SGG2CLM06D");
            }

            // Function to show/hide cookie banner
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

            // Function to accept cookies
            function acceptCookies() {
                localStorage.setItem("cookies-accepted", "granted");
                document.getElementById("cookie-banner").style.display = "none";
                gtag("consent", "update", {
                    analytics_storage: "granted",
                });
                loadGoogleAnalytics();
            }

            // Function to decline cookies
            function declineCookies() {
                localStorage.setItem("cookies-accepted", "denied");
                document.getElementById("cookie-banner").style.display = "none";
                gtag("consent", "update", {
                    analytics_storage: "denied",
                });
            }

            // Show cookie banner after page load
            window.addEventListener("DOMContentLoaded", showCookieBanner);
        </script>
        <!-- SEO meta tags for Google -->
        <meta
            name="description"
            content="SentrySMP is a Minecraft server focused on providing a safe and enjoyable experience for players of all ages. It is SMP with addons."
        />
        <meta
            name="keywords"
            content="Minecraft, SMP, English, Czech, server, safe, enjoyable, experience, players, vip, premium, exclusive"
        />
        <meta name="author" content="Sentry SMP" />

        <!-- Open Graph for Facebook, Discord, etc. -->
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

        <!-- Twitter Cards (optional) -->
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

        <title>VIP Manager - Admin - Sentry SMP</title>
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
        </style>
    </head>
    <body>
        <nav class="navbar" id="navbar-main"></nav>

        <!-- add hover effects to grid-items -->
        <div class="container">
            <div class="main-wrapper">
                <h2 class="main">VIP Manager</h2>
            </div>


            <?php if ($delete_result): ?>
                <p class="success"><?php echo $delete_result; ?></p>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>

            <?php if (count($users) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Minecraft name</th>
                            <th>Expiring date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(
                                    $user["id"]
                                ); ?></td>
                                <td><?php echo htmlspecialchars(
                                    $user["username"]
                                ); ?></td>
                                <td>
                                    <?php
                                    $days_left = getDaysLeft(
                                        $user["created_at"]
                                    );
                                    echo $days_left . " days";
                                    ?>
                                </td>
                                <td>
                                    <form method="post" action="" onsubmit="return confirm('Are you sure you want to delete user <?php echo htmlspecialchars(
                                        $user["username"]
                                    ); ?>?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="username" value="<?php echo htmlspecialchars(
                                            $user["username"]
                                        ); ?>">
                                        <button type="submit" class="action-btn danger">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No VIP waiting users found.</p>
            <?php endif; ?>
        </div>
        <footer id="footer-main"></footer>
        <script src="js/script.js"></script>
    </body>
</html>

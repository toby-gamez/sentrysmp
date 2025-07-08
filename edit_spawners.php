<!-- edit_spawners.php -->
<?php
// Start session at the beginning
session_start();

try {
    $db = new PDO("sqlite:blog.sqlite");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Test query to verify connection and table access
    $test = $db->query("SELECT COUNT(*) FROM spawners")->fetchColumn();
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["delete"])) {
        $stmt = $db->prepare("DELETE FROM spawners WHERE id = ?");
        $stmt->execute([$_POST["id"]]);
    } elseif (isset($_POST["update"])) {
        $sales =
            isset($_POST["sales"]) && $_POST["sales"] !== ""
                ? $_POST["sales"]
                : null;
        try {
            $stmt = $db->prepare(
                "UPDATE spawners SET nazev = ?, popis = ?, obrazek = ?, prikaz = ?, sales = ? WHERE id = ?"
            );
            $params = [
                $_POST["nazev"],
                $_POST["popis"],
                $_POST["obrazek"],
                $_POST["prikaz"],
                $sales,
                $_POST["id"],
            ];
            $result = $stmt->execute($params);

            if (!$result) {
                throw new PDOException(
                    "Failed to update spawner with ID: " . $_POST["id"]
                );
            }
        } catch (PDOException $e) {
            echo "Update error: " . $e->getMessage();
            exit();
        }
    } elseif (isset($_POST["add"])) {
        $imagePath = "";
        if (
            isset($_FILES["new_obrazek"]) &&
            $_FILES["new_obrazek"]["error"] === UPLOAD_ERR_OK
        ) {
            $uploadDir = "images/";
            $filename = basename($_FILES["new_obrazek"]["name"]);
            $targetFile = $uploadDir . $filename;

            if (
                move_uploaded_file(
                    $_FILES["new_obrazek"]["tmp_name"],
                    $targetFile
                )
            ) {
                $imagePath = $targetFile;
            } else {
                echo "Error: Failed to upload image.";
            }
        }

        $sales =
            isset($_POST["new_sales"]) && $_POST["new_sales"] !== ""
                ? $_POST["new_sales"]
                : null;

        try {
            $stmt = $db->prepare(
                "INSERT INTO spawners (nazev, popis, obrazek, prikaz, sales) VALUES (?, ?, ?, ?, ?)"
            );
            $params = [
                $_POST["new_nazev"],
                $_POST["new_popis"],
                $imagePath,
                $_POST["new_prikaz"],
                $sales,
            ];
            $result = $stmt->execute($params);

            if (!$result) {
                throw new PDOException("Failed to insert new spawner");
            }
        } catch (PDOException $e) {
            echo "Insert error: " . $e->getMessage();
            exit();
        }
    }
}

try {
    $spawners = $db
        ->query("SELECT * FROM spawners")
        ->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error retrieving spawners: " . $e->getMessage();
    exit();
}

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login.php");
    exit();
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

            // Show notifications for CRUD operations
            window.addEventListener('DOMContentLoaded', function() {
                <?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
                    <?php if (isset($_POST["delete"])): ?>
                        showNotification("Spawner successfully deleted", "success");
                    <?php elseif (isset($_POST["update"])): ?>
                        showNotification("Spawner successfully updated", "success");
                    <?php elseif (isset($_POST["add"])): ?>
                        showNotification("New spawner successfully added", "success");
                    <?php endif; ?>
                <?php endif; ?>
            });
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

        <title>Edit Spawners - Admin Panel - Sentry SMP</title>
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

        <!-- udělat hovery na grid-itemy -->
        <div class="container">
            <div class="main-wrapper">
                <h1 class="main">Edit Spawners</h1>
            </div>
            <p>When you need to add player name, use "<b><code>$usernamemc</code></b>".</p>
            <?php foreach ($spawners as $s): ?>
            <div class="spaw">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $s["id"] ?>">
                    <input type="text" name="nazev" placeholder="Name" value="<?= htmlspecialchars(
                        $s["nazev"]
                    ) ?>">
                    <input type="text" name="popis" placeholder="Price"  value="<?= htmlspecialchars(
                        $s["popis"]
                    ) ?>">
                    <input type="text" name="obrazek" placeholder="Image" value="<?= htmlspecialchars(
                        $s["obrazek"]
                    ) ?>">
                    <input type="text" name="prikaz" placeholder="Command" value="<?= htmlspecialchars(
                        $s["prikaz"] ?? ""
                    ) ?>">
                    <input type="number" name="sales" placeholder="Discount %" min="0" max="100" value="<?= htmlspecialchars(
                        $s["sales"] ?? ""
                    ) ?>">
                    <button type="submit" name="update" onclick="showNotification('Updating spawner...', 'info', 1500)">Edit</button>
                    <button type="submit" class="danger" name="delete" onclick="return confirm('Are you sure you want to delete this spawner?') && showNotification('Removing spawner...', 'info', 1500);">Remove</button>
                </form>
            </div>
            <?php endforeach; ?>
            <div class="main-wrapper">
            <h2 class="main">Add New Spawner</h2>
            </div>
            <p>When you need to add player name, use "<b><code>$usernamemc</code></b>".</p>
            <form method="post" enctype="multipart/form-data">
                <input type="text" name="new_nazev" placeholder="Name" required>
                <input type="text" name="new_popis" placeholder="Price (with Euro symbol)">
                <input type="file" name="new_obrazek" accept="image/*">
                <input type="text" name="new_prikaz" placeholder="Command">
                <input type="number" name="new_sales" placeholder="Discount %" min="0" max="100">
                <button type="submit" name="add" onclick="showNotification('Adding new spawner...', 'info', 1500)">Přidat</button>
                <p>For discount, enter a percentage (0-100). Leave empty for no discount or write 0.</p>
            </form>
        </div>
        <footer id="footer-main"></footer>
        <script src="js/script.js"></script>
    </body>
</html>

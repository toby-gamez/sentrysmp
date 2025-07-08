<?php
// Start session at the beginning
session_start();

try {
    $db = new PDO("sqlite:ranks.sqlite");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $test = $db->query("SELECT COUNT(*) FROM ranks")->fetchColumn();
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["delete"])) {
        $stmt = $db->prepare("DELETE FROM ranks WHERE id = ?");
        $stmt->execute([$_POST["id"]]);
    } elseif (isset($_POST["update"])) {
        $sales =
            isset($_POST["sales"]) && $_POST["sales"] !== ""
                ? $_POST["sales"]
                : null;
        try {
            $stmt = $db->prepare(
                "UPDATE ranks SET nazev = ?, popis = ?, obrazek = ?, prikaz = ?, sales = ?, cena = ? WHERE id = ?"
            );
            $params = [
                $_POST["nazev"],
                $_POST["popis"],
                $_POST["obrazek"],
                $_POST["prikaz"],
                $sales,
                $_POST["cena"],
                $_POST["id"],
            ];
            $result = $stmt->execute($params);
            if (!$result) {
                throw new PDOException(
                    "Failed to update rank with ID: " . $_POST["id"]
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
                "INSERT INTO ranks (nazev, popis, obrazek, prikaz, sales, cena) VALUES (?, ?, ?, ?, ?, ?)"
            );
            $params = [
                $_POST["new_nazev"],
                $_POST["new_popis"],
                $imagePath,
                $_POST["new_prikaz"],
                $sales,
                $_POST["new_cena"],
            ];
            $result = $stmt->execute($params);
            if (!$result) {
                throw new PDOException("Failed to insert new rank");
            }
        } catch (PDOException $e) {
            echo "Insert error: " . $e->getMessage();
            exit();
        }
    }
}

try {
    $ranks = $db->query("SELECT * FROM ranks")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error retrieving ranks: " . $e->getMessage();
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
                        showNotification("Rank successfully deleted", "success");
                    <?php elseif (isset($_POST["update"])): ?>
                        showNotification("Rank successfully updated", "success");
                    <?php elseif (isset($_POST["add"])): ?>
                        showNotification("New rank successfully added", "success");
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

        <title>Edit Ranks - Admin Panel - Sentry SMP</title>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="css/style.css" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link rel="icon" href="images/favicon.png" />
        <style>
            /* .spaw and .danger already styled in style.css */
        </style>
    </head>
    <body>
        <nav class="navbar" id="navbar-main"></nav>

        <!-- udělat hovery na grid-itemy -->
        <div class="container">
            <div class="main-wrapper">
                <h1 class="main">Edit Ranks</h1>
            </div>
            <?php foreach ($ranks as $r): ?>
            <div class="spaw">
                <form method="post">
                    <input type="hidden" name="id" value="<?= $r["id"] ?>">
                    <input type="text" name="nazev" placeholder="Name" value="<?= htmlspecialchars(
                        $r["nazev"]
                    ) ?>">
                    <input type="text" name="popis" placeholder="Description" value="<?= htmlspecialchars(
                        $r["popis"]
                    ) ?>">
                    <input type="text" name="obrazek" placeholder="Image" value="<?= htmlspecialchars(
                        $r["obrazek"]
                    ) ?>">
                    <input type="text" name="prikaz" placeholder="Command" value="<?= htmlspecialchars(
                        $r["prikaz"]
                    ) ?>">
                    <input type="number" name="cena" placeholder="Price" min="0" value="<?= htmlspecialchars(
                        $r["cena"]
                    ) ?>">
                    <input type="number" name="sales" placeholder="Discount %" min="0" max="100" value="<?= htmlspecialchars(
                        $r["sales"] ?? ""
                    ) ?>">
                    <button type="submit" name="update" onclick="showNotification('Updating rank...', 'info', 1500)">Edit</button>
                    <button type="submit" class="danger" name="delete" onclick="return confirm('Are you sure you want to delete this rank?') && showNotification('Removing rank...', 'info', 1500);">Remove</button>
                </form>
            </div>
            <?php endforeach; ?>
            <div class="main-wrapper">
            <h2 class="main">Add New Rank</h2>
            </div>
            <form method="post" enctype="multipart/form-data">
                <input type="text" name="new_nazev" placeholder="Name" required>
                <input type="text" name="new_popis" placeholder="Description">
                <input type="file" name="new_obrazek" accept="image/*">
                <input type="text" name="new_prikaz" placeholder="Command">
                <input type="number" name="new_cena" placeholder="Price" min="0" required>
                <input type="number" name="new_sales" placeholder="Discount %" min="0" max="100">
                <button type="submit" name="add">Add</button>
                <p>For discount, enter a percentage (0-100). Leave empty for no discount or write 0.</p>
            </form>
        </div>
        <footer id="footer-main"></footer>
        <script src="js/script.js"></script>
    </body>
</html>

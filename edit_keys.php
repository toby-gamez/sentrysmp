<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

$db = new PDO("sqlite:keys.sqlite");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["delete"])) {
        $stmt = $db->prepare("DELETE FROM Keys WHERE id = ?");
        $stmt->execute([$_POST["id"]]);
    } elseif (isset($_POST["update"])) {
        $sales =
            isset($_POST["sales"]) && $_POST["sales"] !== ""
                ? $_POST["sales"]
                : null;
        $stmt = $db->prepare(
            "UPDATE Keys SET name = ?, value = ?, prikaz = ?, price = ?, sales = ? WHERE id = ?"
        );
        $stmt->execute([
            $_POST["name"],
            $_POST["value"],
            $_POST["prikaz"],
            $_POST["price"],
            $sales,
            $_POST["id"],
        ]);
    } elseif (isset($_POST["add"])) {
        $imagePath = "";
        if (
            isset($_FILES["new_image"]) &&
            $_FILES["new_image"]["error"] === UPLOAD_ERR_OK
        ) {
            $uploadDir = "images/";
            $filename = basename($_FILES["new_image"]["name"]);
            $targetFile = $uploadDir . $filename;
            if (
                move_uploaded_file(
                    $_FILES["new_image"]["tmp_name"],
                    $targetFile
                )
            ) {
                $imagePath = $targetFile;
            } else {
                echo "Chyba při nahrávání obrázku!";
            }
        }
        $sales =
            isset($_POST["new_sales"]) && $_POST["new_sales"] !== ""
                ? $_POST["new_sales"]
                : null;
        $stmt = $db->prepare(
            "INSERT INTO Keys (name, value, image, prikaz, price, sales) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $_POST["new_name"],
            $_POST["new_value"],
            $imagePath,
            $_POST["new_prikaz"],
            $_POST["new_price"],
            $sales,
        ]);
    }
}

$keys = $db->query("SELECT * FROM Keys")->fetchAll(PDO::FETCH_ASSOC);

session_start();
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

            // Custom confirmation dialog
            function showConfirmDialog(message, onConfirm, onCancel) {
                createNotificationContainer();

                // Create confirmation dialog
                const dialog = document.createElement("div");
                dialog.className = "notification confirmation-dialog";

                // Create message content
                const messageEl = document.createElement("div");
                messageEl.className = "message";
                messageEl.innerHTML = message;
                dialog.appendChild(messageEl);

                // Create button container
                const buttonContainer = document.createElement("div");
                buttonContainer.className = "confirmation-buttons";

                // Create confirm button
                const confirmBtn = document.createElement("button");
                confirmBtn.textContent = "Yes, delete";
                confirmBtn.className = "confirm-btn";
                confirmBtn.onclick = () => {
                    removeNotification(dialog);
                    if (onConfirm) onConfirm();
                };

                // Create cancel button
                const cancelBtn = document.createElement("button");
                cancelBtn.textContent = "Cancel";
                cancelBtn.className = "cancel-btn";
                cancelBtn.onclick = () => {
                    removeNotification(dialog);
                    if (onCancel) onCancel();
                };

                buttonContainer.appendChild(confirmBtn);
                buttonContainer.appendChild(cancelBtn);
                dialog.appendChild(buttonContainer);

                // Add to container
                notificationContainer.appendChild(dialog);

                return dialog;
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

        <title>Edit keys - Sentry SMP</title>
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

            /* Styles for confirmation dialog */
            .confirmation-dialog {
                background: #f44336 !important;
                border-left: 5px solid #d32f2f !important;
            }

            .confirmation-buttons {
                display: flex;
                gap: 10px;
                margin-top: 15px;
                justify-content: center;
            }

            .confirm-btn, .cancel-btn {
                padding: 8px 16px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-weight: bold;
                transition: background-color 0.3s;
            }

            .confirm-btn {
                background-color: #d32f2f;
                color: white;
            }

            .confirm-btn:hover {
                background-color: #b71c1c;
            }

            .cancel-btn {
                background-color: #757575;
                color: white;
            }

            .cancel-btn:hover {
                background-color: #424242;
            }
        </style>
    </head>
    <body>
        <nav class="navbar" id="navbar-main"></nav>

        <!-- udělat klíče a banner, bez něj se nehnu -->

        <!-- udělat hovery na grid-itemy -->
        <div class="container">
            <div class="main-wrapper">
                <h1 class="main">Edit Keys</h1>
            </div>
            <ul class="edit-keys">
                <?php foreach ($keys as $k): ?>
                    <li class="edit-key">
                        <?php if (!empty($k["image"])): ?>
                            <img src="<?= htmlspecialchars(
                                $k["image"]
                            ) ?>" width="100"><br>
                        <?php endif; ?>
                        <form method="post" class="edit-form">
                            <input type="hidden" name="id"  value="<?= $k[
                                "id"
                            ] ?>">
                            <input type="text" name="name" placeholder="Name" value="<?= htmlspecialchars(
                                $k["name"]
                            ) ?>">
                            <input type="text" name="value" placeholder="Description" value="<?= htmlspecialchars(
                                $k["value"]
                            ) ?>">
                            <input type="text" name="prikaz" placeholder="Command" value="<?= htmlspecialchars(
                                $k["prikaz"] ?? ""
                            ) ?>">
                            <input type="number" name="price" placeholder="Price" value="<?= htmlspecialchars(
                                $k["price"] ?? "3"
                            ) ?>">
                            <input type="number" name="sales" placeholder="Discount %" min="0" max="100" value="<?= htmlspecialchars(
                                $k["sales"] ?? ""
                            ) ?>">
                            <button type="submit" name="update" onclick="showNotification('Updating key...', 'info', 1500)">Edit</button>
                            <button type="button" name="delete" class="delete-btn danger" data-key-id="<?= $k[
                                "id"
                            ] ?>" data-key-name="<?= htmlspecialchars(
    $k["name"]
) ?>">Remove</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="main-wrapper">
            <h1 class="main">Add Key</h1>
            </div>
            <form method="post" enctype="multipart/form-data">
                <input type="text" name="new_name" placeholder="Name" required>
                <input type="text" name="new_value" placeholder="Description">
                <input type="file" name="new_image" accept="image/*">
                <input type="text" name="new_prikaz" placeholder="Command">
                <input type="number" name="new_price" placeholder="Price">
                <input type="number" name="new_sales" placeholder="Discount %" min="0" max="100">
                <button type="submit" name="add">Přidat</button>
                <p>Enter the price as a number (e.g., 3). The euro symbol (€) will be added automatically.</p>
                <p>For discount, enter a percentage (0-100). Leave empty for no discount or write 0.</p>
                <p>When you need to add player name, use "<b>$usernamemc</b>".</p>

                <script>
                    // Check for status in URL parameters
                    window.addEventListener('DOMContentLoaded', function() {
                        const urlParams = new URLSearchParams(window.location.search);

                        <?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
                            <?php if (isset($_POST["delete"])): ?>
                                showNotification("Key successfully deleted", "success");
                            <?php elseif (isset($_POST["update"])): ?>
                                showNotification("Key successfully updated", "success");
                            <?php elseif (isset($_POST["add"])): ?>
                                showNotification("New key successfully added", "success");
                            <?php endif; ?>
                        <?php endif; ?>

                        // Add event listeners for delete buttons
                        document.querySelectorAll('.delete-btn').forEach(button => {
                            button.addEventListener('click', function(e) {
                                e.preventDefault();

                                const keyId = this.dataset.keyId;
                                const keyName = this.dataset.keyName;
                                const form = this.closest('form');

                                showConfirmDialog(
                                    `Are you sure you want to delete the key "${keyName}"?<br>This action cannot be undone.`,
                                    function() {
                                        // User confirmed - submit the form with delete action
                                        const deleteInput = document.createElement('input');
                                        deleteInput.type = 'hidden';
                                        deleteInput.name = 'delete';
                                        deleteInput.value = '1';
                                        form.appendChild(deleteInput);

                                        showNotification("Deleting key...", "info", 2000);
                                        form.submit();
                                    },
                                    function() {
                                        // User cancelled - do nothing
                                        showNotification("Delete cancelled", "info", 2000);
                                    }
                                );
                            });
                        });

                        // Add event listeners for form submissions
                        document.querySelectorAll('.edit-form').forEach(form => {
                            form.addEventListener('submit', function(e) {
                                if (this.querySelector('[name="update"]')) {
                                    showNotification("Updating key...", "info", 2000);
                                }
                            });
                        });

                        // Add event listener for the add form
                        const addForm = document.querySelector('form[enctype="multipart/form-data"]');
                        if (addForm) {
                            addForm.addEventListener('submit', function() {
                                showNotification("Adding new key...", "info", 2000);
                            });
                        }
                    });
                </script>
            </form>
        </div>
        <footer id="footer-main"></footer>
        <script src="js/script.js"></script>
    </body>
</html>

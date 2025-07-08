<?php
session_start();

// Load environment variables
require_once __DIR__ . "/vendor/autoload.php";
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// 1. Pokud už je přihlášený, přesměruj ho
if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
    header("Location: admin");
    exit();
}

// 2. Zpracování přihlášení pouze pokud byl odeslán formulář
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input_username = $_POST["username"];
    $input_password = $_POST["password"];

    // Get admin credentials from environment variables
    $admin_users = [
        [
            "username" => $_ENV["ADMIN_USERNAME_1"] ?? "webdev",
            "password" => $_ENV["ADMIN_PASSWORD_1"] ?? "",
        ],
        [
            "username" => $_ENV["ADMIN_USERNAME_2"] ?? "owner",
            "password" => $_ENV["ADMIN_PASSWORD_2"] ?? "",
        ],
        [
            "username" => $_ENV["ADMIN_USERNAME_3"] ?? "pepeno01",
            "password" => $_ENV["ADMIN_PASSWORD_3"] ?? "",
        ],
    ];

    $valid = false;

    foreach ($admin_users as $user) {
        if (
            $user["username"] === $input_username &&
            $user["password"] === $input_password &&
            !empty($user["password"])
        ) {
            $valid = true;
            break;
        }
    }

    if ($valid) {
        $_SESSION["logged_in"] = true;
        $_SESSION["username"] = $input_username;

        // Set a session variable to show notification on admin page
        $_SESSION["login_success"] = true;

        header("Location: admin");
        exit();
    } else {
        echo "<div class='error'>Invalid username or password.</div>";
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

        <title>Login - Admin Panel - Sentry SMP</title>
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
            form {
                display: flex;
                flex-direction: column;
                align-items: center;
                border: 1px solid #ccc;
                padding: 20px;
                border-radius: 10px;
            }
            .container {
                position: absolute;
                max-width: 400px;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
            }
        </style>
    </head>
    <body>

        <div class="container">
            <div class="main-wrapper">
                <h1 class="main">Login</h1>
            </div>
            <form method="post" action="login">
              <input type="text" name="username" placeholder="Username" required>
              <input type="password" name="password" placeholder="Password" required>
              <button type="submit">Login</button>
            </form>
            <?php if (isset($_GET["logout"]) && $_GET["logout"] == "true"): ?>
            <script>
                // Show logout notification if redirected after logout
                document.addEventListener("DOMContentLoaded", function() {
                    showNotification("You have been successfully logged out", "info");
                });
            </script>
            <?php endif; ?>
            <?php if (isset($_GET["error"]) && $_GET["error"] == "auth"): ?>
            <script>
                // Show error notification if authentication failed
                document.addEventListener("DOMContentLoaded", function() {
                    showNotification("Authentication required. Please log in.", "error");
                });
            </script>
            <?php endif; ?>
            <script>
                // Add event listener for form submission
                document.querySelector("form").addEventListener("submit", function() {
                    // Store references to form fields
                    const usernameField = document.querySelector("input[name='username']");
                    const passwordField = document.querySelector("input[name='password']");

                    // Check if fields are filled
                    if (usernameField.value && passwordField.value) {
                        showNotification("Logging in...", "info", 2000);
                    }
                });
            </script>
        </div>
        <script src="js/script.js"></script>
    </body>
</html>

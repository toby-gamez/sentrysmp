<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
session_start();
file_put_contents("debug.txt", print_r($_REQUEST, true)); // Záznam všech příchozích dat

// Podrobnější debugging
file_put_contents(
    "debug-dump.txt",
    "POST: " .
        print_r($_POST, true) .
        "\n" .
        "GET: " .
        print_r($_GET, true) .
        "\n" .
        "REQUEST: " .
        print_r($_REQUEST, true) .
        "\n" .
        "SERVER: " .
        print_r($_SERVER, true) .
        "\n" .
        "Raw input: " .
        file_get_contents("php://input") .
        "\n",
);

// Zkontrolujeme zdroj dat - zda používáme POST nebo GET
$usernamemc = "";
if (isset($_POST["usernamemc"])) {
    $usernamemc = htmlspecialchars(trim($_POST["usernamemc"]));
} elseif (isset($_REQUEST["usernamemc"])) {
    $usernamemc = htmlspecialchars(trim($_REQUEST["usernamemc"]));
}

// Zjistíme edici Minecraftu - java, bedrock nebo cracked
$edition = "java"; // výchozí hodnota
if (isset($_POST["edition"])) {
    if ($_POST["edition"] === "bedrock") {
        $edition = "bedrock";
    } elseif ($_POST["edition"] === "cracked") {
        $edition = "cracked";
    } else {
        $edition = "java";
    }
} elseif (isset($_REQUEST["edition"])) {
    if ($_REQUEST["edition"] === "bedrock") {
        $edition = "bedrock";
    } elseif ($_REQUEST["edition"] === "cracked") {
        $edition = "cracked";
    } else {
        $edition = "java";
    }
}

// Verification
if (empty($usernamemc)) {
    echo "Error: Username is required. POST data: " . json_encode($_POST);
    exit();
}

if (!preg_match('/^[a-zA-Z0-9_]{3,16}$/', $usernamemc)) {
    echo "Error: Username must be 3-16 characters and contain only letters, numbers and underscore.";
    exit();
}

// Use different skin handling based on account type
if ($edition === "java") {
    // For Java accounts, use their actual skin
    $skinUrl = "https://minotar.net/helm/" . urlencode($usernamemc) . "/100";
} else {
    // For Bedrock and cracked accounts, use default Steve skin
    $skinUrl = "https://minotar.net/helm/MHF_Steve/100";
}
$finalUsername = $usernamemc;

$_SESSION["usernamemc"] = $finalUsername;
$_SESSION["edition"] = $edition;
$_SESSION["skin"] = $skinUrl;

// Send back a script to update localStorage and redirect
echo '
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Logging in...</title>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap");
        body {
            background-color: #090909;
            color: white;
        }
        .main {
            text-align: center;
            padding-bottom: 2rem;
            font-size: 40px;
            color: white;
            font-family: "Poppins", sans-serif;
            font-weight: 400;
            font-style: normal;
        }
        p, a {
            color: white;
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
    <h1 class="main">Logging in...</h1>
    </div>
    <script>
        // Notification system
        let notificationContainer = null;

        // Create the notification container when first needed
        function createNotificationContainer() {
            if (!notificationContainer) {
                notificationContainer = document.createElement("div");
                notificationContainer.className = "notification-container";
                document.body.appendChild(notificationContainer);
            }
            return notificationContainer;
        }

        // Show notification with auto-dismiss timer
        function showNotification(
            message,
            type = "success",
            duration = 5000,
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
                const remainingPercent =
                    100 - (elapsedTime / duration) * 100;

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

        // Store username and edition in localStorage
        localStorage.setItem("minecraft-username", "' .
    $finalUsername .
    '");
        localStorage.setItem("minecraft-edition", "' .
    $edition .
    '");

        // Show notification
        showNotification("Successfully logged in as ' .
    $finalUsername .
    '", "success");

        // Redirect to index page after a short delay
        setTimeout(function() {
            window.location.href = "home";
        }, 1500);
    </script>
    <p>Logging in... If you are not redirected, <a href="home">click here</a>.</p>
</body>
</html>
';
exit();

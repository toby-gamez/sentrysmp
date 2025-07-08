<?php
session_start();
$username = $_SESSION["usernamemc"] ?? "user";
session_destroy();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Logging out...</title>
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
    <h1 class="main">Logging out...</h1>
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

        // Show logout notification
        const username = localStorage.getItem("minecraft-username") || "<?php echo $username; ?>";
        showNotification("Successfully logged out " + username, "info");

        // Remove username from localStorage
        localStorage.removeItem("minecraft-username");
        localStorage.removeItem("minecraft-edition");

        // Redirect to index page after a short delay
        setTimeout(function() {
            window.location.href = "/";
        }, 1500);
    </script>
    <p>Logging out... If you are not redirected, <a href="/">click here</a>.</p>
</body>
</html>

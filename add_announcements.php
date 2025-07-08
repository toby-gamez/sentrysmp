<!DOCTYPE html>
<html lang="en">
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

    <meta charset="UTF-8" />
    <title>Add Announcement - Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <link rel="stylesheet" href="css/style.css" />
    <link rel="icon" href="images/favicon.png" />
    <style>
    body.dark .header-background {
        background-image: url("images/background-image-dark.png");
    }

    body:not(.dark) .header-background {
        background-image: url("images/background-image.png");
    }
        /* Modal styling */
        .modal {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4); /* Semi-transparent background */
            overflow: auto;
        }
        #modal-message {
            color: white;
        }

        /* Modal content */
        .modal-content {
            background-color: #242424;
            margin: 15% auto;
            padding: 20px;
            width: 80%;
            max-width: 400px;
            border-radius: 10px;
            transition: all ease 0.3s;
        }

        /* Close button for modal */
        .close-btn {
            transition: all ease 0.3s;
            color: red;
            float: right;
            font-size: 28px;
            text-align: right;
            font-weight: bold;
            vertical-align: middle;
        }

        .close-btn:hover,
        .close-btn:focus {
            color: #b20000;
            text-decoration: none;
            cursor: pointer;
        }

        .deleteBtn {
            background-color: #e00000;
        }

        /* Notification styles already in style.css, just including JavaScript functions */
    </style>
</head>
<body>
    <!-- Modal for successful actions -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <p id="modal-message"></p>
        </div>
    </div>

    <nav class="navbar" id="navbar-main"></nav>
    <div class="container">
        <div class="main-wrapper">
            <h1 class="main">Add Announcement</h1>
        </div>
        <form id="announcementForm">
            <input type="text" id="title" name="title" placeholder="Title" required /><br />
            <input type="text" id="author" name="author" placeholder="Author" required /><br />
            <textarea oninput="autoResize(this)" rows="1" id="content" name="content" placeholder="Content in Markdown format" required></textarea><br />
            <button type="submit">Add Announcement</button>
        </form>
        <div class="main-wrapper">
        <h1 class="main">Announcement List</h1>
        </div>
        <div id="blogList"></div>
    </div>
    <footer id="footer-main"></footer>

    <script src="js/script.js"></script>
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

            // Function for auto-resizing textarea while typing
            function autoResize(textarea) {
                textarea.style.height = "auto";
                textarea.style.height = textarea.scrollHeight + "px";
            }

            // Function to show modal window
            function showModal(message) {
                const modal = document.getElementById("modal");
                const messageElem = document.getElementById("modal-message");

                messageElem.textContent = message; // Set message in modal window
                modal.style.display = "block"; // Show modal

                // Also show as notification
                showNotification(message, "info");

                // Close modal when clicking the close button
                const closeBtn = document.querySelector(".close-btn");
                closeBtn.addEventListener("click", () => {
                    modal.style.display = "none";
                });

                // Close modal when clicking outside the modal content
                window.onclick = (event) => {
                    if (event.target === modal) {
                        modal.style.display = "none";
                    }
                };
            }

        // Function to load all announcements
        async function loadBlogs() {
            try {
                const res = await fetch("get_announcements.php");
                const blogs = await res.json();

                // If data loads, render it on the page
                const list = document.getElementById("blogList");
                list.innerHTML = ""; // First, clear the list

                if (blogs.length === 0) {
                    list.innerHTML = "<p>No announcements to display.</p>";
                    return;
                }

                blogs.forEach((blog) => {
                    const item = document.createElement("div");
                    const date = new Date(blog.created_at);
                    // Add 2 hours for UTC+2 timezone
                    date.setHours(date.getHours() + 2);

                    item.innerHTML = `
                    <div class="announcement">
                        <div class="info">
                            <h3>${blog.title}</h3>
                            <small>Author: ${blog.author} | ${date.toLocaleString()}</small>
                        </div>
                        <div>${marked.parse(blog.content)}</div>
                        <br>
                        <div class="info">
                            <button class="deleteBtn" data-id="${blog.id}"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                    `;
                    list.appendChild(item);
                });

                // Add event listeners to delete buttons
                document.querySelectorAll(".deleteBtn").forEach((button) => {
                    button.addEventListener("click", async (e) => {
                        const id = e.currentTarget.getAttribute("data-id");
                        if (confirm("Are you sure you want to delete this announcement?")) {
                            showNotification("Deleting announcement...", "info");
                            await deleteBlog(id);
                                }
                            });
                        });
                    } catch (error) {
                        console.error("Error loading announcements:", error);
                        showModal("Failed to load announcements. Please check your server connection.");
                        showNotification("Failed to load announcements", "error");
                    }
                }

        // Function to delete an announcement
        async function deleteBlog(id) {
            try {
                const res = await fetch("delete_announcement.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ id })
                });

                const resultText = await res.text();
                console.log("Server response:", resultText);

                let result;
                try {
                    result = JSON.parse(resultText);
                } catch (err) {
                    showModal("Error parsing response: " + resultText);
                    return;
                }

                if (res.ok) {
                    showModal(result.message);
                    showNotification("Announcement deleted successfully", "success");
                    loadBlogs(); // Reload announcements after deletion
                } else {
                    showModal("Error deleting: " + (result.message || "Unknown error"));
                    showNotification("Error deleting announcement", "error");
                }
            } catch (error) {
                console.error("Error deleting announcement:", error);
                showModal("Failed to delete announcement. Please check your server connection.");
            }
        }

        // Event listener for the add announcement form
        document.getElementById("announcementForm").addEventListener("submit", async function(e) {
            e.preventDefault();

            const title = document.getElementById("title").value.trim();
            const author = document.getElementById("author").value.trim();
            const content = document.getElementById("content").value.trim();

            const data = { title, author, content };
            console.log("Sending JSON:", JSON.stringify(data));

            showNotification("Adding new announcement...", "info");
            try {
                const res = await fetch("add_announcement.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(data)
                });

                const text = await res.text();
                console.log("RAW response:", text);

                let result;
                try {
                    result = JSON.parse(text);
                } catch (err) {
                    showModal("Error parsing server response");
                    return;
                }

                if (res.ok) {
                    showModal(result.message);
                    showNotification("Announcement added successfully", "success");
                    document.getElementById("announcementForm").reset();
                    loadBlogs(); // Reload announcements after adding
                } else {
                    showModal("Error adding: " + (result.message || "Unknown error"));
                    showNotification("Error adding announcement", "error");
                }
            } catch (err) {
                console.error("Network or server error:", err);
                showModal("Network or server error. Please try again later.");
                showNotification("Network or server error", "error");
            }
        });

        // Load announcements on page load
        document.addEventListener("DOMContentLoaded", loadBlogs);
    </script>
</body>
</html>

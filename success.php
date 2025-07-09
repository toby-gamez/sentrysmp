<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Logování pro debugging
error_log("success.php spuštěn, GET: " . json_encode($_GET));

// Set default values
$transaction_id = "";
$username = "";
$payment_status = "";
$payment_amount = "";
$cart_json = "";

// Check for transaction ID in URL
if (isset($_GET["transaction_id"])) {
    $transaction_id = htmlspecialchars($_GET["transaction_id"]);
    $payment_status = "Success";
    // Try to get amount from PayPal transaction
    $payment_amount = isset($_GET["amount"])
        ? htmlspecialchars($_GET["amount"])
        : "";
    // Try to get cart from URL
    $cart_json = isset($_GET["cart"]) ? $_GET["cart"] : "";
    error_log(
        "PayPal transakce: ID=$transaction_id, částka=$payment_amount, košík=" .
            (empty($cart_json) ? "prázdný" : $cart_json)
    );
} elseif (isset($_GET["session_id"])) {
    // For Stripe transactions
    $session_id = htmlspecialchars($_GET["session_id"]);
    $transaction_id = $session_id;
    $payment_status = "Success";
    // Try to get amount from URL or session
    $payment_amount = isset($_GET["amount"])
        ? htmlspecialchars($_GET["amount"])
        : "";
    // Try to get cart from URL
    $cart_json = isset($_GET["cart"]) ? $_GET["cart"] : "";
    error_log(
        "Stripe transakce: ID=$transaction_id, částka=$payment_amount, košík=" .
            (empty($cart_json) ? "prázdný" : $cart_json)
    );
}

// Get username from session if available
if (isset($_SESSION["usernamemc"])) {
    $username = $_SESSION["usernamemc"];
} else {
    // Try to get it from localStorage via JavaScript later
    $username = "";
}

// Save the cart to session for later use in execute_db_command.php
$_SESSION["cart"] = $cart_json;
error_log(
    "Uložen košík do SESSION: " . (empty($cart_json) ? "prázdný" : $cart_json)
);

// Handle POST requests for processing username
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header("Content-Type: application/json");

    // Get username from POST data
    $data = json_decode(file_get_contents("php://input"), true);
    $username = $data["username"] ?? "";

    // Validate username
    if (!preg_match('/^[a-zA-Z0-9_]{3,16}$/', $username)) {
        echo json_encode(["success" => false, "error" => "Invalid username"]);
        exit();
    }

    // Save to database
    try {
        $db = new SQLite3("paid_users.sqlite");
        $db->exec(
            "CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL,
                transaction_id TEXT UNIQUE,
                cart_data TEXT,
                amount REAL DEFAULT 0.0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )"
        );

        // Generate a fallback transaction ID if not provided
        $fallback_tx_id = "manual_" . time() . "_" . rand(100, 999);

        $stmt = $db->prepare(
            "INSERT OR IGNORE INTO users (username, transaction_id, amount) VALUES (:username, :transaction_id, :amount)"
        );
        $stmt->bindValue(":username", $username, SQLITE3_TEXT);
        $stmt->bindValue(":transaction_id", $fallback_tx_id, SQLITE3_TEXT);
        $stmt->bindValue(":amount", 0.0, SQLITE3_FLOAT);
        $result = $stmt->execute();
        echo json_encode([
            "success" => true,
            "transaction_id" => $fallback_tx_id,
        ]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
    exit();
}

// Get commands from database
function getCommandsFromDatabase()
{
    try {
        $commands = [];

        // Get commands from spawners table
        $db = new SQLite3("blog.sqlite");
        $stmt = $db->prepare("SELECT id, nazev, popis, prikaz FROM spawners");
        $result = $stmt->execute();

        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $commands[] = [
                "id" => $row["id"],
                "name" => $row["nazev"],
                "description" => $row["popis"],
                "command" => $row["prikaz"],
                "type" => "spawner",
            ];
        }

        // Get commands from keys table
        $dbKeys = new SQLite3("keys.sqlite");
        $stmtKeys = $dbKeys->prepare("SELECT id, name, prikaz FROM Keys");
        $resultKeys = $stmtKeys->execute();

        while ($row = $resultKeys->fetchArray(SQLITE3_ASSOC)) {
            $commands[] = [
                "id" => "key_" . $row["id"],
                "name" => $row["name"],
                "description" => "",
                "command" => $row["prikaz"],
                "type" => "key",
            ];
        }

        // Get commands from ranks table
        $dbRanks = new SQLite3("ranks.sqlite");
        $stmtRanks = $dbRanks->prepare(
            "SELECT id, nazev, popis, prikaz FROM ranks"
        );
        $resultRanks = $stmtRanks->execute();

        while ($row = $resultRanks->fetchArray(SQLITE3_ASSOC)) {
            $commands[] = [
                "id" => "rank_" . $row["id"],
                "name" => $row["nazev"],
                "description" => $row["popis"],
                "command" => $row["prikaz"],
                "type" => "rank",
            ];
        }

        return ["success" => true, "commands" => $commands];
    } catch (Exception $e) {
        return ["success" => false, "message" => $e->getMessage()];
    }
}

// Execute system command
function executeSystemCommand($username, $command)
{
    // Implementation for system command execution
    try {
        // Sanitize inputs
        $username = preg_replace("/[^a-zA-Z0-9_]/", "", $username);

        // Log the command execution
        $log = fopen("command_log.txt", "a");
        fwrite(
            $log,
            date("Y-m-d H:i:s") .
                " - User: " .
                $username .
                " - Command: " .
                $command .
                "\n"
        );
        fclose($log);

        return [
            "status" => "success",
            "message" => "System command executed for user: " . $username,
        ];
    } catch (Exception $e) {
        return ["status" => "error", "message" => $e->getMessage()];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <!-- Google Consent Mode -->
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() {
            dataLayer.push(arguments);
        }

        // Default state: consent denied unless choice is saved
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
    <!-- Google Analytics -->
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

        // Show cookie banner when page loads
        window.addEventListener("DOMContentLoaded", showCookieBanner);
    </script>
    <!-- SEO meta tags for Google -->
    <meta
        name="description"
        content="SentrySMP is a Minecraft server focused on providing a safe and enjoyable experience for players of all ages. It is SMP with addons."
    />
    <meta
        name="keywords"
        content="Minecraft, SMP, English, Czech, server, safe, enjoyable, experience, players"
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

    <!-- Twitter Cards -->
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

    <title>Payment Successful - Sentry SMP VIP</title>
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
    <div class="container">
        <div class="main-wrapper">
            <h2 class="main">Your Payment Has Been Successful</h2>
        </div>
        <img id="success-checkmark" src="images/fajfla.png" width="200px" style="margin: auto; display: flex; opacity: 0; transition: opacity 0.7s;" alt="success">
        <p id="username-output"></p>
        <div id="rcon-error" style="display: none; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; margin: 15px 0; border-radius: 5px;"></div>
        <div id="rcon-success" style="display: none; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; margin: 15px 0; border-radius: 5px;"></div>

        <h2>Important Instructions ⚠️</h2>
        <p>To activate your purchase, you must be online on the server. Operations are automatically performed when this page loads. If you cannot return and log in to the server, please <strong>take a screenshot</strong> of this page as proof and create a <a href="support.php">ticket</a>. Don't worry 😊!</p>
        <hr>
        <button onclick="window.location.href = 'index'">Home</button>
        <div id="status-message"></div>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                    // Animation for checkmark with slight delay for better visual effect
                    setTimeout(function() {
                        document.getElementById("success-checkmark").style.opacity = 1;
                    }, 300);

                    // Auto-execute operations after loading
                    executeOperations();

                // Get username and display it
                const username = getUserName();
                if (username) {
                    document.getElementById("username-output").textContent = "Your name: " + username;
                } else {
                    document.getElementById("username-output").textContent = "⚠️ Username not found! Contact support immediately!";
                    document.getElementById("username-output").style.backgroundColor = "#fff3cd";
                    document.getElementById("username-output").style.borderLeft = "4px solid #ffc107";
                    document.getElementById("rcon-error").innerHTML = '<strong>Warning:</strong> No valid username found. Please take a screenshot and contact support.';
                    document.getElementById("rcon-error").style.display = 'block';
                }

                // Remove item from cart if it exists
                if (typeof removeFromCart === 'function') {
                    removeFromCart(1);
                }
            });

            // Function to get and validate username
            function getUserName() {
                // Get username from localStorage
                var username = localStorage.getItem("minecraft-username") || "<?php echo $username; ?>";

                // Client-side username validation
                if (username && /^[a-zA-Z0-9_]{2,16}$/.test(username)) {
                    console.log("Username obtained: " + username);

                    // Send to server
                    fetch(window.location.pathname, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({ username: username })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            console.log("Username saved successfully");
                        } else {
                            console.log("Error saving username:", data.error);
                            document.getElementById("rcon-error").innerHTML = '<strong>Error saving username:</strong> ' + data.error;
                            document.getElementById("rcon-error").style.display = 'block';
                        }
                    });

                    return username;
                } else {
                    console.error("Username is not valid or doesn't exist");
                    document.getElementById("rcon-error").innerHTML = '<strong>Error:</strong> Username is not valid or missing';
                    document.getElementById("rcon-error").style.display = 'block';
                    return null;
                }
            }

            function executeOperations() {
                const username = getUserName();

                if (!username) {
                    showStatusMessage("No valid username found. Operations could not be performed automatically. Please take a screenshot for support.", "error");
                    return;
                }

                let cart;
                const urlParams = new URLSearchParams(window.location.search);
                const cartParam = urlParams.get('cart');

                console.log("URL parameters:", window.location.search);
                console.log("Cart parameter from URL:", cartParam);

                // 1. Zkusit načíst z URL
                if (cartParam) {
                    try {
                        cart = JSON.parse(decodeURIComponent(cartParam));
                        console.log("Cart from URL (parsed):", cart);
                        // Save to localStorage for consistency
                        localStorage.setItem('cart', JSON.stringify(cart));
                    } catch (e) {
                        console.error("Error parsing cart from URL:", e);
                        cart = null;
                    }
                }

                // 2. Pokud není v URL nebo je nevalidní, zkusit z localStorage
                if (!cart || !Array.isArray(cart) || cart.length === 0) {
                    try {
                        const localCart = localStorage.getItem('cart');
                        if (localCart) {
                            cart = JSON.parse(localCart);
                            console.log("Cart from localStorage (parsed):", cart);
                        }
                    } catch (e) {
                        console.error("Error parsing cart from localStorage:", e);
                        cart = [];
                    }
                }

                // 3. Pokud stále není validní nebo je prázdný, zobraz jasnou chybu a nabídni návrat na checkout
                if (!cart || !Array.isArray(cart) || cart.length === 0) {
                    showStatusMessage("Košík nebyl nalezen – aktivace nemůže proběhnout. <br>Zkuste se vrátit na <a href='checkout.php'>checkout</a> a zopakovat platbu, nebo kontaktujte podporu.", "error");
                    const rconError = document.getElementById('rcon-error');
                    if (rconError) {
                        rconError.innerHTML = '<strong>Chyba aktivace:</strong> <br>Košík nebyl nalezen – aktivace nemůže proběhnout.<br><a href="checkout.php" style="margin-top: 5px; display:inline-block;">Zpět na checkout</a> nebo <a href="support.php">Kontaktujte podporu</a>';
                        rconError.style.display = 'block';
                        rconError.scrollIntoView({behavior: 'smooth'});
                    }
                    return;
                }

                // 4. Logování pro debug
                console.log("Final cart being sent to execute_db_command.php:", cart);

                // Direct PHP call to execute all operations
                fetch("execute_db_command", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({
                        username: username,
                        cart: cart
                    })
                })
                .then(response => response.text())
                .then(text => {
                    console.log("Raw backend response:", text);
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        showStatusMessage("Backend nevrátil validní JSON: " + text, "error");
                        const rconError = document.getElementById('rcon-error');
                        if (rconError) {
                            rconError.innerHTML = '<strong>Chyba komunikace s backendem:</strong><br><pre style="margin: 5px 0; padding: 5px; background: #f5f5f5; border: 1px solid #ddd;">' +
                                text + '</pre>';
                            rconError.style.display = 'block';
                            rconError.scrollIntoView({behavior: 'smooth'});
                        }
                        return;
                    }
                    if (data.success) {
                        console.log("All operations executed successfully");
                        showStatusMessage("All operations completed successfully", "success");
                        const rconSuccess = document.getElementById('rcon-success');
                        if (rconSuccess) {
                            rconSuccess.innerHTML = '<strong>Operations completed successfully!</strong> <span style="display:inline-block;margin-left:10px">✅</span>';
                            rconSuccess.style.display = 'block';
                            rconSuccess.scrollIntoView({behavior: 'smooth'});
                        }
                        // Vyprázdnění košíku po úspěšném dokončení
                        emptyCart();
                    } else {
                        console.error(`Operations failed: ${data.message}`);

                        // Check if this is the "already executed" message
                        if (data.alreadyExecuted || (data.message && data.message.includes("already been executed"))) {
                            const rconSuccess = document.getElementById('rcon-success');
                            if (rconSuccess) {
                                rconSuccess.innerHTML = '<strong>Operations already processed!</strong> <span style="display:inline-block;margin-left:10px">✅</span>';
                                rconSuccess.style.display = 'block';
                                rconSuccess.scrollIntoView({behavior: 'smooth'});
                            }
                        } else {
                            showStatusMessage("Failed to perform operations: " + data.message, "error");
                            const rconError = document.getElementById('rcon-error');
                            if (rconError) {
                                rconError.innerHTML = '<strong>Error with activation:</strong> ' +
                                    '<pre style="margin: 5px 0; padding: 5px; background: #f5f5f5; border: 1px solid #ddd;">' +
                                    (data.message ? data.message.replace(/commands/g, "operations").replace(/Commands/g, "Operations").replace(/command/g, "operation").replace(/Command/g, "Operation") : "Neznámá chyba") + '</pre>' +
                                    '<br><button onclick="window.location.reload()" style="margin-top: 5px;">Try Again</button> or <a href="support.php">Contact Support</a>';
                                rconError.style.display = 'block';
                                rconError.scrollIntoView({behavior: 'smooth'});
                            }
                        }
                    }
                })
                .catch(error => {
                    console.error("Error performing operations:", error);
                    showStatusMessage("Error connecting to server. Please try again later.", "error");
                    const rconError = document.getElementById('rcon-error');
                    if (rconError) {
                        rconError.innerHTML = '<strong>Server connection issue:</strong> ' +
                            '<pre style="margin: 5px 0; padding: 5px; background: #f5f5f5; border: 1px solid #ddd;">' +
                            error.message + '</pre>' +
                            '<p>Don\'t worry, your purchase was recorded and operations can be performed later.</p>';
                        rconError.style.display = 'block';
                        rconError.scrollIntoView({behavior: 'smooth'});
                    }
                });
            }

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

            function showStatusMessage(message, type) {
                // Show both traditional status and notification
                const statusDiv = document.getElementById("status-message");
                // Convert newlines to <br> tags for proper display
                const formattedMessage = message.replace(/\n/g, '<br>');
                statusDiv.innerHTML = `<div class="status ${type}">${formattedMessage}</div>`;
                statusDiv.style.display = "block";

                // Also show as notification
                showNotification(message, type === "error" ? "error" : "success");
            }

            // Cart functionality
            let cart = JSON.parse(localStorage.getItem('cart') || '[]');
            function saveCart() {
                localStorage.setItem('cart', JSON.stringify(cart));
            }

            function emptyCart() {
                            cart = [];
                            // Explicitní vymazání košíku (jméno zůstává uložené)
                            localStorage.removeItem('cart');
                            console.log("Cart cleared after successful purchase (username preserved)");
                            // Informovat uživatele, že košík byl vyprázdněn
                            showNotification("Cart has been cleared after successful purchase", "success");
                        }
        </script>
    </div>
</body>
</html>

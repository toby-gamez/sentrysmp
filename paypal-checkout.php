<?php
// Load environment variables
require_once __DIR__ . "/vendor/autoload.php";
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$paypalClientId = $_ENV["PAYPAL_CLIENT_ID"] ?? "";
if (empty($paypalClientId)) {
    die("PayPal Client ID not configured in environment variables");
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

        <title>PayPal Checkout - Sentry SMP</title>
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
        <header id="header-main"></header>
        <div class="container">
            <div class="main-wrapper">
                <h2 class="main">PayPal Gateway</h2>
            </div>
            <div class="info">
                <p>
                    To receive your purchase, you must be connected to the
                    Survival server.
                    <b
                        >Being in the lobby or another our server is not
                        enough!</b
                    >
                </p>
            </div>
            <div id="username-output"></div>

            <div id="order-summary">
                <h3>Order Summary</h3>
                <div id="order-items"></div>
                <div class="order-total">
                    <span>Total:</span>
                    <span id="total-price">Calculating...</span>
                </div>
            </div>

            <p>
                You can choose to pay either with your PayPal account or with a
                debit card via PayPal.
            </p>

            <!-- PayPal SDK -->
            <script src="https://www.paypal.com/sdk/js?client-id=<?php echo htmlspecialchars(
                $paypalClientId,
            ); ?>&currency=EUR"></script>

            <p style="color: #f9f9f9">
                *I can't change the colors of the PayPal gateway, it's
                controlled by them. Sorry! :)
            </p>

            <!-- PayPal button container -->
            <div
                id="paypal-button-container"
                style="position: relative; z-index: 50"
            ></div>

            <button
                onclick="window.location.href='checkout.php'"
                style="margin-top: 20px"
            >
                Back to Checkout
            </button>
            <script>
                // Set default payment value
                var totalPriceValue = "5.00"; // Default price in EUR with 2 decimal places

                // Function to get username from localStorage
                function getUserName() {
                    // Try to get username from URL parameters first
                    const urlParams = new URLSearchParams(
                        window.location.search,
                    );
                    const urlUsername = urlParams.get("username");

                    if (
                        urlUsername &&
                        /^[a-zA-Z0-9_]{3,16}$/.test(urlUsername)
                    ) {
                        // Save to localStorage for consistency
                        localStorage.setItem("minecraft-username", urlUsername);
                        showNotification(
                            "Username loaded from URL: " + urlUsername,
                            "info",
                            3000,
                        );
                        return urlUsername;
                    }

                    // Fall back to localStorage
                    const username = localStorage.getItem("minecraft-username");
                    if (username && /^[a-zA-Z0-9_]{3,16}$/.test(username)) {
                        return username;
                    }

                    showNotification(
                        "No valid Minecraft username found. Please set your username before proceeding.",
                        "error",
                        8000,
                    );
                    return null;
                }

                // Function to get cart from URL or localStorage
                function getCartFromUrlOrStorage() {
                    // Try to get cart from URL parameters first
                    const urlParams = new URLSearchParams(
                        window.location.search,
                    );
                    const urlCart = urlParams.get("cart");

                    if (urlCart) {
                        try {
                            const parsedCart = JSON.parse(
                                decodeURIComponent(urlCart),
                            );
                            // Save to localStorage for consistency
                            localStorage.setItem(
                                "cart",
                                JSON.stringify(parsedCart),
                            );
                            console.log("Cart loaded from URL:", parsedCart);
                            return parsedCart;
                        } catch (e) {
                            console.error("Error parsing cart from URL:", e);
                        }
                    }

                    // Fall back to localStorage
                    const localCart = JSON.parse(
                        localStorage.getItem("cart") || "[]",
                    );
                    console.log("Cart loaded from localStorage:", localCart);
                    return localCart;
                }

                // Function to calculate cart total and show items
                function loadCartItems() {
                    try {
                        const cart = getCartFromUrlOrStorage();
                        if (cart.length === 0) {
                            document.getElementById("order-items").innerHTML =
                                "<p>Your cart is empty</p>";
                            document.getElementById(
                                "paypal-button-container",
                            ).innerHTML =
                                '<p style="color: red;">Your cart is empty. Please add items before checkout.</p>';
                            return 0;
                        }

                        // Function to load and process cart items from a specific URL
                        function processItemsFromUrl(url, isKey = false) {
                            return fetch(url)
                                .then((response) => response.text())
                                .then((html) => {
                                    const parser = new DOMParser();
                                    const doc = parser.parseFromString(
                                        html,
                                        "text/html",
                                    );
                                    const items =
                                        doc.querySelectorAll(".spawner");
                                    let localTotal = 0;
                                    console.log(
                                        `Processing items from ${url}, isKey: ${isKey}`,
                                    );

                                    // Process each item in the cart
                                    items.forEach((item) => {
                                        const id = item.getAttribute("data-id");
                                        const isKeyItem =
                                            isKey ||
                                            id.toString().startsWith("key_");
                                        const isRankItem = id
                                            .toString()
                                            .startsWith("rank_");
                                        const inCart = cart.some((cartItem) => {
                                            // Check if cart item is an object with id property (new format)
                                            if (
                                                cartItem &&
                                                typeof cartItem === "object" &&
                                                cartItem.id
                                            ) {
                                                const idMatch =
                                                    String(cartItem.id) ===
                                                    String(id);
                                                console.log(
                                                    `  Comparing cart item ID: ${cartItem.id} (${typeof cartItem.id}) with item: ${id} (${typeof id}), match: ${idMatch}`,
                                                );
                                                return idMatch;
                                            } else {
                                                // Legacy format - direct ID comparison
                                                const stringMatch =
                                                    String(cartItem) ===
                                                    String(id);
                                                console.log(
                                                    `  Comparing cart item: ${cartItem} (${typeof cartItem}) with item: ${id} (${typeof id}), match: ${stringMatch}`,
                                                );
                                                return stringMatch;
                                            }
                                        });
                                        console.log(
                                            `Checking item ID: ${id}, isKeyItem: ${isKeyItem}, isRankItem: ${isRankItem}, in cart: ${inCart}`,
                                        );

                                        if (inCart) {
                                            try {
                                                // We should use the price from cart object if available
                                                let price = 0;
                                                const name =
                                                    item.querySelector(
                                                        "h2",
                                                    ).textContent;

                                                // Prioritize getting price from HTML similar to checkout.php
                                                const priceContainer =
                                                    item.querySelector(
                                                        ".price-container",
                                                    );

                                                if (priceContainer) {
                                                    // Try to get discounted price first (ALWAYS use this if available)
                                                    const discountedElement =
                                                        priceContainer.querySelector(
                                                            ".discounted-price",
                                                        );
                                                    if (discountedElement) {
                                                        const discountedText =
                                                            discountedElement.textContent;
                                                        const priceMatch =
                                                            discountedText.match(
                                                                /(\d+(?:\.\d+)?)€/,
                                                            );
                                                        if (priceMatch) {
                                                            price = parseFloat(
                                                                priceMatch[1],
                                                            );
                                                            console.log(
                                                                `Using discounted price for ${name}: ${price}€`,
                                                            );
                                                        }
                                                    } else {
                                                        // If no discount, get original price
                                                        const originalPriceElement =
                                                            priceContainer.querySelector(
                                                                ".original-price",
                                                            );
                                                        if (
                                                            originalPriceElement
                                                        ) {
                                                            const priceMatch =
                                                                originalPriceElement.textContent.match(
                                                                    /(\d+(?:\.\d+)?)€/,
                                                                );
                                                            if (priceMatch) {
                                                                price =
                                                                    parseFloat(
                                                                        priceMatch[1],
                                                                    );
                                                                console.log(
                                                                    `Using original price for ${name}: ${price}€`,
                                                                );
                                                            }
                                                        }
                                                    }
                                                }

                                                // If price still not found, check cart item (as secondary source)
                                                const cartItem = cart.find(
                                                    (item) =>
                                                        item &&
                                                        typeof item ===
                                                            "object" &&
                                                        item.id
                                                            ? String(
                                                                  item.id,
                                                              ) === String(id)
                                                            : String(item) ===
                                                              String(id),
                                                );
                                                if (
                                                    price === 0 &&
                                                    cartItem &&
                                                    cartItem.price &&
                                                    cartItem.price > 0
                                                ) {
                                                    price = parseFloat(
                                                        cartItem.price,
                                                    );
                                                    console.log(
                                                        `Using price from cart for item ${id}: ${price}€`,
                                                    );
                                                }

                                                // If price still not found, check description
                                                if (price === 0) {
                                                    const description =
                                                        item.querySelector(
                                                            "p",
                                                        )?.textContent;
                                                    if (description) {
                                                        console.log(
                                                            "Item description:",
                                                            description,
                                                        );
                                                        // Look for price pattern: digits followed by € symbol, handle different formats
                                                        const priceMatch =
                                                            description.match(
                                                                /(\d+(?:\.\d+)?)[ ]?€/,
                                                            ) ||
                                                            description.match(
                                                                /(\d+)[.,]?(\d*)[ ]?€/,
                                                            );

                                                        if (priceMatch) {
                                                            if (priceMatch[2]) {
                                                                // Handle decimal point
                                                                price =
                                                                    parseFloat(
                                                                        priceMatch[1] +
                                                                            "." +
                                                                            priceMatch[2],
                                                                    );
                                                            } else {
                                                                price =
                                                                    parseFloat(
                                                                        priceMatch[1],
                                                                    );
                                                            }
                                                            console.log(
                                                                `Found price in description for ${name}: ${price}€`,
                                                            );
                                                        }
                                                    }
                                                }

                                                // Default price if still not found
                                                if (price === 0) {
                                                    price = 3;
                                                    console.log(
                                                        `Using default price for ${name}: ${price}€`,
                                                    );
                                                }

                                                // Get quantity from cart item
                                                let quantity = 1;

                                                if (
                                                    cartItem &&
                                                    typeof cartItem ===
                                                        "object" &&
                                                    cartItem.quantity
                                                ) {
                                                    quantity =
                                                        cartItem.quantity;
                                                    // Use price from cart if available
                                                    if (cartItem.price) {
                                                        price = parseFloat(
                                                            cartItem.price,
                                                        );
                                                    }
                                                }

                                                // Calculate item total price (price * quantity)
                                                const itemTotalPrice =
                                                    price * quantity;

                                                // Create order item
                                                const orderItem =
                                                    document.createElement(
                                                        "div",
                                                    );
                                                orderItem.className =
                                                    "order-detail";
                                                orderItem.dataset.id = id;
                                                orderItem.dataset.quantity =
                                                    quantity;
                                                orderItem.dataset.price = price;
                                                orderItem.dataset.type =
                                                    isKeyItem
                                                        ? "key"
                                                        : isRankItem
                                                          ? "rank"
                                                          : "spawner";
                                                orderItem.innerHTML = `
                                                    <span class="item-name">${name}${isKeyItem ? " (Key)" : isRankItem ? " (Rank)" : ""} ${quantity > 1 ? `(${quantity}×)` : ""}</span>
                                                    <span class="item-price">€${Number(itemTotalPrice).toLocaleString("en", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                                                `;
                                                orderItems.appendChild(
                                                    orderItem,
                                                );

                                                // Update local total with the correct item total price
                                                localTotal +=
                                                    itemTotalPrice - price; // Adjust the total (price was already added once)

                                                console.log(
                                                    `Added item to order: ${name}, Price: ${price}€, ID: ${id}, Type: ${isKeyItem ? "key" : isRankItem ? "rank" : "spawner"}`,
                                                );

                                                // Warn about zero-price items
                                                if (price === 0) {
                                                    console.warn(
                                                        `WARNING: Item ${name} (ID: ${id}) has zero price - this item will not contribute to the total`,
                                                    );
                                                }
                                            } catch (err) {
                                                console.error(
                                                    `Error processing ${isKeyItem ? "key" : "spawner"}:`,
                                                    err,
                                                );
                                            }
                                        }
                                    });

                                    return localTotal;
                                });
                        }

                        let totalPrice = 0;
                        const orderItems =
                            document.getElementById("order-items");
                        orderItems.innerHTML = "";

                        // First process spawners
                        processItemsFromUrl("shards.php")
                            .then((spawnerTotal) => {
                                totalPrice += spawnerTotal;

                                // Then process keys
                                return processItemsFromUrl("keys.php", true);
                            })
                            .then((keysTotal) => {
                                totalPrice += keysTotal;

                                // Then process ranks
                                return processItemsFromUrl("ranks.php", false);
                            })
                            .then((ranksTotal) => {
                                totalPrice += ranksTotal;

                                // Check if we have any items with valid prices (> 0)
                                let hasValidItems = false;
                                const validTotal = Array.from(
                                    orderItems.children,
                                ).reduce((sum, item) => {
                                    const price = parseFloat(
                                        item.dataset.price || 0,
                                    );
                                    const quantity = parseInt(
                                        item.dataset.quantity || 1,
                                    );
                                    const itemTotal = price * quantity;
                                    if (itemTotal > 0) {
                                        hasValidItems = true;
                                        return sum + itemTotal;
                                    }
                                    return sum;
                                }, 0);

                                // If no items were added or no valid items with price > 0, show error message
                                if (
                                    orderItems.children.length === 0 ||
                                    !hasValidItems
                                ) {
                                    console.error(
                                        "No valid items in cart with valid prices",
                                    );
                                    document.getElementById(
                                        "paypal-button-container",
                                    ).innerHTML =
                                        '<p style="color: red;">Your cart appears to be empty or the items have invalid prices. Please add items before checkout.</p>';

                                    // Show error message in order items
                                    const errorMessage =
                                        document.createElement("div");
                                    errorMessage.className = "order-error";
                                    errorMessage.innerHTML = `
                                        <p class="error-text">Your cart appears to be empty or the items have invalid prices.</p>
                                        <p>Please <a href="shards.php">add shards</a>, <a href="keys.php">add keys</a>, or <a href="ranks.php">add ranks</a> to your cart before checkout.</p>
                                        <p><small>Debug info: Cart contains ${cart.length} items. Cart JSON: ${JSON.stringify(cart)}</small></p>
                                    `;
                                    orderItems.appendChild(errorMessage);
                                    return; // Exit function
                                }

                                // Use validTotal instead of totalPrice if it's different (handles items with 0 price)
                                if (
                                    validTotal !== totalPrice &&
                                    validTotal > 0
                                ) {
                                    totalPrice = validTotal;
                                    console.log(
                                        "Adjusted total price to exclude zero-price items:",
                                        totalPrice,
                                    );
                                }

                                // Log the final total price for debugging
                                console.log(
                                    "CART TOTAL PRICE BEFORE CHECKOUT:",
                                    totalPrice,
                                    "€",
                                );

                                // Alternatively, calculate total from cart if available
                                let manualTotal = 0;
                                let useManualTotal = true;
                                try {
                                    // Try to calculate total from cart with quantities and prices
                                    cart.forEach((item) => {
                                        if (
                                            item &&
                                            typeof item === "object" &&
                                            item.price &&
                                            item.quantity
                                        ) {
                                            manualTotal +=
                                                parseFloat(item.price) *
                                                parseInt(item.quantity);
                                        } else {
                                            useManualTotal = false;
                                        }
                                    });
                                } catch (e) {
                                    console.error(
                                        "Error calculating manual total:",
                                        e,
                                    );
                                    showNotification(
                                        "Error calculating total price, using provided price.",
                                        "warning",
                                    );
                                    useManualTotal = false;
                                }

                                // Use manual calculation if all items have price and quantity
                                if (useManualTotal && manualTotal > 0) {
                                    console.log(
                                        "Using manual total calculation:",
                                        manualTotal,
                                    );
                                    totalPrice = manualTotal;
                                }

                                // Format total price with two decimal places
                                const formattedPrice = totalPrice.toFixed(2);
                                document.getElementById(
                                    "total-price",
                                ).textContent =
                                    `€${Number(formattedPrice).toLocaleString("en", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                                totalPriceValue = formattedPrice;

                                console.log(
                                    "CART CALCULATION - Total price calculated:",
                                    totalPrice,
                                );
                                console.log(
                                    "CART CALCULATION - Formatted price displayed:",
                                    formattedPrice + "€",
                                );

                                // Initialize PayPal with the calculated total
                                initPayPal(formattedPrice);
                            })
                            .catch((error) => {
                                console.error(
                                    "Error loading cart details:",
                                    error,
                                );

                                // Add fallback item on error
                                const orderItems =
                                    document.getElementById("order-items");
                                orderItems.innerHTML = `
                                    <div class="order-detail">
                                        <span class="item-name">Minecraft Items</span>
                                        <span class="item-price">5€</span>
                                    </div>
                                `;
                                document.getElementById(
                                    "total-price",
                                ).textContent = "€5.00";
                                totalPriceValue = "5";
                                initPayPal("5");
                                return 0;
                            });
                    } catch (e) {
                        console.error("Error processing cart:", e);
                        return 0;
                    }
                }

                // Initialize PayPal buttons
                function initPayPal(amount) {
                    console.log("Initializing PayPal with amount:", amount);
                    if (!amount || parseFloat(amount) <= 0) {
                        document.getElementById(
                            "paypal-button-container",
                        ).innerHTML =
                            '<p style="color: red;">Invalid order amount. Please try again.</p>';
                        return;
                    }

                    // Clear previous buttons if any
                    document.getElementById(
                        "paypal-button-container",
                    ).innerHTML = "";

                    // Get cart for item details
                    const cart = getCartFromUrlOrStorage();
                    console.log("Cart for PayPal initialization:", cart);

                    // Create item descriptions for PayPal
                    const itemDescriptions = [];
                    if (cart && Array.isArray(cart)) {
                        cart.forEach((item) => {
                            if (item && typeof item === "object" && item.id) {
                                const itemName = item.id;
                                const quantity = item.quantity || 1;
                                itemDescriptions.push(
                                    `${itemName} (${quantity}x)`,
                                );
                            }
                        });
                    }

                    // Create combined description or use default
                    const description =
                        itemDescriptions.length > 0
                            ? `Minecraft Items: ${itemDescriptions.join(", ")}`
                            : "Minecraft Items";

                    paypal
                        .Buttons({
                            createOrder: function (data, actions) {
                                return actions.order.create({
                                    purchase_units: [
                                        {
                                            description: description.substring(
                                                0,
                                                127,
                                            ), // PayPal limits description length
                                            amount: {
                                                value: amount,
                                            },
                                        },
                                    ],
                                });
                            },
                            onApprove: function (data, actions) {
                                return actions.order
                                    .capture()
                                    .then(function (details) {
                                        // Get cart information before clearing
                                        const cartToSave = JSON.stringify(cart);

                                        // Empty the cart
                                        localStorage.setItem("cart", "[]");

                                        // Save amount and cart details to localStorage
                                        try {
                                            localStorage.setItem(
                                                "checkout_data",
                                                JSON.stringify({
                                                    amount: parseFloat(
                                                        amount,
                                                    ).toFixed(2), // Ensure consistent 2 decimal format
                                                    timestamp:
                                                        new Date().toISOString(),
                                                    items: cart,
                                                }),
                                            );
                                        } catch (e) {
                                            console.error(
                                                "Error saving checkout data:",
                                                e,
                                            );
                                        }

                                        // Redirect to success page with properly formatted amount and cart
                                        const formattedAmount =
                                            parseFloat(amount).toFixed(2);
                                        const username = getUserName() || "";
                                        window.location.href =
                                            "https://sentrysmp.eu/success.php?session_id=" +
                                            details.id +
                                            "&cart=" +
                                            encodeURIComponent(cartToSave) +
                                            "&username=" +
                                            encodeURIComponent(username);
                                    });
                            },
                            onError: function (err) {
                                console.error("PayPal Error:", err);
                                window.location.href =
                                    "https://sentrysmp.eu/cancel.html";
                            },
                            style: {
                                label: "pay",
                            },
                        })
                        .render("#paypal-button-container");
                }

                // Run on page load
                document.addEventListener("DOMContentLoaded", function () {
                    const username = getUserName();

                    // Display username
                    const usernameOutput =
                        document.getElementById("username-output");
                    if (username) {
                        usernameOutput.textContent =
                            "Your Minecraft username: " + username;
                    } else {
                        usernameOutput.innerHTML =
                            "<span style='color:red'>No username found! Please <a href='login-players.html'>login</a> first.</span>";
                        document.getElementById(
                            "paypal-button-container",
                        ).innerHTML =
                            '<p style="color: red;">Please log in with your Minecraft username first.</p>';
                        return;
                    }

                    // Load cart and initialize PayPal
                    loadCartItems();
                });
            </script>
        </div>
        <footer id="footer-main"></footer>
        <script src="js/script.js"></script>
    </body>
</html>

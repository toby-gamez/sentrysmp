<?php
$db = new PDO("sqlite:blog.sqlite");
$spawners = $db->query("SELECT * FROM spawners")->fetchAll(PDO::FETCH_ASSOC);

// Get cart data from localStorage via JavaScript
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
        <script src="https://js.stripe.com/v3/"></script>
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

        <title>Checkout - Sentry SMP</title>
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

            /* Styles for discount display */
            .discount-badge {
                background-color: #e74c3c;
                color: white;
                padding: 3px 6px;
                border-radius: 4px;
                font-size: 0.8em;
                font-weight: bold;
                margin-left: 5px;
                transform: rotate(5deg);
                display: inline-block;
            }

            .original-price {
                color: #888;
                font-size: 0.9em;
                text-decoration: line-through;
                margin-right: 5px;
            }

            .item-price {
                position: relative;
                display: flex;
                align-items: center;
                gap: 5px;
            }
        </style>
    </head>
    <body>
        <nav class="navbar" id="navbar-main"></nav>

        <!-- udělat hovery na grid-itemy -->
        <header id="header-main"></header>
        <div class="container">
            <div class="main-wrapper">
                <h2 class="main">Checkout</h2>
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
            <div id="username-output">Loading username...</div>

            <div id="order-summary">
                <h3>Order Summary</h3>
                <div id="order-items">
                    <!-- Items will be loaded here -->
                </div>
                <div class="order-total">
                    <span>Total:</span>
                    <span id="total-price">€0.00</span>
                </div>
            </div>

            <h2><i class="bi bi-bank"></i> By Bank Transfer</h2>
            <p>
                You will be redirected to the payment gateway. After successful
                payment, your items will be delivered to your account automatically.
                <u>Available payment methods include credit/debit card (Google
                Pay, Apple Pay, etc.), Bancontact, Multibanco, MobilePay,
                Klarna, Revolut Pay, and EPS.</u>
                <b>Please check your username above before proceeding with the
                payment</b>.
            </p>
            <button id="checkout-button">Go to payment gateway</button>

            <script>
                // Add event listener to checkout button
                document.getElementById('checkout-button').addEventListener('click', function() {
                    // Store button reference for use inside callbacks
                    const button = this;

                    // Show loading state
                    button.disabled = true;
                    button.textContent = 'Processing...';

                    try {
                        const price = calculateTotalPrice();
                        const username = getUserName() || 'Anonymous';

                        // Get cart items and encode for URL
                        const cart = JSON.parse(localStorage.getItem("cart") || "[]");
                        const cartParam = encodeURIComponent(JSON.stringify(cart));

                        // Use fetch to avoid full page reload
                        fetch('create-checkout-session.php?price=' + price.toFixed(2) + '&username=' + encodeURIComponent(username) + '&cart=' + cartParam)
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Network response was not ok');
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (data.url) {
                                    // Show notification and redirect to Stripe checkout
                                    showNotification('Redirecting to payment gateway...', 'info', 2000);
                                    window.location.href = data.url;
                                } else {
                                    showNotification('No checkout URL received from the server', 'error');
                                    throw new Error('No checkout URL received');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('There was a problem connecting to the payment gateway. Please try again.');
                                button.disabled = false;
                                button.textContent = 'Go to payment gateway';
                            });
                    } catch (e) {
                        console.error("Error processing checkout: ", e);
                        alert("There was a problem processing your request. Please try again.");
                        button.disabled = false;
                        button.textContent = 'Go to payment gateway';
                    }
                });
            </script>

            <h2><i class="bi bi-paypal"></i> By PayPal</h2>
            <p>
                You will be redirected to the payment gateway. After successful
                payment, your items will be delivered to your account automatically.
                <u>You can choose to pay either with your PayPal account or
                with a debit card via PayPal.</u>
                <b>Please check your username above before proceeding with the
                payment</b>.
            </p>
            <button
                id="paypal-button"
                onclick="redirectToPaypal()"
            >
                Go to PayPal gateway
            </button>

            <script src="https://js.stripe.com/v3/"></script>
            <script>
                // Function to calculate the total price
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

                function calculateTotalPrice() {
                        // Try to get the stored cart-calculated price first
                        if (window.checkoutTotalPrice && !isNaN(parseFloat(window.checkoutTotalPrice))) {
                            const storedPrice = parseFloat(window.checkoutTotalPrice);
                            if (storedPrice > 0) {
                                console.log("Using stored price from cart calculation:", storedPrice, "€");
                                return storedPrice; // Return the exact cart price
                            }
                        }

                    // Fallback to reading from the DOM element
                    const totalPriceElement = document.getElementById("total-price");
                    if (!totalPriceElement) {
                        console.error("Total price element not found");
                        showNotification("Error: Could not find price information. Please refresh the page.", "error");
                        return 0; // Return invalid price to trigger error
                    }

                    // For consistency with cart.html, we'll manually calculate total from order details
                    const orderItems = document.querySelectorAll('.order-detail');
                    let calculatedTotal = 0;

                    orderItems.forEach(item => {
                        const price = parseFloat(item.dataset.price || 0);
                        const quantity = parseInt(item.dataset.quantity || 1);
                        calculatedTotal += price * quantity;
                    });

                    // Add VIP item price if present
                    const vipItem = document.querySelector('.order-detail[data-type="vip"]');
                    if (vipItem) {
                        calculatedTotal += 6; // VIP item costs 6€
                    }

                    if (calculatedTotal > 0) {
                        console.log("Manually calculated total price:", calculatedTotal, "€");
                        return calculatedTotal;
                    }

                    // Last fallback - parse price from the total-price element
                    const priceText = totalPriceElement.textContent;
                    const price = parseFloat(priceText.replace(/[^0-9.]/g, ''));
                    if (isNaN(price) || price <= 0) {
                        console.error("Invalid price found in element:", priceText);
                        return 0; // Return invalid price to trigger error
                    }
                    return price;
                }

                // Jednoduchá funkce pro získání jména
                function getUserName() {
                    // Získání jména z localStorage
                    var username = localStorage.getItem("minecraft-username");

                    // Validace jména na straně klienta
                    if (username && /^[a-zA-Z0-9_]{3,16}$/.test(username)) {
                        console.log("Username získán: " + username);
                        return username;
                    } else {
                        console.error("Username není platný nebo neexistuje");
                        return null;
                    }
                }

                // Load cart and display details
                function loadCartDetails() {
                        try {
                            let cart = JSON.parse(localStorage.getItem("cart") || "[]");
                            console.log("Cart from localStorage:", cart);

                            // Convert old format cart if needed
                            if (cart.length > 0 && typeof cart[0] !== 'object') {
                                console.log("Converting old format cart to new format");
                                const newCart = [];
                                cart.forEach((itemId) => {
                                    newCart.push({id: itemId, quantity: 1, price: 0});
                                });
                                cart = newCart;
                                localStorage.setItem('cart', JSON.stringify(cart));
                                console.log("Cart converted:", cart);
                            }

                            // Debug cart contents
                            if (cart.length > 0) {
                                console.log("Cart items details:");
                                cart.forEach((item, index) => {
                                    console.log(`Item ${index}: ID=${item.id}, Quantity=${item.quantity}, Price=${item.price} (type: ${typeof item})`);
                                });
                            }

                            if (!Array.isArray(cart) || cart.length === 0) {
                                console.error("Cart is empty or invalid");
                                document.getElementById("order-items").innerHTML = '<p>Your cart is empty</p>';
                                document.getElementById("checkout-button").disabled = true;
                                document.getElementById("paypal-button").disabled = true;
                                return;
                            }

                            // Function to load and process cart items from a specific URL
                            function processItemsFromUrl(url, isKey = false) {
                                                return fetch(url)
                                                    .then(response => response.text())
                                                    .then(html => {
                                                        const parser = new DOMParser();
                                                        const doc = parser.parseFromString(html, "text/html");
                                                        const items = doc.querySelectorAll(".spawner");
                                                        let localTotal = 0;
                                                        console.log(`Processing items from ${url}, isKey: ${isKey}`);

                                        // Process each item in the cart
                                            items.forEach(item => {
                                                const id = item.getAttribute("data-id");
                                                const isKeyItem = isKey || id.toString().startsWith('key_');
                                                const isRankItem = id.toString().startsWith('rank_');
                                                const isBattlepassItem = id.toString().startsWith('battlepass_');
                                                const cartItem = cart.find(item => String(item.id) === String(id));
                                                const inCart = cartItem !== undefined;
                                                console.log(`Checking item ID: ${id}, isKeyItem: ${isKeyItem}, isRankItem: ${isRankItem}, isBattlepassItem: ${isBattlepassItem}, in cart: ${inCart}, quantity: ${inCart ? cartItem.quantity : 0}`);

                                                if (inCart) {
                                                try {
                                                    // We should use the price from cart object if available
                                                    let price = 0;
                                                    const name = item.querySelector("h2").textContent;

                                                    // Prioritize getting price from HTML similar to cart.html
                                                    const priceContainer = item.querySelector(".price-container");

                                                    if (priceContainer) {
                                                        // Try to get discounted price first (ALWAYS use this if available)
                                                        const discountedElement = priceContainer.querySelector(".discounted-price");
                                                        if (discountedElement) {
                                                            const discountedText = discountedElement.textContent;
                                                            const priceMatch = discountedText.match(/(\d+(?:\.\d+)?)€/);
                                                            if (priceMatch) {
                                                                price = parseFloat(priceMatch[1]);
                                                                console.log(`Using discounted price for ${name}: ${price}€`);
                                                            }
                                                        } else {
                                                            // If no discount, get original price
                                                            const originalPriceElement = priceContainer.querySelector(".original-price");
                                                            if (originalPriceElement) {
                                                                const priceMatch = originalPriceElement.textContent.match(/(\d+(?:\.\d+)?)€/);
                                                                if (priceMatch) {
                                                                    price = parseFloat(priceMatch[1]);
                                                                    console.log(`Using original price for ${name}: ${price}€`);
                                                                }
                                                            }
                                                        }
                                                    }

                                                    // If price still not found, check cart item (as secondary source)
                                                    if (price === 0 && cartItem && cartItem.price && cartItem.price > 0) {
                                                        price = parseFloat(cartItem.price);
                                                        console.log(`Using price from cart for item ${id}: ${price}€`);
                                                    }

                                                    // If price still not found, check description
                                                    if (price === 0) {
                                                        const description = item.querySelector("p")?.textContent;
                                                        if (description) {
                                                            console.log("Item description:", description);
                                                            // Look for price pattern: digits followed by € symbol, handle different formats
                                                            const priceMatch = description.match(/(\d+(?:\.\d+)?)[ ]?€/) || description.match(/(\d+)[.,]?(\d*)[ ]?€/);

                                                            if (priceMatch) {
                                                                if (priceMatch[2]) { // Handle decimal point
                                                                    price = parseFloat(priceMatch[1] + '.' + priceMatch[2]);
                                                                } else {
                                                                    price = parseFloat(priceMatch[1]);
                                                                }
                                                                console.log(`Found price in description for ${name}: ${price}€`);
                                                            }
                                                        }
                                                    }

                                                    // Default price if still not found
                                                    if (price === 0) {
                                                        price = 3;
                                                        console.log(`Using default price for ${name}: ${price}€`);
                                                    }

                                                    // Note: We don't add to localTotal here anymore, it's done in the next section
                                                    // after considering the quantity

                                                    // Get item quantity from cart
                                                    const quantity = cartItem.quantity || 1;

                                                    // Calculate total price for this item
                                                    const itemTotal = price * quantity;

                                                    // Create order item
                                                    const orderItem = document.createElement("div");
                                                    orderItem.className = "order-detail";
                                                    orderItem.dataset.id = id;
                                                    orderItem.dataset.quantity = quantity;
                                                    orderItem.dataset.price = price;
                                                    orderItem.dataset.type = isKeyItem ? 'key' : (isRankItem ? 'rank' : 'shard');
                                                    // Check if the item has a discount
                                                    let discountInfo = '';
                                                    if (cartItem && cartItem.discountPercent > 0) {
                                                        discountInfo = ` <span class="discount-badge">${cartItem.discountPercent}% OFF</span>`;
                                                    }

                                                    // Show original price if there's a discount
                                                    let originalPriceInfo = '';
                                                    if (cartItem && cartItem.originalPrice && cartItem.originalPrice > price) {
                                                        originalPriceInfo = `<span class="original-price"><s>€${Number(cartItem.originalPrice).toLocaleString("en", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</s></span> `;
                                                    }

                                                    orderItem.innerHTML = `
                                                        <span class="item-name">${name}${isKeyItem ? ' (Key)' : (isRankItem ? ' (Rank)' : '')} x${quantity}</span>
                                                        <span class="item-price">${originalPriceInfo}€${Number(price).toLocaleString("en", { minimumFractionDigits: 2, maximumFractionDigits: 2 })} x ${quantity} = €${itemTotal.toLocaleString("en", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}${discountInfo}</span>
                                                    `;
                                                    orderItems.appendChild(orderItem);

                                                    // Always update the cart price to ensure consistency with what's displayed
                                                                                    // This ensures we use the same price calculation logic as in the cart
                                                                                    cartItem.price = price;
                                                                                    localStorage.setItem('cart', JSON.stringify(cart));

                                                    console.log(`Added item to order: ${name}, Price: ${price}€, Quantity: ${quantity}, Total: ${itemTotal}€, ID: ${id}, Type: ${isKeyItem ? 'key' : (isRankItem ? 'rank' : 'shard')}`);

                                                    // Warn about zero-price items
                                                    if (price === 0) {
                                                        console.warn(`WARNING: Item ${name} (ID: ${id}) has zero price - this item will not contribute to the total`);
                                                    }

                                                    // Use actual item price * quantity for the total
                                                    localTotal += itemTotal;
                                                } catch (err) {
                                                    console.error(`Error processing ${isKeyItem ? 'key' : 'shard'}:`, err);
                                                }
                                            }
                                        });

                                        return localTotal;
                                    });
                            }

                            let totalPrice = 0;
                            const orderItems = document.getElementById("order-items");
                            orderItems.innerHTML = '';

                            // First process spawners
                            processItemsFromUrl("shards.php")
                                .then(spawnerTotal => {
                                    totalPrice += spawnerTotal;

                                    // Then process keys
                                    return processItemsFromUrl("keys.php", true);
                                })
                                .then(keysTotal => {
                                    totalPrice += keysTotal;

                                    // Then process battlepasses
                                    return processItemsFromUrl("battlepasses.php", false);
                                })
                                .then(battlepassesTotal => {
                                    totalPrice += battlepassesTotal;

                                    // Then process ranks
                                    return processItemsFromUrl("ranks.php", false);
                                })
                                .then(ranksTotal => {
                                    totalPrice += ranksTotal;

                                            // Check if we have any items with valid prices (> 0)
                                            let hasValidItems = false;
                                            const validTotal = Array.from(orderItems.children).reduce((sum, item) => {
                                                const price = parseFloat(item.dataset.price || 0);
                                                const quantity = parseInt(item.dataset.quantity || 1);
                                                const itemTotal = price * quantity;
                                                if (itemTotal > 0) {
                                                    hasValidItems = true;
                                                    return sum + itemTotal;
                                                }
                                                return sum;
                                            }, 0);

                                            // If no items were added or no valid items with price > 0, show error message
                                            if (orderItems.children.length === 0 || !hasValidItems) {
                                                console.error("No valid items in cart with valid prices");
                                                document.getElementById("checkout-button").disabled = true;
                                                document.getElementById("paypal-button").disabled = true;

                                                // Show error message in order items
                                                const errorMessage = document.createElement("div");
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
                                            if (validTotal !== totalPrice && validTotal > 0) {
                                                totalPrice = validTotal;
                                                console.log("Adjusted total price to exclude zero-price items:", totalPrice);
                                            }

                                            // Log the final total price for debugging
                                            console.log("CART TOTAL PRICE BEFORE CHECKOUT:", totalPrice, "€");

                                            // Use the actual calculated total price with no minimums
                                            let finalTotal = totalPrice;

                                            // Format total price with two decimal places and euro symbol in front
                                            const formattedPrice = "€" + Number(finalTotal).toLocaleString("en", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                            document.getElementById("total-price").textContent = formattedPrice;

                                            // Save the total price for direct access in checkout
                                            window.checkoutTotalPrice = finalTotal; // Store as number, not string
                                            console.log("CART CALCULATION - Total price calculated:", finalTotal);
                                            console.log("CART CALCULATION - Formatted price displayed:", formattedPrice + "€");
                                            console.log("CART CALCULATION - Price saved to window variable:", window.checkoutTotalPrice);

                                            // Enable buttons
                                            document.getElementById("checkout-button").disabled = false;
                                            document.getElementById("paypal-button").disabled = false;
                                        })
                                .catch(error => {
                                    console.error("Error loading cart details:", error);

                                    // Add fallback item on error
                                    const orderItems = document.getElementById("order-items");
                                    orderItems.innerHTML = `
                                        <div class="order-detail">
                                            <span class="item-name">Minecraft Items</span>
                                            <span class="item-price">5€</span>
                                        </div>
                                    `;
                                    document.getElementById("total-price").textContent = "€5.00";
                                    document.getElementById("checkout-button").disabled = false;
                                    document.getElementById("paypal-button").disabled = false;
                                });
                        } catch (e) {
                            console.error("Error processing cart:", e);

                            // Add fallback item on error
                            const orderItems = document.getElementById("order-items");
                            orderItems.innerHTML = `
                                <div class="order-detail">
                                    <span class="item-name">Minecraft Items</span>
                                    <span class="item-price">5.00€</span>
                                </div>
                            `;

                            document.getElementById("checkout-button").disabled = false;
                            document.getElementById("paypal-button").disabled = false;
                        }
                    }

                // Function to redirect to PayPal with cart data
                function redirectToPaypal() {
                    const cart = JSON.parse(localStorage.getItem("cart") || "[]");
                    const cartParam = encodeURIComponent(JSON.stringify(cart));
                    const username = getUserName() || 'Anonymous';
                    window.location.href = 'paypal-checkout.php?cart=' + cartParam + '&username=' + encodeURIComponent(username);
                }

                // Spustit funkce po načtení stránky
                document.addEventListener("DOMContentLoaded", function () {
                    const username = getUserName();

                    // Display username
                    if (username) {
                        document.getElementById("username-output").textContent = "Your Minecraft username: " + username;
                    } else {
                        document.getElementById("username-output").innerHTML =
                            "<span class='error-text'>No username found! Please <a href='login-players.php'>login</a> first.</span>";
                        document.getElementById("checkout-button").disabled = true;
                        document.getElementById("paypal-button").disabled = true;
                    }

                    // Load cart details
                    loadCartDetails();
                });
            </script>
        </div>
        <footer id="footer-main"></footer>
        <script src="js/script.js"></script>
    </body>
</html>

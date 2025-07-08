<?php
$db = new PDO("sqlite:ranks.sqlite");
$ranks = $db->query("SELECT * FROM ranks")->fetchAll(PDO::FETCH_ASSOC);
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

            // Notification system variables and functions
            let notificationContainer = null;

            function createNotificationContainer() {
                if (!notificationContainer) {
                    notificationContainer = document.createElement('div');
                    notificationContainer.className = 'notification-container';
                    notificationContainer.style.position = 'fixed';
                    notificationContainer.style.top = '20px';
                    notificationContainer.style.right = '20px';
                    notificationContainer.style.zIndex = '9999';
                    notificationContainer.style.maxWidth = '300px';
                    notificationContainer.style.display = 'flex';
                    notificationContainer.style.flexDirection = 'column';
                    notificationContainer.style.gap = '10px';
                    document.body.appendChild(notificationContainer);
                }
                return notificationContainer;
            }

            function showNotification(message, type = "success", duration = 5000) {
                createNotificationContainer();

                // Create notification element
                const notification = document.createElement("div");
                notification.className = "notification " + type;
                notification.style.backgroundColor = '#fff';
                notification.style.color = '#333';
                notification.style.borderRadius = '4px';
                notification.style.padding = '12px 15px';
                notification.style.marginBottom = '10px';
                notification.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
                notification.style.position = 'relative';
                notification.style.overflow = 'hidden';
                notification.style.width = '100%';
                notification.style.animation = 'fadeIn 0.3s forwards';

                // Define fadeIn animation if it doesn't exist
                if (!document.querySelector('#notification-animations')) {
                    const styleSheet = document.createElement('style');
                    styleSheet.id = 'notification-animations';
                    styleSheet.textContent = `
                        @keyframes fadeIn {
                            from {
                                opacity: 0;
                                transform: translateY(-10px);
                            }
                            to {
                                opacity: 1;
                                transform: translateY(0);
                            }
                        }
                        @keyframes fadeOut {
                            from {
                                opacity: 1;
                                transform: translateY(0);
                            }
                            to {
                                opacity: 0;
                                transform: translateY(-10px);
                            }
                        }
                    `;
                    document.head.appendChild(styleSheet);
                }

                if (type === 'success') {
                    notification.style.borderLeft = '4px solid #28a745';
                } else if (type === 'error') {
                    notification.style.borderLeft = '4px solid #dc3545';
                } else if (type === 'warning') {
                    notification.style.borderLeft = '4px solid #ffc107';
                } else if (type === 'info') {
                    notification.style.borderLeft = '4px solid #17a2b8';
                }

                // Create message content
                const messageEl = document.createElement("div");
                messageEl.className = "message";
                messageEl.style.marginRight = '20px';
                messageEl.innerHTML = message.replace(/\n/g, "<br>");
                notification.appendChild(messageEl);

                // Add close button
                const closeBtn = document.createElement("span");
                closeBtn.className = "close-btn";
                closeBtn.innerHTML = "&times;";
                closeBtn.style.position = 'absolute';
                closeBtn.style.top = '5px';
                closeBtn.style.right = '10px';
                closeBtn.style.cursor = 'pointer';
                closeBtn.style.fontSize = '18px';
                closeBtn.style.color = '#999';
                closeBtn.onclick = () => removeNotification(notification);
                notification.appendChild(closeBtn);

                // Add timer bar
                const timerBar = document.createElement("div");
                timerBar.className = "timer-bar";
                timerBar.style.position = 'absolute';
                timerBar.style.bottom = '0';
                timerBar.style.left = '0';
                timerBar.style.height = '3px';
                timerBar.style.width = '100%';
                timerBar.style.backgroundColor = '#007bff';
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
                    if (notificationContainer && notificationContainer.children.length === 0) {
                        document.body.removeChild(notificationContainer);
                        notificationContainer = null;
                    }
                }, 300);
            }

            // Definition of functions for working with cart


            // Reset cart function for emergencies
            function resetCart() {
                if (confirm("Are you sure you want to reset your cart? This cannot be undone.")) {
                    try {
                        localStorage.setItem('cart', '[]');
                        console.log("Cart reset to empty array");

                        // Reset all buttons
                        document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
                            btn.textContent = 'Add to Cart';
                            btn.classList.remove('added', 'update');
                            btn.disabled = false;
                        });



                        // Update UI
                        updateCartCount();

                        alert("Cart has been reset. All items removed.");
                    } catch (err) {
                        console.error("Error resetting cart:", err);
                        alert("Error resetting cart. See console for details.");
                    }
                }
            }

            function addToCart(button, id) {
                try {
                    console.log("=== ADDING TO CART ===");
                    console.log("Adding item with ID:", id);

                    // Track event with dataLayer if available
                    if (window.dataLayer) {
                        window.dataLayer.push({
                            'event': 'addToCart',
                            'itemId': id,
                            'itemType': 'rank'
                        });
                    }

                    // Najít kontejner
                    let container = button;
                    while (container && !container.classList.contains('spawner')) {
                        container = container.parentNode;
                    }

                    if (!container) {
                        console.error("Kontejner nenalezen!");
                        return;
                    }

                    // Používat vždy množství 1
                    const quantity = 1;

                    // Debug information
                    console.log("Adding to cart - ID:", id, "Quantity:", quantity);

                    // Get price from the description or discount info
                    const priceContainer = container.querySelector('.price-container');
                    const descriptionElement = container.querySelector('p');

                    let price = null; // Will be determined from elements

                    console.log("Looking for price in container:", container);
                    console.log("Price container found:", priceContainer);

                    // First try to get discounted price
                    const discountedElement = container.querySelector('.discounted-price');
                    if (discountedElement) {
                        const discountedText = discountedElement.textContent.trim();
                        console.log("Found discounted price element, text:", discountedText);
                        const priceMatch = discountedText.match(/€(\d+(?:\.\d+)?)/);
                        if (priceMatch) {
                            price = parseFloat(priceMatch[1]);
                            console.log("Parsed discounted price:", price);
                        }
                    }

                    // If no discounted price found, try regular price from description
                    if (price === null && descriptionElement) {
                        const description = descriptionElement.textContent.trim();
                        console.log("No discounted price, checking description:", description);
                        const priceMatch = description.match(/€(\d+(?:\.\d+)?)/);
                        if (priceMatch) {
                            price = parseFloat(priceMatch[1]);
                            console.log("Parsed regular price from description:", price);
                        }
                    }

                    // Final fallback to default price
                    if (price === null) {
                        price = 3;
                        console.warn("No price found in elements, using default price 3€");
                    }

                    console.log("Final price determined:", price);

                    // Show calculation details for user
                    console.log(`Item price: ${price}€, Quantity: ${quantity}, Total: ${price * quantity}€`);

                    // Načíst košík
                    let cart = [];
                    try {
                        const rawCart = localStorage.getItem('cart');
                        if (rawCart) {
                            cart = JSON.parse(rawCart);
                            if (!Array.isArray(cart)) {
                                console.warn("Cart is not an array, resetting:", cart);
                                cart = [];
                            }
                        }
                    } catch (err) {
                        console.error("Error loading cart:", err);
                        cart = [];
                    }

                    // Convert old format cart if needed
                    if (cart.length > 0 && typeof cart[0] !== 'object') {
                        console.log("Converting old cart format to new format");
                        const newCart = [];
                        cart.forEach((itemId) => {
                            newCart.push({id: itemId, quantity: 1, price: 0});
                        });
                        cart = newCart;
                        console.log("Cart conversion complete:", cart);
                    }

                    // Check if item already exists in cart
                    const idString = String(id);
                    const existingItemIndex = cart.findIndex(item => String(item.id) === idString);
                    console.log("Kontrola položky:", idString, "Nalezeno na indexu:", existingItemIndex, "Current cart:", JSON.stringify(cart));

                    // Zpráva pro uživatele
                    let feedbackMsg = '';

                    // Update cart
                    if (existingItemIndex !== -1) {
                        // Item already exists - just update price
                        cart[existingItemIndex].price = price;
                        feedbackMsg = `Already in cart!`;
                        console.log(`Item already in cart - ID: ${idString}`);
                    } else {
                        // Add new item
                        cart.push({id: idString, quantity: quantity, price: price});
                        feedbackMsg = `Added to cart!`;
                    }

                    // Save cart to localStorage
                    const cartJson = JSON.stringify(cart);
                    localStorage.setItem('cart', cartJson);
                    console.log("Cart saved:", cartJson);

                    // Use notification system instead of custom feedback
                    showNotification(feedbackMsg + " 🛒", "success", 3000);

                    // Update button appearance
                    if (existingItemIndex === -1) {
                        button.textContent = '✓ Added!';
                        button.classList.add('added');
                        button.disabled = true;
                    }

                    // Visual feedback animation for the container
                    container.style.transition = "all 0.3s ease";
                    container.style.boxShadow = "0 0 10px 2px #4CAF50";
                    container.style.transform = "translateY(-2px)";
                    setTimeout(() => {
                        container.style.boxShadow = "";
                        container.style.transform = "";
                    }, 1000);

                    console.log("Updated cart:", idString, "Current cart:", JSON.parse(localStorage.getItem('cart')));

                    // Aktualizovat počet v navigaci a synchronizovat UI
                    try {
                        updateCartCount();

                        // Added - synchronize UI after change
                    setTimeout(function() {
                        // Use direct function call to avoid reference errors
                        try {
                            const updatedCart = JSON.parse(localStorage.getItem('cart') || '[]');
                            syncButtonsWithCart(updatedCart);
                            updateCartCount();
                            console.log("Cart synchronized after update");
                        } catch (e) {
                            console.error("Error in delayed sync:", e);
                        }
                    }, 50);
                    } catch (err) {
                        console.error("Chyba při aktualizaci počtu:", err);
                    }


                } catch (error) {
                    console.error("Error adding to cart:", error);
                    showNotification("Error adding to cart: " + error.message, "error");
                }
            }
        </script>
        <!-- Google Analytics function declaration -->
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag() {
                dataLayer.push(arguments);
            }
            gtag("js", new Date());
            gtag("config", "G-SGG2CLM06D");
        </script>
        <!-- Load Google Analytics only if consent was given -->
        <script>
            function loadGoogleAnalytics() {
                gtag("js", new Date());
                gtag("config", "G-SGG2CLM06D");
                console.log("Google Analytics loaded (cookies accepted)");
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

            // Show cookie banner after page loads
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

        <title>Ranks - Sentry SMP</title>
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
                <h1 class="main">Ranks</h1>
            </div>
            <p></p>
            <div class="spawner-grid">
                <?php foreach ($ranks as $r): ?>
                    <div class="spawner" data-id="rank_<?= $r["id"] ?>">
                        <img src="<?= htmlspecialchars(
                            $r["obrazek"]
                        ) ?>" alt="image" class="spawner-image" width="100px">
                        <h2><?= htmlspecialchars($r["nazev"]) ?></h2>
                        <?php if (!empty($r["sales"])): ?>
                            <?php
                            $originalPrice = $r["cena"];
                            $discountPercent = $r["sales"];
                            $discountedPrice =
                                "€" .
                                number_format(
                                    $originalPrice *
                                        (1 - $discountPercent / 100),
                                    2,
                                    ".",
                                    ""
                                );
                            $formattedOriginal =
                                "€" . number_format($originalPrice, 2, ".", "");
                            ?>
                            <div class="price-container">
                                <p class="original-price"><?= $formattedOriginal ?></p>
                                <p class="discounted-price"><?= $discountedPrice ?></p>
                                <span class="discount-badge"><?= $discountPercent ?>% OFF</span>
                            </div>
                        <?php else: ?>
                            <?php $formatted =
                                "€" . number_format($r["cena"], 2, ".", ""); ?>
                            <p><?= $formatted ?></p>
                        <?php endif; ?>
                        <p style="font-size: 14px; font-weight: normal;"><?= htmlspecialchars(
                            $r["popis"]
                        ) ?></p>
                        <div class="cart-controls">
                            <button onclick="addToCart(this, 'rank_<?= $r[
                                "id"
                            ] ?>')" class="add-to-cart-btn">Add to Cart</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <footer id="footer-main"></footer>
        <script src="js/script.js"></script>
        <script>
            // Function to sync buttons with cart contents
            function syncButtonsWithCart(cart) {
                try {
                    if (!Array.isArray(cart)) {
                        console.warn("Cart is not an array:", cart);
                        return;
                    }

                    // Reset all buttons first
                    document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
                        btn.textContent = 'Add to Cart';
                        btn.classList.remove('added', 'update');
                        btn.disabled = false;
                    });

                    // Update buttons for items in cart
                    cart.forEach(item => {
                        if (!item || !item.id) return;

                        const id = String(item.id);
                        const container = document.querySelector(`.spawner[data-id="${id}"]`);

                        if (container) {
                            const button = container.querySelector('.add-to-cart-btn');

                            if (button) {
                                button.textContent = '✓ Added!';
                                button.disabled = true;
                                button.classList.add('added');
                            }

                        }
                    });
                } catch (err) {
                    console.error("Error syncing buttons with cart:", err);
                }
            }

            // Initialize cart synchronization when page loads
            document.addEventListener('DOMContentLoaded', function() {
                try {
                    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
                    syncButtonsWithCart(cart);
                    console.log("Initial cart sync on page load");
                } catch (e) {
                    console.error("Error in initial cart sync:", e);
                }
            });
        </script>
        <script>
        const savedCart = JSON.parse(localStorage.getItem('cart') || '[]');

                // Funkce pro aktualizaci počtu v navigaci
                function updateCartCount() {
                    try {
                        // Načíst košík
                        let cart = [];
                        try {
                            const rawCart = localStorage.getItem('cart');
                            if (rawCart) {
                                cart = JSON.parse(rawCart);
                                if (!Array.isArray(cart)) {
                                    cart = [];
                                }
                            }
                        } catch (err) {
                            cart = [];
                        }

                        // Najít element pro zobrazení počtu
                        const cartCountElement = document.getElementById("cart-count");
                        if (cartCountElement) {
                            // Spočítat celkový počet položek
                            let totalItems = 0;
                            cart.forEach(item => {
                                const itemQuantity = parseInt(item.quantity) || 1;
                                totalItems += itemQuantity;
                            });

                            // Aktualizovat zobrazení
                            cartCountElement.textContent = totalItems;
                            cartCountElement.style.display = totalItems > 0 ? "inline-block" : "none";

                            console.log("Updated number of items in cart:", totalItems);
                        } else {
                            console.warn("Cart count element not found in the DOM");
                        }

                        return cart;
                    } catch (err) {
                        console.error("Chyba při aktualizaci počtu:", err);
                        return [];
                    }
                }

                // Function to display current cart contents for debugging
                function debugCart() {
                    try {
                        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
                        console.log("=== CART DEBUG ===");
                        console.log("Raw cart data:", localStorage.getItem('cart'));
                        console.log("Parsed cart:", cart);
                        let totalItems = 0;

                        cart.forEach((item, index) => {
                            console.log(`Item ${index+1}:`, {
                                id: item.id,
                                quantity: item.quantity,
                                price: item.price
                            });
                            totalItems += parseInt(item.quantity) || 1;
                        });

                        console.log("Total items:", totalItems);
                        console.log("=================");

                        alert(`Cart contains ${cart.length} unique items with ${totalItems} total quantity. See console for details.`);
                    } catch (err) {
                        console.error("Error debugging cart:", err);
                        alert("Error reading cart data. See console for details.");
                    }
                }

                // Add debugging buttons to the page
                function addDebugButtons() {
                    // Check if buttons already exist
                    if (document.getElementById('debug-buttons')) return;

                    const container = document.createElement('div');
                    container.id = 'debug-buttons';
                    container.style.position = 'fixed';
                    container.style.bottom = '20px';
                    container.style.right = '20px';
                    container.style.zIndex = '9999';
                    container.style.display = 'flex';
                    container.style.flexDirection = 'column';
                    container.style.gap = '10px';

                    // Add main visible button for cart synchronization
                    const mainSyncButton = document.createElement('button');
                    mainSyncButton.textContent = '🛒 SYNCHRONIZE CART';
                    mainSyncButton.onclick = function() {
                        try {
                            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
                            syncButtonsWithCart(cart);
                            updateCartCount();
                            console.log("Cart successfully synchronized manually");
                            showNotification('Cart has been successfully synchronized!');
                        } catch(err) {
                            console.error("Error synchronizing cart:", err);
                            showNotification('Error during synchronization: ' + err.message, 'error');
                        }
                    };
                    mainSyncButton.style.padding = '15px';
                    mainSyncButton.style.backgroundColor = '#ff6600';
                    mainSyncButton.style.color = 'white';
                    mainSyncButton.style.border = 'none';
                    mainSyncButton.style.borderRadius = '5px';
                    mainSyncButton.style.cursor = 'pointer';
                    mainSyncButton.style.fontWeight = 'bold';
                    mainSyncButton.style.fontSize = '16px';
                    mainSyncButton.style.boxShadow = '0 4px 8px rgba(0,0,0,0.2)';
                    mainSyncButton.style.marginBottom = '20px';

                    container.appendChild(mainSyncButton);

                    // Add debug button
                    const debugButton = document.createElement('button');
                    debugButton.textContent = '🔍 Debug Cart';
                    debugButton.onclick = debugCart;
                    debugButton.style.padding = '10px';
                    debugButton.style.backgroundColor = '#007bff';
                    debugButton.style.color = 'white';
                    debugButton.style.border = 'none';
                    debugButton.style.borderRadius = '5px';
                    debugButton.style.cursor = 'pointer';
                    container.appendChild(debugButton);

                    // Add reset button
                    const resetButton = document.createElement('button');
                    resetButton.textContent = '🗑️ Reset Cart';
                    resetButton.onclick = resetCart;
                    resetButton.style.padding = '10px';
                    resetButton.style.backgroundColor = '#dc3545';
                    resetButton.style.color = 'white';
                    resetButton.style.border = 'none';
                    resetButton.style.borderRadius = '5px';
                    resetButton.style.cursor = 'pointer';
                    container.appendChild(resetButton);

                    document.body.appendChild(container);
                }

                // Function to sync buttons with cart contents
                function syncButtonsWithCart(cart) {
                    try {
                        if (!Array.isArray(cart)) {
                            console.warn("Cart is not an array:", cart);
                            return;
                        }

                        // Reset all buttons first
                        document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
                            btn.textContent = 'Add to Cart';
                            btn.classList.remove('added', 'update');
                            btn.disabled = false;
                        });

                        // Update buttons for items in cart
                        cart.forEach(item => {
                            if (!item || !item.id) return;

                            const id = String(item.id);
                            const container = document.querySelector(`.spawner[data-id="${id}"]`);

                            if (container) {
                                const button = container.querySelector('.add-to-cart-btn');

                                if (button) {
                                    button.textContent = '✓ Added!';
                                    button.disabled = true;
                                    button.classList.add('added');
                                }

                            }
                        });
                    } catch (err) {
                        console.error("Error syncing buttons with cart:", err);
                    }
                }

                // Function to initialize cart on page load
                function initializeCart() {
                    try {
                        // Get the cart from localStorage
                        const cart = JSON.parse(localStorage.getItem('cart') || '[]');

                        // Sync buttons with cart
                        syncButtonsWithCart(cart);

                        // Update cart count in navigation
                        updateCartCount();

                        // Add debug buttons
                        addDebugButtons();

                        // Set up automatic sync for cart changes from other tabs
                        window.addEventListener('storage', function(e) {
                            if (e.key === 'cart') {
                                try {
                                    const newCart = JSON.parse(e.newValue || '[]');
                                    syncButtonsWithCart(newCart);
                                    updateCartCount();
                                    console.log("Cart synchronized from another tab");
                                } catch (err) {
                                    console.error("Error handling storage event:", err);
                                }
                            }
                        });

                        // Initial sync
                        directSyncFromCart();
                    } catch (err) {
                        console.error("Error initializing cart:", err);
                    }
                }

                // Run initialization after the page is loaded
                document.addEventListener('DOMContentLoaded', initializeCart);

                // Direct synchronization function for debugging
                function directSyncFromCart() {
                    try {
                        const cart = JSON.parse(localStorage.getItem('cart') || '[]');

                        // Check each spawner in the page
                        document.querySelectorAll('.spawner').forEach(spawner => {
                            const id = spawner.getAttribute('data-id');
                            if (!id) return;

                            // Find corresponding item in cart
                            const cartItem = cart.find(item => String(item.id) === String(id));
                            if (!cartItem) return;

                            // Update button
                            const button = spawner.querySelector('.add-to-cart-btn');

                            if (button) {
                                button.textContent = '✓ Added!';
                                button.disabled = true;
                                button.classList.add('added');
                            }
                        });

                        console.log("Direct sync from cart completed");
                        showNotification("Cart synchronized successfully", "success", 2000);
                    } catch (err) {
                        console.error("Error in direct sync:", err);
                        showNotification("Error synchronizing cart: " + err.message, "error");
                    }
                }

                // Define global syncCart function to avoid scope issues
                window.syncCart = function() {
                    try {
                        console.log("Syncing cart...");
                        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
                        syncButtonsWithCart(cart);
                        updateCartCount();
                        console.log("Cart synchronized successfully");
                    } catch (err) {
                        console.error("Error syncing cart:", err);
                    }
                };

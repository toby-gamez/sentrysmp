<?php
$db = new PDO("sqlite:blog.sqlite");
$spawners = $db->query("SELECT * FROM spawners")->fetchAll(PDO::FETCH_ASSOC);
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

            // Definition of functions for working with cart
            function validateQuantity(input) {
                // Store the original value for logging
                const originalValue = input.value;

                // Ensure quantity is at least 1
                if (input.value < 1) {
                    input.value = 1;
                }
                // Restrict to whole numbers
                input.value = Math.floor(input.value);

                // Log the change and give visual feedback if value changed
                if (originalValue != input.value) {
                    console.log(`Quantity adjusted from ${originalValue} to ${input.value}`);
                    // Add brief highlight effect
                    input.style.backgroundColor = "#ffff99";
                    setTimeout(() => {
                        input.style.backgroundColor = "";
                    }, 500);
                }

                // Update the add-to-cart button if it exists in the cart
                const container = input.closest('.spawner');
                if (container) {
                    const button = container.querySelector('.add-to-cart-btn');
                    if (!button) return;

                    try {
                        // Extract the ID from the onclick attribute
                        const onclickAttr = button.getAttribute('onclick');
                        const match = onclickAttr.match(/addToCart\(this,\s*['"]([^'"]+)['"]\)/);
                        const id = match ? match[1] : null;

                        if (!id) return;

                        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
                        const existingItemIndex = cart.findIndex(item => String(item.id) === String(id));

                        // Update the add-to-cart button if it exists in the cart
                        if (existingItemIndex !== -1) {
                            // Only update if it's not currently in "added" state
                            if (!button.classList.contains('added')) {
                                button.textContent = 'Update Cart';
                                button.classList.remove('added');
                                button.classList.add('update');
                                button.disabled = false;
                            }
                        }
                    } catch (err) {
                        console.error("Error updating button state:", err);
                    }
                }
            }

            // Function to adjust quantity with + and - buttons
            function adjustQuantity(button, delta) {
                try {
                    // Find input element
                    const input = button.parentNode.querySelector('input.item-quantity');
                    if (!input) {
                        console.error("Quantity input not found");
                        return;
                    }

                    // Convert values to numbers
                    const oldValue = parseInt(input.value || "1");
                    delta = parseInt(delta);

                    // Calculate new value
                    let newValue = oldValue + delta;
                    if (newValue < 1) newValue = 1;

                    // Set new value
                    input.value = newValue;

                    // Debug log
                    console.log(`Quantity changed: ${oldValue} -> ${newValue} (${delta > 0 ? 'increased' : 'decreased'})`);

                    // Visual feedback
                    input.style.backgroundColor = "#ffff99";
                    setTimeout(() => {
                        input.style.backgroundColor = "";
                    }, 500);

                    // Display a small floating indicator
                    const indicator = document.createElement("span");
                    indicator.textContent = delta > 0 ? "+" + delta : delta;
                    indicator.style.position = "absolute";
                    indicator.style.background = delta > 0 ? "#4CAF50" : "#f44336";
                    indicator.style.color = "white";
                    indicator.style.borderRadius = "50%";
                    indicator.style.width = "20px";
                    indicator.style.height = "20px";
                    indicator.style.textAlign = "center";
                    indicator.style.lineHeight = "20px";
                    indicator.style.fontSize = "12px";
                    indicator.style.opacity = "0.9";
                    indicator.style.transition = "all 0.5s ease-out";

                    const buttonRect = button.getBoundingClientRect();
                    indicator.style.left = (buttonRect.left + buttonRect.width/2) + "px";
                    indicator.style.top = buttonRect.top + "px";
                    document.body.appendChild(indicator);

                    // Animate and remove
                    setTimeout(() => {
                        indicator.style.transform = "translateY(-20px)";
                        indicator.style.opacity = "0";
                        setTimeout(() => document.body.removeChild(indicator), 500);
                    }, 10);
                } catch (error) {
                    console.error("Error in adjustQuantity:", error);
                }
            }

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

                        // Reset all quantity inputs to 1
                        document.querySelectorAll('input.item-quantity').forEach(input => {
                            input.value = 1;
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
                            'itemType': 'spawner'
                        });
                    }

                    // Zjistit, zda je tlačítko v režimu aktualizace
                    const isUpdateMode = button.classList.contains('update');
                    if (isUpdateMode) {
                        button.classList.remove('update');
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

                    // Najít input pro množství
                    const quantityInput = container.querySelector('input.item-quantity');

                    // Ensure we have a valid quantity by running validation first
                    if (quantityInput) validateQuantity(quantityInput);

                    // Získat množství
                    let quantity = 1;
                    if (quantityInput) {
                        quantity = parseInt(quantityInput.value);
                        if (isNaN(quantity) || quantity < 1) {
                            quantity = 1;
                            quantityInput.value = 1;
                        }
                    }

                    // Debug information
                    console.log("Adding to cart - ID:", id, "Quantity:", quantity);

                    // Get price from the description or discount info
                    const priceContainer = container.querySelector('.price-container');
                    const descriptionElement = container.querySelector('p');

                    let price = 3; // Default price

                    // Check if item has a discount
                    if (priceContainer) {
                        // Has discount - get the discounted price
                        const discountedElement = priceContainer.querySelector('.discounted-price');
                        if (discountedElement) {
                            const discountedText = discountedElement.textContent;
                            const priceMatch = discountedText.match(/€(\d+(?:\.\d+)?)/);
                            if (priceMatch) {
                                price = parseFloat(priceMatch[1]);
                            }
                        }
                    } else if (descriptionElement) {
                        // No discount - get regular price
                        const description = descriptionElement.textContent;
                        const priceMatch = description.match(/€(\d+(?:\.\d+)?)/);
                        if (priceMatch) {
                            price = parseFloat(priceMatch[1]);
                        }
                    } else {
                        console.warn("Description element not found, using default price 3€");
                    }

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
                        // Item already exists
                        const oldQuantity = parseInt(cart[existingItemIndex].quantity) || 1;

                        if (isUpdateMode) {
                            // Replace quantity
                            cart[existingItemIndex].quantity = quantity;
                            feedbackMsg = `Updated to ${quantity} pcs`;
                        } else {
                            // Replace existing quantity (same behavior as in keys.php)
                            const oldQuantity = cart[existingItemIndex].quantity;
                            cart[existingItemIndex].quantity = quantity;
                            feedbackMsg = `Updated to ${quantity} pcs (was ${oldQuantity})`;
                        }

                        cart[existingItemIndex].price = price;
                        console.log(`Item update - ID: ${idString}, Quantity: ${cart[existingItemIndex].quantity}, Previous: ${oldQuantity}`);
                    } else {
                        // Add new item
                        cart.push({id: idString, quantity: quantity, price: price});
                        feedbackMsg = `Added ${quantity} pcs to cart!`;
                    }

                    // Save cart to localStorage
                    const cartJson = JSON.stringify(cart);
                    localStorage.setItem('cart', cartJson);
                    console.log("Cart saved:", cartJson);

                    // Use notification system instead of custom feedback
                    showNotification(feedbackMsg + " 🛒", "success", 3000);

                    // Update button appearance
                    button.textContent = '✓ Added to Cart!';
                    button.classList.add('added');
                    button.disabled = true;

                    // Re-enable the button after a delay and change to update mode
                    setTimeout(() => {
                        button.disabled = false;
                        button.textContent = 'Update Cart';
                        button.classList.remove('added');
                        button.classList.add('update');
                    }, 1500);

                    // Visual feedback animation for the container
                    container.style.transition = "all 0.3s ease";
                    container.style.boxShadow = "0 0 10px 2px #4CAF50";
                    container.style.transform = "translateY(-2px)";
                    setTimeout(() => {
                        container.style.boxShadow = "";
                        container.style.transform = "";
                    }, 1000);

                    // Show cart count indicator
                    const cartCount = document.createElement("div");
                    cartCount.textContent = quantity;
                    cartCount.style.position = "absolute";
                    cartCount.style.top = "10px";
                    cartCount.style.right = "10px";
                    cartCount.style.background = "#ff5722";
                    cartCount.style.color = "white";
                    cartCount.style.borderRadius = "50%";
                    cartCount.style.width = "25px";
                    cartCount.style.height = "25px";
                    cartCount.style.textAlign = "center";
                    cartCount.style.lineHeight = "25px";
                    cartCount.style.fontWeight = "bold";
                    cartCount.style.zIndex = "10";
                    cartCount.style.animation = "pulse 1s";
                    cartCount.style.boxShadow = "0 2px 5px rgba(0,0,0,0.2)";
                    container.style.position = "relative";
                    container.appendChild(cartCount);

                    console.log("Updated cart:", idString, "Quantity:", quantity, "Current cart:", JSON.parse(localStorage.getItem('cart')));

                    // Aktualizovat počet v navigaci a synchronizovat UI
                    try {
                        updateCartCount();

                        // Added - synchronize UI after change
                        setTimeout(function() {
                            syncCart();
                        }, 50);
                    } catch (err) {
                        console.error("Chyba při aktualizaci počtu:", err);
                    }

                    // Odstranit dočasné indikátory po chvíli
                    setTimeout(() => {
                        const tempIndicators = container.querySelectorAll('div:not(.quantity-controls)');
                        tempIndicators.forEach(indicator => {
                            if (indicator.style.borderRadius === '50%') {
                                indicator.remove();
                            }
                        });
                    }, 2000);
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

            function loadGoogleAnalytics() {
                try {
                    gtag('js', new Date());
                    gtag('config', 'G-SGG2CLM06D');
                    console.log("Google Analytics configured");
                } catch (err) {
                    console.error("Error loading Google Analytics:", err);
                }
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

        <title>Spawners - Sentry SMP</title>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="css/style.css" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link rel="icon" href="images/favicon.png" />
        <link rel="stylesheet" href="css/cart-styles.css" />
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
                <h1 class="main">Spawners</h1>
            </div>
            <div class="spawner-grid">
                <?php foreach ($spawners as $s): ?>
                    <div class="spawner" data-id="<?= $s["id"] ?>">
                        <img src="<?= htmlspecialchars(
                            $s["obrazek"]
                        ) ?>" alt="image" class="spawner-image" width="100px">
                        <h2><?= htmlspecialchars($s["nazev"]) ?></h2>
                        <?php if (!empty($s["sales"])): ?>
                            <?php
                            $originalPrice = preg_replace(
                                "/[^0-9.]/",
                                "",
                                $s["popis"]
                            );
                            $discountPercent = $s["sales"];
                            $discountedPrice =
                                "€" .
                                number_format(
                                    $originalPrice *
                                        (1 - $discountPercent / 100),
                                    2,
                                    ".",
                                    ""
                                );
                            ?>
                            <div class="price-container">
                                <p class="original-price"><s>
                                    <?php
                                    $orig = preg_replace(
                                        "/[^0-9.]/",
                                        "",
                                        $s["popis"]
                                    );
                                    echo "€" . number_format($orig, 2, ".", "");
                                    ?>
                                </s></p>
                                <p class="discounted-price"><?= $discountedPrice ?></p>
                                <span class="discount-badge"><?= $discountPercent ?>% OFF</span>
                            </div>
                        <?php else: ?>
                            <p style="font-size: 14px; font-weight: normal;">
                                <?php
                                $orig = preg_replace(
                                    "/[^0-9.]/",
                                    "",
                                    $s["popis"]
                                );
                                echo "€" . number_format($orig, 2, ".", "");
                                ?>
                            </p>
                        <?php endif; ?>
                        <div class="cart-controls">
                            <div class="quantity-controls">
                                <button type="button" class="quantity-btn minus" onclick="adjustQuantity(this, -1)" title="Decrease quantity"><i class="bi bi-dash"></i></button>
                                <input type="number" min="1" value="1" class="item-quantity" onchange="validateQuantity(this)" oninput="validateQuantity(this)">
                                <button type="button" class="quantity-btn plus" onclick="adjustQuantity(this, 1)" title="Increase quantity"><i class="bi bi-plus"></i></button>
                            </div>
                            <button onclick="addToCart(this, <?= $s[
                                "id"
                            ] ?>)" class="add-to-cart-btn">Add to Cart</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <footer id="footer-main"></footer>
        <script src="js/script.js"></script>
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

                    const debugSyncButton = document.createElement('button');
                    debugButton.textContent = '🔍 Debug Cart';
                    debugButton.onclick = debugCart;
                    debugButton.style.padding = '10px';
                    debugButton.style.backgroundColor = '#007bff';
                    debugButton.style.color = 'white';
                    debugButton.style.border = 'none';
                    debugButton.style.borderRadius = '5px';
                    debugButton.style.cursor = 'pointer';

                    const resetButton = document.createElement('button');
                    resetButton.textContent = '🗑️ Reset Cart';
                    resetButton.onclick = resetCart;
                    resetButton.style.padding = '10px';
                    resetButton.style.backgroundColor = '#dc3545';
                    resetButton.style.color = 'white';
                    resetButton.style.border = 'none';
                    resetButton.style.borderRadius = '5px';
                    resetButton.style.cursor = 'pointer';

                    const uiSyncButton = document.createElement('button');
                    uiSyncButton.textContent = '🔄 Sync UI';
                    uiSyncButton.onclick = function() {
                        try {
                            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
                            syncButtonsWithCart(cart);
                            console.log("UI successfully synchronized with cart");
                            showNotification('UI synchronized with cart');
                        } catch(err) {
                            console.error("Error syncing UI:", err);
                            showNotification('Error syncing UI: ' + err.message, 'error');
                        }
                    };
                    uiSyncButton.style.padding = '10px';
                    uiSyncButton.style.margin = '5px';
                    uiSyncButton.style.backgroundColor = '#2196F3';
                    uiSyncButton.style.color = 'white';
                    uiSyncButton.style.border = 'none';
                    uiSyncButton.style.borderRadius = '5px';
                    uiSyncButton.style.cursor = 'pointer';

                    container.appendChild(mainSyncButton);
                    container.appendChild(uiSyncButton);
                    container.appendChild(debugButton);
                    container.appendChild(resetButton);
                    document.body.appendChild(container);
                }

                // Function for synchronizing UI buttons with cart
                function syncButtonsWithCart(cart) {
                    try {
                        console.log("Synchronizing UI with cart, number of items in cart:", cart.length);

                        // Debug - print entire cart to console for verification
                        cart.forEach((item, index) => {
                            console.log(`Item ${index+1} in cart:`, item);
                        });

                        // Go through cart and find corresponding button for each item
                        // This approach is more reliable than checking all buttons
                        for (let item of cart) {
                            console.log("Looking for UI elements for item", item.id);

                            // Find all buttons with this ID in onclick attribute
                            const buttons = [];
                            document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
                                const onclick = btn.getAttribute('onclick') || '';
                                if (onclick.includes(`'${item.id}'`) || onclick.includes(`"${item.id}"`)) {
                                    buttons.push(btn);
                                }
                            });

                            console.log(`Nalezeno ${buttons.length} tlačítek pro ID: ${item.id}`);

                            // Aktualizovat všechna nalezená tlačítka
                            buttons.forEach(button => {
                                const container = button.closest('.spawner');
                                if (container) {
                                    const quantityInput = container.querySelector('input.item-quantity');
                                    if (quantityInput) {
                                        console.log(`Nastavuji množství pro ${item.id}: ${item.quantity}`);

                                        // Aktualizovat hodnotu
                                        quantityInput.value = item.quantity;

                                        // Aktualizovat tlačítko
                                        button.textContent = 'Update Cart';
                                        button.classList.remove('added');
                                        button.classList.add('update');

                                        // Zvýraznit změnu
                                        quantityInput.style.backgroundColor = "#e6ffe6";
                                        setTimeout(() => {
                                            quantityInput.style.backgroundColor = "";
                                        }, 1000);
                                    }
                                }
                            });
                        }

                        // Reset state for all buttons that are not in cart
                        document.querySelectorAll('.add-to-cart-btn.update').forEach(button => {
                            const onclick = button.getAttribute('onclick') || '';
                            const match = onclick.match(/addToCart\(this,\s*['"]([^'"]+)['"]\)/);

                            if (match) {
                                const buttonId = match[1];
                                const exists = cart.some(item => String(item.id) === buttonId);

                                if (!exists) {
                                    button.textContent = 'Add to Cart';
                                    button.classList.remove('update', 'added');
                                }
                            }
                        });

                    } catch (err) {
                        console.error("Error synchronizing UI:", err);
                        showNotification("Error synchronizing UI: " + err.message, "error");
                    }
                }

                // Initialize cart on page load - simplified version
                function initializeCart() {
                    console.log("===== INITIALIZING CART =====");
                    try {
                        const rawCart = localStorage.getItem('cart');
                        console.log("Raw cart data:", rawCart);

                        if (!rawCart) {
                            console.warn("No cart data in localStorage");
                            return [];
                        }

                        const cart = JSON.parse(rawCart);
                        console.log("Parsed cart:", cart);

                        if (!Array.isArray(cart)) {
                            console.error("Cart is not an array, resetting");
                            localStorage.setItem('cart', '[]');
                            return [];
                        }

                        if (cart.length > 0) {
                            console.log("Cart contains items:", cart.length);
                            cart.forEach(item => {
                                console.log(`- Item: ${item.id}, Quantity: ${item.quantity}`);
                            });
                        } else {
                            console.log("Cart is empty");
                        }

                        // First update cart count
                        updateCartCount();
                        console.log("Cart count updated");

                        return cart;
                    } catch (err) {
                        console.error("Error initializing cart:", err);
                        localStorage.setItem('cart', '[]');
                        return [];
                    }
                }

                // Function removed - no sync button needed

                // Direct synchronization from cart - simplified version
                function directSyncFromCart() {
                    try {
                        // Load cart directly
                        const cart = JSON.parse(localStorage.getItem('cart') || '[]');

                        // For each item in the cart, find its UI elements
                        cart.forEach(item => {
                            // Find all buttons with this ID in onclick attribute
                            document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
                                // Check onclick attribute for ID
                                const onclick = btn.getAttribute('onclick');
                                if (onclick && (onclick.includes(`'${item.id}'`) || onclick.includes(`"${item.id}"`))) {
                                    // Find container
                                    const container = btn.closest('.spawner');
                                    if (container) {
                                        // Najít input pro množství
                                        const input = container.querySelector('input.item-quantity');
                                        if (input) {
                                            // PŘÍMO NASTAVIT HODNOTY
                                            input.value = item.quantity;
                                            btn.textContent = 'Update Cart';
                                            btn.classList.remove('added');
                                            btn.classList.add('update');

                                            // Zvýraznit změnu
                                            input.style.backgroundColor = "#ffff99";
                                            setTimeout(() => {
                                                input.style.backgroundColor = "";
                                            }, 1500);
                                        }
                                    }
                                }
                            });
                        });

                        // Update item count
                        updateCartCount();

                        console.log("Cart successfully synchronized!");
                    } catch (error) {
                        console.error("Error during synchronization:", error);
                    }
                }

                // Initialization when page loads
                window.addEventListener('load', function() {
                    // Provést synchronizaci
                    setTimeout(directSyncFromCart, 300);

                    // Další pokus o synchronizaci po delším intervalu
                    setTimeout(directSyncFromCart, 1000);
                });

                // Inicializace při načtení DOM - rychlejší než čekat na load
                document.addEventListener('DOMContentLoaded', function() {
                    // Provést synchronizaci
                    setTimeout(directSyncFromCart, 300);
                });

                // Globální funkce pro ruční synchronizaci - vylepšená s alertem
                window.syncCart = function() {
                    try {
                        const rawCart = localStorage.getItem('cart');
                        console.log("Raw cart data:", rawCart);

                        if (!rawCart) {
                            console.log("Cart is empty");
                            return [];
                        }

                        const cart = JSON.parse(rawCart);
                        console.log("Parsed cart:", cart);

                        // Debug info
                        let cartItems = [];
                        let totalItems = 0;
                        cart.forEach(item => {
                            cartItems.push(`ID: ${item.id}, Množství: ${item.quantity}`);
                            totalItems += parseInt(item.quantity) || 1;
                        });

                        // Synchronizovat UI
                        syncButtonsWithCart(cart);
                        updateCartCount();

                        console.log("Cart was successfully synchronized");
                        console.log("Cart contents:", cartItems.join(" | "));

                        return cart;
                    } catch(err) {
                        console.error("Error during synchronization:", err);
                        showNotification("Error synchronizing cart: " + err.message, "error");
                        return [];
                    }
                };

                // Immediate initialization if page is already loaded
                if (document.readyState === 'complete') {
                    console.log("Page is already loaded, initializing immediately");
                    directSyncFromCart();
                }

                // Global function for cart synchronization accessible from console
                window.forceCartSync = function() {
                    try {
                        showNotification("Starting forced cart synchronization...", "info");
                        const rawCart = localStorage.getItem('cart');
                        console.log("Cart contents (raw):", rawCart);

                        if (!rawCart) {
                            showNotification("Cart is empty!", "warning");
                            return;
                        }

                        const cart = JSON.parse(rawCart);
                        const itemsList = [];
                        const cartItems = [];
                        let totalQuantity = 0;

                        cart.forEach(item => {
                            cartItems.push(`${item.id}: ${item.quantity}x`);
                            totalQuantity += parseInt(item.quantity || 1);
                        });

                        // Force update all items in UI
                        cart.forEach(item => {
                            // Find all buttons with this ID
                            document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
                                const onclick = btn.getAttribute('onclick') || '';
                                if (onclick.includes(`'${item.id}'`) || onclick.includes(`"${item.id}"`)) {
                                    // Get container and input
                                    const container = btn.closest('.spawner');
                                    if (container) {
                                        const input = container.querySelector('input.item-quantity');
                                        if (input) {
                                            // Update quantity value directly
                                            input.value = item.quantity;
                                            // Update button state
                                            btn.textContent = 'Update Cart';
                                            btn.classList.remove('added');
                                            btn.classList.add('update');

                                            // Highlight the change
                                            input.style.backgroundColor = "#ff9900";
                                            setTimeout(() => {
                                                input.style.backgroundColor = "";
                                            }, 2000);
                                        }
                                    }
                                }
                            });
                        });

                        // Update cart count
                        updateCartCount();

                        showNotification(`Synchronization complete!\n\nItems: ${cart.length}\nTotal quantity: ${totalQuantity}`, "success", 7000);

                        return cart;
                    } catch(err) {
                        console.error("Error during forced synchronization:", err);
                        showNotification("Error: " + err.message, "error");
                    }
                };

                // Add global function for easy access from console
                window.syncCartUI = function() {
                    try {
                        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
                        syncButtonsWithCart(cart);
                        updateCartCount();
                        console.log("Cart successfully synchronized manually");
                        return "Synchronization complete";
                    } catch(err) {
                        console.error("Error during manual synchronization:", err);
                        return "Error: " + err.message;
                    }
                };

                console.log("Current cart in spawners.php:", savedCart);

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

                // No welcome notification needed

                // Run after a short delay to ensure all elements are loaded
                setTimeout(() => {
                    document.querySelectorAll('.spawner').forEach(spawner => {
            const id = spawner.getAttribute('data-id');
            const button = spawner.querySelector('.add-to-cart-btn');
            const quantityInput = spawner.querySelector('input.item-quantity');
            const quantityControls = spawner.querySelector('.quantity-controls');

            // Convert old format cart check
            if (savedCart.length > 0 && typeof savedCart[0] !== 'object') {
                // Old format cart check
                if (savedCart.some(item => String(item) === String(id))) {
                    button.textContent = 'Update Cart';
                    button.classList.add('update');
                    console.log("Found item in cart (old format):", id);
                }
            } else {
                // New format cart check
                const cartItem = savedCart.find(item => String(item.id) === String(id));
                if (cartItem) {
                    // Update quantity input to show current quantity
                    if (quantityInput) {
                        quantityInput.value = cartItem.quantity;
                    }
                    button.textContent = 'Update Cart';
                    button.classList.add('update');
                    console.log("Found item in cart:", id, "Quantity:", cartItem.quantity);
                }
            }

            // Ensure all quantity inputs have initial values
            if (quantityInput && (!quantityInput.value || quantityInput.value < 1)) {
                quantityInput.value = 1;
            }

            // Add change event listener to quantity inputs
            if (quantityInput) {
                quantityInput.addEventListener('change', function() {
                    validateQuantity(this);
                });
            }
        });
                }, 100); // 100ms delay to ensure DOM is fully loaded
                </script>

        <!-- Notifications container will be created dynamically -->
    </body>
</html>

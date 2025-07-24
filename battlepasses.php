<?php
$db = new SQLite3("battlepasses.sqlite");

// Ujistíme se, že tabulka má sloupec price
$db->exec("ALTER TABLE Battlepasses ADD COLUMN price INTEGER DEFAULT 5;");

$results = $db->query("SELECT * FROM Battlepasses");

// Ujistíme se, že je tabulka správně vytvořena
$db->exec("CREATE TABLE IF NOT EXISTS Battlepasses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    value TEXT NOT NULL,
    image TEXT,
    prikaz TEXT,
    price INTEGER DEFAULT 5
)");
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

            // Default state: consent denied if choice not saved
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

        <title>Battle Pass - Sentry SMP</title>
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

        <script>
            // Funkce pro validaci množství
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

            // Funkce pro úpravu množství tlačítky + a -
            function adjustQuantity(button, delta) {
                try {
                    // Najít input element
                    const input = button.parentNode.querySelector('input.item-quantity');
                    if (!input) {
                        console.error("Quantity input not found");
                        return;
                    }

                    // Převést hodnoty na čísla
                    const oldValue = parseInt(input.value || "1");
                    delta = parseInt(delta);

                    // Spočítat novou hodnotu
                    let newValue = oldValue + delta;
                    if (newValue < 1) newValue = 1;

                    // Nastavit novou hodnotu
                    input.value = newValue;

                    // Log pro debug
                    console.log(`Quantity changed: ${oldValue} -> ${newValue} (${delta > 0 ? 'increased' : 'decreased'})`);

                    // Vizuální zpětná vazba
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

            // Function for adding to cart
            function addToCart(button, id) {
                try {
                    console.log("Adding to cart with ID:", id);

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

                    // Získat množství
                    let quantity = 1;
                    if (quantityInput) {
                        quantity = parseInt(quantityInput.value);
                        if (isNaN(quantity) || quantity < 1) {
                            quantity = 1;
                            quantityInput.value = 1;
                        }
                    }

                    // Debug informace
                    console.log("Adding to cart - ID:", id, "Quantity:", quantity);

                    // Získat cenu z popisu
                    // Get price from the description or discount info
                    const priceContainer = container.querySelector('.price-container');
                    const descriptionElement = container.querySelector('p');

                    // Default price
                    let price = 3;

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
                    }

                    // Detaily výpočtu pro uživatele
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

                    // Convert old cart format if needed
                    if (cart.length > 0 && typeof cart[0] !== 'object') {
                        const newCart = [];
                        cart.forEach((itemId) => {
                            newCart.push({id: itemId, quantity: 1, price: 0});
                        });
                        cart = newCart;
                    }

                    // Check if item already exists in cart
                    const idString = String(id);
                    const existingItemIndex = cart.findIndex(item => String(item.id) === idString);

                    // Zpráva pro uživatele
                    let feedbackMsg = '';

                    // Update cart
                    if (existingItemIndex !== -1) {
                        // Položka již existuje
                        const oldQuantity = parseInt(cart[existingItemIndex].quantity) || 1;

                        if (isUpdateMode) {
                            // Nahradit množství
                            cart[existingItemIndex].quantity = quantity;
                            feedbackMsg = `Updated to ${quantity} pcs`;
                        } else {
                            // Přidat k existujícímu množství
                            cart[existingItemIndex].quantity = oldQuantity + quantity;
                            feedbackMsg = `Added ${quantity} pcs. Total: ${cart[existingItemIndex].quantity}`;
                        }

                        cart[existingItemIndex].price = price;
                    } else {
                        // Přidat novou položku
                        cart.push({id: idString, quantity: quantity, price: price});
                        feedbackMsg = `Added ${quantity} pcs to cart!`;
                    }

                    // Save cart to localStorage
                    localStorage.setItem('cart', JSON.stringify(cart));

                    // Zobrazit feedback
                    // Use notification system for feedback
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

                    // No need for feedback hide timer - handled by notification system

                    // Aktualizovat počet v navigaci
                    updateCartCount();

                    // Synchronizovat UI po změně
                    setTimeout(function() {
                        syncCart();
                    }, 50);

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

            // Function to update cart item count
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
                    console.error("Error updating count:", err);
                    return [];
                }
            }

            // Function to synchronize UI buttons with cart
            function syncButtonsWithCart(cart) {
                try {
                    // Projít všechny spawnery
                    document.querySelectorAll('.spawner').forEach(container => {
                        // Najít tlačítko a input
                        const button = container.querySelector('.add-to-cart-btn');
                        const quantityInput = container.querySelector('input.item-quantity');

                        if (!button || !quantityInput) {
                            console.warn("Missing button or input in spawner:", container);
                            return;
                        }

                        // Získat ID položky z tlačítka
                        const onclickAttr = button.getAttribute('onclick');
                        if (!onclickAttr) return;

                        const match = onclickAttr.match(/addToCart\(this,\s*['"]([^'"]+)['"]\)/);
                        if (!match) return;

                        const buttonId = match[1];

                        // Zkontrolovat, zda je položka v košíku
                        if (cart && cart.length > 0) {
                            const existingItem = cart.find(item => String(item.id) === String(buttonId));

                            if (existingItem) {
                                // Položka je v košíku - aktualizovat tlačítko
                                button.textContent = 'Update Cart';
                                button.classList.remove('added');
                                button.classList.add('update');

                                // Aktualizovat input
                                quantityInput.value = existingItem.quantity;
                                console.log(`Synchronizuji tlačítko pro ${buttonId}: množství = ${existingItem.quantity}`);

                                // Zvýraznit aktualizaci
                                quantityInput.style.backgroundColor = "#e6ffe6";
                                setTimeout(() => {
                                    quantityInput.style.backgroundColor = "";
                                }, 1000);
                            } else {
                                // Položka není v košíku
                                if (button.classList.contains('update')) {
                                    button.textContent = 'Add to Cart';
                                    button.classList.remove('update', 'added');
                                }
                            }
                        } else {
                            // Košík je prázdný
                            if (button.classList.contains('update')) {
                                button.textContent = 'Add to Cart';
                                button.classList.remove('update', 'added');
                            }
                        }
                    });
                } catch (err) {
                    console.error("Error synchronizing UI:", err);
                }
            }

            // Resetování košíku (pro nouzové případy)
            function resetCart() {
                if (confirm("Are you sure you want to empty your cart? This action cannot be undone.")) {
                    localStorage.setItem('cart', '[]');
                    showNotification("Cart has been emptied.", "info");
                    updateCartCount();

                    // Resetovat všechna tlačítka
                    document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
                        btn.textContent = 'Add to Cart';
                        btn.classList.remove('added', 'update');
                    });
                }
            }

            // Zobrazit obsah košíku (pro debug)
            function debugCart() {
                try {
                    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
                    console.log("=== OBSAH KOŠÍKU ===");
                    console.log("Raw data:", localStorage.getItem('cart'));
                    console.log("Parsed data:", cart);

                    let totalItems = 0;
                    cart.forEach((item, index) => {
                        console.log(`Položka ${index+1}:`, {
                            id: item.id,
                            množství: item.quantity,
                            cena: item.price
                        });
                        totalItems += parseInt(item.quantity) || 1;
                    });

                    console.log("Celkem položek:", totalItems);

                    alert(`Košík obsahuje ${cart.length} unikátních položek s celkovým počtem ${totalItems} ks.`);
                } catch (err) {
                    console.error("Chyba při zobrazení košíku:", err);
                    alert("Chyba při čtení dat košíku: " + err.message);
                }
            }

            // Funkce pro synchronizaci košíku
            function syncCart() {
                try {
                    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
                    syncButtonsWithCart(cart);
                    updateCartCount();
                    console.log("Košík byl synchronizován");
                    return cart;
                } catch(err) {
                    console.error("Chyba při synchronizaci:", err);
                    return [];
                }
            }

            // Initialization when page loads
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    console.log("Initializing on DOM load");
                    try {
                        // Získat aktuální košík
                        const rawCart = localStorage.getItem('cart');
                        if (rawCart) {
                            const cart = JSON.parse(rawCart);
                            console.log("Načten košík z localStorage:", cart);

                            // Nejprve synchronizovat UI tlačítek
                            syncButtonsWithCart(cart);

                            // Pak aktualizovat počet v košíku
                            updateCartCount();
                        } else {
                            console.log("Košík je prázdný");
                        }


                    } catch (error) {
                        console.error("Chyba při inicializaci:", error);
                    }
                }, 100);
            });

            // Also run on complete page load to be sure
            window.addEventListener('load', function() {
                console.log("Initializing on page load");
                try {
                    // Získat aktuální košík
                    const rawCart = localStorage.getItem('cart');
                    if (rawCart) {
                        const cart = JSON.parse(rawCart);
                        console.log("Načten košík z localStorage:", cart);

                        // Nejprve synchronizovat UI tlačítek
                        syncButtonsWithCart(cart);

                        // Pak aktualizovat počet v košíku
                        updateCartCount();
                    } else {
                        console.log("Košík je prázdný");
                    }
                } catch (error) {
                    console.error("Chyba při inicializaci:", error);
                }
            });
        </script>
    </head>
    <body>
        <nav class="navbar" id="navbar-main"></nav>

        <!-- udělat hovery na grid-itemy -->
        <header id="header-main"></header>
        <div class="container">
            <div class="main-wrapper">
                <h1 class="main">Battle Pass</h1>
            </div>
            <div class="spawner-grid">
                <?php while ($row = $results->fetchArray(SQLITE3_ASSOC)): ?>
                    <div class="spawner" data-id="battlepass_<?= $row["id"] ?>">
                        <?php if (!empty($row["image"])): ?>
                            <img src="<?= htmlspecialchars(
                                $row["image"],
                            ) ?>" alt="image" class="spawner-image" width="100">
                        <?php endif; ?>
                        <h2><?= htmlspecialchars($row["name"]) ?></h2>
                        <?php if (!empty($row["sales"])): ?>
                            <?php
                            $originalPrice = $row["price"] ?? 3;
                            $discountPercent = $row["sales"];
                            $discountedPrice =
                                "€" .
                                number_format(
                                    $originalPrice *
                                        (1 - $discountPercent / 100),
                                    2,
                                    ".",
                                    "",
                                );
                            $formattedOriginal =
                                "€" . number_format($originalPrice, 2, ".", "");
                            ?>
                            <div class="price-container">
                                <p class="original-price"><?= $formattedOriginal ?></p>
                                <p class="discounted-price"><?= $discountedPrice ?></p>
                                <span class="discount-badge"><?= $discountPercent ?>% OFF</span>
                            </div>
                            <p><?= htmlspecialchars($row["value"]) ?></p>
                        <?php else: ?>
                            <?php
                            $orig = $row["price"] ?? "3";
                            $formatted = "€" . number_format($orig, 2, ".", "");
                            ?>
                            <p><strong><?= $formatted ?></strong></p>
                            <p><?= htmlspecialchars($row["value"]) ?></p>
                        <?php endif; ?>
                        <div class="cart-controls">
                            <div class="quantity-controls">
                                <button type="button" class="quantity-btn minus" onclick="adjustQuantity(this, -1)" title="Decrease quantity" aria-label="Decrease quantity"><i class="bi bi-dash"></i></button>
                                <input type="number" min="1" value="1" class="item-quantity" onchange="validateQuantity(this)" oninput="validateQuantity(this)" aria-label="Item quantity">
                                <button type="button" class="quantity-btn plus" onclick="adjustQuantity(this, 1)" title="Increase quantity" aria-label="Increase quantity"><i class="bi bi-plus"></i></button>
                            </div>
                            <button onclick="addToCart(this, 'battlepass_<?= $row[
                                "id"
                            ] ?>')" class="add-to-cart-btn">Add to Cart</button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>



        <footer id="footer-main"></footer>
        <script src="js/script.js"></script>
    </body>
</html>

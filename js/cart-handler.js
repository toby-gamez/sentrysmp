// cart-handler.js - Comprehensive cart handler with discount support
document.addEventListener('DOMContentLoaded', function() {
  console.log("Cart handler loaded");

  // Initialize cart from localStorage
  let cart = [];

  try {
    // Try to get cart from localStorage and parse it
    const cartData = localStorage.getItem("cart");
    if (cartData) {
      // Check for the problematic format - cart:"[...]";
      if (cartData.startsWith('cart:"') && cartData.endsWith('";')) {
        // Fix the format
        const fixedData = cartData.replace(/^cart:"/, '').replace(/";$/, '');
        localStorage.setItem("cart", fixedData);
        cart = JSON.parse(fixedData);
      } else {
        // Normal format
        cart = JSON.parse(cartData);
      }
    } else {
      // If no cart data exists, initialize an empty array
      cart = [];
    }

    // Verify the items follow the expected format (support for both old and new formats)
    if (cart.length > 0) {
      if (typeof cart[0] === 'object') {
        // This is the new format with price, discounts, etc.
        console.log("Cart is using object format");
      } else {
        // This is the old format with just IDs
        console.log("Converting old cart format to new format");
        const newCart = [];
        cart.forEach((id) => {
          newCart.push({
            id: id,
            quantity: 1,
            price: 0,
            originalPrice: 0,
            discountPercent: 0
          });
        });
        cart = newCart;
      }
    }

    // Save properly formatted cart back to localStorage
    localStorage.setItem("cart", JSON.stringify(cart));

  } catch (error) {
    console.error("Error loading cart from localStorage:", error);
    cart = [];
    localStorage.setItem("cart", JSON.stringify(cart));
  }

  // Main cart functions
  window.cartHandler = {
    getCart: function() {
      return cart;
    },

    saveCart: function() {
      localStorage.setItem("cart", JSON.stringify(cart));
    },

    addToCart: function(item) {
      // Check if item is already in cart
      const itemIndex = cart.findIndex(cartItem =>
        typeof cartItem === 'object' ? cartItem.id === item.id : cartItem === item.id
      );

      if (itemIndex !== -1) {
        // If item exists, increase quantity
        if (typeof cart[itemIndex] === 'object') {
          cart[itemIndex].quantity += item.quantity || 1;
        } else {
          // Replace old format with new format
          cart[itemIndex] = {
            id: item.id,
            quantity: item.quantity || 1,
            price: item.price || 0,
            originalPrice: item.originalPrice || 0,
            discountPercent: item.discountPercent || 0
          };
        }
      } else {
        // Add new item
        if (typeof item === 'object') {
          // Ensure all required properties exist
          const newItem = {
            id: item.id,
            quantity: item.quantity || 1,
            price: item.price || 0,
            originalPrice: item.originalPrice || 0,
            discountPercent: item.discountPercent || 0
          };
          cart.push(newItem);
        } else {
          // Simple ID format (backwards compatibility)
          cart.push({
            id: item,
            quantity: 1,
            price: 0,
            originalPrice: 0,
            discountPercent: 0
          });
        }
      }

      this.saveCart();
      this.renderCart();

      // Return the updated cart
      return cart;
    },

    updateItemQuantity: function(itemId, change) {
      const itemIndex = cart.findIndex(item =>
        typeof item === 'object' ? item.id === itemId : item === itemId
      );

      if (itemIndex !== -1) {
        if (typeof cart[itemIndex] === 'object') {
          cart[itemIndex].quantity += change;

          // Remove item if quantity is 0 or less
          if (cart[itemIndex].quantity <= 0) {
            cart.splice(itemIndex, 1);
          }
        } else {
          // Handle old format (just remove item if change is negative)
          if (change < 0) {
            cart.splice(itemIndex, 1);
          }
        }

        this.saveCart();
        this.renderCart();
      }
    },

    removeFromCart: function(itemId) {
      // Convert itemId to string for consistent comparison
      const idString = String(itemId);

      cart = cart.filter(item =>
        typeof item === 'object' ? String(item.id) !== idString : String(item) !== idString
      );

      this.saveCart();
      this.renderCart();

      // Show feedback to the user
      this.showNotification("Item removed from cart!", "success");
    },

    emptyCart: function() {
      if (cart.length === 0) {
        this.showNotification("Cart is already empty!", "info");
        return;
      }

      if (confirm("Are you sure you want to empty your cart?")) {
        cart = [];
        this.saveCart();
        this.renderCart();
        this.showNotification("Cart emptied successfully!", "success");
      }
    },

    calculateTotal: function() {
      let total = 0;

      cart.forEach(item => {
        if (typeof item === 'object') {
          total += (item.price || 0) * (item.quantity || 1);
        }
      });

      return total;
    },

    updateCartTotal: function() {
      const total = this.calculateTotal();
      const formattedTotal = new Intl.NumberFormat('cs-CZ', {
        style: 'currency',
        currency: 'CZK',
        minimumFractionDigits: 0
      }).format(total);

      // Find or create the total element
      let totalElement = document.querySelector(".cart-total");
      if (!totalElement) {
        totalElement = document.createElement("div");
        totalElement.className = "cart-total";
        const cartList = document.getElementById("cart-list");
        if (cartList) {
          cartList.parentNode.insertBefore(totalElement, cartList.nextSibling);
        }
      }

      totalElement.innerHTML = `<h3>Total: ${formattedTotal}</h3>`;
    },

    updateCheckoutButtonVisibility: function() {
      const checkoutButton = document.querySelector(".checkout-btn");
      const emptyButton = document.querySelector(".empty-btn");

      if (checkoutButton) {
        if (cart.length > 0) {
          checkoutButton.style.display = "inline-block";
          checkoutButton.disabled = false;
          checkoutButton.style.opacity = "1";
          checkoutButton.style.cursor = "pointer";
        } else {
          checkoutButton.style.display = "inline-block";
          checkoutButton.disabled = true;
          checkoutButton.style.opacity = "0.5";
          checkoutButton.style.cursor = "not-allowed";
        }
      }

      if (emptyButton) {
        emptyButton.style.display = cart.length > 0 ? "inline-block" : "none";
      }
    },

    renderCartItem: function(item, data) {
      const itemElement = document.createElement("div");
      itemElement.className = "item-card";
      itemElement.dataset.id = item.id;

      // Determine item name based on data and ID
      let itemName = "";
      let itemImage = "";
      let itemPrice = 0;

      if (data) {
        itemName = data.name || `Item #${item.id}`;
        itemImage = data.image || "";
        itemPrice = item.price || data.price || 0;
      } else {
        // Default values if no data provided
        if (String(item.id).startsWith('key_')) {
          itemName = `Key #${String(item.id).replace('key_', '')}`;
        } else {
          itemName = `Item #${item.id}`;
        }
      }

      // Create HTML structure for the item card
      let priceDisplay = '';

      if (item.originalPrice && item.originalPrice > item.price) {
        // Item has a discount
        priceDisplay = `
          <div class="cart-price-container">
            <div class="cart-regular-price">
              <p><span class="original-price">${item.originalPrice} Kč</span></p>
            </div>
            <div class="discount-badge">-${item.discountPercent}%</div>
            <p class="final-price">${item.price} Kč</p>
            <p class="unit-price">Price per unit: ${item.price} Kč</p>
          </div>
        `;
      } else {
        // Regular price
        priceDisplay = `
          <div class="cart-price-container">
            <p class="final-price">${itemPrice} Kč</p>
            <p class="unit-price">Price per unit: ${itemPrice} Kč</p>
          </div>
        `;
      }

      // Create the quantity controls
      const quantityControl = `
        <div class="quantity-control">
          <button class="quantity-btn decrease" onclick="cartHandler.updateItemQuantity('${item.id}', -1)">-</button>
          <span class="quantity">${item.quantity}</span>
          <button class="quantity-btn increase" onclick="cartHandler.updateItemQuantity('${item.id}', 1)">+</button>
        </div>
      `;

      // Image display if available
      const imageDisplay = itemImage ?
        `<img src="${itemImage}" alt="${itemName}" class="item-image">` : '';

      // Assemble the complete item card
      itemElement.innerHTML = `
        <h3>${itemName}</h3>
        ${imageDisplay}
        ${priceDisplay}
        ${quantityControl}
        <button class="remove-btn" onclick="cartHandler.removeFromCart('${item.id}')">Remove</button>
      `;

      return itemElement;
    },

    fetchSpawners: function() {
      return fetch("spawners.php")
        .then(response => response.text())
        .then(html => {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, "text/html");
          const allSpawners = doc.querySelectorAll(".spawner");

          // Process spawners from cart
          const cartSpawners = cart.filter(item => {
            const id = typeof item === 'object' ? item.id : item;
            return !String(id).startsWith('key_');
          });

          // Map to collect spawner data
          const spawnerData = {};

          // Extract data from all spawners
          allSpawners.forEach(spawner => {
            const id = spawner.getAttribute("data-id");
            const name = spawner.querySelector("h3")?.textContent || `Spawner #${id}`;
            const image = spawner.querySelector("img")?.src || "";
            const priceElement = spawner.querySelector(".price");
            let price = 0;

            if (priceElement) {
              const priceText = priceElement.textContent;
              const priceMatch = priceText.match(/\d+/);
              if (priceMatch) {
                price = parseInt(priceMatch[0], 10);
              }
            }

            spawnerData[id] = { name, image, price };
          });

          // Add spawners from cart to display
          const cartList = document.getElementById("cart-list");
          if (!cartList) return;

          cartSpawners.forEach(item => {
            const id = typeof item === 'object' ? item.id : item;
            const data = spawnerData[id];

            if (data) {
              // Update price info if not already set
              if (typeof item === 'object' && item.price === 0) {
                item.price = data.price;
              }

              // Create and add item to cart display
              const itemElement = this.renderCartItem(
                typeof item === 'object' ? item : { id, quantity: 1, price: data.price },
                data
              );
              cartList.appendChild(itemElement);
            }
          });
        })
        .catch(error => {
          console.error("Error fetching spawners:", error);
        });
    },

    fetchKeys: function() {
      return fetch("keys.php")
        .then(response => response.text())
        .then(html => {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, "text/html");
          const allKeys = doc.querySelectorAll(".spawner");

          // Process keys from cart
          const cartKeys = cart.filter(item => {
            const id = typeof item === 'object' ? item.id : item;
            return String(id).startsWith('key_');
          });

          // Map to collect key data
          const keyData = {};

          // Extract data from all keys
          allKeys.forEach(key => {
            const id = key.getAttribute("data-id");
            const name = key.querySelector("h3")?.textContent || `Key #${id.replace('key_', '')}`;
            const image = key.querySelector("img")?.src || "";
            const priceElement = key.querySelector(".price");
            let price = 0;

            if (priceElement) {
              const priceText = priceElement.textContent;
              const priceMatch = priceText.match(/\d+/);
              if (priceMatch) {
                price = parseInt(priceMatch[0], 10);
              }
            }

            keyData[id] = { name, image, price };
          });

          // Add keys from cart to display
          const cartList = document.getElementById("cart-list");
          if (!cartList) return;

          cartKeys.forEach(item => {
            const id = typeof item === 'object' ? item.id : item;
            const data = keyData[id];

            if (data) {
              // Update price info if not already set
              if (typeof item === 'object' && item.price === 0) {
                item.price = data.price;
              }

              // Create and add item to cart display
              const itemElement = this.renderCartItem(
                typeof item === 'object' ? item : { id, quantity: 1, price: data.price },
                data
              );
              cartList.appendChild(itemElement);
            }
          });
        })
        .catch(error => {
          console.error("Error fetching keys:", error);
        });
    },

    renderCart: function() {
      // Clear the cart list
      const cartList = document.getElementById("cart-list");
      if (!cartList) return;

      cartList.innerHTML = "";

      // Show a message if cart is empty
      if (cart.length === 0) {
        const emptyMessage = document.createElement("p");
        emptyMessage.className = "empty-cart-message";
        emptyMessage.textContent = "Your cart is empty";
        cartList.appendChild(emptyMessage);
      } else {
        // Get spawners first
        this.fetchSpawners()
          .then(() => {
            // Then get keys
            return this.fetchKeys();
          })
          .then(() => {
            // Update checkout button visibility and cart total
            this.updateCheckoutButtonVisibility();
            this.updateCartTotal();

            // Update cart count badge if available
            if (typeof updateCartCount === "function") {
              updateCartCount();
            }

            console.log("Cart rendering complete");
          });
      }
    },

    showNotification: function(message, type = 'success', duration = 3000) {
      // Create notification container if it doesn't exist
      let notificationContainer = document.querySelector('.notification-container');
      if (!notificationContainer) {
        notificationContainer = document.createElement('div');
        notificationContainer.className = 'notification-container';
        document.body.appendChild(notificationContainer);
      }

      // Create notification element
      const notification = document.createElement('div');
      notification.className = `notification ${type}`;
      notification.innerHTML = `
        <div class="notification-content">
          <span class="message">${message}</span>
          <button class="close-btn">&times;</button>
        </div>
      `;

      // Add to container
      notificationContainer.appendChild(notification);

      // Add click handler for close button
      notification.querySelector('.close-btn').addEventListener('click', function() {
        notification.classList.add('dismissing');
        setTimeout(() => {
          if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
          }
        }, 300);
      });

      // Auto-dismiss after duration
      setTimeout(() => {
        notification.classList.add('dismissing');
        setTimeout(() => {
          if (notification && notification.parentNode) {
            notification.parentNode.removeChild(notification);
          }
        }, 300);
      }, duration);

      return notification;
    }
  };

  // Initialize checkout button
  window.cartHandler.updateCheckoutButtonVisibility();

  // Initialize the cart display
  window.cartHandler.renderCart();

  // Expose cart functions globally
  window.updateItemQuantity = function(itemId, change) {
    window.cartHandler.updateItemQuantity(itemId, change);
  };

  window.removeFromCart = function(itemId) {
    window.cartHandler.removeFromCart(itemId);
  };

  window.emptyCart = function() {
    window.cartHandler.emptyCart();
  };

  window.renderCart = function() {
    window.cartHandler.renderCart();
  };

  console.log("Cart handler initialization complete");
});

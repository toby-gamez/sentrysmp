document.addEventListener("DOMContentLoaded", function () {
  var navbar = document.getElementById("navbar-main");
  var navbarContent = `
      <div class="navbar-container">
        <a href="/" class="logonav">
          <img src="images/logo.png" class="logoimage" width="150px" alt="" />
        </a>
        <button class="toggle-btn" id="toggleBtn" style="text-align: center; margin-top: auto">&#9776;</button>
        <ul class="nav-links" id="navLinks">
          <li><a href="/"><i class="bi bi-house"></i> Home</a></li>
          <li><a href="about.html"><i class="bi bi-info-circle"></i> About Server</a></li>
          <li><a href="our-team.php"><i class="bi bi-people"></i> Our Team</a></li>
          <li><a href="vote.html"><i class="bi bi-heart"></i> Vote For Us</a></li>
          <li><a href="login.php"><i class="bi bi-person"></i> Admin</a></li>
          <label class="switch">
              <input type="checkbox" id="modeToggle" />
              <span class="slider"></span>
          </label>
        </ul>
      </div>
      <div id="cookie-banner">
          <span>We use cookies to analyze traffic and improve your experience. By continuing to browse, you agree to our use of cookies. <a href="privacy-policy.html">Learn more</a></span>
          <div>
              <button class="accept" onclick="acceptCookies()">
                  Přijmout
              </button>
              <button class="decline" onclick="declineCookies()">
                  Odmítnout
              </button>
          </div>
      </div>
    `;
  navbar.innerHTML = navbarContent;

  // Toggle hlavního menu
  const toggleBtn = document.getElementById("toggleBtn");
  const navLinks = document.getElementById("navLinks");

  toggleBtn.addEventListener("click", () => {
    navLinks.classList.toggle("active");
  });

  // Aktualizace počtu položek v košíku
  function updateCartCount() {
    const cartCountElement = document.getElementById("cart-count");
    if (cartCountElement) {
      try {
        let cart = JSON.parse(localStorage.getItem("cart") || "[]");
        let totalItems = 0;

        if (cart.length > 0) {
          // Nový formát košíku (objekty s množstvím)
          if (typeof cart[0] === "object") {
            cart.forEach((item) => {
              totalItems += parseInt(item.quantity) || 1;
            });
          } else {
            // Starší formát (pole ID)
            totalItems = cart.length;
          }
        }

        console.log("Cart count updated: " + totalItems + " items");
        cartCountElement.textContent = totalItems;

        // Skrytí/zobrazení počítadla podle množství
        if (totalItems > 0) {
          cartCountElement.style.display = "inline-block";
        } else {
          cartCountElement.style.display = "none";
        }
      } catch (error) {
        console.error("Error updating cart count:", error);
        cartCountElement.textContent = "!";
        cartCountElement.style.display = "inline-block";
      }
    } else {
      console.warn("Cart count element not found in the DOM");
    }
  }

  // Aktualizace při načtení stránky
  updateCartCount();

  // Pravidelná aktualizace počtu položek v košíku
  setInterval(updateCartCount, 2000);

  // Nastavení stylu pro počítadlo košíku
  const style = document.createElement("style");
  style.textContent = `
    .cart-count {
      background-color: #ff5722;
      color: white;
      border-radius: 50%;
      padding: 2px 6px;
      font-size: 12px;
      font-weight: bold;
      margin-left: 5px;
      position: relative;
      top: -2px;
    }

    .cart-link {
      position: relative;
    }
  `;
  document.head.appendChild(style);

  // Dropdown toggle na mobilu
  const dropdown = document.getElementById("dropdown");
  const dropdownToggle = document.getElementById("dropdownToggle");

  if (dropdown && dropdownToggle) {
    dropdownToggle.addEventListener("click", (e) => {
      if (window.innerWidth <= 768) {
        e.preventDefault();
        dropdown.classList.toggle("open");
      }
    });
  }

  var footer = document.getElementById("footer-main");
  var footerContent = `
    <div class="footer-section">
        <div class="footer-column">
            <h3>Support</h3>
            <ul>
                <li><a href="support.html">Support</a></li>
                <li><a href="https://discord.gg/gXrXMwpuH4" target="_blank">Report Issue</a></li>
                <li><a href="vote.html">Vote For Us</a></li>
            </ul>
        </div>
        <div class="footer-column">
            <h3>About Us</h3>
            <ul>
                <li><a href="about.html">About server</a></li>
                <li><a href="our-team.php">Our Team</a></li>
                <li><a href="news.html">News</a></li>
                <li><a href="changelog.html">Web Changelog</a></li>
            </ul>
        </div>
        <div class="footer-column">
            <h3>Rules</h3>
            <ul>
                <li><a href="rules.html">Server Rules</a></li>
                <li><a href="rules-minecraft.html">Minecraft Server Rules</a></li>
                <li><a href="privacy-policy.html">Privacy Policy</a></li>
                <li><a href="terms-of-use.html">Terms of Use</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">

        <p class="footer-p">© 2025 Sentry SMP. All rights reserved.</p>
        <p class="mojang-notice">We are not affiliated with or endorsed by Mojang, AB.</p>
    </div>
    <p class="web-version">2.3</p>
  `;
  footer.innerHTML = footerContent;

  var header = document.getElementById("header-main");
  var headerContent = `
    <div class="header-background"></div>
    <div class="grid">
        <a href="/"
            ><div class="logo grid-item">
                <img
                    src="images/logo.png"
                    class="logo"
                    alt="logo"
                /></div
        ></a>
        <i class="bi bi-play mobile-none" style="font-size: 3rem"></i>
        <div
            class="grid-item"
            id="copy-ip"
            title="Copy IP address"
            style="padding-left: 5px; cursor: pointer; min-width: 210px"
            onclick="(() => {
                navigator.clipboard.writeText('mc.sentrysmp.eu');
                const el = document.querySelector('#copy-ip .big');
                el.textContent = 'IP COPIED';
                setTimeout(() => {
                    el.textContent = 'MC.SENTRYSMP.EU';
                }, 2000);
            })()"
        >
        <span>
          <small>PLAYING <span id="player-count">0</span></small>
          <a href="join.html" title="How can I join?" style="color:inherit;text-decoration:none;">
            <i class="bi bi-info-circle"></i>
          </a>
        </span>
            <br />
            <span class="big">MC.SENTRYSMP.EU</span>
        </div>
        <a href="https://discord.gg/gXrXMwpuH4" target="_blank"
            ><div
                style="padding-right: 5px; text-align: right"
                class="grid-item"
                title="Join our Discord"
            >
                <span><small><span id="discord-count">0</span> MEMBERS</small></span>
                <br />
                <span class="big"
                    >JOIN<span style="visibility: hidden">.</span
                    >DISCORD</span
                >
            </div></a
        >
        <i
            style="font-size: 3rem"
            class="bi bi-discord mobile-none"
        ></i>
        </div>
        <div id="login-box-outer">
        <div id="login-box" >
        </div>
        </div>
  `;
  header.innerHTML = headerContent;

  fetch("discord.php")
    .then((res) => res.json())
    .then((data) => {
      if (data.total !== undefined) {
        document.getElementById("discord-count").textContent = data.total;
      } else {
        document.getElementById("discord-count").textContent = "Can't load";
      }
    })
    .catch(() => {
      document.getElementById("discord-count").textContent = "Error:connection";
    });

  // Opravená funkce pro načítání počtu hráčů
  function fetchPlayerCount() {
    fetch("player_count.php")
      .then((response) => response.json())
      .then((data) => {
        const el = document.getElementById("player-count");
        if (data.status === "success" && el) {
          el.textContent = data.players; // jen číslo
        }
      })
      .catch((error) => {
        console.error("Chyba při načítání dat:", error);
      });
  }

  // Volání funkcí pro načtení dat při načtení stránky
  window.onload = function () {
    fetchPlayerCount();
    updateCartCount(); // Zajistí, že počet položek v košíku se aktualizuje i když je stránka plně načtena
  };

  // Nastavit window.updateCartCount pro možnost volání z jiných skriptů
  window.updateCartCount = updateCartCount;

  // Funkce pro výpočet celkové hodnoty košíku
  function getCartTotal() {
    let cart = JSON.parse(localStorage.getItem("cart") || "[]");
    let total = 0;
    if (cart.length > 0 && typeof cart[0] === "object") {
      cart.forEach((item) => {
        let price = parseFloat(item.price) || 0;
        let quantity = parseInt(item.quantity) || 1;
        total += price * quantity;
      });
    }
    return total;
  }

  fetch("login-status-players.php")
    .then((res) => res.json()) // Zpracujeme odpověď jako JSON
    .then((data) => {
      const box = document.getElementById("login-box");

      if (!data.logged_in) {
        // Pokud uživatel není přihlášen, zobrazíme tlačítko pro přihlášení
        box.classList.remove("logged-in");
        box.classList.add("not-logged-in");
        box.innerHTML = `
              <div class="login-info">
                    <img src="https://minotar.net/helm/MHF_Steve/100" alt="skin" class="skin-img">
                    <strong class="username">Guest</strong>
                  </div>
                  <div class="actions">
                  <a href="login-players.php"><button class="logout-button">Login</button></a>
                  </div>
            `;
      } else {
        // Pokud je přihlášen, zobrazíme jeho skin a jméno
        // Správná URL podle edice
        let skinUrl;
        if (data.edition === "java") {
          // Pro Java účty použijeme jejich skutečný skin
          skinUrl = `https://minotar.net/helm/${encodeURIComponent(data.username)}/100`;
        } else {
          // Pro Bedrock a cracked účty použijeme výchozí Steve skin
          skinUrl = "https://minotar.net/helm/MHF_Steve/100";
        }

        // Získání celkové hodnoty košíku
        let cartTotal = getCartTotal();
        let cartTotalHtml = "";
        if (cartTotal > 0) {
          cartTotalHtml = `€<span id="cart-total-value">${cartTotal.toFixed(2)}</span>`;
        }

        box.classList.remove("not-logged-in");
        box.classList.add("logged-in");
        box.innerHTML = `
            <div class="login-info">
                  <img src="${skinUrl}" alt="skin" class="skin-img" onerror="this.src='https://minotar.net/helm/MHF_Steve/100'">
                  <strong class="username">${data.username}</strong>
                </div>

                <div class="actions">
                ${cartTotalHtml}
                <a href="cart.html"><button class="logout-button secondary"><i class="bi bi-cart"></i> Cart</button></a>
                <a href="logout-players.php"><button class="logout-button">Logout</button></a>
                </div>
            `;

        // Aktualizace hodnoty při změně košíku (např. v jiném okně)
        window.addEventListener("storage", function (e) {
          if (e.key === "cart") {
            let newTotal = getCartTotal();
            const valueEl = document.getElementById("cart-total-value");
            if (valueEl) valueEl.textContent = newTotal.toFixed(2);
          }
        });
      }
    })
    .catch((err) => {
      console.error("Error loading status:", err);
      document.getElementById("login-box").innerText = "Error loading status.";
    });
});
document.addEventListener("DOMContentLoaded", function () {
  // Dark mode toggle functionality
  function initThemeSwitcher() {
    const toggleButton = document.getElementById("modeToggle");
    const body = document.body;

    function enableDarkMode() {
      body.classList.add("dark");

      // Přidání tmavého režimu pro všechny relevantní selektory
      document.querySelectorAll("body").forEach((element) => {
        element.classList.add("dark");
      });

      // Uložíme stav do localStorage
      localStorage.setItem("darkMode", "enabled");
    }

    function disableDarkMode() {
      body.classList.remove("dark");

      // Odstranění tmavého režimu pro všechny relevantní selektory
      document.querySelectorAll("body").forEach((element) => {
        element.classList.remove("dark");
      });

      // Uložíme stav do localStorage
      localStorage.setItem("darkMode", "disabled");
    }

    // Apply dark mode if previously enabled
    if (localStorage.getItem("darkMode") === "enabled") {
      console.log("Dark mode enabled from localStorage");
      enableDarkMode();
      if (toggleButton) toggleButton.checked = true;
    }

    if (toggleButton) {
      toggleButton.addEventListener("change", function () {
        console.log("Dark mode toggle clicked");

        if (body.classList.contains("dark")) {
          disableDarkMode();
        } else {
          enableDarkMode();
        }
      });
    }
  }

  initThemeSwitcher(); // Zavolání funkce uvnitř DOMContentLoaded
});

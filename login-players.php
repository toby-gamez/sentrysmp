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

        <title>Login - Sentry SMP</title>
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
                <h1 class="main">Login</h1>
            </div>
            <div class="skin-preview-div">
                <img
                    id="skin-preview"
                    src="https://minotar.net/helm/MHF_Steve/100"
                    alt="Náhled skinu"
                />
            </div>
            <form method="post" action="login-player.php">
                <input
                    type="text"
                    name="usernamemc"
                    id="usernamemc"
                    placeholder="Your Minecraft username"
                    required
                />
                <select name="edition" id="edition">
                    <option value="java">Java</option>
                    <option value="bedrock">Bedrock</option>
                    <option value="cracked">Java (cracked)</option>
                </select>
                <button type="submit">Login</button>
            </form>
            <p>*Cracked is supported, just select "Java (cracked)".</p>
        </div>
        <footer id="footer-main"></footer>
        <script src="js/script.js"></script>
        <script>
            const usernamemc = document.getElementById("usernamemc");
            const editionSelect = document.getElementById("edition");
            const previewImg = document.getElementById("skin-preview");

            async function updatePreview() {
                const usernamemc_value = usernamemc.value.trim();
                const edition = editionSelect.value;

                if (usernamemc_value === "") {
                    previewImg.src = "https://minotar.net/helm/MHF_Steve/100";
                    return;
                }

                // Pro bedrock a cracked použijeme výchozí Steve skin
                if (edition === "bedrock" || edition === "cracked") {
                    previewImg.src = "https://minotar.net/helm/MHF_Steve/100";
                    return;
                }

                // Pro Java účty použijeme jejich skutečný skin
                previewImg.src = "https://minotar.net/helm/" + encodeURIComponent(usernamemc_value) + "/100";

                // Pokud chceme i nadále používat API endpoint pro Java edici:
                /*
                if (edition === "java") {
                    try {
                        const response = await fetch(
                            "preview-players.php?usernamemc=" +
                                encodeURIComponent(usernamemc_value)
                        );
                        const data = await response.json();
                        if (data.success) {
                            previewImg.src = data.skin;
                        }
                    } catch (e) {
                        console.error("Error fetching skin:", e);
                    }
                }
                */
            }

            usernamemc.addEventListener("input", updatePreview);
            editionSelect.addEventListener("change", updatePreview);

            // Run updatePreview once on page load to handle initial state
            document.addEventListener("DOMContentLoaded", updatePreview);
        </script>
    </body>
</html>

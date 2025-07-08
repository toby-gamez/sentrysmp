<?php
// Basic error handling and reporting
ini_set("display_errors", 0);
error_reporting(E_ALL);

try {
    // Include Stripe library
    require "vendor/autoload.php";

    // Load environment variables
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    // Set your Stripe API key
    $stripeKey = $_ENV["STRIPE_SECRET_KEY"] ?? null;
    if (!$stripeKey) {
        throw new Exception(
            "STRIPE_SECRET_KEY environment variable is not set"
        );
    }
    \Stripe\Stripe::setApiKey($stripeKey);

    // Get price from query parameter
    $price = isset($_GET["price"]) ? floatval($_GET["price"]) : 0;
    if ($price <= 0) {
        throw new Exception("Invalid price");
    }

    // Get username if provided
    $username = isset($_GET["username"]) ? $_GET["username"] : "Customer";

    // Get cart if provided
    $cart = isset($_GET["cart"]) ? $_GET["cart"] : "";

    // Try to decode and validate the cart structure for a better product name
    $cartItems = [];
    try {
        $decodedCart = json_decode(urldecode($cart), true);
        if (is_array($decodedCart)) {
            $itemCount = count($decodedCart);
            foreach ($decodedCart as $item) {
                if (isset($item["quantity"]) && $item["quantity"] > 0) {
                    $cartItems[] = $item;
                }
            }
        }
    } catch (Exception $e) {
        // Silently ignore JSON parsing errors
    }

    // Convert price to cents for Stripe
    $priceInCents = round($price * 100);

    // Product description
    $itemCount = count($cartItems);
    $productName =
        $itemCount > 0
            ? "Minecraft Items ($itemCount items) for $username"
            : "Minecraft Items for $username";

    // Create a Stripe checkout session
    $session = \Stripe\Checkout\Session::create([
        "payment_method_types" => [
            "card", // Aktivní (včetně Google Pay, Apple Pay a Link)
            "bancontact", // Aktivní
            "eps", // Aktivní
            "klarna", // Aktivní
            "link", // Aktivní
            "mobilepay", // Aktivní
            "multibanco", // Aktivní
            "revolut_pay", // Aktivní
            "twint", // Aktivní
        ],
        "line_items" => [
            [
                "price_data" => [
                    "currency" => "EUR",
                    "product_data" => [
                        "name" => $productName,
                    ],
                    "unit_amount" => $priceInCents,
                ],
                "quantity" => 1,
            ],
        ],
        "mode" => "payment",
        "success_url" =>
            "https://sentrysmp.eu/success.php?session_id={CHECKOUT_SESSION_ID}&cart=" .
            urlencode($cart) .
            "&username=" .
            urlencode($username) .
            "&total=" .
            $price,
        "cancel_url" => "https://sentrysmp.eu/checkout.php",
        "metadata" => [
            "username" => $username,
            "price_eur" => $price,
            "item_count" => $itemCount,
        ],
    ]);

    // Return JSON response with checkout URL for JavaScript to use
    header("Content-Type: application/json");
    echo json_encode(["url" => $session->url]);
    exit();
} catch (Exception $e) {
    // Log error
    error_log("Stripe Error: " . $e->getMessage());

    // Redirect to error page
    header("Location: /checkout?error=payment");
    exit();
}

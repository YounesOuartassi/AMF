<?php
// Include PHPMailer
require 'PHPMailer/PHPMailer-master/src/Exception.php';
require 'PHPMailer/PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include your database connection
include('db_connect.php');

// Start session
session_start();


// Initialize a flag to show the modal
$showLoginModal = false;
$loginMessage = "";

// Function to get the user ID (Assuming you have a session variable 'user_id' set upon login)
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

if ($user_id == 0) {
    // Set the flag to show the login modal
    $showLoginModal = true;
    $loginMessage = "Veuillez vous connecter pour voir votre panier";
}

// Handle removing an item from the cart
if (isset($_GET['remove'])) {
    $item_id = intval($_GET['remove']);
    
    // Delete item from the cart for the current user
    $delete_query = "DELETE FROM order_items WHERE order_id = (SELECT MAX(order_id) FROM orders WHERE user_id = $user_id) AND product_id = $item_id";
    mysqli_query($conn, $delete_query);
    
    // Redirect to cart page
    header("Location: cart.php");
    exit;
}

// Handle quantity update
if (isset($_POST['update_quantity'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    
    if ($quantity > 0) {
        $update_quantity_query = "
            UPDATE order_items
            SET quantity = $quantity
            WHERE order_id = (SELECT MAX(order_id) FROM orders WHERE user_id = $user_id AND status = 'cart')
            AND product_id = $product_id
        ";
        mysqli_query($conn, $update_quantity_query);
    }
    
    // Redirect to cart page
    header("Location: cart.php");
    exit;
}

// Get the current cart
$query = "
    SELECT * FROM order_items oi
    JOIN product p ON oi.product_id = p.product_id
    WHERE oi.order_id = (SELECT MAX(order_id) FROM orders WHERE user_id = $user_id AND status = 'cart')
";
$result = mysqli_query($conn, $query);

// Calculate subtotal for cart
$subtotal_query = "
    SELECT SUM(p.price_per_unit * oi.quantity) AS subtotal
    FROM order_items oi
    JOIN product p ON oi.product_id = p.product_id
    WHERE oi.order_id = (SELECT MAX(order_id) FROM orders WHERE user_id = $user_id AND status = 'cart')
";
$subtotal_result = mysqli_query($conn, $subtotal_query);
$subtotal = mysqli_fetch_assoc($subtotal_result)['subtotal'];

// Get user's delivery address
$address_query = "
    SELECT address, postal_code, city
    FROM users
    WHERE user_id = $user_id
";
$address_result = mysqli_query($conn, $address_query);
$address = mysqli_fetch_assoc($address_result);

// Handle address update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_address'])) {
    $new_address = mysqli_real_escape_string($conn, $_POST['address']);
    $new_postal_code = mysqli_real_escape_string($conn, $_POST['postal_code']);
    $new_city = mysqli_real_escape_string($conn, $_POST['city']);

    if (!empty($new_address) && !empty($new_postal_code) && !empty($new_city)) {
        $update_address_query = "
            UPDATE users
            SET address = '$new_address', postal_code = '$new_postal_code', city = '$new_city'
            WHERE user_id = $user_id
        ";
        if (mysqli_query($conn, $update_address_query)) {
            $message = 'Adresse mise à jour avec succès.';
            $_SESSION['update_message'] = $message;
        } else {
            $_SESSION['update_message'] = 'Une erreur est survenue lors de la mise à jour.';
        }
    } else {
        $_SESSION['update_message'] = 'Tous les champs sont obligatoires.';
    }

    // Refresh the page to show updated address
    header("Location: cart.php");
    exit;
}
// Handle checkout and send order email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    // Retrieve updated cart data
    $order_items = [];
    $result = mysqli_query($conn, $query); // Re-run query to get cart items
    
    while ($row = mysqli_fetch_assoc($result)) {
        $order_items[] = [
            'name' => $row['name'],
            'quantity' => $row['quantity'],
            'total' => $row['price_per_unit'] * $row['quantity']
        ];
    }

    // Update existing order status to 'ordered'
    $update_order_query = "
        UPDATE orders
        SET status = 'ordered', total_amount = $subtotal, order_date = NOW()
        WHERE user_id = $user_id AND status = 'cart'
    ";
    if (mysqli_query($conn, $update_order_query)) {
        // Clear the cart
        $clear_cart_query = "DELETE FROM order_items WHERE order_id = (SELECT MAX(order_id) FROM orders WHERE user_id = $user_id AND status = 'cart')";
        mysqli_query($conn, $clear_cart_query);

        // Send an email to the admin
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; // Your SMTP server
            $mail->SMTPAuth   = true;
            $mail->Username   = 'oiyounes2.0@gmail.com'; // Your SMTP username
            $mail->Password   = 'danz lcmf gbtw qzbu'; // Your SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom('oiyounes2.0@gmail.com', 'naisoo');
            $mail->addAddress('oiyounes2.0@gmail.com');

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Nouvelle commande';
            $mail->Body    = "Vous avez une nouvelle commande.<br><br>";
            foreach ($order_items as $item) {
                $mail->Body .= "{$item['name']} - Quantité: {$item['quantity']} - Total: €{$item['total']}<br>";
            }
            $mail->Body .= "<br>Sous-total: €" . number_format($subtotal, 2);
            $mail->Body .= "<br>Adresse de livraison: {$address['address']}, {$address['postal_code']} {$address['city']}";

            $mail->send();
            $_SESSION['checkout_message'] = 'Votre commande a été effectuée avec succès.';
        } catch (Exception $e) {
            $_SESSION['checkout_message'] = "Une erreur est survenue lors de l'envoi de l'email: {$mail->ErrorInfo}";
        }

        // Refresh to display success message
        header("Location: cart.php");
        exit;
    } else {
        // Log the error
        error_log("Order update failed: " . mysqli_error($conn));
        $_SESSION['checkout_message'] = 'Une erreur est survenue lors de la passation de la commande.';
        
        // Refresh to display failure message
        header("Location: cart.php");
        exit;
    }
}

include 'modals/modals_php.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Au Maraicher Des Flandres</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="./images/logo2.png" type="image/icon type">

    <link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,500,600,700,800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Lora:400,400i,700,700i&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Amatic+SC:400,700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="css/open-iconic-bootstrap.min.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <link rel="stylesheet" href="css/owl.theme.default.min.css">
    <link rel="stylesheet" href="css/magnific-popup.css">
    <link rel="stylesheet" href="css/aos.css">
    <link rel="stylesheet" href="css/ionicons.min.css">
    <link rel="stylesheet" href="css/bootstrap-datepicker.css">
    <link rel="stylesheet" href="css/jquery.timepicker.css">
    <link rel="stylesheet" href="css/flaticon.css">
    <link rel="stylesheet" href="css/icomoon.css">
    <link rel="stylesheet" href="css/style.css">
</head>    
<body>
    <?php include 'components/navbar.php'; ?>

    <div class="hero-wrap hero-bread" style="background-image: url('images/bg_1.jpg');">
        <div class="container">
            <div class="row no-gutters slider-text align-items-center justify-content-center">
                <div class="col-md-9 ftco-animate text-center">
                    <p class="breadcrumbs"><span class="mr-2"><a href="index.html">Home</a></span> <span>Cart</span></p>
                    <h1 class="mb-0 bread">My Cart</h1>
                </div>
            </div>
        </div>
    </div>
    <!-- Display Checkout Message -->
    <div class="container mt-4">
    <?php if (isset($_SESSION['checkout_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['checkout_message']; ?>
        </div>
        <?php unset($_SESSION['checkout_message']); ?>
    <?php endif; ?>
    </div>

    <!-- Login Modal -->
    <?php include 'modals/login.php'; ?>
    <!-- Register Modal -->
    <?php include 'modals/register.php'; ?>
    <!-- Welcome Modal -->
    <?php include 'modals/welcome.php'; ?>

    <!-- Update Address Modal -->
    <div class="modal fade add" tabindex="-1" role="dialog" aria-labelledby="updateAddressModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateAddressModalLabel">Changer l'adresse de Livraison</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" class="info">
                        <div class="form-group">
                            <label for="address">Adresse:</label>
                            <input type="text" name="address" class="form-control text-left px-3" value="<?php echo htmlspecialchars($address['address']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="postal_code">Code Postal:</label>
                            <input type="number" name="postal_code" class="form-control text-left px-3" value="<?php echo htmlspecialchars($address['postal_code']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="city">Ville:</label>
                            <input type="text" name="city" class="form-control text-left px-3" value="<?php echo htmlspecialchars($address['city']); ?>" required>
                        </div>
                        <div class="modal-footer">
                        <input type="hidden" name="update_address" value="1">
                        <button type="submit" class="btn btn-primary">Mettre à jour</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <section class="ftco-section ftco-cart">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="cart-list">
                        <form method="POST" action="cart.php">
                            <table class="table">
                                <thead class="thead-primary">
                                    <tr class="text-center">
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                        <th>Nom du produit</th>
                                        <th>Prix</th>
                                        <th>Quantité</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr class="text-center">
                                            <td class="product-remove">
                                                <a href="?remove=<?= $row['product_id'] ?>"><span class="ion-ios-close"></span></a>
                                            </td>
                                            <td class="image-prod">
                                                <div class="img" style="background-image:url(<?= htmlspecialchars($row['image_url']); ?>);"></div>
                                            </td>

                                            <td class="product-name">
                                                <h3><?= htmlspecialchars($row['name']); ?></h3>
                                            </td>
                                            <td class="price">€<?= htmlspecialchars($row['price_per_unit']); ?></td>
                                            <td class="quantity">
                                                <div class="input-group mb-3">
                                                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($row['product_id']); ?>">
                                                    <input type="number" name="quantity" class="quantity form-control input-number" value="<?= htmlspecialchars($row['quantity']); ?>" min="1" max="100">
                                                    <div class="input-group-append">
                                                        <button type="submit" name="update_quantity" class="btn btn-outline-secondary">Mettre à jour</button>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="total">€<?= htmlspecialchars($row['price_per_unit'] * $row['quantity']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </form>
                    </div>
                </div>                
                
        <!-- User Address -->
        <div class="col-lg-5 mt-5 cart-wrap">
                <div class="cart-wrap-inner d-flex">
                    <div class="cart-total mb-3">
                        <div class="address-info mb-3">
                            <h4>Adresse de Livraison</h4>
                            <p><?= htmlspecialchars($address['address']); ?><br>
                               <?= htmlspecialchars($address['postal_code']); ?> <?= htmlspecialchars($address['city']); ?>
                            </p>
                            <button type="button" class="btn btn-secondary py-2 px-4" data-toggle="modal" data-target=".add">Changer l'adresse</button>
                        </div>
                    </div>
                </div>
            </div>
        <!-- Cart Totals and Address -->
        <div class="col-lg-5 mt-5 cart-wrap">
    <div class="cart-wrap-inner d-flex">
        <div class="cart-total mb-3">
            <h3>Total</h3>
            <p class="d-flex">
                <span>Sous-total</span>
                <span>€<?= number_format($subtotal, 2); ?></span>
            </p>

            <!-- Uncomment this if you want to show shipping cost -->
            <!-- <p class="d-flex">
                <span>Livraison</span>
                <span>€0.00</span>
            </p> -->
            <hr>
            <p class="d-flex total-price">
                <span>Total</span>
                <span>€<?= number_format($subtotal, 2); ?></span>
            </p>
        </div>
    </div>

    <!-- Checkout Button -->
    <button type="button" class="btn btn-primary py-3 px-4" data-toggle="modal" data-target="#checkoutModal">Commander</button>
    </div>
                
    </section>
        
  <!-- loader -->
  <div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px"><circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/><circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00"/></svg></div>

  <!-- Checkout Confirmation Modal -->
  <div class="modal fade" id="checkoutModal" tabindex="-1" role="dialog" aria-labelledby="checkoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="checkoutModalLabel">Confirmer la Commande</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>Êtes-vous sûr de vouloir passer cette commande ?</p>
        </div>
        <div class="modal-footer">
          <form action="cart.php" method="POST">
            <input type="hidden" name="checkout" value="1">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-primary">Confirmer</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <?php include 'components/footer.php'; ?>
  <script src="js/jquery.min.js"></script>
  <script src="js/jquery-migrate-3.0.1.min.js"></script>
  <script src="js/popper.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/jquery.easing.1.3.js"></script>
  <script src="js/jquery.waypoints.min.js"></script>
  <script src="js/jquery.stellar.min.js"></script>
  <script src="js/owl.carousel.min.js"></script>
  <script src="js/jquery.magnific-popup.min.js"></script>
  <script src="js/aos.js"></script>
  <script src="js/jquery.animateNumber.min.js"></script>
  <script src="js/bootstrap-datepicker.js"></script>
  <script src="js/jquery.timepicker.min.js"></script>
  <script src="js/main.js"></script>
</body>
</html>

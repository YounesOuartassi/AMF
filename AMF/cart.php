<?php
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

// Get the current cart
$query = "
    SELECT * FROM order_items oi
    JOIN product p ON oi.product_id = p.product_id
    WHERE oi.order_id = (SELECT MAX(order_id) FROM orders WHERE user_id = $user_id)
";
$result = mysqli_query($conn, $query);

// Calculate subtotal for cart
$subtotal_query = "
    SELECT SUM(p.price_per_unit * oi.quantity) AS subtotal
    FROM order_items oi
    JOIN product p ON oi.product_id = p.product_id
    WHERE oi.order_id = (SELECT MAX(order_id) FROM orders WHERE user_id = $user_id)
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

    // Construct the email content
    $to = "oiyounes2.0@gmail.com";
    $subject = "Nouvelle commande de {$_SESSION['first_name']} {$_SESSION['last_name']}";
    $message = "Nouvelle commande reçue :\r\n\r\n";
    foreach ($order_items as $item) {
        $message .= "{$item['name']} - Quantité: {$item['quantity']} - Total: €{$item['total']}\r\n";
    }
    $message .= "\r\nSous-total: €" . number_format($subtotal, 2);
    $message .= "\r\nAdresse de livraison: {$address['address']}, {$address['postal_code']} {$address['city']}";

    // Send the email and handle success or failure
    if (mail($to, $subject, $message)) {
        // Clear the cart
        $clear_cart_query = "DELETE FROM order_items WHERE order_id = (SELECT MAX(order_id) FROM orders WHERE user_id = $user_id)";
        mysqli_query($conn, $clear_cart_query);

        // Set success message
        $_SESSION['checkout_message'] = 'Votre commande a été effectuée avec succès.';

        // Refresh to display success message
        header("Location: cart.php");
        exit;
    } else {
        // Log the error
        error_log("Mail sending failed: " . error_get_last()['message']);
        $_SESSION['checkout_message'] = 'Une erreur est survenue lors de l\'envoi de votre commande.';
        
        // Refresh to display failure message
        header("Location: cart.php");
        exit;
    }
}

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
    <nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
	    <div class="container">
        <a class="navbar-brand" href="index.php">Au Maraicher Des Flandres</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
					<span class="oi oi-menu"></span> Menu
				</button>

        <div class="collapse navbar-collapse" id="ftco-nav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item active"><a href="index.php" class="nav-link">Acceuil</a></li>
                <li class="nav-item"><a href="shop.php" class="nav-link">Acheter</a></li>
                <li class="nav-item"><a href="about.php" class="nav-link">à propos</a></li>
                <li class="nav-item"><a href="contact.php" class="nav-link">Contact</a></li>
                <button type="button" class="icon-users btn" data-toggle="modal" data-target="<?php echo isset($_SESSION['loggedin']) && $_SESSION['loggedin'] ? '.welcome-modal' : '.conn'; ?>"></button>

                <li class="nav-item cta cta-colored"><a href="cart.php" class="nav-link"><span class="icon-shopping_cart"></span>[0]</a></li>
            </ul>
        </div>
	    </div>
	  </nav>

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
<div class="modal fade conn" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="connexion" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Se Connecter</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php if ($showLoginModal): ?>
                        <div class="alert alert-warning" role="alert">
                            <?php echo htmlspecialchars($loginMessage); ?>
                        </div>
                    <?php endif; ?>
                    <form method="post" action="">
                        <label for="email_login">Email:</label>
                        <input type="email" id="email_login" name="email" class="form-control" required>

                        <label for="password_login">Mot de passe:</label>
                        <input type="password" id="password_login" name="password" class="form-control" required>

                        <input type="hidden" name="login" value="1">

                        <?php if (isset($errors['login'])): ?>
                            <div class="alert alert-danger"><?php echo $errors['login']; ?></div>
                        <?php endif; ?>

                        <?php if (isset($message) && empty($errors)): ?>
                            <div class="alert alert-success"><?php echo $message; ?></div>
                        <?php endif; ?>

                        <p>Vous n'avez pas de compte ?
                            <button type="button" class="btn btn-white" data-toggle="modal" data-target=".iden">Inscrivez-vous ici</button></p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Connexion</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal fade iden" tabindex="-1" role="dialog" aria-labelledby="identification" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">S'identifier</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form method="post" action="">
                    <div class="modal-body">
                        <label for="first_name">Prénom</label>
                        <input type="text" id="first_name" name="first_name" class="form-control" required>
                        
                        <label for="last_name">Nom</label>
                        <input type="text" id="last_name" name="last_name" class="form-control" required>

                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" required>

                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" class="form-control" required>

                        <label for="phone">Téléphone</label>
                        <input type="text" id="phone" name="phone" class="form-control" required>

                        <label for="address">Adresse</label>
                        <input type="text" id="address" name="address" class="form-control" required>

                        <label for="postal_code">Code postal</label>
                        <input type="text" id="postal_code" name="postal_code" class="form-control" required>

                        <label for="city">Ville</label>
                        <input type="text" id="city" name="city" class="form-control" required>

                        <input type="hidden" name="register" value="1">

                        <?php if (isset($errors['fields'])): ?>
                            <div class="alert alert-danger"><?php echo $errors['fields']; ?></div>
                        <?php endif; ?>

                        <?php if (isset($errors['email'])): ?>
                            <div class="alert alert-danger"><?php echo $errors['email']; ?></div>
                        <?php endif; ?>

                        <?php if (isset($errors['phone'])): ?>
                            <div class="alert alert-danger"><?php echo $errors['phone']; ?></div>
                        <?php endif; ?>

                        <?php if (isset($message)): ?>
                            <div class="alert alert-success"><?php echo $message; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Inscription</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
<!-- Welcome Modal -->
<div class="modal fade welcome-modal" tabindex="-1" role="dialog" aria-labelledby="Welcome" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bienvenue</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Bonjour, <span class="font-weight-bold text-primary"><?php echo htmlspecialchars($_SESSION['first_name']); ?></span> !</p>
                <p>Merci de vous être connecté</p>

            </div>
            <div class="modal-footer">
                <form action="logout.php" method="post">
                    <button type="submit" class="btn btn-danger">Se déconnecter</button>
                </form>
                <a href="cart.php" class="btn btn-primary">Voir votre panier</a>
                </div>
        </div>
    </div>
</div>

<!-- Display Alerts -->
<div class="container mt-4">
    <?php if (isset($_SESSION['errors'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php foreach ($_SESSION['errors'] as $error): ?>
                <?php echo $error . "<br>"; ?>
            <?php endforeach; ?>
            <!-- Trigger the correct modal based on the error context -->
            <button type="button" class="btn btn-link" data-toggle="modal" data-target=".<?php echo $show_modal_class; ?>">
                Cliquez ici pour corriger les erreurs
            </button>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>


    <?php if (isset($_SESSION['registration_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['registration_message']; ?>
        <button type="button" class="btn btn-link" data-toggle="modal" data-target=".conn">Cliquez ici pour se connecter</button>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php unset($_SESSION['registration_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['login_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['login_message']; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php unset($_SESSION['login_message']); ?>
<?php endif; ?>

</div>


    <section class="ftco-section ftco-cart">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="cart-list">
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
                                                <input type="text" name="quantity" class="quantity form-control input-number" value="<?= htmlspecialchars($row['quantity']); ?>" min="1" max="100" readonly>
                                            </div>
                                        </td>
                                        <td class="total">€<?= htmlspecialchars($row['price_per_unit'] * $row['quantity']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
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
    <form action="https://api.web3forms.com/submit" method="POST">
        <input type="hidden" name="access_key" value="d681eb48-2de6-4e54-9f3b-0470d57c0863">

        <button type="submit" name="checkout" class="btn btn-primary py-3 px-4">Commander</button>
    </form>
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
        
                
    </section>
    		
  <!-- loader -->
  <div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px"><circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/><circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00"/></svg></div>


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
  <script src="js/scrollax.min.js"></script>
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false"></script>
  <script src="js/google-map.js"></script>
  <script src="js/main.js"></script>

  <script>
		$(document).ready(function(){

		var quantitiy=0;
		   $('.quantity-right-plus').click(function(e){
		        
		        // Stop acting like a button
		        e.preventDefault();
		        // Get the field name
		        var quantity = parseInt($('#quantity').val());
		        
		        // If is not undefined
		            
		            $('#quantity').val(quantity + 1);

		          
		            // Increment
		        
		    });

		     $('.quantity-left-minus').click(function(e){
		        // Stop acting like a button
		        e.preventDefault();
		        // Get the field name
		        var quantity = parseInt($('#quantity').val());
		        
		        // If is not undefined
		      
		            // Increment
		            if(quantity>0){
		            $('#quantity').val(quantity - 1);
		            }
		    });
		    
		});
	</script>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        <?php if ($showLoginModal): ?>
          $('#loginModal').modal('show');
        <?php endif; ?>
      });
    </script>
  </body>
</html>
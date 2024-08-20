<?php
session_start();
include 'db_connect.php';

// Initialize cart in session if not already set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Process the 'Add to Cart' request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    // Validate quantity
    if ($quantity < 1) {
        echo 'Quantité invalide.';
        exit;
    }

    // Check if product already in cart
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    // Insert order into the database
    $order_date = date('Y-m-d H:i:s');
    $total_amount = 0;

    // Calculate total amount
    $cart_items = $_SESSION['cart'];
    foreach ($cart_items as $id => $qty) {
        $product_query = "SELECT price_per_unit FROM product WHERE product_id = $id";
        $product_result = mysqli_query($conn, $product_query);
        $product = mysqli_fetch_assoc($product_result);
        $total_amount += $product['price_per_unit'] * $qty;
    }

    // Insert order
    $insert_order_query = "INSERT INTO orders (full_name, email, phone, address, postal_code, city, order_date, total_amount) VALUES ('', '', '', '', '', '', '$order_date', $total_amount)";
    if (!mysqli_query($conn, $insert_order_query)) {
        die('Error inserting order: ' . mysqli_error($conn));
    }
    $order_id = mysqli_insert_id($conn);

    // Insert order items
    foreach ($cart_items as $id => $qty) {
        $insert_order_items_query = "INSERT INTO order_items (order_id, product_id, quantity) VALUES ($order_id, $id, $qty)";
        if (!mysqli_query($conn, $insert_order_items_query)) {
            die('Error inserting order item: ' . mysqli_error($conn));
        }
    }

    // Clear the cart
    $_SESSION['cart'] = [];

    echo '<p>Order has been placed successfully. <a href="cart.php?order_id=' . $order_id . '">View your order</a></p>';
}

// Fetch categories for the filter menu and order them by category_id
$category_query = "SELECT * FROM categories ORDER BY category_id ASC";
$category_result = mysqli_query($conn, $category_query);

// Get selected category from the URL
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;

// Fetch products based on the selected category
$query = $category_id ? "SELECT * FROM product WHERE category_id = $category_id" : "SELECT * FROM product";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Au Maraicher Des Flandres</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,500,600,700,800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Lora:400,400i,700,700i&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Amatic+SC:400,700&display=swap" rel="stylesheet">

    <!-- Stylesheets -->
     
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
            <a class="navbar-brand" href="index.html">Au Maraicher Des Flandres</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="oi oi-menu"></span> Menu
            </button>

            <div class="collapse navbar-collapse" id="ftco-nav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item active"><a href="index.php" class="nav-link">Acceuil</a></li>
                <li class="nav-item"><a href="shop.php" class="nav-link">Acheter</a></li>
                <li class="nav-item"><a href="about.html" class="nav-link">à propos</a></li>
                <li class="nav-item"><a href="contact.html" class="nav-link">Contact</a></li>
                <button type="button" class="icon-users btn" data-toggle="modal" data-target="<?php echo isset($_SESSION['loggedin']) && $_SESSION['loggedin'] ? '.welcome-modal' : '.conn'; ?>"></button>

                <li class="nav-item cta cta-colored"><a href="cart.php" class="nav-link"><span class="icon-shopping_cart"></span>[0]</a></li>
            </ul>
        </div>
        </div>
    </nav>
    <div class="modal fade conn" tabindex="-1" role="dialog" aria-labelledby="connexion" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
					<h5 class="modal-title">Se Connecter</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">×</span>
					</button>
				</div>
                <div class="modal-body">
                <form method="post" id="contactFrm">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" class="form-control"required>
                        
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password"class="form-control" required>
                        <p>Vous n'avez pas de compte ?                     
                        <button type="button" class="btn btn-white " data-toggle="modal" data-target=".iden">Inscrivez-vous ici</button>
                </form></div>
                <div class="modal-footer">
					<button type="submit" class="btn btn-primary">connexion</button>
				</div>
                
            </div>
        </div>
    </div>
    <div class="modal fade iden" tabindex="-1" role="dialog" aria-labelledby="identification" aria-hidden="true">
    <div class="modal-dialog ">
        <div class="modal-content">
                <div class="modal-header">
					<h5 class="modal-title">S'identifier</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">×</span>
					</button>
				</div>
				<form method="post" id="contactFrm">
				<div class="modal-body">
						<label for="first_name">Prénom</label>
						<input type="text" id="first_name" name="first_name" class="form-control" required>

						<label for="last_name">Nom</label>
						<input type="text" id="last_name" name="last_name" class="form-control" required>
						
						<label for="email">Email:</label>
						<input type="email" id="email" name="email"class="form-control" required>
						
						<label for="password">Mot de passe</label>
						<input type="password" id="password" name="password" class="form-control" required>
						
						<label for="phone">Numéro de téléphone </label>
						<input type="text" id="phone" name="phone" class="form-control">
						
						<label for="address">Addresse</label>
						<textarea id="address" name="address" class="form-control"></textarea>
						
						<label for="postal_code">Code Postale:</label>
						<input type="text" id="postal_code" name="postal_code"class="form-control">
						
						<label for="city">Ville</label>
						<input type="text" id="city" name="city"class="form-control">					
					
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary">identification</button>
				</div>    
        </div>
    </div>
    </div>
    <div class="hero-wrap hero-bread" style="background-image: url('images/bg_1.jpg');">
        <div class="container">
            <div class="row no-gutters slider-text align-items-center justify-content-center">
                <div class="col-md-9 ftco-animate text-center">
                    <p class="breadcrumbs"><span class="mr-2"><a href="index.html">Produits</a></span> <span>Frais</span></p>
                    <h1 class="mb-0 bread">Produits</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="ftco-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10 mb-5 text-center">
                    <ul class="product-category">
                        <li><a href="shop.php?category_id=" class="<?= is_null($category_id) ? 'active' : '' ?>">Tous</a></li>
                        <?php while ($cat_row = mysqli_fetch_assoc($category_result)) { ?>
                            <li><a href="shop.php?category_id=<?= $cat_row['category_id']; ?>" class="<?= $category_id == $cat_row['category_id'] ? 'active' : '' ?>"><?= $cat_row['category_name']; ?></a></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>

            <div class="row">
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <div class="col-md-6 col-lg-3 ftco-animate">
                        <div class="product">
                            <div class="text py-3 pb-4 px-3 text-center">
                                <img class="img-fluid" src="<?= $row['image_url']; ?>" alt="<?= $row['name']; ?>">
                                    <h3><?= $row['name']; ?></h3>
                                    <div class="pricing">
                                        <p class="price">€<?= $row['price_per_unit']; ?> Kg</p>
                                    </div>

                                    <form action="product-single.php" method="POST" class="bottom-area d-flex flex-column align-items-center px-3">
                                        <input type="hidden" name="product_id" value="<?= $row['product_id']; ?>">
                                        
                                        <div class="buy-now-wrapper w-100">
                                            <input type="submit" name="add_to_cart" value="Ajouter au panier" class="btn btn-primary buy-now w-100">
                                        </div>
                                        
                                    </form>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </section>

    <footer class="ftco-footer ftco-section">
        <div class="container">
            <div class="row">
                <div class="mouse">
                    <a href="#" class="mouse-icon">
                        <div class="mouse-wheel"><span class="ion-ios-arrow-up"></span></div>
                    </a>
                </div>
            </div>
            <div class="row mb-5">
                <div class="col-md">
                    <div class="ftco-footer-widget mb-4">
                        <h2 class="ftco-heading-2">Au Maraicher Des Flandres</h2>
                        <ul class="ftco-footer-social list-unstyled float-md-left float-lft mt-5">
                            <li class="ftco-animate"><a href="#"><span class="icon-twitter"></span></a></li>
                            <li class="ftco-animate"><a href="https://www.facebook.com/aumaraichersdesflandres/"><span class="icon-facebook"></span></a></li>
                            <li class="ftco-animate"><a href="https://www.instagram.com/au_maraicher_des_flandres"><span class="icon-instagram"></span></a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md">
                    <div class="ftco-footer-widget mb-4 ml-md-5">
                        <h2 class="ftco-heading-2">Menu</h2>
                        <ul class="list-unstyled">
                            <li><a href="#" class="py-2 d-block">Boutique</a></li>
                            <li><a href="#" class="py-2 d-block">À propos</a></li>
                            <li><a href="#" class="py-2 d-block">Journal</a></li>
                            <li><a href="#" class="py-2 d-block">Contactez-nous</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="ftco-footer-widget mb-4">
                        <h2 class="ftco-heading-2">Aide</h2>
                        <div class="d-flex">
                            <ul class="list-unstyled">
                                <li><a href="#" class="py-2 d-block">Politique de confidentialité</a></li>
                                <li><a href="#" class="py-2 d-block">Politique de retour</a></li>
                                <li><a href="#" class="py-2 d-block">Expédition</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- loader -->
    <div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px"><circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/><circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00"/></svg></div>

    <!-- Scripts -->
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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <!-- JavaScript for Quantity Adjustment -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Loop through all quantity wrappers on the page
        document.querySelectorAll('.quantity-wrapper').forEach(function (wrapper) {
            const quantityInput = wrapper.querySelector('.quantity-input');
            const minQuantity = 1; // Minimum quantity
            const maxQuantity = parseInt(wrapper.getAttribute('data-max-quantity'), 10); // Max quantity from data attribute

            // Decrement button event
            wrapper.querySelector('.quantity-left-minus').addEventListener('click', function () {
                let currentValue = parseInt(quantityInput.value, 10);
                if (currentValue > minQuantity) {
                    quantityInput.value = currentValue - 1;
                }
            });

            // Increment button event
            wrapper.querySelector('.quantity-right-plus').addEventListener('click', function () {
                let currentValue = parseInt(quantityInput.value, 10);
                if (currentValue < maxQuantity) {
                    quantityInput.value = currentValue + 1;
                }
            });
        });
    });
    </script>
</body>
</html>

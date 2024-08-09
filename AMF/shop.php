<?php
// Start session at the very beginning of the file
session_start();

// Include database connection
include 'db_connect.php'; 

// Fetch categories for the filter menu and order them by category_id
$category_query = "SELECT * FROM categories ORDER BY category_id ASC";
$category_result = mysqli_query($conn, $category_query);

// Get selected category from the URL
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;

// Fetch products based on the selected category
if ($category_id) {
    $query = "SELECT * FROM product WHERE category_id = $category_id";
} else {
    $query = "SELECT * FROM product"; // If no category is selected, show all products
}

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
                    <li class="nav-item active"><a href="index.html" class="nav-link">Accueil</a></li>
                    <li class="nav-item"><a href="shop.php" class="nav-link">Acheter</a></li>
                    <li class="nav-item"><a href="about.html" class="nav-link">À propos</a></li>
                    <li class="nav-item"><a href="contact.html" class="nav-link">Contact</a></li>
                    <li class="nav-item cta cta-colored"><a href="cart.html" class="nav-link"><span class="icon-shopping_cart"></span>[0]</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="hero-wrap hero-bread" style="background-image: url('images/bg_1.jpg');">
        <div class="container">
            <div class="row no-gutters slider-text align-items-center justify-content-center">
                <div class="col-md-9 ftco-animate text-center">
                    <p class="breadcrumbs"><span class="mr-2"><a href="index.html">Home</a></span> <span>Produits</span></p>
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
                                <div class="text py-3 pb-4 px-3 text-center">
                                    <h3><?= $row['name']; ?></h3>
                                    <div class="pricing">
                                        <p class="price">€<?= $row['price_per_unit']; ?> par <?= $row['unit']; ?></p>
                                    </div>

                                    <form action="cart.php" method="POST" class="bottom-area d-flex flex-column align-items-center px-3">
                                        <input type="hidden" name="product_id" value="<?= $row['product_id']; ?>">
                                        
                                        <div class="form-group quantity-wrapper mb-2 d-flex align-items-center" data-max-quantity="<?= $row['stock_quantity']; ?>">
                                            <button type="button" class="quantity-left-minus btn" data-field="quantity">
                                                <i class="ion-ios-remove"></i>
                                            </button>
                                            <input type="number" name="quantity" value="1" class="form-control quantity-input text-center" readonly>
                                            <button type="button" class="quantity-right-plus btn" data-field="quantity">
                                                <i class="ion-ios-add"></i>
                                            </button>
                                        </div>
                                        
                                        <div class="buy-now-wrapper w-100">
                                            <input type="submit" name="add_to_cart" value="Ajouter au panier" class="btn btn-primary buy-now w-100">
                                        </div>
                                    </form>
                                </div>
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

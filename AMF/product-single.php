<?php
session_start();
include 'db_connect.php';

// Initialize an empty array for errors
$errors = [];
// Check if the user is logged in
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

// Handle form submission for adding to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : null;
    $quantity = isset($_POST['quantity']) ? floatval($_POST['quantity']) : null;

    if (!$user_id || !$product_id || !$quantity) {
        $errors[] = 'Invalid input.';
    } else {
        // Check if there is an existing cart for the user
        $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? AND status = 'cart'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // Create a new order
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount) VALUES (?, 0.00)");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $order_id = $stmt->insert_id;
            $stmt->close();
        } else {
            $order = $result->fetch_assoc();
            $order_id = $order['order_id'];
        }

        // Check if the product is already in the order
        $stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $order_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update existing order item
            $stmt = $conn->prepare("UPDATE order_items SET quantity = quantity + ? WHERE order_id = ? AND product_id = ?");
            $stmt->bind_param("dii", $quantity, $order_id, $product_id);
            $stmt->execute();
        } else {
            // Insert new order item
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit) VALUES (?, ?, ?, 'kg')");
            $stmt->bind_param("iid", $order_id, $product_id, $quantity);
            $stmt->execute();
        }
        $stmt->close();

        // Update the total amount in the order
        $stmt = $conn->prepare("UPDATE orders o JOIN order_items oi ON o.order_id = oi.order_id SET o.total_amount = (SELECT SUM(p.price_per_unit * oi.quantity) FROM order_items oi JOIN product p ON oi.product_id = p.product_id WHERE oi.order_id = o.order_id) WHERE o.order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->close();

        // Redirect to shop page
        header('Location: shop.php');
        exit;
    }
}

// Check if the product ID is provided via POST or GET
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : (isset($_GET['product_id']) ? intval($_GET['product_id']) : null);

// Validate the product ID
if ($product_id === null) {
    $errors[] = 'Produit non spécifié.';
} else {
    // Fetch product details from the database
    $stmt = $conn->prepare("SELECT * FROM product WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the product exists
    if ($result->num_rows === 1) {
        $product = $result->fetch_assoc();
    } else {
        $errors[] = 'Produit introuvable.';
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Au Maraicher Des Flandres</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="./images/logo2.png" type="image/icon type">

    <!-- Stylesheets -->
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
    
<?php 
include 'components/navbar.php';
include 'modals/login.php';
include 'modals/login_cart.php';
include 'modals/register.php';
include 'modals/welcome.php'; 
?>

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

<div class="hero-wrap hero-bread" style="background-image: url('images/bg_1.jpg');">
    <div class="container">
        <div class="row no-gutters slider-text align-items-center justify-content-center">
            <div class="col-md-9 ftco-animate text-center">
                <h1 class="mb-0 bread">Produit séléctionné</h1>
            </div>
        </div>
    </div>
</div>

<section class="ftco-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-5 mb-5 ftco-animate">
                <a href="<?= htmlspecialchars($product['image_url']); ?>" class="image-popup">
                    <img src="<?= htmlspecialchars($product['image_url']); ?>" class="img-fluid" alt="<?= htmlspecialchars($product['name']); ?>">
                </a>
            </div>
            <div class="col-lg-6 product-details pl-md-5 ftco-animate">
                <h3><?= htmlspecialchars($product['name']); ?> - <small>Provenance: <?= htmlspecialchars($product['provenance']); ?></small></h3>
                <p class="price"><span>€<?= htmlspecialchars($product['price_per_unit']); ?> par <?= htmlspecialchars($product['unit']); ?></span></p>

                <form action="product-single.php?product_id=<?= $product_id; ?>" method="POST">
                    <input type="hidden" name="product_id" value="<?= $product_id; ?>">
                    <div class="row mt-4">
                        <div class="w-100"></div>

                        <div class="input-group col-md-6 d-flex mb-3">
                            <input type="number" id="quantity" name="quantity" class="form-control input-number" min="1" placeholder="Entrer la quantité" required>
                        </div>
                         <!-- the clients selects the unit                -->
                        <!-- <div class="input-group col-md-6 d-flex mb-3">
                            <select id="unit" name="unit" class="form-control" required>
                                <option value="kilogram" <?= $product['unit'] == 'kilogram' ? 'selected' : ''; ?>>Kilogram</option>
                                <option value="pièce" <?= $product['unit'] == 'pièce' ? 'selected' : ''; ?>>Unité</option>
                            </select>
                        </div> -->

                        <!-- the cliendoesnt select the unit -->
                        <div class="input-group col-md-6 d-flex mb-3 align-items-center">
                            <label class="mr-2">Unité:</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($product['unit']); ?>" readonly>
                        </div>
                    </div>

                    <div class="buy-now-wrapper w-100">
                        <input type="submit" name="add_to_cart" value="Ajouter au panier" class="btn btn-primary buy-now w-100">
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include 'components/footer.php'; ?>


<!-- loader -->
<div id="ftco-loader" class="show fullscreen">
    <svg class="circular" width="48px" height="48px">
        <circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/>
        <circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00"/>
    </svg>
</div>

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
    var quantity = 0;
    $('.quantity-right-plus').click(function(e){
        e.preventDefault();
        var quantity = parseInt($('#quantity').val());
        $('#quantity').val(quantity + 1);
    });

    $('.quantity-left-minus').click(function(e){
        e.preventDefault();
        var quantity = parseInt($('#quantity').val());
        if(quantity > 0){
            $('#quantity').val(quantity - 1);
        }
    });
});
</script>
</body>
</html>

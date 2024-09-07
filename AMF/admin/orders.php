<?php
session_start();
include '../db_connect.php'; // Database connection

// Check if the user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin'] || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    // Redirect to the home page or show an error message
    header('Location: ../index.php'); // Adjust the path as necessary
    exit();
}

// Handle order status update when "Commande est faite" is clicked
if (isset($_GET['action']) && $_GET['action'] === 'update' && isset($_GET['id'])) {
    $order_id = intval($_GET['id']);
    
    // Update order status to 'delivered'
    $updateOrderSql = "UPDATE orders SET status = 'delivered' WHERE order_id = ?";
    $stmt = $conn->prepare($updateOrderSql);
    $stmt->bind_param("i", $order_id);
    
    if ($stmt->execute()) {
        // Success message
        $_SESSION['status_message'] = ['type' => 'success', 'text' => 'Commande marquée comme livrée avec succès.'];
    } else {
        // Error message
        $_SESSION['status_message'] = ['type' => 'error', 'text' => 'Erreur lors de la mise à jour du statut de la commande.'];
    }
    
    $stmt->close();
    
    // Redirect back to orders page
    header("Location: orders.php");
    exit;
}

// Fetch all orders with status 'ordered'
$sql = "SELECT o.order_id, o.order_date, o.total_amount, u.first_name, u.last_name, u.email, u.phone, u.address, u.postal_code, u.city 
        FROM orders o
        JOIN users u ON o.user_id = u.user_id
        WHERE o.status = 'ordered'";
$result = $conn->query($sql);

$orders = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Au Maraicher Des Flandres</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="../images/logo2.png" type="image/icon type">

    <link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,500,600,700,800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Lora:400,400i,700,700i&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Amatic+SC:400,700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../css/open-iconic-bootstrap.min.css">
    <link rel="stylesheet" href="../css/animate.css">

    <link rel="stylesheet" href="../css/owl.carousel.min.css">
    <link rel="stylesheet" href="../css/owl.theme.default.min.css">
    <link rel="stylesheet" href="../css/magnific-popup.css">

    <link rel="stylesheet" href="../css/aos.css">

    <link rel="stylesheet" href="../css/ionicons.min.css">

    <link rel="stylesheet" href="../css/bootstrap-datepicker.css">
    <link rel="stylesheet" href="../css/jquery.timepicker.css">

    <link rel="stylesheet" href="../css/flaticon.css">
    <link rel="stylesheet" href="../css/icomoon.css">
    <link rel="stylesheet" href="../css/style.css">

    <!-- Custom CSS for status messages -->
    <style>
        .status-message.success {
            color: green;
        }
        .status-message.error {
            color: red;
        }
    </style>
</head>
<body>

<?php include 'admin_navbar.php'; ?>
<div class="container my-5">
    <h2>Commandes</h2>
    
    <?php if (isset($_SESSION['status_message'])): ?>
        <div class="alert status-message <?php echo htmlspecialchars($_SESSION['status_message']['type']); ?>">
            <?php echo htmlspecialchars($_SESSION['status_message']['text']); ?>
        </div>
        <?php unset($_SESSION['status_message']); ?>
    <?php endif; ?>
    
    <?php if (empty($orders)): ?>
        <div class="alert alert-info">
            Vous n'avez aucune commande.
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="card mb-4 position-relative">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5>Commande ID: <?php echo $order['order_id']; ?> | Date: <?php echo $order['order_date']; ?></h5>
                        <p class="mb-1"><strong>Client:</strong> <?php echo htmlspecialchars($order['first_name']) . ' ' . htmlspecialchars($order['last_name']); ?> | <strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                        <p class="mb-1"><strong>Téléphone:</strong> <?php echo htmlspecialchars($order['phone']); ?> | <strong>Adresse:</strong> <?php echo htmlspecialchars($order['address']) . ', ' . htmlspecialchars($order['postal_code']) . ' ' . htmlspecialchars($order['city']); ?></p>
                        <p class="mb-1"><strong>Total:</strong> €<?php echo number_format($order['total_amount'], 2); ?></p>
                    </div>
                    <!-- Update status to delivered -->
                    <a href="orders.php?action=update&id=<?php echo $order['order_id']; ?>" class="btn btn-success" onclick="return confirm('Êtes-vous sûr de vouloir marquer cette commande comme livrée ?');">
                        Commande est faite
                    </a>
                </div>
                <div class="card-body">
                    <h6>Produits Commandés:</h6>
                    <div class="row">
                        <?php
                        // Fetch products for each order
                        $orderItemsSql = "SELECT p.name, oi.quantity, oi.unit, p.price_per_unit, p.image_url
                                          FROM order_items oi
                                          JOIN product p ON oi.product_id = p.product_id
                                          WHERE oi.order_id = ?
                                          GROUP BY p.product_id, oi.unit";
                        $stmt = $conn->prepare($orderItemsSql);
                        $stmt->bind_param("i", $order['order_id']);
                        $stmt->execute();
                        $orderItemsResult = $stmt->get_result();

                        $products = [];
                        while ($item = $orderItemsResult->fetch_assoc()) {
                            $product_id = $item['name'];
                            if (!isset($products[$product_id])) {
                                $products[$product_id] = [
                                    'name' => $item['name'],
                                    'quantity' => 0,
                                    'unit' => $item['unit'],
                                    'price_per_unit' => $item['price_per_unit'],
                                    'image_url' => $item['image_url']
                                ];
                            }
                            $products[$product_id]['quantity'] += $item['quantity'];
                        }

                        foreach ($products as $product):
                        ?>
                            <div class="col-md-6 col-lg-3 d-flex justify-content-center">
                                <div class="product-card text-center p-2">
                                    <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid mb-2" style="width: 120px; height: 120px; object-fit: cover;">
                                    <h5 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h5>
                                    <p class="product-quantity">Quantité: <?php echo $product['quantity']; ?></p>
                                    <p class="product-unit">Unité: <?php echo htmlspecialchars($product['unit']); ?></p>
                                    <p class="product-price">Prix Unitaire: €<?php echo number_format($product['price_per_unit'], 2); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php $stmt->close(); ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Login Modal -->
<?php include '../modals/login.php'; ?>
<!-- Register Modal -->
<?php include '../modals/register.php'; ?>
<!-- Welcome Modal -->
<?php include '../modals/admin_welcome.php'; ?>

<?php include '../components/footer.php'; ?>

<!-- loader -->
<div id="ftco-loader" class="show fullscreen">
    <svg class="circular" width="48px" height="48px">
        <circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/>
        <circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00"/>
    </svg>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="../js/jquery.min.js"></script>
<script src="../js/jquery-migrate-3.0.1.min.js"></script>
<script src="../js/popper.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/jquery.easing.1.3.js"></script>
<script src="../js/jquery.waypoints.min.js"></script>
<script src="../js/jquery.stellar.min.js"></script>
<script src="../js/owl.carousel.min.js"></script>
<script src="../js/jquery.magnific-popup.min.js"></script>
<script src="../js/aos.js"></script>
<script src="../js/jquery.animateNumber.min.js"></script>
<script src="../js/bootstrap-datepicker.js"></script>
<script src="../js/scrollax.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false"></script>
<script src="../js/google-map.js"></script>
<script src="../js/main.js"></script>
<script src="../js/modal-switch.js"></script>

</body>
</html>

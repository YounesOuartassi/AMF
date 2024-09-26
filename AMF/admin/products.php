<?php
session_start();
include '../db_connect.php'; // Database connection
// Check if the user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin'] || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    // Redirect to the home page or show an error message
    header('Location: ../index.php'); // Adjust the path as necessary
    exit();
}

// Handle adding a product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $price_per_unit = $_POST['price_per_unit'];
    $unit = $_POST['unit'];
    $category_id = $_POST['category_id'];
    $provenance = $_POST['provenance'];

    if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == 0) {
        $image = $_FILES['image_url'];
        $imageName = time() . '-' . basename($image['name']);
        $uploadDir = '../images/';
        $uploadFile = $uploadDir . $imageName;

        if (move_uploaded_file($image['tmp_name'], $uploadFile)) {
            $image_url = 'images/' . $imageName;
        } else {
            $_SESSION['errors'][] = "Échec du téléchargement de l'image.";
            header('Location: products.php');
            exit();
        }
    } else {
        $image_url = NULL;
    }

    $stmt = $conn->prepare("INSERT INTO product (name, price_per_unit, unit, image_url, category_id, provenance) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sdssis", $name, $price_per_unit, $unit, $image_url, $category_id, $provenance);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Produit ajouté avec succès.";
    } else {
        $_SESSION['errors'][] = "Échec de l'ajout du produit : " . $stmt->error;
    }

    $stmt->close();
    header('Location: products.php');
    exit();
}

// Handle modifying a product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['modify_product'])) {
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $price_per_unit = $_POST['price_per_unit'];
    $unit = $_POST['unit'];
    $category_id = $_POST['category_id'];
    $provenance = $_POST['provenance'];

    if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == 0) {
        $image = $_FILES['image_url'];
        $imageName = time() . '-' . basename($image['name']);
        $uploadDir = '../images/';
        $uploadFile = $uploadDir . $imageName;

        if (move_uploaded_file($image['tmp_name'], $uploadFile)) {
            $image_url = 'images/' . $imageName;
        } else {
            $_SESSION['errors'][] = "Échec du téléchargement de l'image.";
            header("Location: products.php?action=modify&id=$product_id");
            exit();
        }
    } else {
        // Keep existing image URL if no new image is provided
        $stmt = $conn->prepare("SELECT image_url FROM product WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->bind_result($image_url);
        $stmt->fetch();
        $stmt->close();
    }

    $stmt = $conn->prepare("UPDATE product SET name = ?, price_per_unit = ?, unit = ?, image_url = ?, category_id = ?, provenance = ? WHERE product_id = ?");
    $stmt->bind_param("sdssisi", $name, $price_per_unit, $unit, $image_url, $category_id, $provenance, $product_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Produit mis à jour avec succès.";
    } else {
        $_SESSION['errors'][] = "Échec de la mise à jour du produit : " . $stmt->error;
    }

    $stmt->close();
    header("Location: products.php");
    exit();
}

// Handle deleting a product
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Fetch the current image URL to delete it from the server
    $stmt = $conn->prepare("SELECT image_url FROM product WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->bind_result($image_url);
    $stmt->fetch();
    $stmt->close();

    // Delete the product from the database
    $stmt = $conn->prepare("DELETE FROM product WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        // Remove image file if exists
        if ($image_url && file_exists('../' . $image_url)) {
            unlink('../' . $image_url);
        }
        $_SESSION['success_message'] = "Produit supprimé avec succès.";
    } else {
        $_SESSION['errors'][] = "Échec de la suppression du produit : " . $stmt->error;
    }

    $stmt->close();
    header('Location: products.php');
    exit();
}

// Fetch all products for display
$products = $conn->query("SELECT * FROM product")->fetch_all(MYSQLI_ASSOC);

// Fetch categories for dropdown
$categories = $conn->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);

$conn->close();
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
</head>
<body>

<?php include 'admin_navbar.php'; ?>

<div class="container mt-4">
    <?php if (isset($_SESSION['errors'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php foreach ($_SESSION['errors'] as $error): ?>
                <?php echo htmlspecialchars($error) . "<br>"; ?>
            <?php endforeach; ?>
            <button type="button" class="btn btn-link" data-toggle="modal" data-target=".<?php echo $show_modal_class; ?>">
                Cliquez ici pour corriger les erreurs
            </button>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['success_message']); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <h2 class="mt-4">Ajouter un Nouveau Produit</h2>
    <form action="products.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="add_product">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="name">Nom du Produit</label>
                <input type="text" class="form-control" name="name" id="name" required>
            </div>
            <div class="form-group col-md-6">
                <label for="price_per_unit">Prix par Unité (€)</label>
                <input type="number" class="form-control" name="price_per_unit" id="price_per_unit" step="0.01" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="unit">Unité</label>
                <input type="text" class="form-control" name="unit" id="unit" required>
            </div>
            <div class="form-group col-md-6">
                <label for="image_url">Image du Produit</label>
                <input type="file" class="form-control-file" name="image_url" id="image_url">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="category_id">Catégorie</label>
                <select class="form-control" name="category_id" id="category_id" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="provenance">Provenance</label>
                <input type="text" class="form-control" name="provenance" id="provenance" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Ajouter le Produit</button>
    </form>

    <h2 class="mt-4">Liste des Produits</h2>
    <div class="row">
    <?php foreach ($products as $product): ?>
        <div class="col-md-6 col-lg-3 d-flex justify-content-center mb-4">
            <div class="product-card text-center p-2">
                <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid mb-2" style="width: 120px; height: 120px; object-fit: cover;">
                <h5 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h5>
                <div class="pricing">
                    <p class="product-price">€<?php echo number_format($product['price_per_unit'], 2); ?> <?php echo htmlspecialchars($product['unit']); ?></p>
                </div>
                <form action="products.php" method="POST" class="bottom-area d-flex justify-content-center align-items-center px-3">
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                    <input type="hidden" name="modify_product">
                    <button type="button" class="btn btn-warning mr-2" data-toggle="modal" data-target="#modifyProductModal-<?php echo $product['product_id']; ?>">
                        <img src="../images/edit-icon.png" alt="Modifier" style="width: 20px; height: 20px;">
                    </button>
                    <a href="products.php?action=delete&id=<?php echo $product['product_id']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?');">
                        <img src="../images/delete-icon.png" alt="Supprimer" style="width: 20px; height: 20px;">
                    </a>
                </form>

            </div>
        </div>

        <!-- Modify Product Modal -->
        <div class="modal fade" id="modifyProductModal-<?php echo $product['product_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="modifyProductModalLabel-<?php echo $product['product_id']; ?>" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modifyProductModalLabel-<?php echo $product['product_id']; ?>">Modifier le Produit</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="products.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="modify_product">
                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="modify_name-<?php echo $product['product_id']; ?>">Nom du Produit</label>
                                <input type="text" class="form-control" name="name" id="modify_name-<?php echo $product['product_id']; ?>" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="modify_price_per_unit-<?php echo $product['product_id']; ?>">Prix par Unité (€)</label>
                                <input type="number" class="form-control" name="price_per_unit" id="modify_price_per_unit-<?php echo $product['product_id']; ?>" value="<?php echo number_format($product['price_per_unit'], 2); ?>" step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label for="modify_unit-<?php echo $product['product_id']; ?>">Unité</label>
                                <input type="text" class="form-control" name="unit" id="modify_unit-<?php echo $product['product_id']; ?>" value="<?php echo htmlspecialchars($product['unit']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="modify_image_url-<?php echo $product['product_id']; ?>">Image du Produit</label>
                                <input type="file" class="form-control-file" name="image_url" id="modify_image_url-<?php echo $product['product_id']; ?>">
                                <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid mt-2" style="max-width: 100px;">
                            </div>
                            <div class="form-group">
                                <label for="modify_category_id-<?php echo $product['product_id']; ?>">Catégorie</label>
                                <select class="form-control" name="category_id" id="modify_category_id-<?php echo $product['product_id']; ?>" required>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['category_id']; ?>" <?php echo $category['category_id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['category_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="modify_provenance-<?php echo $product['product_id']; ?>">Provenance</label>
                                <input type="text" class="form-control" name="provenance" id="modify_provenance-<?php echo $product['product_id']; ?>" value="<?php echo htmlspecialchars($product['provenance']); ?>" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                            <button type="submit" class="btn btn-primary">Modifier le Produit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

</div>

<!-- Welcome Modal -->
<?php include '../modals/admin_welcome.php'; ?>


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

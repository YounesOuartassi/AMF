<?php
session_start();
include 'db_connect.php';

// Initialize cart in session if not already set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}



// Handle form submissions for login and registration
$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['register'])) {
        // Registration logic
        $first_name = htmlspecialchars(trim($_POST['first_name']));
        $last_name = htmlspecialchars(trim($_POST['last_name']));
        $email = htmlspecialchars(trim($_POST['email']));
        $password = htmlspecialchars(trim($_POST['password']));
        $phone = htmlspecialchars(trim($_POST['phone']));
        $address = htmlspecialchars(trim($_POST['address']));
        $postal_code = htmlspecialchars(trim($_POST['postal_code']));
        $city = htmlspecialchars(trim($_POST['city']));

        // Server-side validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Adresse e-mail invalide.';
        }
        if (strlen($phone) != 10 || !ctype_digit($phone)) {
            $errors['phone'] = 'Le numéro de téléphone doit comporter 10 chiffres.';
        }
        if (strlen($password) < 6) {
            $errors['password'] = 'Le mot de passe doit comporter au moins 6 caractères.';
        }
        if (empty($first_name) || empty($last_name) || empty($address) || empty($postal_code) || empty($city)) {
            $errors['fields'] = 'Tous les champs sont obligatoires.';
        }

        if (empty($errors)) {
            $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $errors['email'] = 'Un compte avec cette adresse e-mail existe déjà.';
            } else {
                $password_hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, password_hash, email, phone, address, postal_code, city) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssss", $first_name, $last_name, $password_hash, $email, $phone, $address, $postal_code, $city);

                if ($stmt->execute()) {
                    $_SESSION['registration_message'] = "Inscription réussie !";
                } else {
                    $errors['database'] = "Erreur: " . $stmt->error;
                }
            }

            $stmt->close();
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['show_modal'] = 'register';
        }
    }

    if (isset($_POST['login'])) {
        // Login logic
        $email = htmlspecialchars(trim($_POST['email']));
        $password = htmlspecialchars(trim($_POST['password']));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Adresse e-mail invalide.';
        }

        if (empty($errors)) {
            $stmt = $conn->prepare("SELECT user_id, first_name, password_hash FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($user_id, $first_name, $password_hash);
                $stmt->fetch();

                if (password_verify($password, $password_hash)) {
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['email'] = $email;
                    $_SESSION['first_name'] = $first_name;
                    $_SESSION['loggedin'] = true;

                    $_SESSION['login_message'] = "Connexion réussie !";
                    header('Location: shop.php');
                    exit();
                } else {
                    $errors['login'] = "Mot de passe invalide";
                }
            } else {
                $errors['login'] = "Aucun compte trouvé avec cette adresse e-mail.";
            }

            $stmt->close();
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['show_modal'] = 'login';
        }
    }

    $conn->close();

    if (!isset($_SESSION['login_message'])) {
        header('Location: shop.php');
        exit();
    }
}

// Determine which modal to show
$show_modal_class = '';
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
    if (isset($_SESSION['registration_message'])) {
        $message = $_SESSION['registration_message'];
        unset($_SESSION['registration_message']);
    }
} else {
    if (isset($_SESSION['errors'])) {
        if (isset($_SESSION['errors']['login'])) {
            $show_modal_class = 'conn'; // Login modal
        } elseif (isset($_SESSION['errors']['registration'])) {
            $show_modal_class = 'iden'; // Registration modal
        }
        unset($_SESSION['errors']);
    }
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
    
    <!-- Fonts -->    <link rel="icon" href="./images/logo2.png" type="image/icon type">

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
<?php include 'components/navbar.php'; ?>

    <!-- Login Modal -->
<?php include 'modals/login.php'; ?>
<!-- Register Modal -->
<?php include 'modals/register.php'; ?>
<!-- Welcome Modal -->
<?php include 'modals/welcome.php'; ?>
<!-- Display Alerts -->
<?php include 'modals/alerts.php'; ?>


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

    <?php include 'components/footer.php'; ?>


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

    
    </script>
</body>
</html>

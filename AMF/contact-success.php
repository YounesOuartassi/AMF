<?php
// Include database configuration
include('db_connect.php'); // Ensure this file contains your database connection settings

session_start();

$message = '';
$errors = [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['register'])) {
        // Collect and sanitize form data for registration
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
            // Check if the email already exists
            $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $errors['email'] = 'Un compte avec cette adresse e-mail existe déjà.';
            } else {
                // Hash the password
                $password_hash = password_hash($password, PASSWORD_BCRYPT);

                // Prepare SQL statement to prevent SQL injection
                $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, password_hash, email, phone, address, postal_code, city) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssss", $first_name, $last_name, $password_hash, $email, $phone, $address, $postal_code, $city);

                // Execute the statement
                if ($stmt->execute()) {
                    $_SESSION['registration_message'] = "Inscription réussie !";
                } else {
                    $errors['database'] = "Erreur: " . $stmt->error;
                }
            }

            // Close the statement
            $stmt->close();
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['show_modal'] = 'register';
        }
    }

    if (isset($_POST['login'])) {
        // Collect and sanitize form data for login
        $email = htmlspecialchars(trim($_POST['email']));
        $password = htmlspecialchars(trim($_POST['password']));

        // Server-side validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Adresse e-mail invalide.';
        }

        if (empty($errors)) {
            // Prepare SQL statement to prevent SQL injection
            $stmt = $conn->prepare("SELECT user_id, first_name, password_hash FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                // Bind result variables
                $stmt->bind_result($user_id, $first_name, $password_hash);
                $stmt->fetch();

                // Verify password
                if (password_verify($password, $password_hash)) {
                    // Password is correct
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['email'] = $email;
                    $_SESSION['first_name'] = $first_name;
                    $_SESSION['loggedin'] = true;

                    $_SESSION['login_message'] = "Connexion réussie !";
                    header('Location: index.php');
                    exit();
                } else {
                    $errors['login'] = "Mot de passe invalide";
                }
            } else {
                $errors['login'] = "Aucun compte trouvé avec cette adresse e-mail.";
            }

            // Close the statement
            $stmt->close();
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['show_modal'] = 'login';
        }
    }

    // Close the connection
    $conn->close();

    // Redirect to the same page to avoid resubmission
    if (!isset($_SESSION['login_message'])) {
        header('Location: index.php');
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

$message = '';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['access_key'])) {
    // Assuming that the API response will redirect or contain a specific response
    // Since Web3Forms API redirects on success, we'll just handle a simple redirect here
    header('Location: contact.php?success=1');
    exit();
}

if (isset($_GET['success']) && $_GET['success'] == 1) {
    $message = "Votre message a bien été envoyé.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Au Maraicher Des Flandres</title>
  <link rel="icon" href="./images/logo2.png" type="image/icon type">
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

  <!-- Google Maps API Script -->
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDWYEzjaZwBo1tS4zLC8lJQGUiMsAXGsKQ&callback=initMap" async defer></script>

  <!-- Inline Styles for Map -->
  <style>
    #map {
      height: 500px; /* Adjust as needed */
      width: 100%;
    }
  </style>

  <!-- Initialize Google Map -->
  <script>
    function initMap() {
      var location = { lat: 50.67116376823245, lng: 3.1451315679452234 }; // Coordinates for 252 Rue Jean Jaurès, Villeneuve-d'Ascq, France
      var map = new google.maps.Map(document.getElementById('map'), {
        zoom: 15,
        center: location
      });
      var marker = new google.maps.Marker({
        position: location,
        map: map,
        title: '252 Rue Jean Jaurès, Villeneuve-d\'Ascq, France'
      });
    }
  </script>
</head>
<body>
  <!-- Navbar -->
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

  <!-- Hero Section -->
  <div class="hero-wrap hero-bread" style="background-image: url('images/bg_1.jpg');">
    <div class="container">
      <div class="row no-gutters slider-text align-items-center justify-content-center">
        <div class="col-md-9 ftco-animate text-center">
          <p class="breadcrumbs"><span class="mr-2"><a href="index.html">Acceuil</a></span> <span>Contact</span></p>
          <h1 class="mb-0 bread">Nous Contacter</h1>
        </div>
      </div>
    </div>
  </div>
  
  <div class="container mt-4">



      <div class="alert alert-success alert-dismissible fade show" role="alert">
      votre message a bien été envoyé
      </div>

  </div>

  

<!-- Login Modal -->
<div class="modal fade conn" tabindex="-1" role="dialog" aria-labelledby="connexion" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Se Connecter</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
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

                    <?php if (isset($errors['password'])): ?>
                        <div class="alert alert-danger"><?php echo $errors['password']; ?></div>
                    <?php endif; ?>

                    <?php if (isset($message) && empty($errors)): ?>
                        <div class="alert alert-success"><?php echo $message; ?></div>
                    <?php endif; ?>

                    <p>Déjà inscrit ?
						<button type="button" class="btn btn-white" id="switchToLogin">Connectez-vous ici</button>
					</p>
										
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">S'inscrire</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Welcome Modal -->
<div class="modal fade welcome-modal" tabindex="-1" role="dialog" aria-labelledby="Welcome" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bonjour</h5>
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

<?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php endif; ?>

</div>
  <!-- Contact Section -->
  <section class="ftco-section contact-section bg-light">
    <div class="container">
      <!-- Contact Info -->
      <div class="row d-flex mb-5 contact-info">
        <div class="w-100"></div>
        <div class="col-md-3 d-flex">
          <div class="info bg-white p-4">
            <p><span>Address:</span> 252 Rue Jean Jaurès, Villeneuve-d'Ascq 59491, France</p>
          </div>
        </div>
        <div class="col-md-3 d-flex">
          <div class="info bg-white p-4">
            <p><span>Numero:</span> <a href="tel://+33 3 20 72 47 33">+33 3 20 72 47 33</a></p>
          </div>
        </div>
        <div class="col-md-6 d-flex">
          <div class="info bg-white p-4">
            <p><span>Email:</span> <a href="mailto:aumaraicherdesflandres@gmail.com">aumaraicherdesflandres@gmail.com</a></p>
          </div>
        </div>
      </div>

      <!-- Contact Form and Map -->
      <div class="row block-9">
        <div class="col-md-6 order-md-last d-flex">
          <form action="https://api.web3forms.com/submit" method="POST" class="bg-white p-5 contact-form">
            <input type="hidden" name="access_key" value="d681eb48-2de6-4e54-9f3b-0470d57c0863">
            <input type="hidden" name="redirect" value="http://localhost:3000/AMF/contact-success.php">
            <input type="hidden" name="subject" value="Nouveau message de contact">
            <input type="hidden" name="from_name" value="Message Contact">
            <div class="form-group">
              <input type="text" name="Nom" class="form-control" placeholder="Votre Nom" required>
            </div>
            <div class="form-group">
              <input type="email" name="email" class="form-control" placeholder="Votre Mail" required>
            </div>
            <div class="form-group">
              <input type="text" name="Sujet" class="form-control" placeholder="Sujet" required>
            </div>
            <div class="form-group">
              <textarea name="message" id="" cols="30" rows="7" class="form-control" placeholder="Message" required></textarea>
            </div>
            <div class="form-group">
              <input type="submit" value="Envoyer le Message" class="btn btn-primary py-3 px-5">
            </div>
          </form>
          
        </div>

        <div class="col-md-6 d-flex">
          <div id="map" class="bg-white"></div>
        </div>
      </div>



  <!-- Footer -->
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
              <ul class="list-unstyled mr-l-5 pr-l-3 mr-4">
                <li><a href="#" class="py-2 d-block">Informations sur la livraison</a></li>
                <li><a href="#" class="py-2 d-block">Retours & Échanges</a></li>
                <li><a href="#" class="py-2 d-block">Conditions générales</a></li>
                <li><a href="#" class="py-2 d-block">Politique de confidentialité</a></li>
              </ul>
              <ul class="list-unstyled">
                <li><a href="#" class="py-2 d-block">FAQ</a></li>
                <li><a href="#" class="py-2 d-block">Contact</a></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col-md">
          <div class="ftco-footer-widget mb-4">
            <h2 class="ftco-heading-2">Vous avez des questions ?</h2>
            <div class="block-23 mb-3">
              <ul>
                <li><span class="icon icon-map-marker"></span><span class="text">252 Rue Jean Jaurès, Villeneuve-d'Ascq 59491, France</span></li>
                <li><a href="#"><span class="icon icon-phone"></span><span class="text">+33 3 20 72 47 33</span></a></li>
                <li><a href="#"><span class="icon icon-envelope"></span><span class="text">aumaraicherdesflandres@gmail.com</span></a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <!-- Loader -->
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
  <script src="js/main.js"></script>
</body>
</html>

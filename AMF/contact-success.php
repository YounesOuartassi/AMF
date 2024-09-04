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
  <?php include 'components/navbar.php'; ?>


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
<?php include 'modals/login.php'; ?>
<!-- Register Modal -->
<?php include 'modals/register.php'; ?>
<!-- Welcome Modal -->
<?php include 'modals/welcome.php'; ?>
<!-- Display Alerts -->
<?php include 'modals/alerts.php'; ?>

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
  <?php include 'components/footer.php'; ?>


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

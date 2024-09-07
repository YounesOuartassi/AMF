<?php
// Include database configuration
include('db_connect.php'); // Ensure this file contains your database connection settings

session_start();

$message = '';
$errors = [];
include 'modals/modals_php.php';

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

  
  <?php 
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

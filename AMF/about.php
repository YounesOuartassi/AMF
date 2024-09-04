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
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Au Maraicher Des Flandres</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
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
    <link rel="icon" href="./images/logo2.png" type="image/icon type">

    
    <link rel="stylesheet" href="css/flaticon.css">
    <link rel="stylesheet" href="css/icomoon.css">
    <link rel="stylesheet" href="css/style.css">
  </head>	
  <?php include 'components/navbar.php'; ?>



    <div class="hero-wrap hero-bread" style="background-image: url('images/bg_1.jpg');">
      <div class="container">
        <div class="row no-gutters slider-text align-items-center justify-content-center">
          <div class="col-md-9 ftco-animate text-center">
          	<p class="breadcrumbs"><span class="mr-2"><a href="index.html">Acceuil</a></span> <span>à propos</span></p>
            <h1 class="mb-0 bread">à propos de nous</h1>
          </div>
        </div>
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

    <section class="ftco-section ftco-no-pb ftco-no-pt bg-light">
			<div class="container">
				<div class="row">
					<div class="col-md-5 p-md-5 img img-2 d-flex justify-content-center align-items-center" style="background-image: url(images/about.jpg);">
						<!-- <a href="https://vimeo.com/45830194" class="icon popup-vimeo d-flex justify-content-center align-items-center">
							<span class="icon-play"></span>
						</a> -->
					</div>
					<div class="col-md-7 py-5 wrap-about pb-md-5 ftco-animate">
	          <div class="heading-section-bold mb-4 mt-md-5">
	          	<div class="ml-md-0">
		            <h2 class="mb-4">Bienvenue Au Maraicher Des Flandres</h2>
	            </div>
	          </div>
	          <div class="pb-md-5">
	          	<p>Loin, très loin, derrière les montagnes de mots, loin des régions de Provence et de Bretagne, se trouvent les vergers de Fraîcheur, où poussent des fruits et légumes exceptionnels. Séparés de l'agitation urbaine, ils prospèrent à Verdureville, sur la côte des Saveurs, un vaste océan de fraîcheur et de goût.</p>
							<p>Mais rien ne pouvait convaincre la citadine habituée aux supermarchés, jusqu'à ce qu'une équipe de passionnés l'invite à découvrir nos produits. Ils l'ont emmenée en excursion, l'ont fait goûter aux délices de la nature, et l'ont guidée vers notre marché. Là, elle a découvert les saveurs authentiques et la qualité incomparable de nos fruits et légumes frais..</p>
							<p><a href="shop.html" class="btn btn-primary">Acheter maintenant</a></p>
						</div>
					</div>
				</div>
			</div>
		</section>

		
		<section class="ftco-section ftco-counter img" id="section-counter" style="background-image: url(images/bg_3.jpg);">
    	<div class="container">
    		<div class="row justify-content-center py-5">
    			<div class="col-md-10">
		    		<div class="row">
		          <div class="col-md-3 d-flex justify-content-center counter-wrap ftco-animate">
		            <div class="block-18 text-center">
		              <div class="text">
		                <strong class="number" data-number="10000">0</strong>
		                <span>Happy Customers</span>
		              </div>
		            </div>
		          </div>
		          <div class="col-md-3 d-flex justify-content-center counter-wrap ftco-animate">
		            <div class="block-18 text-center">
		              <div class="text">
		                <strong class="number" data-number="100">0</strong>
		                <span>Branches</span>
		              </div>
		            </div>
		          </div>
		          <div class="col-md-3 d-flex justify-content-center counter-wrap ftco-animate">
		            <div class="block-18 text-center">
		              <div class="text">
		                <strong class="number" data-number="1000">0</strong>
		                <span>Partner</span>
		              </div>
		            </div>
		          </div>
		          <div class="col-md-3 d-flex justify-content-center counter-wrap ftco-animate">
		            <div class="block-18 text-center">
		              <div class="text">
		                <strong class="number" data-number="100">0</strong>
		                <span>Awards</span>
		              </div>
		            </div>
		          </div>
		        </div>
	        </div>
        </div>
    	</div>
    </section>
		<section class="ftco-section">
			<div class="container">
				<div class="row no-gutters ftco-services">
          <div class="col-md-3 text-center d-flex align-self-stretch ftco-animate">
            <div class="media block-6 services mb-md-0 mb-4">
              <div class="icon bg-color-1 active d-flex justify-content-center align-items-center mb-2">
            		<span class="flaticon-shipped"></span>
              </div>
              <div class="media-body">
                <h3 class="heading">Livraison Gratuite</h3>
                <span>des 20€ D'achat</span>
              </div>
            </div>      
          </div>
          <div class="col-md-3 text-center d-flex align-self-stretch ftco-animate">
            <div class="media block-6 services mb-md-0 mb-4">
              <div class="icon bg-color-2 d-flex justify-content-center align-items-center mb-2">
            		<span class="flaticon-diet"></span>
              </div>
              <div class="media-body">
                <h3 class="heading">Toujours Frais</h3>
                <span>Produits bien emballé</span>
              </div>
            </div>    
          </div>
          <div class="col-md-3 text-center d-flex align-self-stretch ftco-animate">
            <div class="media block-6 services mb-md-0 mb-4">
              <div class="icon bg-color-3 d-flex justify-content-center align-items-center mb-2">
            		<span class="flaticon-award"></span>
              </div>
              <div class="media-body">
                <h3 class="heading">Qualité superieur</h3>
                <!-- <span>Q</span> -->
              </div>
            </div>      
          </div>
          <div class="col-md-3 text-center d-flex align-self-stretch ftco-animate">
            <div class="media block-6 services mb-md-0 mb-4">
              <div class="icon bg-color-4 d-flex justify-content-center align-items-center mb-2">
            		<span class="flaticon-customer-service"></span>
              </div>
              <div class="media-body">
                <h3 class="heading">Support</h3>
                <span>24/7 Support</span>
              </div>
            </div>      
          </div>
        </div>
			</div>
		</section>

    <?php include 'components/footer.php'; ?>

    
  

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
    
  </body>
</html>
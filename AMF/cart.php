<?php
// Include your database connection
include('db_connect.php');

// Start session to store messages
session_start();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $unit = htmlspecialchars($_POST['unit']);

    // Check if the product exists in the database
    $query = "SELECT * FROM product WHERE product_id = $product_id";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        // Product exists
        $product = mysqli_fetch_assoc($result);

        // Calculate the order total (This is a simplified version, assuming one item per order)
        $total_amount = $product['price_per_unit'] * $quantity;

        // Insert a new order in the `orders` table (You may need to add more details depending on your requirements)
        $insert_order_query = "INSERT INTO orders (full_name, email, total_amount) VALUES ('', '', $total_amount)";
        mysqli_query($conn, $insert_order_query);
        
        // Get the last inserted order_id
        $order_id = mysqli_insert_id($conn);

        // Insert the order item into the `order_items` table
        $insert_order_item_query = "INSERT INTO order_items (order_id, product_id, quantity, unit) VALUES ($order_id, $product_id, $quantity, '$unit')";
        mysqli_query($conn, $insert_order_item_query);

        // Set a session message
        $_SESSION['message'] = "Votre article a été ajouté au panier";

        // Redirect back to the shop page
        header("Location: shop.php");
        exit;
    } else {
        echo "Product not found.";
        exit;
    }
}

// Display the cart items
$query = "SELECT * FROM order_items oi JOIN product p ON oi.product_id = p.product_id WHERE oi.order_id IN (SELECT MAX(order_id) FROM orders)";
$result = mysqli_query($conn, $query);

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

    
    <link rel="stylesheet" href="css/flaticon.css">
    <link rel="stylesheet" href="css/icomoon.css">
    <link rel="stylesheet" href="css/style.css">
  </head>	
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

    <div class="hero-wrap hero-bread" style="background-image: url('images/bg_1.jpg');">
      <div class="container">
        <div class="row no-gutters slider-text align-items-center justify-content-center">
          <div class="col-md-9 ftco-animate text-center">
          	<p class="breadcrumbs"><span class="mr-2"><a href="index.html">Home</a></span> <span>Cart</span></p>
            <h1 class="mb-0 bread">My Cart</h1>
          </div>
        </div>
      </div>
    </div>

    <section class="ftco-section ftco-cart">
	<div class="container">
        <div class="row">
            <div class="col-md-12 ftco-animate">
                <div class="cart-list">
                    <table class="table">
                        <thead class="thead-primary">
                            <tr class="text-center">
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>Nom du produit</th>
                                <th>Prix</th>
                                <th>Quantité</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr class="text-center">
                                    <td class="product-remove"><a href="#"><span class="ion-ios-close"></span></a></td>
                                    
                                    <td class="image-">
                                        <div class="img" style="background-image:url(<?= htmlspecialchars($row['image_url']); ?>);"></div>
                                    </td>
                                    
                                    <td class="product-name">
                                        <h3><?= htmlspecialchars($row['name']); ?></h3>
                                    </td>
                                    
                                    <td class="price">€<?= htmlspecialchars($row['price_per_unit']); ?></td>
                                    
                                    <td class="quantity">
                                        <div class="input-group mb-3">
                                            <input type="text" name="quantity" class="quantity form-control input-number" value="<?= htmlspecialchars($row['quantity']); ?>" min="1" max="100" readonly>
                                        </div>
                                    </td>
                                    
                                    <td class="total">€<?= htmlspecialchars($row['price_per_unit'] * $row['quantity']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Shipping Information -->
            <div class="col-lg-4 mt-5 cart-wrap ftco-animate">
                <div class="cart-total mb-3">
                    <h3>Livraison</h3>
                    <p>Entrer votre adresse de Livraison</p>
                    <form action="#" class="info">
                        <div class="form-group">
                            <label for="numero">Numero</label>
                            <input type="text" class="form-control text-left px-3" placeholder="Entrer le numero">
                        </div>
                        <div class="form-group">
                            <label for="adresse">Adresse</label>
                            <input type="text" class="form-control text-left px-3" placeholder="Entrer l'adresse">
                        </div>
                        <div class="form-group">
                            <label for="zipcode">Zip/Code Postal</label>
                            <input type="text" class="form-control text-left px-3" placeholder="Entrer le code postal">
                        </div>
                        <div class="form-group">
                            <label for="region">Région</label>
                            <input type="text" class="form-control text-left px-3" placeholder="Entrer la région">
                        </div>
                    </form>
                </div>
                <p><a href="checkout.html" class="btn btn-primary py-3 px-4">Estimate</a></p>
            </div>

            <!-- Cart Totals -->
            <div class="col-lg-4 mt-5 cart-wrap ftco-animate">
                <div class="cart-total mb-3">
                    <h3>Total</h3>
                    <p class="d-flex">
                        <span>Sous-total</span>
                        <span>€<?php 
                            // Calculate subtotal
                            $subtotal_query = "SELECT SUM(p.price_per_unit * oi.quantity) AS subtotal FROM order_items oi JOIN product p ON oi.product_id = p.product_id WHERE oi.order_id IN (SELECT MAX(order_id) FROM orders)";
                            $subtotal_result = mysqli_query($conn, $subtotal_query);
                            $subtotal = mysqli_fetch_assoc($subtotal_result)['subtotal'];
                            echo number_format($subtotal, 2);
                        ?></span>
                    </p>
                    <p class="d-flex">
                        <span>Livraison</span>
                        <span>€0.00</span>
                    </p>
                    
                    <hr>
                    <p class="d-flex total-price">
                        <span>Total</span>
                        <span>€<?= number_format($subtotal, 2); ?></span>
                    </p>
                </div>
                <p><a href="checkout.html" class="btn btn-primary py-3 px-4">Passer à la caisse</a></p>
            </div>
        </div>
    </div>
    		</div>
    		<div class="row justify-content-end">
    			<!-- <div class="col-lg-4 mt-5 cart-wrap ftco-animate">
    				<div class="cart-total mb-3">
    					<h3>Coupon Code</h3>
    					<p>Enter your coupon code if you have one</p>
  						<form action="#" class="info">
	              <div class="form-group">
	              	<label for="">Coupon code</label>
	                <input type="text" class="form-control text-left px-3" placeholder="">
	              </div>
	            </form>
    				<
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
				  <li><a href="#" class="py-2 d-block">Contactez-nous</a></li></ul>
			  </div>
			</div>
			<div class="col-md-4">
			  <div class="ftco-footer-widget mb-4">
				<h2 class="ftco-heading-2">Aide</h2>
				<div class="d-flex">
				  <ul class="list-unstyled mr-l-5 pr-l-3 mr-4">
					<li><a href="#" class="py-2 d-block">Informations sur la livraison</a></li>
					<li><a href="#" class="py-2 d-block">Retours &amp; Échanges</a></li>
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
					<li><span class="icon icon-map-marker"></span><span class="text">252 Rue Jean Jaurès, Villeneuve-d'Ascq 59491, France				</span></li>
					<li><a href="#"><span class="icon icon-phone"></span><span class="text">+33 3 20 72 47 33</span></a></li>
					<li><a href="#"><span class="icon icon-envelope"></span><span class="text">aumaraicherdesflandres@gmail.com</span></a></li>
				  </ul>
				</div>
			  </div>
			</div>
		</div>
	</footer>
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

  <script>
		$(document).ready(function(){

		var quantitiy=0;
		   $('.quantity-right-plus').click(function(e){
		        
		        // Stop acting like a button
		        e.preventDefault();
		        // Get the field name
		        var quantity = parseInt($('#quantity').val());
		        
		        // If is not undefined
		            
		            $('#quantity').val(quantity + 1);

		          
		            // Increment
		        
		    });

		     $('.quantity-left-minus').click(function(e){
		        // Stop acting like a button
		        e.preventDefault();
		        // Get the field name
		        var quantity = parseInt($('#quantity').val());
		        
		        // If is not undefined
		      
		            // Increment
		            if(quantity>0){
		            $('#quantity').val(quantity - 1);
		            }
		    });
		    
		});
	</script>
    
  </body>
</html>
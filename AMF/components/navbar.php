<nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
    <div class="container">
        <a class="navbar-brand" href="index.php">Au Maraicher Des Flandres</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="oi oi-menu"></span> Menu
        </button>
        <div class="collapse navbar-collapse" id="ftco-nav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item active"><a href="index.php" class="nav-link">Accueil</a></li>
                <li class="nav-item"><a href="shop.php" class="nav-link">Acheter</a></li>
                <li class="nav-item"><a href="about.php" class="nav-link">À propos</a></li>
                <li class="nav-item"><a href="contact.php" class="nav-link">Contact</a></li>
                <button type="button" class="icon-users btn" data-toggle="modal" data-target="<?php echo isset($_SESSION['loggedin']) && $_SESSION['loggedin'] ? '.welcome-modal' : '.conn'; ?>"></button>

                <?php 
                // Check if the user is logged in
                if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) : 
                    // Count the number of items in the cart
                    $cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
                ?>
                    <li class="nav-item cta cta-colored">
                        <a href="cart.php" class="nav-link">
                            <span class="icon-shopping_cart"></span>[<?php echo $cartCount; ?>]
                        </a>
                    </li>
                <?php else : ?>
                    <li class="nav-item cta cta-colored">
                        <a href="#" class="nav-link" data-toggle="modal" data-target=".connx">
                            <span class="icon-shopping_cart"></span>[0]
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

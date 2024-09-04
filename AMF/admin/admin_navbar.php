<nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">Au Maraicher Des Flandres</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="oi oi-menu"></span> Menu
        </button>
        <div class="collapse navbar-collapse" id="ftco-nav">
            <ul class="navbar-nav ml-auto">
            <li class="nav-item active"><a href="dashboard.php" class="nav-link">Acceuil</a></li>
                <li class="nav-item"><a href="orders.php" class="nav-link">Commandes</a></li>
                <li class="nav-item"><a href="products.php" class="nav-link">Produits</a></li>
                <button type="button" class="icon-users btn" data-toggle="modal" data-target="<?php echo isset($_SESSION['loggedin']) && $_SESSION['loggedin'] ? '.welcome-modal' : '.conn'; ?>"></button>

            </ul>
        </div>
    </div>
</nav>
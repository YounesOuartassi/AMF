
<div class="modal fade welcome-modal" tabindex="-1" role="dialog" aria-labelledby="Welcome" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bienvenue</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Bonjour, <span class="font-weight-bold text-primary"><?php echo htmlspecialchars($_SESSION['first_name']); ?></span> !</p>
                <p>Merci d'être connecté</p>

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



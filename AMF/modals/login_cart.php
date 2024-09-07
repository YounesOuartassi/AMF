

<div class="modal fade connx" tabindex="-1" role="dialog" aria-labelledby="connexion" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Se Connecter</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="alert alert-success alert-dismissible fade show" role="alert">     
            Veuillez vous connecter pour voir votre panier !
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

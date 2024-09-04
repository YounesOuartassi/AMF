
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
                    <input type="number" id="postal_code" name="postal_code" class="form-control" required>

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
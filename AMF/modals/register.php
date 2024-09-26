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
                    <div class="form-row">
                        <div class="col">
                            <label for="first_name">Prénom</label>
                            <input type="text" id="first_name" name="first_name" class="form-control form-control-sm" required>
                        </div>
                        <div class="col">
                            <label for="last_name">Nom</label>
                            <input type="text" id="last_name" name="last_name" class="form-control form-control-sm" required>
                        </div>
                    </div>

                    <label for="email" class="mt-2">Email</label>
                    <input type="email" id="email" name="email" class="form-control form-control-sm" required>

                    <label for="password" class="mt-2">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control form-control-sm" required>

                    <label for="phone" class="mt-2">Téléphone</label>
                    <input type="number" id="phone" name="phone" class="form-control form-control-sm" required>

                    <label for="address" class="mt-2">Adresse</label>
                    <input type="text" id="address" name="address" class="form-control form-control-sm" required>

                    <div class="form-row mt-2">
                        <div class="col">
                            <label for="postal_code">Code postal</label>
                            <input type="number" id="postal_code" name="postal_code" class="form-control form-control-sm" required>
                        </div>
                        <div class="col">
                            <label for="city">Ville</label>
                            <input type="text" id="city" name="city" class="form-control form-control-sm" required>
                        </div>
                    </div>

                    <input type="hidden" name="register" value="1">

                    <!-- Error Messages -->
                    <?php if (isset($errors['fields'])): ?>
                        <div class="alert alert-danger mt-2"><?php echo htmlspecialchars($errors['fields']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($errors['email'])): ?>
                        <div class="alert alert-danger mt-2"><?php echo htmlspecialchars($errors['email']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($errors['phone'])): ?>
                        <div class="alert alert-danger mt-2"><?php echo htmlspecialchars($errors['phone']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($errors['password'])): ?>
                        <div class="alert alert-danger mt-2"><?php echo htmlspecialchars($errors['password']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($message) && empty($errors)): ?>
                        <div class="alert alert-success mt-2"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>

                    <p>Déjà inscrit ?
                        <button type="button" class="btn btn-white" id="switchToLogin">Connectez-vous ici</button>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-sm">S'inscrire</button>
                </div>
            </form>
        </div>
    </div>
</div>

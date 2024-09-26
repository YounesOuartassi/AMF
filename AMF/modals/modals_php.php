<?php

// Handle form submissions for login and registration
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors_register = [];
    $errors_login = [];
    $message = '';

    // Registration logic
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
            $errors_register['email'] = 'Adresse e-mail invalide.';
        }
        if (strlen($phone) != 10 || !ctype_digit($phone)) {
            $errors_register['phone'] = 'Le numéro de téléphone doit comporter 10 chiffres.';
        }
        if (strlen($password) < 6) {
            $errors_register['password'] = 'Le mot de passe doit comporter au moins 6 caractères.';
        }
        if (empty($first_name) || empty($last_name) || empty($address) || empty($postal_code) || empty($city)) {
            $errors_register['fields'] = 'Tous les champs sont obligatoires.';
        }

        if (empty($errors_register)) {
            // Check if the email already exists
            $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $errors_register['email'] = 'Un compte avec cette adresse e-mail existe déjà.';
            } else {
                // Hash the password
                $password_hash = password_hash($password, PASSWORD_BCRYPT);
                // Prepare SQL statement to prevent SQL injection
                $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, password_hash, email, phone, address, postal_code, city) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssss", $first_name, $last_name, $password_hash, $email, $phone, $address, $postal_code, $city);
                // Execute the statement
                if ($stmt->execute()) {
                    $message = "Inscription réussie !";
                    $_SESSION['registration_message'] = $message;
                } else {
                    $errors_register['database'] = "Erreur: " . $stmt->error;
                }
            }
            // Close the statement
            $stmt->close();
        }

        // Store registration errors if any
        if (!empty($errors_register)) {
            $_SESSION['errors_register'] = $errors_register;
            $_SESSION['show_modal'] = 'iden'; // Register modal
        }
    }

    // Login logic
    if (isset($_POST['login'])) {
        // Collect and sanitize form data for login
        $email = htmlspecialchars(trim($_POST['email']));
        $password = htmlspecialchars(trim($_POST['password']));

        // Server-side validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors_login['email'] = 'Adresse e-mail invalide.';
        }

        if (empty($errors_login)) {
            // Prepare SQL statement to prevent SQL injection
            $stmt = $conn->prepare("SELECT user_id, first_name, password_hash, is_admin FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                // Bind result variables, including `is_admin`
                $stmt->bind_result($user_id, $first_name, $password_hash, $is_admin);
                $stmt->fetch();

                // Verify password
                if (password_verify($password, $password_hash)) {
                    // Password is correct
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['email'] = $email;
                    $_SESSION['first_name'] = $first_name;
                    $_SESSION['loggedin'] = true;
                    $_SESSION['is_admin'] = $is_admin;

                    // Check if the user is an admin and redirect accordingly
                    if ($is_admin) {
                        header('Location: admin/dashboard.php');
                    } else {
                        $message = "Connexion réussie !";
                        $_SESSION['login_message'] = $message;
                    }
                    exit();
                } else {
                    $errors_login['login'] = "Mot de passe invalide";
                }
            } else {
                $errors_login['login'] = "Aucun compte trouvé avec cette adresse e-mail.";
            }

            // Close the statement
            $stmt->close();
        }

        // Store login errors if any
        if (!empty($errors_login)) {
            $_SESSION['errors_login'] = $errors_login;
            $_SESSION['show_modal'] = 'conn'; // Login modal
        }
    }

    // Close the connection
    $conn->close();

    // Redirect to avoid form resubmission
    if (empty($errors_register) && empty($errors_login) && empty($message)) {
        header('Location: index.php');
        exit();
    }
}
?>

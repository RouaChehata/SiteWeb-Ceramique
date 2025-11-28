<?php
require_once 'config.php';

$errors = [];
$success = false;
$validToken = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    try {
        $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expiry > NOW() AND is_active = TRUE");
        $stmt->execute([$token]);
        
        if ($stmt->rowCount() > 0) {
            $validToken = true;
        } else {
            $errors[] = "Le lien de réinitialisation est invalide ou a expiré";
        }
    } catch(PDOException $e) {
        $errors[] = "Une erreur est survenue";
    }
} else {
    $errors[] = "Token de réinitialisation manquant";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    if (empty($password)) {
        $errors[] = "Le mot de passe est requis";
    } elseif (strlen($password) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
    } elseif (!preg_match("/[A-Z]/", $password)) {
        $errors[] = "Le mot de passe doit contenir au moins une majuscule";
    } elseif (!preg_match("/[a-z]/", $password)) {
        $errors[] = "Le mot de passe doit contenir au moins une minuscule";
    } elseif (!preg_match("/[0-9]/", $password)) {
        $errors[] = "Le mot de passe doit contenir au moins un chiffre";
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = "Les mots de passe ne correspondent pas";
    }

    if (empty($errors)) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE reset_token = ?");
            $stmt->execute([$hashedPassword, $token]);
            $success = true;
        } catch(PDOException $e) {
            $errors[] = "Une erreur est survenue lors de la réinitialisation du mot de passe";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau mot de passe - Ceramic Art Nour</title>
    <link rel="stylesheet" href="ce.css">
    <style>
        .new-password-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .new-password-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .new-password-header h1 {
            color: #b48a92;
            margin-bottom: 10px;
        }
        .new-password-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .form-group label {
            color: #333;
            font-weight: 500;
        }
        .form-group input {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #b48a92;
        }
        .new-password-btn {
            background-color: #b48a92;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .new-password-btn:hover {
            background-color: #e8a7b0;
        }
        .back-home {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #b48a92;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .back-home:hover {
            background-color: #e8a7b0;
        }
        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .success-message {
            color: #28a745;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <header>
        <nav class="main-nav">
            <div class="header-left">
                <img src="im0.png" alt="Logo Ceramic Art Nour" class="header-logo">
            </div>
            <ul class="nav-links">
                <li><a href="ce.php" class="nav-button">Accueil</a></li>
                <li><a href="#categories" class="nav-button">Catégories</a></li>
                <li><a href="contact.php" class="nav-button">Contact</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="new-password-container">
            <div class="new-password-header">
                <h1>Nouveau mot de passe</h1>
                <p>Créez un nouveau mot de passe pour votre compte</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message">
                    <p>Votre mot de passe a été réinitialisé avec succès.</p>
                    <p>Vous pouvez maintenant vous connecter avec votre nouveau mot de passe.</p>
                </div>
            <?php endif; ?>

            <?php if ($validToken && !$success): ?>
                <form class="new-password-form" action="new_password.php?token=<?php echo htmlspecialchars($token); ?>" method="POST">
                    <div class="form-group">
                        <label for="password">Nouveau mot de passe</label>
                        <input type="password" id="password" name="password" required placeholder="Votre nouveau mot de passe">
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirmer le mot de passe</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" required placeholder="Confirmez votre nouveau mot de passe">
                    </div>
                    <button type="submit" class="new-password-btn">Réinitialiser le mot de passe</button>
                </form>
            <?php endif; ?>

            <div style="text-align: center; margin-top: 20px;">
                <a href="login.php" class="back-home">Retour à la connexion</a>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Ceramic Art Nour. Tous droits réservés.</p>
        <div class="footer-bottom">
            <img src="im0.png" alt="Logo Ceramic Art Nour" class="footer-logo">
            <a href="https://www.instagram.com/ceramic_art_nour/" target="_blank" class="instagram-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-instagram">
                    <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                    <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                    <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                </svg>
                @ceramic_art_nour
            </a>
            <p class="location">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-home">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                Mahdia Tunisie
            </p>
        </div>
    </footer>
</body>
</html> 
<?php
ob_start();
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

$errors = [];

// Check for and display database errors from session
if (isset($_SESSION['db_error'])) {
    $errors[] = $_SESSION['db_error'];
    unset($_SESSION['db_error']);
}

// Initialize variables for input values to retain them after submission
$username_val = $_POST['username'] ?? '';
$email_val = $_POST['email'] ?? '';
$fullname_val = $_POST['fullname'] ?? '';
$address_val = $_POST['address'] ?? '';
$phone_val = $_POST['phone'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validation des données
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $fullname = trim($_POST['fullname'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    // Update values for retaining input
    $username_val = htmlspecialchars($username);
    $email_val = htmlspecialchars($email);
    $fullname_val = htmlspecialchars($fullname);
    $address_val = htmlspecialchars($address);
    $phone_val = htmlspecialchars($phone);

    // Validation du nom d'utilisateur
    if (empty($username)) {
        $errors[] = "Le nom d'utilisateur est requis";
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = "Le nom d'utilisateur doit contenir entre 3 et 50 caractères";
    }

    // Validation de l'email
    if (empty($email)) {
        $errors[] = "L'email est requis";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide";
    }

    // Validation du mot de passe
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

    // Validation du nom complet
    if (empty($fullname)) {
        $errors[] = "Le nom complet est requis";
    }

    // Validation de l'adresse
    if (empty($address)) {
        $errors[] = "L'adresse est requise";
    }

    // Validation du téléphone
    if (empty($phone)) {
        $errors[] = "Le numéro de téléphone est requis";
    } elseif (!preg_match("/^[0-9]{8,15}$/", $phone)) {
        $errors[] = "Format de numéro de téléphone invalide";
    }

    if (empty($errors)) {
        try {
            // Vérification si l'email ou le nom d'utilisateur existe déjà
            $checkStmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR username = ?");
            $checkStmt->execute([$email, $username]);
            if ($checkStmt->fetchColumn() > 0) {
                $errors[] = "Cet email ou ce nom d'utilisateur est déjà utilisé";
            } else {
                // Hashage du mot de passe
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Insertion de l'utilisateur
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, fullname, address, phone) VALUES (:username, :email, :password, :fullname, :address, :phone)");
                
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashedPassword);
                $stmt->bindParam(':fullname', $fullname);
                $stmt->bindParam(':address', $address);
                $stmt->bindParam(':phone', $phone);

                $stmt->execute();
                
                // Création de la session
                $_SESSION['user_id'] = $conn->lastInsertId();
                $_SESSION['user_name'] = $fullname;
                $_SESSION['user_email'] = $email;
                
                header("Location: login.php");
                exit();
            }
        } catch(PDOException $e) {
            $errors[] = "Erreur lors de l'inscription : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Ceramic Art Nour</title>
    <link rel="stylesheet" href="ce.css">
    <script src="validation.js"></script>
    <style>
        .register-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .register-btn {
            background-color: #e8a7b0;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        .register-btn:hover {
            background-color: #d48a94;
        }
        .login-link {
            text-align: center;
            margin-top: 15px;
        }
        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
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
        <div class="register-container">
            <h2>Inscription</h2>
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form action="register.php" method="POST">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" required value="<?php echo $username_val; ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required value="<?php echo $email_val; ?>">
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="fullname">Nom complet</label>
                    <input type="text" id="fullname" name="fullname" required value="<?php echo $fullname_val; ?>">
                </div>
                <div class="form-group">
                    <label for="address">Adresse</label>
                    <input type="text" id="address" name="address" required value="<?php echo $address_val; ?>">
                </div>
                <div class="form-group">
                    <label for="phone">Téléphone</label>
                    <input type="tel" id="phone" name="phone" required value="<?php echo $phone_val; ?>">
                </div>
                <button type="submit" class="register-btn">S'inscrire</button>
            </form>
            <div class="login-link">
                <p>Déjà inscrit ? <a href="login.php">Connectez-vous</a></p>
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
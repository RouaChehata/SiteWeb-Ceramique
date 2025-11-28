<?php
session_start();
require_once 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$errors = [];
$success = false;

// Récupérer les informations de l'utilisateur
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch(PDOException $e) {
    $errors[] = "Une erreur est survenue lors de la récupération des données";
}

// Traiter le formulaire de mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    // Validation des données
    if (empty($fullname)) {
        $errors[] = "Le nom complet est requis";
    }
    if (empty($email)) {
        $errors[] = "L'email est requis";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email n'est pas valide";
    }
    if (empty($phone)) {
        $errors[] = "Le numéro de téléphone est requis";
    }

    // Vérifier si l'email est déjà utilisé par un autre utilisateur
    if ($email !== $user['email']) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        if ($stmt->rowCount() > 0) {
            $errors[] = "Cet email est déjà utilisé";
        }
    }

    // Si un nouveau mot de passe est fourni
    if (!empty($newPassword)) {
        if (strlen($newPassword) < 6) {
            $errors[] = "Le nouveau mot de passe doit contenir au moins 6 caractères";
        }
        if ($newPassword !== $confirmPassword) {
            $errors[] = "Les mots de passe ne correspondent pas";
        }
        if (empty($currentPassword)) {
            $errors[] = "Le mot de passe actuel est requis pour changer le mot de passe";
        } elseif (!password_verify($currentPassword, $user['password'])) {
            $errors[] = "Le mot de passe actuel est incorrect";
        }
    }

    if (empty($errors)) {
        try {
            if (!empty($newPassword)) {
                // Mettre à jour avec le nouveau mot de passe
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ?, phone = ?, password = ? WHERE id = ?");
                $stmt->execute([$fullname, $email, $phone, $hashedPassword, $_SESSION['user_id']]);
            } else {
                // Mettre à jour sans changer le mot de passe
                $stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ?, phone = ? WHERE id = ?");
                $stmt->execute([$fullname, $email, $phone, $_SESSION['user_id']]);
            }
            $success = true;
            $_SESSION['user_name'] = $fullname;
        } catch(PDOException $e) {
            $errors[] = "Une erreur est survenue lors de la mise à jour du profil";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Ceramic Art Nour</title>
    <link rel="stylesheet" href="ce.css">
    <style>
        .profile-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .profile-header h1 {
            color: #b48a92;
            margin-bottom: 10px;
        }
        .profile-form {
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
        .update-btn {
            background-color: #b48a92;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .update-btn:hover {
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
        .form-row {
            display: flex;
            gap: 20px;
        }
        .form-row .form-group {
            flex: 1;
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
        .password-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
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
            <div class="user-menu">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="profile.php" class="profile-btn">Mon profil</a>
                <a href="logout.php" class="logout-btn">Déconnexion</a>
            </div>
        </nav>
    </header>

    <main>
        <div class="profile-container">
            <div class="profile-header">
                <h1>Mon Profil</h1>
                <p>Gérez vos informations personnelles</p>
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
                    <p>Votre profil a été mis à jour avec succès.</p>
                </div>
            <?php endif; ?>

            <form class="profile-form" action="profile.php" method="POST">
                <div class="form-group">
                    <label for="fullname">Nom complet</label>
                    <input type="text" id="fullname" name="fullname" required value="<?php echo htmlspecialchars($user['fullname']); ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>
                <div class="form-group">
                    <label for="phone">Téléphone</label>
                    <input type="tel" id="phone" name="phone" required value="<?php echo htmlspecialchars($user['phone']); ?>">
                </div>
                <div class="password-section">
                    <h3>Changer le mot de passe</h3>
                    <div class="form-group">
                        <label for="currentPassword">Mot de passe actuel</label>
                        <input type="password" id="currentPassword" name="currentPassword" placeholder="Votre mot de passe actuel">
                    </div>
                    <div class="form-group">
                        <label for="newPassword">Nouveau mot de passe</label>
                        <input type="password" id="newPassword" name="newPassword" placeholder="Votre nouveau mot de passe">
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirmer le nouveau mot de passe</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirmez votre nouveau mot de passe">
                    </div>
                </div>
                <button type="submit" class="update-btn">Mettre à jour le profil</button>
            </form>
            <div style="text-align: center; margin-top: 20px;">
                <a href="ce.php" class="back-home">Retour à l'accueil</a>
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
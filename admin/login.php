<?php
session_start();
require_once '../config.php';

// Si déjà connecté, rediriger vers le dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username)) {
        $errors[] = "Le nom d'utilisateur est requis";
    }
    if (empty($password)) {
        $errors[] = "Le mot de passe est requis";
    }

    if (empty($errors)) {
        try {
            // Vérifier si c'est un admin (pour l'instant, on utilise un admin par défaut)
            // En production, vous devriez avoir une table admins séparée
            if ($username === 'admin' && $password === 'admin123') {
                $_SESSION['admin_id'] = 1;
                $_SESSION['admin_username'] = 'admin';
                header('Location: index.php');
                exit();
            } else {
                $errors[] = "Nom d'utilisateur ou mot de passe incorrect";
            }
        } catch(PDOException $e) {
            $errors[] = "Une erreur est survenue lors de la connexion";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin - Ceramic Art Nour</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <img src="../im0.png" alt="Logo" class="login-logo">
                <h1>Administration</h1>
                <p>Connectez-vous à votre espace d'administration</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form class="login-form" method="POST">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i>
                        Nom d'utilisateur
                    </label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Mot de passe
                    </label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Se connecter
                </button>
            </form>

            <div class="login-footer">
                <a href="../ce.html" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    Retour au site
                </a>
            </div>

            <div class="demo-info">
                <p><strong>Identifiants de démonstration :</strong></p>
                <p>Utilisateur : admin</p>
                <p>Mot de passe : admin123</p>
            </div>
        </div>
    </div>
</body>
</html> 
<?php
session_start();
$name = $email = $message = '';
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if ($name && $email && $message) {
        // Enregistrer le message dans la base de données
        require_once 'config.php';
        try {
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $message]);
            $success = 'Merci pour votre message ! Nous vous répondrons bientôt.';
            $name = $email = $message = '';
        } catch (PDOException $e) {
            $error = "Erreur lors de l'enregistrement du message. Veuillez réessayer.";
        }
    } else {
        $error = 'Merci de remplir tous les champs.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Contact - Ceramic Art Nour">
    <title>Contact - Ceramic Art Nour</title>
    <link rel="stylesheet" href="ce.css">
    <link rel="stylesheet" href="cart.css">
</head>
<body>
    <header>
        <nav class="main-nav">
            <div class="header-left">
                <img src="im0.png" alt="Logo Ceramic Art Nour" class="header-logo">
                <form class="search-form">
                    <div class="search-input-wrapper">
                        <span class="search-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#b48a92" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </span>
                        <input type="text" placeholder="chercher des produits">
                    </div>
                    <button type="submit" class="search-btn">Rechercher</button>
                </form>
            </div>
            <ul class="nav-links">
                <li><a href="ce.php" class="nav-button">Accueil</a></li>
                <li><a href="categories.php" class="nav-button">Catégories</a></li>
                <li><a href="contact.php" class="nav-button active">Contact</a></li>
            </ul>
            <button class="cart-icon" onclick="toggleCart()" title="Voir le panier">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#e8a7b0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            </button>
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="profile-dropdown">
                    <button class="profile-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#e8a7b0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </button>
                    <div class="profile-dropdown-content">
                        <a href="profile.php">Profile</a>
                        <a href="order_history.php">Order History</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php" class="login-btn">Login</a>
            <?php endif; ?>
        </nav>
    </header>

    <!-- Conteneur du panier -->
    <div class="cart-overlay" onclick="toggleCart()"></div>
    <div class="cart-container">
        <div class="cart-header">
            <h2>Votre Panier</h2>
            <button class="close-cart" onclick="toggleCart()">×</button>
        </div>
        <div id="cart-items"></div>
        <form id="checkoutForm" action="checkout.php" method="POST">
            <input type="hidden" name="cartData" id="cartDataInput">
            <button type="submit" class="checkout-btn">Passer la commande</button>
        </form>
    </div>

    <main>
        <section class="contact-section">
            <h2 class="products-title">Contactez-nous</h2>
            <?php if ($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php elseif ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="post" class="contact-form" autocomplete="off">
                <div class="form-group">
                    <label for="name">Nom</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" rows="5" required><?php echo htmlspecialchars($message); ?></textarea>
                </div>
                <button type="submit" class="search-btn">Envoyer</button>
            </form>
        </section>
        <section class="contact-infos">
            <div class="info-item">
                <span class="info-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#b48a92" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                </span>
                <span>Mahdia, Tunisie</span>
            </div>
            <div class="info-item">
                <span class="info-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#b48a92" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                </span>
                <span><a href="https://www.instagram.com/ceramic_art_nour/" target="_blank" style="color:inherit;text-decoration:none;">@ceramic_art_nour</a></span>
            </div>
            <div class="info-item">
                <span class="info-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#b48a92" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92V19a2 2 0 0 1-2.18 2A19.72 19.72 0 0 1 3 5.18 2 2 0 0 1 5 3h2.09a2 2 0 0 1 2 1.72c.13 1.06.37 2.09.73 3.08a2 2 0 0 1-.45 2.11l-.27.27a16 16 0 0 0 6.29 6.29l.27-.27a2 2 0 0 1 2.11-.45c.99.36 2.02.6 3.08.73A2 2 0 0 1 22 16.92z"></path></svg>
                </span>
                <span>+216 XX XXX XXX</span>
            </div>
        </section>
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

    <script src="carousel.js"></script>
    <script src="cart.js"></script>
    <script>
        function toggleCart() {
            document.querySelector('.cart-container').classList.toggle('open');
            document.querySelector('.cart-overlay').classList.toggle('open');
        }
    </script>
    <style>
        .contact-section {
            max-width: 500px;
            margin: 3rem auto;
            background: #f8e1e4;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(180,138,146,0.08);
            padding: 2.5rem 2rem;
        }
        .contact-form .form-group {
            margin-bottom: 1.5rem;
        }
        .contact-form label {
            display: block;
            margin-bottom: 0.5rem;
            color: #b48a92;
            font-weight: 500;
        }
        .contact-form input,
        .contact-form textarea {
            width: 100%;
            padding: 0.7rem 1rem;
            border: 1px solid #e8a7b0;
            border-radius: 8px;
            font-size: 1rem;
            background: #fff;
            color: #b48a92;
        }
        .contact-form textarea {
            resize: vertical;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .contact-infos {
            margin: 2.5rem auto 0 auto;
            max-width: 500px;
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
            align-items: flex-start;
        }
        .info-item {
            display: flex;
            align-items: center;
            font-size: 1.2rem;
            color: #7d5c65;
            gap: 0.7rem;
        }
        .info-icon {
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</body>
</html> 
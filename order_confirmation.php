<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de Commande - Ceramic Art Nour</title>
    <link rel="stylesheet" href="ce.css">
    <style>
        .confirmation-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        .confirmation-container h1 {
            color: #b48a92;
            margin-bottom: 20px;
        }
        .confirmation-container p {
            font-size: 1.1em;
            margin-bottom: 15px;
        }
        .order-id {
            font-weight: bold;
            color: #e8a7b0;
        }
        .delivery-details {
            margin: 25px 0;
            padding: 20px;
            background-color: #f9f0f2;
            border-radius: 8px;
            text-align: left;
        }
        .delivery-details h3 {
            color: #b48a92;
            margin-top: 0;
            border-bottom: 1px solid #e8a7b0;
            padding-bottom: 10px;
        }
        .delivery-details p {
            margin: 10px 0;
            font-size: 1em;
        }
        .back-to-home {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 25px;
            background-color: #b48a92;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease, box-shadow 0.3s ease, transform 0.3s ease;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.05em;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .back-to-home:hover {
            background-color: #e8a7b0;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
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
        <div class="confirmation-container">
            <h1>Commande Confirmée !</h1>
            <?php
                session_start();
                if (isset($_GET['order_id'])) {
                    $orderId = $_GET['order_id'];
                    $orderKey = 'order_' . $orderId;
                    
                    echo '<p>Merci pour votre commande. Votre numéro de commande est : <span class="order-id">' . htmlspecialchars($orderId) . '</span></p>';
                    
                    // Afficher les détails de livraison si disponibles
                    if (isset($_SESSION[$orderKey])) {
                        $deliveryInfo = $_SESSION[$orderKey];
                        echo '<div class="delivery-details">';
                        echo '<h3>Détails de livraison</h3>';
                        echo '<p><strong>Nom :</strong> ' . htmlspecialchars($deliveryInfo['fullname']) . '</p>';
                        echo '<p><strong>Email :</strong> ' . htmlspecialchars($deliveryInfo['email']) . '</p>';
                        echo '<p><strong>Adresse :</strong> ' . htmlspecialchars($deliveryInfo['address']) . '</p>';
                        echo '<p><strong>Téléphone :</strong> ' . htmlspecialchars($deliveryInfo['phone']) . '</p>';
                        if (!empty($deliveryInfo['notes'])) {
                            echo '<p><strong>Notes :</strong> ' . htmlspecialchars($deliveryInfo['notes']) . '</p>';
                        }
                        echo '</div>';
                        
                        // Nettoyer la session après affichage
                        unset($_SESSION[$orderKey]);
                    }
                } else {
                    echo '<p>Merci pour votre commande !</p>';
                }
            ?>
            <p>Votre commande est en cours de préparation. Nous vous contacterons bientôt pour confirmer la livraison.</p>
            <a href="ce.html" class="back-to-home">Retour à l'accueil</a>
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
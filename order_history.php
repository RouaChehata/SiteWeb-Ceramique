<?php
session_start();
require_once 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$orders = [];
$error_message = '';

try {
    // Récupérer les commandes de l'utilisateur
    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pour chaque commande, récupérer les articles commandés avec les détails des produits
    foreach ($orders as &$order) {
        $stmt_items = $conn->prepare("
            SELECT oi.*, p.name as product_name 
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
        $stmt_items->execute([$order['id']]);
        $order['items'] = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    $error_message = "Erreur lors de la récupération de l'historique des commandes : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique des Commandes - Ceramic Art Nour</title>
    <link rel="stylesheet" href="ce.css">
    <style>
        .order-history-container {
            max-width: 900px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .order-history-container h1 {
            color: #b48a92;
            text-align: center;
            margin-bottom: 30px;
        }
        .order-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .order-card h2 {
            color: #b48a92;
            font-size: 1.5em;
            margin-top: 0;
            border-bottom: 1px solid #e8a7b0;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .order-details p {
            margin: 5px 0;
            font-size: 1.1em;
            color: #555;
        }
        .order-items {
            margin-top: 20px;
            border-top: 1px dashed #e0e0e0;
            padding-top: 15px;
        }
        .order-items h3 {
            color: #777;
            font-size: 1.2em;
            margin-bottom: 10px;
        }
        .order-items ul {
            list-style: none;
            padding: 0;
        }
        .order-items li {
            background-color: #fff;
            border: 1px solid #eee;
            padding: 10px;
            margin-bottom: 8px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 1em;
        }
        .order-items li span {
            font-weight: bold;
            color: #333;
        }
        .no-orders {
            text-align: center;
            color: #777;
            font-size: 1.2em;
            padding: 50px;
        }
        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
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

    <main>
        <div class="order-history-container">
            <h1>Historique des Commandes</h1>

            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <p><?php echo htmlspecialchars($error_message); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <h2>Commande #<?php echo htmlspecialchars($order['id']); ?> - Date: <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($order['created_at']))); ?></h2>
                        <div class="order-details">
                            <p><strong>Total:</strong> <?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?> TND</p>
                            <p><strong>Statut:</strong> <?php echo htmlspecialchars(ucfirst($order['status'])); ?></p>
                            <?php 
                            $orderKey = 'order_' . $order['id'];
                            if (isset($_SESSION[$orderKey])): 
                                $deliveryInfo = $_SESSION[$orderKey];
                            ?>
                                <p><strong>Adresse de livraison:</strong> <?php echo htmlspecialchars($deliveryInfo['address']); ?></p>
                                <p><strong>Téléphone:</strong> <?php echo htmlspecialchars($deliveryInfo['phone']); ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($order['items'])): ?>
                            <div class="order-items">
                                <h3>Articles:</h3>
                                <ul>
                                    <?php foreach ($order['items'] as $item): ?>
                                        <li>
                                            <span><?php echo htmlspecialchars($item['product_name']); ?></span>
                                            <span>Quantité: <?php echo htmlspecialchars($item['quantity']); ?></span>
                                            <span>Prix Unitaire: <?php echo htmlspecialchars(number_format($item['price'], 2)); ?> TND</span>
                                            <?php if (!empty($item['color'])): ?>
                                                <span>Couleur: <?php echo htmlspecialchars($item['color']); ?></span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php else: ?>
                            <p>Aucun article pour cette commande.</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-orders">Vous n'avez pas encore passé de commandes.</p>
            <?php endif; ?>
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
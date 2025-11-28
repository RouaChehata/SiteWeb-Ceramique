<?php
session_start();
require_once 'config.php';

$errors = [];
$cartItems = [];
$totalAmount = 0;

// Vérifier si des données de panier ont été soumises
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cartData'])) {
    $cartData = json_decode($_POST['cartData'], true);
    if (json_last_error() === JSON_ERROR_NONE && !empty($cartData)) {
        $cartItems = $cartData;
        foreach ($cartItems as $item) {
            $totalAmount += (isset($item['price']) ? $item['price'] : 0) * (isset($item['quantity']) ? $item['quantity'] : 1);
        }
    } else {
        $errors[] = "Erreur de lecture des données du panier ou panier vide.";
    }
}

// Si l'utilisateur est connecté, récupérer ses informations pour pré-remplir le formulaire
$user = null;
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $conn->prepare("SELECT fullname, email, address, phone FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        $errors[] = "Erreur lors de la récupération des informations utilisateur: " . $e->getMessage();
    }
}

// Traitement du formulaire de commande
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    if (empty($cartItems)) {
        $errors[] = "Votre panier est vide. Impossible de passer commande.";
    }

    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (empty($fullname)) { $errors[] = "Le nom complet est requis."; }
    if (empty($email)) { $errors[] = "L'email est requis."; } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "Format d'email invalide."; }
    if (empty($address)) { $errors[] = "L'adresse de livraison est requise."; }
    if (empty($phone)) { $errors[] = "Le numéro de téléphone est requis."; }

    if (empty($errors)) {
        try {
            $conn->beginTransaction();

            // Insérer la commande
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, ?)");
            $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            $stmt->execute([$userId, $totalAmount, 'pending']);
            $orderId = $conn->lastInsertId();

            // Stocker les informations de livraison dans la session
            $_SESSION['order_' . $orderId] = [
                'fullname' => $fullname,
                'email' => $email,
                'address' => $address,
                'phone' => $phone,
                'notes' => $notes
            ];

            // Insérer les articles de la commande
            $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $updateStockStmt = $conn->prepare("UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE id = ?");
            foreach ($cartItems as $item) {
                $itemStmt->execute([$orderId, $item['id'], $item['quantity'], $item['price']]);
                // Mettre à jour le stock du produit
                $updateStockStmt->execute([$item['quantity'], $item['id']]);
            }

            $conn->commit();

            // Vider le panier côté client après la commande réussie
            echo "<script>localStorage.removeItem('cart');</script>";
            header('Location: order_confirmation.php?order_id=' . $orderId);
            exit();

        } catch (PDOException $e) {
            $conn->rollBack();
            $errors[] = "Erreur lors de la commande: " . $e->getMessage();
            file_put_contents('db_error.log', 'Erreur de commande: ' . $e->getMessage() . "\n", FILE_APPEND);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commande - Ceramic Art Nour</title>
    <link rel="stylesheet" href="ce.css">
    <style>
        .checkout-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .checkout-container h1 {
            color: #b48a92;
            margin-bottom: 20px;
            text-align: center;
        }
        .checkout-summary, .delivery-form {
            margin-bottom: 30px;
            border: 1px solid #f0c4c8;
            border-radius: 8px;
            padding: 20px;
            background-color: #fff9fa;
        }
        .checkout-summary h2, .delivery-form h2 {
            color: #b48a92;
            margin-bottom: 15px;
            text-align: center;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #f8e1e4;
        }
        .summary-item:last-child {
            border-bottom: none;
        }
        .summary-total {
            font-weight: bold;
            font-size: 1.2em;
            padding-top: 15px;
            border-top: 2px solid #e8a7b0;
            margin-top: 15px;
            text-align: right;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }
        .form-group input, .form-group textarea {
            width: calc(100% - 22px);
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .place-order-btn {
            background-color: #b48a92;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            transition: background-color 0.3s ease, box-shadow 0.3s ease, transform 0.3s ease;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.05em;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .place-order-btn:hover {
            background-color: #e8a7b0;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
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
        <div class="checkout-container">
            <h1>Passer votre commande</h1>

            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="checkout-summary">
                <h2>Récapitulatif de la commande</h2>
                <?php if (!empty($cartItems)): ?>
                    <?php foreach ($cartItems as $item): ?>
                        <div class="summary-item">
                            <span><?php echo isset($item['name']) ? htmlspecialchars($item['name']) : 'Produit'; ?> (<?php echo isset($item['color']) ? htmlspecialchars($item['color']) : ''; ?>) x <?php echo isset($item['quantity']) ? htmlspecialchars($item['quantity']) : '1'; ?></span>
                            <span><?php echo isset($item['price']) ? htmlspecialchars(number_format($item['price'] * (isset($item['quantity']) ? $item['quantity'] : 1), 2)) : '0.00'; ?> TND</span>
                        </div>
                    <?php endforeach; ?>
                    <div class="summary-total">
                        Total: <?php echo htmlspecialchars(number_format($totalAmount, 2)); ?> TND
                    </div>
                <?php else: ?>
                    <p>Votre panier est vide.</p>
                <?php endif; ?>
            </div>

            <div class="delivery-form">
                <h2>Informations de livraison</h2>
                <form action="checkout.php" method="POST">
                    <input type="hidden" name="cartData" value="<?php echo htmlspecialchars(json_encode($cartItems)); ?>">
                    
                    <div class="form-group">
                        <label for="fullname">Nom complet</label>
                        <input type="text" id="fullname" name="fullname" required value="<?php echo htmlspecialchars($user['fullname'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="address">Adresse de livraison</label>
                        <textarea id="address" name="address" rows="3" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="phone">Numéro de téléphone</label>
                        <input type="tel" id="phone" name="phone" required value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="notes">Notes (facultatif)</label>
                        <textarea id="notes" name="notes" rows="3"></textarea>
                    </div>
                    
                    <button type="submit" name="place_order" class="place-order-btn">Confirmer la commande</button>
                </form>
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
<?php
session_start();
require_once '../config.php';

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$order = null;
$order_items = [];
$error = '';

if ($order_id > 0) {
    // Récupérer les détails de la commande avec les informations du client
    $stmt = $conn->prepare("
        SELECT o.*, 
               COALESCE(u.fullname, 'Guest') as fullname,
               COALESCE(u.email, 'N/A') as email,
               COALESCE(u.phone, 'N/A') as phone,
               COALESCE(u.address, 'N/A') as delivery_address
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if ($order) {
        // Récupérer les articles de la commande avec les détails des produits
        $stmt_items = $conn->prepare("
            SELECT oi.*, p.name as product_name 
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmt_items->execute([$order_id]);
        $order_items = $stmt_items->fetchAll();
        
        // Récupérer les informations de livraison depuis la session si disponibles
        $orderKey = 'order_' . $order_id;
        if (isset($_SESSION[$orderKey])) {
            $deliveryInfo = $_SESSION[$orderKey];
            $order['fullname'] = $deliveryInfo['fullname'];
            $order['email'] = $deliveryInfo['email'];
            $order['phone'] = $deliveryInfo['phone'];
            $order['delivery_address'] = $deliveryInfo['address'];
            $order['notes'] = $deliveryInfo['notes'] ?? '';
        }
    } else {
        $error = "Commande non trouvée.";
    }
} else {
    $error = "ID de commande invalide.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la Commande #<?php echo htmlspecialchars($order_id); ?></title>
    <link rel="stylesheet" href="admin.css">
    <style>
        body {
            background: #f7f6fa;
        }
        .sidebar {
            width: 220px;
            min-height: 100vh;
            background: #f8e1e4;
            border-top-right-radius: 18px;
            border-bottom-right-radius: 18px;
            box-shadow: 2px 0 12px rgba(180,138,146,0.07);
            padding: 2.5rem 1.2rem 2rem 1.2rem;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        .sidebar h2 {
            color: #b48a92;
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 2.2rem;
            letter-spacing: 1px;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
            width: 100%;
        }
        .sidebar ul li {
            margin-bottom: 1.1rem;
        }
        .sidebar ul li:last-child {
            margin-bottom: 0;
        }
        .sidebar ul li a {
            display: block;
            color: #b48a92;
            background: none;
            text-decoration: none;
            font-size: 1.08rem;
            font-weight: 600;
            padding: 0.7rem 1.2rem;
            border-radius: 8px;
            transition: background 0.18s, color 0.18s;
        }
        .sidebar ul li a.active,
        .sidebar ul li a:hover {
            background: #e8a7b0;
            color: #fff;
        }
        .main-content {
            margin-left: 220px;
            padding: 0;
        }
        .container {
            max-width: 900px;
            margin: 40px auto 0 auto;
            padding: 2rem 1.5rem;
        }
        .order-details-card, .customer-details-card, .items-list-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(180,138,146,0.10);
            margin-bottom: 2.5rem;
            padding: 2rem 2.5rem;
            transition: box-shadow 0.2s;
        }
        .order-details-card:hover, .customer-details-card:hover, .items-list-card:hover {
            box-shadow: 0 8px 32px rgba(180,138,146,0.18);
        }
        .card-header {
            font-size: 1.7rem;
            color: #b48a92;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #f8e1e4;
            padding-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.7rem;
        }
        .card-header svg {
            color: #e8a7b0;
        }
        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 0.7rem 0;
            font-size: 1.08rem;
        }
        .detail-item strong {
            color: #7d5c65;
            min-width: 180px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .items-table th, .items-table td {
            padding: 1rem 0.7rem;
            text-align: left;
            border-bottom: 1px solid #f3e6e8;
        }
        .items-table th {
            background-color: #f8e1e4;
            color: #b48a92;
            font-weight: 600;
            font-size: 1.08rem;
        }
        .items-table tr:nth-child(even) td {
            background: #f7f6fa;
        }
        .total-row td {
            font-weight: bold;
            font-size: 1.15rem;
            text-align: right;
            padding-top: 1.2rem;
            background: #fff9fa;
            color: #b48a92;
        }
        .btn-retour {
            display: inline-block;
            background: #e8a7b0;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px 28px;
            font-size: 1.08rem;
            font-weight: 600;
            margin-bottom: 2rem;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(180,138,146,0.08);
            transition: background 0.2s, box-shadow 0.2s;
        }
        .btn-retour:hover {
            background: #b48a92;
            color: #fff;
            box-shadow: 0 4px 16px rgba(180,138,146,0.18);
        }
        @media (max-width: 900px) {
            .container {
                padding: 1rem 0.2rem;
            }
            .order-details-card, .customer-details-card, .items-list-card {
                padding: 1.2rem 0.7rem;
            }
        }
        @media (max-width: 600px) {
            .container {
                max-width: 100vw;
                padding: 0.2rem;
            }
            .order-details-card, .customer-details-card, .items-list-card {
                padding: 0.7rem 0.2rem;
            }
            .card-header {
                font-size: 1.1rem;
            }
        }
        @media (max-width: 900px) {
            .sidebar {
                width: 100vw;
                min-height: unset;
                flex-direction: row;
                align-items: center;
                border-radius: 0 0 18px 18px;
                padding: 1rem 0.5rem;
                position: static;
                box-shadow: none;
            }
            .sidebar ul {
                display: flex;
                flex-direction: row;
                gap: 0.5rem;
            }
            .sidebar ul li {
                margin-bottom: 0;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Admin</h2>
        <ul>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="products.php">Produits</a></li>
            <li><a href="categories.php">Catégories</a></li>
            <li><a href="orders.php" class="active">Commandes</a></li>
            <li><a href="users.php">Utilisateurs</a></li>
            <li><a href="messages.php">Messages</a></li>
            <li><a href="settings.php">Paramètres</a></li>
            <li><a href="logout.php">Déconnexion</a></li>
        </ul>
    </div>
    <div class="main-content">
        <header>
            <h1>Détails de la Commande #<?php echo htmlspecialchars($order_id); ?></h1>
        </header>
        <main>
            <div class="container">
                <a href="orders.php" class="btn-retour">&larr; Retour aux commandes</a>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php elseif ($order): ?>
                    <div class="order-details-card">
                        <div class="card-header">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24"><path stroke="#e8a7b0" stroke-width="2" d="M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/><path stroke="#e8a7b0" stroke-width="2" d="M16 3v4M8 3v4"/></svg>
                            Informations sur la commande
                        </div>
                        <div class="detail-item"><strong>Date de la commande:</strong> <span><?php
if (!empty($order['created_at'])) {
    echo date('d/m/Y H:i', strtotime($order['created_at']));
} else {
    echo 'Non disponible';
}
?></span></div>
                        <div class="detail-item"><strong>Statut:</strong> <span class="status-<?php echo strtolower(htmlspecialchars($order['status'])); ?>"><?php echo ucfirst(htmlspecialchars($order['status'])); ?></span></div>
                        <div class="detail-item"><strong>Montant total:</strong> <span><?php echo number_format($order['total_amount'], 2); ?> TND</span></div>
                    </div>

                    <div class="customer-details-card">
                        <div class="card-header">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24"><path stroke="#e8a7b0" stroke-width="2" d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4Z"/><path stroke="#e8a7b0" stroke-width="2" d="M4 20v-1a4 4 0 0 1 4-4h8a4 4 0 0 1 4 4v1"/></svg>
                            Informations du client
                        </div>
                        <div class="detail-item"><strong>Nom complet:</strong> <span><?php echo htmlspecialchars($order['fullname']); ?></span></div>
                        <div class="detail-item"><strong>Email:</strong> <span><?php echo htmlspecialchars($order['email']); ?></span></div>
                        <div class="detail-item"><strong>Téléphone:</strong> <span><?php echo htmlspecialchars($order['phone']); ?></span></div>
                        <div class="detail-item"><strong>Adresse de livraison:</strong> <span><?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></span></div>
                        <?php if (!empty($order['notes'])): ?>
                            <div class="detail-item"><strong>Notes:</strong> <span><?php echo nl2br(htmlspecialchars($order['notes'])); ?></span></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="items-list-card">
                        <div class="card-header">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24"><path stroke="#e8a7b0" stroke-width="2" d="M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/><path stroke="#e8a7b0" stroke-width="2" d="M16 3v4M8 3v4"/></svg>
                            Articles commandés
                        </div>
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Couleur</th>
                                    <th>Quantité</th>
                                    <th>Prix unitaire</th>
                                    <th>Sous-total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['color'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                    <td><?php echo number_format($item['price'], 2); ?> TND</td>
                                    <td><?php echo number_format($item['price'] * $item['quantity'], 2); ?> TND</td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="total-row">
                                    <td colspan="4">Total</td>
                                    <td><?php echo number_format($order['total_amount'], 2); ?> TND</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html> 
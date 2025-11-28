<?php
session_start();
require_once '../config.php';

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user = null;
$error = '';

if ($user_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if (!$user) {
        $error = "Utilisateur non trouvé.";
    }
} else {
    $error = "ID utilisateur invalide.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de l'utilisateur #<?php echo htmlspecialchars($user_id); ?></title>
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
            max-width: 700px;
            margin: 40px auto 0 auto;
            padding: 2rem 1.5rem;
        }
        .user-details-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(180,138,146,0.10);
            margin-bottom: 2.5rem;
            padding: 2rem 2.5rem;
            transition: box-shadow 0.2s;
        }
        .user-details-card:hover {
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
            <li><a href="orders.php">Commandes</a></li>
            <li><a href="users.php" class="active">Utilisateurs</a></li>
            <li><a href="messages.php">Messages</a></li>
            <li><a href="settings.php">Paramètres</a></li>
            <li><a href="logout.php">Déconnexion</a></li>
        </ul>
    </div>
    <div class="main-content">
        <header>
            <h1>Détails de l'utilisateur #<?php echo htmlspecialchars($user_id); ?></h1>
        </header>
        <main>
            <div class="container">
                <a href="users.php" class="btn-retour">&larr; Retour aux utilisateurs</a>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php elseif ($user): ?>
                    <div class="user-details-card">
                        <div class="card-header">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24"><path stroke="#e8a7b0" stroke-width="2" d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4Z"/><path stroke="#e8a7b0" stroke-width="2" d="M4 20v-1a4 4 0 0 1 4-4h8a4 4 0 0 1 4 4v1"/></svg>
                            Informations de l'utilisateur
                        </div>
                        <div class="detail-item"><strong>Nom complet:</strong> <span><?php echo htmlspecialchars($user['fullname'] ?? ''); ?></span></div>
                        <div class="detail-item"><strong>Email:</strong> <span><?php echo htmlspecialchars($user['email'] ?? ''); ?></span></div>
                        <div class="detail-item"><strong>Téléphone:</strong> <span><?php echo htmlspecialchars($user['phone'] ?? ''); ?></span></div>
                        <div class="detail-item"><strong>Adresse:</strong> <span><?php echo htmlspecialchars($user['address'] ?? ''); ?></span></div>
                        <div class="detail-item"><strong>Date d'inscription:</strong> <span><?php
                            if (!empty($user['created_at'])) {
                                echo date('d/m/Y H:i', strtotime($user['created_at']));
                            } else {
                                echo 'Non disponible';
                            }
                        ?></span></div>
                        <div class="detail-item"><strong>Rôle:</strong> <span><?php echo htmlspecialchars($user['role'] ?? 'Utilisateur'); ?></span></div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html> 
<?php
session_start();
require_once '../config.php';

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Vérifier si la table contact_messages existe, sinon la créer
try {
    // Essayer de sélectionner pour voir si la table existe
    $stmt = $conn->query("SELECT 1 FROM contact_messages LIMIT 1");
} catch(PDOException $e) {
    // Si la table n'existe pas, la créer
    try {
        $sql = "CREATE TABLE IF NOT EXISTS contact_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            is_read BOOLEAN DEFAULT FALSE
        )";
        $conn->exec($sql);
        $info = "La table des messages a été créée avec succès.";
    } catch(PDOException $createError) {
        $error = "Erreur lors de la création de la table des messages: " . $createError->getMessage();
    }
}

// Récupérer tous les messages de contact
try {
    $stmt = $conn->prepare("SELECT * FROM contact_messages ORDER BY created_at DESC");
    $stmt->execute();
    $messages = $stmt->fetchAll();
} catch(PDOException $e) {
    $messages = [];
    $error = "Erreur lors de la récupération des messages: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages de contact - Administration</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../im0.png" alt="Logo" class="admin-logo">
                <h2>Administration</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="index.php" class="nav-item"><i class="fas fa-tachometer-alt"></i><span>Tableau de bord</span></a>
                <a href="users.php" class="nav-item"><i class="fas fa-users"></i><span>Utilisateurs</span></a>
                <a href="orders.php" class="nav-item"><i class="fas fa-shopping-cart"></i><span>Commandes</span></a>
                <a href="products.php" class="nav-item"><i class="fas fa-box"></i><span>Produits</span></a>
                <a href="categories.php" class="nav-item"><i class="fas fa-tags"></i><span>Catégories</span></a>
                <a href="messages.php" class="nav-item active"><i class="fas fa-envelope"></i><span>Messages</span></a>
                <a href="settings.php" class="nav-item"><i class="fas fa-cog"></i><span>Paramètres</span></a>
                <a href="logout.php" class="nav-item logout"><i class="fas fa-sign-out-alt"></i><span>Déconnexion</span></a>
            </nav>
        </aside>
        <!-- Main Content -->
        <main class="main-content">
            <header class="admin-header">
                <h1>Messages de contact</h1>
                <div class="admin-user">
                    <span>Admin</span>
                    <i class="fas fa-user-circle"></i>
                </div>
            </header>
            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>
            <?php if (!empty($info)): ?>
                <div class="info-message">
                    <p><?php echo htmlspecialchars($info); ?></p>
                </div>
            <?php endif; ?>
            <div class="content-section">
                <div class="section-header">
                    <h2>Liste des messages reçus</h2>
                </div>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Message</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($messages as $msg): ?>
                            <tr>
                                <td>#<?php echo $msg['id']; ?></td>
                                <td><?php echo htmlspecialchars($msg['name']); ?></td>
                                <td><a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>"><?php echo htmlspecialchars($msg['email']); ?></a></td>
                                <td><?php echo nl2br(htmlspecialchars($msg['message'])); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($messages)): ?>
                            <tr><td colspan="5" style="text-align:center;">Aucun message reçu.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html> 
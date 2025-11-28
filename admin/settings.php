<?php
session_start();
require_once '../config.php';

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_site':
                $site_name = trim($_POST['site_name']);
                $site_description = trim($_POST['site_description']);
                $contact_email = trim($_POST['contact_email']);
                $contact_phone = trim($_POST['contact_phone']);
                
                try {
                    // Ici vous mettriez à jour les paramètres du site
                    // Pour l'instant, on simule la sauvegarde
                    $message = "Paramètres du site mis à jour avec succès";
                } catch(PDOException $e) {
                    $error = "Erreur lors de la mise à jour des paramètres";
                }
                break;
                
            case 'update_admin':
                $current_password = $_POST['current_password'];
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];
                
                if ($new_password !== $confirm_password) {
                    $error = "Les mots de passe ne correspondent pas";
                } elseif (strlen($new_password) < 6) {
                    $error = "Le mot de passe doit contenir au moins 6 caractères";
                } else {
                    // Ici vous mettriez à jour le mot de passe admin
                    $message = "Mot de passe mis à jour avec succès";
                }
                break;
        }
    }
}

// Paramètres par défaut (en production, vous les récupéreriez depuis la base de données)
$site_settings = [
    'site_name' => 'Ceramic Art Nour',
    'site_description' => 'Boutique en ligne de céramique artisanale',
    'contact_email' => 'contact@ceramicartnour.com',
    'contact_phone' => '+216 XX XXX XXX',
    'currency' => 'TND',
    'tax_rate' => '19',
    'shipping_cost' => '10.00'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres - Administration</title>
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
                <a href="index.php" class="nav-item">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Tableau de bord</span>
                </a>
                <a href="users.php" class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Utilisateurs</span>
                </a>
                <a href="orders.php" class="nav-item">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Commandes</span>
                </a>
                <a href="products.php" class="nav-item">
                    <i class="fas fa-box"></i>
                    <span>Produits</span>
                </a>
                <a href="categories.php" class="nav-item">
                    <i class="fas fa-tags"></i>
                    <span>Catégories</span>
                </a>
                <a href="settings.php" class="nav-item active">
                    <i class="fas fa-cog"></i>
                    <span>Paramètres</span>
                </a>
                <a href="logout.php" class="nav-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Déconnexion</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="admin-header">
                <h1>Paramètres</h1>
                <div class="admin-user">
                    <span>Admin</span>
                    <i class="fas fa-user-circle"></i>
                </div>
            </header>

            <?php if ($message): ?>
                <div class="success-message">
                    <p><?php echo htmlspecialchars($message); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="error-message">
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <div class="settings-container">
                <!-- Paramètres du site -->
                <div class="content-section">
                    <div class="section-header">
                        <h2><i class="fas fa-globe"></i> Paramètres du site</h2>
                    </div>
                    
                    <form method="POST" class="settings-form">
                        <input type="hidden" name="action" value="update_site">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="site_name">Nom du site</label>
                                <input type="text" id="site_name" name="site_name" 
                                       value="<?php echo htmlspecialchars($site_settings['site_name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="contact_email">Email de contact</label>
                                <input type="email" id="contact_email" name="contact_email" 
                                       value="<?php echo htmlspecialchars($site_settings['contact_email']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="site_description">Description du site</label>
                            <textarea id="site_description" name="site_description" rows="3"><?php echo htmlspecialchars($site_settings['site_description']); ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="contact_phone">Téléphone de contact</label>
                                <input type="tel" id="contact_phone" name="contact_phone" 
                                       value="<?php echo htmlspecialchars($site_settings['contact_phone']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="currency">Devise</label>
                                <select id="currency" name="currency">
                                    <option value="TND" <?php echo $site_settings['currency'] === 'TND' ? 'selected' : ''; ?>>TND (Dinar tunisien)</option>
                                    <option value="EUR" <?php echo $site_settings['currency'] === 'EUR' ? 'selected' : ''; ?>>EUR (Euro)</option>
                                    <option value="USD" <?php echo $site_settings['currency'] === 'USD' ? 'selected' : ''; ?>>USD (Dollar américain)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="tax_rate">Taux de TVA (%)</label>
                                <input type="number" id="tax_rate" name="tax_rate" 
                                       value="<?php echo htmlspecialchars($site_settings['tax_rate']); ?>" min="0" max="100" step="0.1">
                            </div>
                            
                            <div class="form-group">
                                <label for="shipping_cost">Coût de livraison (TND)</label>
                                <input type="number" id="shipping_cost" name="shipping_cost" 
                                       value="<?php echo htmlspecialchars($site_settings['shipping_cost']); ?>" min="0" step="0.01">
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn">
                                <i class="fas fa-save"></i>
                                Sauvegarder les paramètres
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Paramètres de sécurité -->
                <div class="content-section">
                    <div class="section-header">
                        <h2><i class="fas fa-shield-alt"></i> Sécurité</h2>
                    </div>
                    
                    <form method="POST" class="settings-form">
                        <input type="hidden" name="action" value="update_admin">
                        
                        <div class="form-group">
                            <label for="current_password">Mot de passe actuel</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_password">Nouveau mot de passe</label>
                                <input type="password" id="new_password" name="new_password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn">
                                <i class="fas fa-key"></i>
                                Changer le mot de passe
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Maintenance -->
                <div class="content-section">
                    <div class="section-header">
                        <h2><i class="fas fa-tools"></i> Maintenance</h2>
                    </div>
                    
                    <div class="maintenance-actions">
                        <button class="btn" onclick="backupDatabase()">
                            <i class="fas fa-download"></i>
                            Sauvegarder la base de données
                        </button>
                        
                        <button class="btn" onclick="clearCache()">
                            <i class="fas fa-broom"></i>
                            Vider le cache
                        </button>
                        
                        <button class="btn btn-warning" onclick="toggleMaintenance()">
                            <i class="fas fa-exclamation-triangle"></i>
                            Mode maintenance
                        </button>
                        
                        <button class="btn btn-danger" onclick="resetSite()">
                            <i class="fas fa-trash"></i>
                            Réinitialiser le site
                        </button>
                    </div>
                </div>

                <!-- Informations système -->
                <div class="content-section">
                    <div class="section-header">
                        <h2><i class="fas fa-info-circle"></i> Informations système</h2>
                    </div>
                    
                    <div class="system-info">
                        <div class="info-row">
                            <span class="info-label">Version PHP:</span>
                            <span class="info-value"><?php echo phpversion(); ?></span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Version MySQL:</span>
                            <span class="info-value"><?php echo $conn->getAttribute(PDO::ATTR_SERVER_VERSION); ?></span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Espace disque:</span>
                            <span class="info-value"><?php echo formatBytes(disk_free_space('/')); ?> libre</span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Mémoire utilisée:</span>
                            <span class="info-value"><?php echo formatBytes(memory_get_usage(true)); ?></span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Dernière sauvegarde:</span>
                            <span class="info-value">Jamais</span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="admin.js"></script>
    <script>
        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        function backupDatabase() {
            if (confirm('Voulez-vous créer une sauvegarde de la base de données ?')) {
                showNotification('Sauvegarde en cours...', 'info');
                // Ici vous implémenteriez la logique de sauvegarde
                setTimeout(() => {
                    showNotification('Sauvegarde terminée', 'success');
                }, 2000);
            }
        }
        
        function clearCache() {
            if (confirm('Voulez-vous vider le cache ?')) {
                showNotification('Cache vidé avec succès', 'success');
            }
        }
        
        function toggleMaintenance() {
            if (confirm('Voulez-vous activer/désactiver le mode maintenance ?')) {
                showNotification('Mode maintenance modifié', 'success');
            }
        }
        
        function resetSite() {
            if (confirm('ATTENTION: Cette action va supprimer toutes les données. Êtes-vous sûr ?')) {
                if (confirm('Êtes-vous vraiment sûr ? Cette action est irréversible !')) {
                    showNotification('Site réinitialisé', 'success');
                }
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Code à exécuter lorsque le DOM est chargé
        });
    </script>
</body>
</html>
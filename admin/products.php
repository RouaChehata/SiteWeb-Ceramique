<?php
session_start();
require_once '../config.php';

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
            case 'add':
                $name = trim($_POST['name']);
                $description = trim($_POST['description']);
                $price = floatval($_POST['price']);
                $category = trim($_POST['category']);
                $stock = intval($_POST['stock']);
                
                // Gestion du téléchargement de l'image
                $image = 'default.jpg'; // Image par défaut
                $uploadDir = '../uploads/';
                
                // Vérifier si un fichier a été téléchargé
                if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $_FILES['image']['tmp_name'];
                    $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
                    $targetPath = $uploadDir . $fileName;
                    
                    // Vérifier le type de fichier
                    $fileType = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
                    $allowedTypes = array('jpg', 'jpeg', 'png', 'gif');
                    
                    if(in_array($fileType, $allowedTypes)) {
                        if(move_uploaded_file($fileTmpPath, $targetPath)) {
                            $image = $fileName;
                        }
                    }
                }
                
                try {
                    $stmt = $conn->prepare("INSERT INTO products (name, description, price, category, image, stock) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $description, $price, $category, $image, $stock]);
                    $message = "Produit ajouté avec succès";
                } catch(PDOException $e) {
                    $error = "Erreur lors de l'ajout du produit: " . $e->getMessage();
                }
                break;
                
            case 'update':
                $id = $_POST['id'];
                $name = trim($_POST['name']);
                $description = trim($_POST['description']);
                $price = floatval($_POST['price']);
                $category = trim($_POST['category']);
                $stock = intval($_POST['stock']);
                
                // Récupérer l'image actuelle
                $currentImage = '';
                try {
                    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
                    $stmt->execute([$id]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    if($result) {
                        $currentImage = $result['image'];
                    }
                } catch(PDOException $e) {
                    $error = "Erreur lors de la récupération de l'image actuelle";
                }
                
                $image = $currentImage; // Conserver l'image actuelle par défaut
                $uploadDir = '../uploads/';
                
                // Vérifier si un nouveau fichier a été téléchargé
                if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $_FILES['image']['tmp_name'];
                    $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
                    $targetPath = $uploadDir . $fileName;
                    
                    // Vérifier le type de fichier
                    $fileType = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
                    $allowedTypes = array('jpg', 'jpeg', 'png', 'gif');
                    
                    if(in_array($fileType, $allowedTypes)) {
                        if(move_uploaded_file($fileTmpPath, $targetPath)) {
                            // Supprimer l'ancienne image si ce n'est pas l'image par défaut
                            if($currentImage !== 'default.jpg' && file_exists($uploadDir . $currentImage)) {
                                unlink($uploadDir . $currentImage);
                            }
                            $image = $fileName;
                        }
                    }
                }
                
                try {
                    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, category = ?, image = ?, stock = ? WHERE id = ?");
                    $stmt->execute([$name, $description, $price, $category, $image, $stock, $id]);
                    $message = "Produit mis à jour avec succès";
                } catch(PDOException $e) {
                    $error = "Erreur lors de la mise à jour: " . $e->getMessage();
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                try {
                    // Récupérer le nom de l'image avant de supprimer le produit
                    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
                    $stmt->execute([$id]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if($result && $result['image'] !== 'default.jpg') {
                        $imagePath = '../uploads/' . $result['image'];
                        if(file_exists($imagePath)) {
                            unlink($imagePath); // Supprimer le fichier image
                        }
                    }
                    
                    // Supprimer le produit de la base de données
                    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
                    $stmt->execute([$id]);
                    $message = "Produit supprimé avec succès";
                } catch(PDOException $e) {
                    $error = "Erreur lors de la suppression: " . $e->getMessage();
                }
                break;
        }
    }
}

// Récupérer tous les produits
try {
    $stmt = $conn->prepare("SELECT * FROM products ORDER BY created_at DESC");
    $stmt->execute();
    $products = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Erreur lors de la récupération des produits";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Produits - Administration</title>
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
                <a href="products.php" class="nav-item active">
                    <i class="fas fa-box"></i>
                    <span>Produits</span>
                </a>
                <a href="categories.php" class="nav-item">
                    <i class="fas fa-tags"></i>
                    <span>Catégories</span>
                </a>
                <a href="settings.php" class="nav-item">
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
                <h1>Gestion des Produits</h1>
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

            <div class="content-section">
                <div class="section-header">
                    <h2>Liste des Produits</h2>
                    <div class="section-actions">
                        <button class="btn" onclick="openAddModal()">
                            <i class="fas fa-plus"></i>
                            Ajouter un produit
                        </button>
                        <button class="btn" onclick="exportProducts()">
                            <i class="fas fa-download"></i>
                            Exporter
                        </button>
                    </div>
                </div>

                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Nom</th>
                                <th>Catégorie</th>
                                <th>Prix</th>
                                <th>Stock</th>
                                <th>Date d'ajout</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td>#<?php echo $product['id']; ?></td>
                                <td>
                                    <?php
                                    // Définir le chemin de base pour les images
                                    $basePath = '../uploads/';
                                    $defaultImage = '../im0.png';
                                    
                                    // Vérifier si le produit a une image spécifique
                                    if (!empty($product['image'])) {
                                        // Construire le chemin complet du fichier
                                        $imagePath = $basePath . $product['image'];
                                        // Vérifier si le fichier existe
                                        if (file_exists(__DIR__ . '/../uploads/' . $product['image'])) {
                                            $img = $imagePath;
                                        } else {
                                            $img = $defaultImage;
                                        }
                                    } else {
                                        // Utiliser l'image par défaut si aucune image n'est spécifiée
                                        $img = $defaultImage;
                                    }
                                    ?>
                                    <img src="<?php echo $img; ?>"
                                         alt="<?php echo htmlspecialchars($product['name'] ?? 'Produit'); ?>"
                                         class="product-thumbnail">
                                </td>
                                <td><?php echo htmlspecialchars($product['name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($product['category'] ?? ''); ?></td>
                                <td><?php echo number_format($product['price'] ?? 0, 2); ?> TND</td>
                                <td>
                                    <span class="stock-badge <?php echo (isset($product['stock']) && $product['stock'] > 0) ? 'in-stock' : 'out-of-stock'; ?>">
                                        <?php echo $product['stock'] ?? 0; ?>
                                    </span>
                                </td>
                                <td><?php echo isset($product['created_at']) ? date('d/m/Y', strtotime($product['created_at'])) : ''; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-small" onclick="openEditModal(<?php echo $product['id']; ?>)" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="btn-small btn-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Ajout/Modification Produit -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle">Ajouter un produit</h2>
            
            <form id="productForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="productId">
                
                <div class="form-group">
                    <label for="name">Nom du produit</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Prix (TND)</label>
                        <input type="number" id="price" name="price" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="stock">Stock</label>
                        <input type="number" id="stock" name="stock" required>
                    </div>
                </div>
                
                <?php
                // Récupérer toutes les catégories depuis la base de données
                $categories = [];
                try {
                    $stmt = $conn->query("SELECT name FROM categories ORDER BY name");
                    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
                } catch(PDOException $e) {
                    $error = "Erreur lors de la récupération des catégories";
                }
                ?>
                <div class="form-group">
                    <label for="category">Catégorie</label>
                    <select id="category" name="category" required>
                        <option value="">Sélectionner une catégorie</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="image">Image</label>
                    <input type="file" id="image" name="image" accept="image/*" required>
                    <small class="form-text text-muted">Téléchargez une image pour ce produit</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">Enregistrer</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <script src="admin.js"></script>
    <script>
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Ajouter un produit';
            document.getElementById('formAction').value = 'add';
            document.getElementById('productForm').reset();
            document.getElementById('productModal').style.display = 'block';
        }
        
        function openEditModal(productId) {
            // Ici vous pouvez charger les données du produit via AJAX
            document.getElementById('modalTitle').textContent = 'Modifier le produit';
            document.getElementById('formAction').value = 'update';
            document.getElementById('productId').value = productId;
            document.getElementById('productModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('productModal').style.display = 'none';
        }
        
        function exportProducts() {
            alert('Fonctionnalité d\'export à implémenter');
        }
        
        // Fermer le modal en cliquant sur X
        document.querySelector('.close').onclick = closeModal;
        
        // Fermer le modal en cliquant en dehors
        window.onclick = function(event) {
            var modal = document.getElementById('productModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html> 
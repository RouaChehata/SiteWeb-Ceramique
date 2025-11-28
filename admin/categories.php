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
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $id = $_POST['id'] ?? null;
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add' && $name) {
            $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $description]);
            $message = "Catégorie ajoutée avec succès.";
        } elseif ($action === 'edit' && $id && $name) {
            $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $description, $id]);
            $message = "Catégorie modifiée avec succès.";
        } elseif ($action === 'delete' && $id) {
            $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Catégorie supprimée.";
        }
    } catch(PDOException $e) {
        $error = "Erreur lors de l'opération : " . $e->getMessage();
    }
}

// Récupérer toutes les catégories
try {
    $stmt = $conn->prepare("SELECT * FROM categories ORDER BY created_at DESC");
    $stmt->execute();
    $categories = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Erreur lors de la récupération des catégories.";
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Catégories - Administration</title>
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
                <a href="categories.php" class="nav-item active">
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
                <h1>Gestion des Catégories</h1>
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
                    <h2>Liste des Catégories</h2>
                    <div class="section-actions">
                        <button class="btn" onclick="openAddModal()">
                            <i class="fas fa-plus"></i>
                            Ajouter une catégorie
                        </button>
                    </div>
                </div>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Description</th>
                                <th>Date de création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td>#<?php echo $cat['id']; ?></td>
                                <td><?php echo htmlspecialchars($cat['name']); ?></td>
                                <td><?php echo htmlspecialchars($cat['description']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($cat['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-small" onclick="openEditModal(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars(addslashes($cat['name'])); ?>', '<?php echo htmlspecialchars(addslashes($cat['description'])); ?>')" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="btn-small btn-danger" title="Supprimer" onclick="return confirm('Supprimer cette catégorie ?')">
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

            <!-- Modal Ajout/Modification Catégorie -->
            <div id="categoryModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <h2 id="modalTitle">Ajouter une catégorie</h2>
                    <form id="categoryForm" method="POST">
                        <input type="hidden" name="action" id="formAction" value="add">
                        <input type="hidden" name="id" id="categoryId">
                        <div class="form-group">
                            <label for="name">Nom de la catégorie</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="image">Image (nom de fichier, ex: mug.jpg)</label>
                            <input type="text" id="image" name="image" placeholder="ex: mug.jpg">
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn">Enregistrer</button>
                            <button type="button" class="btn btn-secondary" onclick="closeModal()">Annuler</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script src="admin.js"></script>
    <script>
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Ajouter une catégorie';
            document.getElementById('formAction').value = 'add';
            document.getElementById('categoryForm').reset();
            document.getElementById('categoryId').value = '';
            document.getElementById('image').value = ''; // Clear image field for new category
            document.getElementById('categoryModal').style.display = 'block';
        }
        function openEditModal(id, name, description) {
            document.getElementById('modalTitle').textContent = 'Modifier la catégorie';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('categoryId').value = id;
            document.getElementById('name').value = name;
            document.getElementById('description').value = description;
            document.getElementById('categoryModal').style.display = 'block';
        }
        function closeModal() {
            document.getElementById('categoryModal').style.display = 'none';
        }
        window.onclick = function(event) {
            var modal = document.getElementById('categoryModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html> 
<?php
session_start();
require_once 'config.php';

$category = null;
$products = [];
$error = '';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $cat_id = (int)$_GET['id'];
    // Récupérer la catégorie
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$cat_id]);
    $category = $stmt->fetch();
    if ($category) {
        // Récupérer les produits de cette catégorie
        $stmt = $conn->prepare("SELECT * FROM products WHERE category = ?");
        $stmt->execute([$category['name']]);
        $products = $stmt->fetchAll();
    } else {
        $error = "Catégorie introuvable.";
    }
} else {
    $error = "Catégorie non spécifiée.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Catégorie - Ceramic Art Nour">
    <title><?php echo $category ? htmlspecialchars($category['name']) : 'Catégorie'; ?> - Ceramic Art Nour</title>
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
        <section class="category-section">
            <div style="text-align:left;max-width:1200px;margin:0 auto 1.5rem auto;">
                <a href="categories.php" style="display:inline-block;padding:0.5rem 1.2rem;background:#e8a7b0;color:#fff;border-radius:8px;text-decoration:none;font-weight:500;font-size:1rem;box-shadow:0 1px 4px rgba(180,138,146,0.08);transition:background 0.2s;">&larr; Retour aux catégories</a>
            </div>
            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php elseif ($category): ?>
                <h2 class="products-title"><?php echo htmlspecialchars($category['name']); ?></h2>
                <p style="text-align:center; color:#b48a92; margin-bottom:2rem;"><?php echo htmlspecialchars($category['description']); ?></p>
                <?php if (!empty($products)): ?>
                    <div class="products-grid">
                        <?php foreach ($products as $product): ?>
                            <?php
                            // Gestion de l'image du produit
                            $basePath = 'uploads/';
                            $defaultImage = 'im0.png';
                            
                            if (!empty($product['image'])) {
                                $imagePath = $basePath . $product['image'];
                                $productImage = file_exists(__DIR__ . '/' . $basePath . $product['image']) ? $imagePath : $defaultImage;
                            } else {
                                $productImage = $defaultImage;
                            }
                            ?>
                            <div class="product-card">
                                <img src="<?php echo htmlspecialchars($productImage); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-thumbnail">
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p><?php echo htmlspecialchars($product['description']); ?></p>
                                <div class="product-price"><?php echo number_format($product['price'], 2); ?> TND</div>
                                <?php if ((int)$product['stock'] <= 0): ?>
                                    <div style="color:#c0392b;font-weight:bold;margin-bottom:0.7rem;">Stock : Rupture</div>
                                    <button class="search-btn" style="background:#ccc;cursor:not-allowed;" disabled>Ajouter au panier</button>
                                <?php else: ?>
                                    <div style="color:#7d5c65;font-weight:500;margin-bottom:0.7rem;">Stock : <?php echo (int)$product['stock']; ?> pièce<?php echo ((int)$product['stock'] > 1) ? 's' : ''; ?></div>
                                    <button class="search-btn" onclick='addToCart({
                                        id: <?php echo json_encode($product['id']); ?>,
                                        name: <?php echo json_encode($product['name']); ?>,
                                        price: <?php echo json_encode($product['price']); ?>,
                                        image: <?php echo ($productImage === $defaultImage) ? json_encode($defaultImage) : json_encode($basePath . $product['image']); ?>,
                                        color: ""
                                    }); updateCartDisplay(); addClearCartButton();'>Ajouter au panier</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="text-align:center;">Aucun produit dans cette catégorie.</p>
                <?php endif; ?>
            <?php endif; ?>
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
        .products-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            justify-content: center;
            margin-top: 2rem;
        }
        .product-card {
            background: #f8e1e4;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(180,138,146,0.08);
            padding: 2rem 2.5rem;
            min-width: 220px;
            max-width: 320px;
            text-align: center;
            transition: box-shadow 0.2s;
        }
        .product-card:hover {
            box-shadow: 0 4px 16px rgba(180,138,146,0.18);
        }
        .product-thumbnail {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 1rem;
        }
    </style>
</body>
</html> 
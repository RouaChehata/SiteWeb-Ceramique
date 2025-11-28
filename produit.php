<?php
require_once 'config.php';

// Récupérer l'ID du produit depuis l'URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = null;

if ($id > 0) {
    try {
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
    } catch(PDOException $e) {
        $product = null;
    }
}

if (!$product) {
    echo '<h2 style="text-align:center;margin-top:100px;">Produit introuvable</h2>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Ceramic Art Nour</title>
    <link rel="stylesheet" href="ce.css">
    <link rel="stylesheet" href="cart.css">
    <style>
        .product-detail-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: flex-start;
            gap: 40px;
            margin: 60px auto 40px auto;
            max-width: 1100px;
        }
        .product-detail-image {
            max-width: 400px;
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }
        .product-detail-info {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 40px 30px;
            min-width: 320px;
            max-width: 400px;
        }
        .product-detail-info h1 {
            color: #b48a92;
            font-size: 2.2rem;
            margin-bottom: 20px;
        }
        .product-detail-info .product-price {
            font-size: 1.5rem;
            font-weight: bold;
            color: #444;
            margin-bottom: 20px;
        }
        .product-detail-info .product-description {
            color: #666;
            margin-bottom: 25px;
        }
        .back-btn {
            display: inline-block;
            margin: 30px 0 0 40px;
            background: #f8e1e4;
            color: #b48a92;
            border: none;
            border-radius: 8px;
            padding: 10px 22px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
        }
        .back-btn:hover {
            background: #e8a7b0;
            color: #fff;
        }
        .add-to-cart-btn {
            width: 100%;
            background: #e8a7b0;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 16px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
            transition: background 0.2s;
        }
        .add-to-cart-btn:hover {
            background: #b48a92;
        }
    </style>
</head>
<body>
    <header>
        <nav class="main-nav">
            <div class="header-left">
                <a href="ce.php"><img src="im0.png" alt="Logo Ceramic Art Nour" class="header-logo"></a>
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
                <li><a href="#categories" class="nav-button">Catégories</a></li>
                <li><a href="contact.php" class="nav-button">Contact</a></li>
            </ul>
            <button class="cart-icon" onclick="toggleCart()" title="Voir le panier">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#e8a7b0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            </button>
            <a href="login.php" class="login-btn">Login</a>
        </nav>
    </header>

    <!-- Conteneur du panier (ajouté) -->
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

    <a href="javascript:history.back()" class="back-btn">&larr; Retour</a>

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
    <div class="product-detail-container">
        <img src="<?php echo htmlspecialchars($productImage); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-detail-image">
        <div class="product-detail-info">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <div class="product-price"><?php echo number_format($product['price'], 2); ?> TND</div>
            <div class="product-stock" id="productStock" data-stock="<?php echo (int)$product['stock']; ?>">
                En stock: <?php echo (int)$product['stock']; ?> pièce<?php echo (int)$product['stock'] > 1 ? 's' : ''; ?>
            </div>
            <div class="product-description"><?php echo nl2br(htmlspecialchars($product['description'] ?? '')); ?></div>
            <form method="post" action="#" onsubmit="addToCartFromPage(event)">
                <div class="quantity-selector" style="margin: 20px 0;">
                    <label for="quantity">Quantité :</label>
                    <input type="number" id="quantity" name="quantity" min="1" max="<?php echo (int)$product['stock']; ?>" value="1" style="margin-left: 10px; padding: 8px; width: 60px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <button type="submit" class="add-to-cart-btn" id="addToCartBtn" <?php echo (int)$product['stock'] <= 0 ? 'disabled style="opacity: 0.6; cursor: not-allowed;"' : ''; ?>>
                    <?php echo (int)$product['stock'] > 0 ? 'Ajouter au panier' : 'Rupture de stock'; ?>
                </button>
            </form>
        </div>
    </div>

    <script src="cart.js"></script>
    <script>
    function toggleCart() {
        document.querySelector('.cart-container').classList.toggle('open');
        document.querySelector('.cart-overlay').classList.toggle('open');
    }
    function addToCartFromPage(event) {
        event.preventDefault();
        const quantity = parseInt(document.getElementById('quantity').value) || 1;
        const stock = parseInt(document.getElementById('productStock').dataset.stock) || 0;
        
        if (quantity <= 0) {
            showNotification('Veuillez sélectionner une quantité valide.');
            return;
        }
        
        if (stock <= 0) {
            showNotification('Désolé, ce produit est en rupture de stock.');
            return;
        }
        
        // Appel de la fonction addToCart avec la quantité
        addToCart({
            id: <?php echo json_encode($product['id']); ?>,
            name: <?php echo json_encode($product['name']); ?>,
            price: <?php echo json_encode($product['price']); ?>,
            color: '',
            image: <?php echo json_encode($product['image']); ?>
        }, quantity);
        
        // Afficher le panier
        toggleCart();
    }
    // Gestion du formulaire de checkout
    document.addEventListener('DOMContentLoaded', () => {
        const checkoutForm = document.getElementById('checkoutForm');
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', (event) => {
                const cart = JSON.parse(localStorage.getItem('cart')) || [];
                if (cart.length === 0) {
                    alert("Votre panier est vide. Veuillez ajouter des produits avant de passer commande.");
                    event.preventDefault();
                    return;
                }
                document.getElementById('cartDataInput').value = JSON.stringify(cart);
            });
        }
    });
    </script>
</body>
</html> 
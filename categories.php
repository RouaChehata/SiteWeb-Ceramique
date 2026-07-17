<?php
session_start();
require_once 'config.php';

// Récupérer toutes les catégories avec le nombre de produits
try {
    $stmt = $conn->prepare("
        SELECT c.*, COUNT(p.id) as product_count 
        FROM categories c 
        LEFT JOIN products p ON c.name = p.category 
        GROUP BY c.id 
        ORDER BY c.created_at DESC
    ");
    $stmt->execute();
    $categories = $stmt->fetchAll();
} catch(PDOException $e) {
    $categories = [];
    $error = "Erreur lors de la récupération des catégories.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Catégories - Ceramic Art Nour">
    <title>Catégories - Ceramic Art Nour</title>
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
                <li><a href="categories.php" class="nav-button active">Catégories</a></li>
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
        <section class="categories-section">
            <h2 class="products-title">Nos catégories</h2>
            <?php if (!empty($categories)): ?>
                <div class="categories-grid">
                    <?php foreach ($categories as $cat): ?>
                        <a href="categorie.php?id=<?php echo $cat['id']; ?>" class="category-card">
                            <div class="category-icon">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#e8a7b0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                                    <line x1="7" y1="7" x2="7.01" y2="7"></line>
                                </svg>
                            </div>
                            <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
                            <p><?php echo htmlspecialchars($cat['description']); ?></p>
                            <div class="product-count">
                                <span class="count-number"><?php echo (int)$cat['product_count']; ?></span>
                                <span class="count-label">produit<?php echo ((int)$cat['product_count'] > 1) ? 's' : ''; ?></span>
                            </div>
                            <div class="category-arrow">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#e8a7b0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Aucune catégorie trouvée.</p>
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
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
            padding: 0 2rem;
        }
        
        .category-card {
            background: linear-gradient(135deg, #f8e1e4 0%, #f0c4c8 100%);
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(180,138,146,0.12);
            padding: 2.5rem;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .category-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #e8a7b0, #d6959f, #e8a7b0);
            background-size: 200% 100%;
            animation: shimmer 3s ease-in-out infinite;
        }
        
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        
        .category-card:hover {
            transform: translateY(-12px) scale(1.03);
            box-shadow: 0 20px 40px rgba(180,138,146,0.25);
            text-decoration: none;
            color: inherit;
        }
        
        .category-icon {
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease;
        }
        
        .category-card:hover .category-icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        .category-card h3 {
            font-size: 1.6rem;
            font-weight: 700;
            color: #4a4a4a;
            margin-bottom: 1rem;
            line-height: 1.3;
        }
        
        .category-card p {
            color: #6b6b6b;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            flex-grow: 1;
        }
        
        .product-count {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: rgba(255,255,255,0.3);
            padding: 0.8rem 1.5rem;
            border-radius: 20px;
            margin-bottom: 1rem;
            backdrop-filter: blur(5px);
        }
        
        .count-number {
            font-size: 1.2rem;
            font-weight: 700;
            color: #e8a7b0;
        }
        
        .count-label {
            font-size: 0.9rem;
            color: #7d5c65;
            font-weight: 500;
        }
        
        .category-arrow {
            margin-top: auto;
            opacity: 0;
            transform: translateX(-10px);
            transition: all 0.3s ease;
        }
        
        .category-card:hover .category-arrow {
            opacity: 1;
            transform: translateX(0);
        }
        
        @media (max-width: 768px) {
            .categories-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 1.5rem;
                padding: 0 1rem;
                margin-top: 2rem;
            }
            
            .category-card {
                padding: 2rem;
            }
            
            .category-card h3 {
                font-size: 1.4rem;
            }
        }
        
        @media (max-width: 480px) {
            .categories-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
                padding: 0 0.5rem;
            }
            
            .category-card {
                padding: 1.5rem;
            }
        }
    </style>
</body>
</html>
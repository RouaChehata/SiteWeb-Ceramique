<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Ceramic Art Nour - Boutique en ligne de céramique artisanale tunisienne. Découvrez nos créations uniques en céramique, faites main à Mahdia.">
    <meta name="keywords" content="céramique, artisanat, Tunisie, Mahdia, poterie, céramique artisanale, boutique en ligne">
    <meta name="author" content="Ceramic Art Nour">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="Ceramic Art Nour - Céramique Artisanale Tunisienne">
    <meta property="og:description" content="Découvrez nos créations uniques en céramique, faites main à Mahdia, Tunisie.">
    <meta property="og:image" content="im0.png">
    <meta property="og:url" content="http://localhost/ceramicc/ce.php">
    <link rel="canonical" href="http://localhost/ceramicc/ce.php">
    <title>Ceramic Art Nour - Céramique Artisanale Tunisienne</title>
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
        <div class="carousel-container">
            <button class="carousel-btn prev">&#10094;</button>
            <div class="carousel-slide">
                <img src="im1.jpg" alt="Photo 1" class="carousel-image active">
                <img src="im9.jpg" alt="Photo 9" class="carousel-image">
                <img src="im2.jpg" alt="Photo 2" class="carousel-image">
                <img src="im3.jpg" alt="Photo 3" class="carousel-image">
                <img src="im4.jpg" alt="Photo 4" class="carousel-image">
                <img src="im5.jpg" alt="Photo 5" class="carousel-image">
                <img src="im6.jpg" alt="Photo 6" class="carousel-image">
                <img src="im7.jpg" alt="Photo 7" class="carousel-image">
                <img src="im8.jpg" alt="Photo 8" class="carousel-image">
            </div>
            <button class="carousel-btn next">&#10095;</button>
            <div class="carousel-dots">
                <span class="carousel-dot active"></span>
                <span class="carousel-dot"></span>
                <span class="carousel-dot"></span>
                <span class="carousel-dot"></span>
                <span class="carousel-dot"></span>
                <span class="carousel-dot"></span>
                <span class="carousel-dot"></span>
                <span class="carousel-dot"></span>
            </div>
        </div>
        <section class="products-section">
            <h2 class="products-title">Tous les produits</h2>
            <div class="products-grid">
                <?php include 'products.php'; ?>
            </div>
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
    <style>
        .profile-dropdown {
            position: relative;
            display: inline-block;
        }
        .profile-icon {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
        }
        .profile-dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #fff;
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            z-index: 1;
        }
        .profile-dropdown:hover .profile-dropdown-content {
            display: block;
        }
        .profile-dropdown-content a {
            color: #b48a92;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }
        .profile-dropdown-content a:hover {
            background-color: #f8e1e4;
        }
    </style>
</body>
</html> 
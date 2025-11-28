<?php
require_once 'config.php';

try {
    $stmt = $conn->prepare("SELECT * FROM products ORDER BY created_at DESC");
    $stmt->execute();
    $products = $stmt->fetchAll();
} catch(PDOException $e) {
    echo '<p class="error-message">Erreur lors de la récupération des produits.</p>';
    $products = [];
}

foreach ($products as $product): ?>
    <div class="product-card">
        <div class="product-image-wrapper">
            <?php
            // Définir le chemin de base pour les images
            $basePath = 'uploads/';
            $defaultImage = 'im0.png';
            
            // Vérifier si le produit a une image spécifique
            if (!empty($product['image'])) {
                // Construire le chemin complet du fichier
                $imagePath = $basePath . $product['image'];
                // Vérifier si le fichier existe
                if (file_exists(__DIR__ . '/' . $basePath . $product['image'])) {
                    $img = $imagePath;
                } else {
                    $img = $defaultImage;
                }
            } else {
                // Utiliser l'image par défaut si aucune image n'est spécifiée
                $img = $defaultImage;
            }
            ?>
            <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($product['name'] ?? 'Produit'); ?>" class="product-image">
        </div>
        <div class="product-info">
            <h3><?php echo htmlspecialchars($product['name'] ?? ''); ?></h3>
            <p class="product-price"><?php echo isset($product['price']) ? number_format($product['price'], 2) . ' TND' : ''; ?></p>
            <?php if ((int)$product['stock'] <= 0): ?>
                <div style="color:#c0392b;font-weight:bold;margin-bottom:0.7rem;">Stock : Rupture</div>
                <button class="add-to-cart-btn" style="background:#ccc;cursor:not-allowed;" disabled>Ajouter au panier
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#4a4a4a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"/>
                        <circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                </button>
            <?php else: ?>
                <div style="color:#7d5c65;font-weight:500;margin-bottom:0.7rem;">Stock : <?php echo (int)$product['stock']; ?> pièce<?php echo ((int)$product['stock'] > 1) ? 's' : ''; ?></div>
                <button class="add-to-cart-btn" onclick="addToCart({
                    id: '<?php echo $product['id']; ?>',
                    name: '<?php echo htmlspecialchars($product['name'] ?? ''); ?>',
                    price: <?php echo isset($product['price']) ? $product['price'] : 0; ?>,
                    color: '<?php echo htmlspecialchars($product['color'] ?? 'Standard'); ?>',
                    image: '<?php echo ($img === 'im0.png') ? $img : $basePath . $product['image']; ?>'
                })">
                    Ajouter au panier
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#4a4a4a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"/>
                        <circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                </button>
            <?php endif; ?>
            <a href="produit.php?id=<?php echo $product['id']; ?>" class="shop-btn">
                Voir détails
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#4a4a4a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </a>
        </div>
    </div>
<?php endforeach; ?> 
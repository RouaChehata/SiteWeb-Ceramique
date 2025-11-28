// Gestion du panier
let cart = JSON.parse(localStorage.getItem('cart')) || [];

// Fonction pour ajouter un produit au panier
function addToCart(product, quantity = 1) {
    // Vérifier la quantité en stock via une requête AJAX
    fetch(`check_stock.php?id=${product.id}`)
        .then(response => response.json())
        .then(stockData => {
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            const existingProduct = cart.find(item => 
                String(item.id) === String(product.id) && item.color === product.color
            );

            // Calculer la quantité totale souhaitée
            const newQuantity = existingProduct ? (existingProduct.quantity + quantity) : quantity;

            if (newQuantity > stockData.quantity) {
                const remaining = Math.max(0, stockData.quantity - (existingProduct?.quantity || 0));
                showNotification(`Désolé, vous ne pouvez pas ajouter plus de ${remaining} pièce${remaining > 1 ? 's' : ''} au panier.`);
                return;
            }

            if (existingProduct) {
                existingProduct.quantity = newQuantity;
                showNotification(`${product.name} mis à jour (${existingProduct.quantity} dans le panier)`);
            } else {
                // Pour un nouveau produit, vérifier qu'il y a du stock
                if (stockData.quantity <= 0) {
                    showNotification(`Désolé, ce produit est en rupture de stock.`);
                    return;
                }
                cart.push({
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    color: product.color || '',
                    image: product.image,
                    quantity: quantity
                });
                showNotification(`${product.name} ajouté au panier`);
            }

            // Sauvegarder le panier dans le localStorage
            localStorage.setItem('cart', JSON.stringify(cart));
            
            // Mettre à jour l'affichage du panier
            updateCartDisplay();
        })
        .catch(error => {
            console.error('Erreur lors de la vérification du stock:', error);
            showNotification('Erreur lors de la vérification du stock');
        });
}

// Fonction pour supprimer un produit du panier
function removeFromCart(productId, color) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    console.log('Removing product:', productId, color);
    cart = cart.filter(item => !(String(item.id) === String(productId) && item.color === color));
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartDisplay();
    showNotification('Produit supprimé du panier');
}

// Fonction pour mettre à jour la quantité d'un produit
function updateQuantity(productId, color, newQuantity) {
    // Vérifier le stock avant de mettre à jour
    fetch(`check_stock.php?id=${productId}`)
        .then(response => response.json())
        .then(stockData => {
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            const productIndex = cart.findIndex(item => String(item.id) === String(productId) && item.color === color);
            
            if (productIndex === -1) return;
            
            const product = cart[productIndex];
            const currentQuantity = product.quantity || 1;
            
            // Si la nouvelle quantité est inférieure ou égale à 0, supprimer l'article
            if (newQuantity <= 0) {
                removeFromCart(productId, color);
                return;
            }
            
            // Vérifier si la nouvelle quantité dépasse le stock disponible
            if (newQuantity > stockData.quantity) {
                showNotification(`Désolé, il ne reste que ${stockData.quantity} pièce${stockData.quantity > 1 ? 's' : ''} en stock.`);
                // Si le stock est épuisé, supprimer l'article
                if (stockData.quantity <= 0) {
                    removeFromCart(productId, color);
                    return;
                }
                // Sinon, ajuster à la quantité maximale disponible
                newQuantity = stockData.quantity;
            }
            
            // Mettre à jour la quantité
            product.quantity = newQuantity;
            cart[productIndex] = product;
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartDisplay();
        })
        .catch(error => {
            console.error('Erreur lors de la vérification du stock:', error);
            showNotification('Erreur lors de la mise à jour de la quantité');
        });
}

// Fonction pour calculer le total du panier
function calculateTotal() {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    return cart.reduce((total, item) => total + (parseFloat(item.price) * item.quantity), 0);
}

// Fonction pour afficher une notification
function showNotification(message) {
    // Supprimer les notifications existantes
    const existingNotification = document.querySelector('.cart-notification');
    if (existingNotification) {
        existingNotification.remove();
    }

    // Créer une nouvelle notification
    const notification = document.createElement('div');
    notification.className = 'cart-notification';
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #e8c7a7;
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        z-index: 10000;
        font-weight: bold;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;

    document.body.appendChild(notification);

    // Animer l'entrée
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);

    // Supprimer après 3 secondes
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, 3000);
}

// Fonction pour afficher le panier
function updateCartDisplay() {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    const cartContainer = document.getElementById('cart-items');
    
    if (!cartContainer) {
        console.error('Cart container #cart-items not found');
        return;
    }

    // Vider le conteneur avant de le remplir
    cartContainer.innerHTML = '';

    if (cart.length === 0) {
        cartContainer.innerHTML = '<p class="empty-cart">Votre panier est vide</p>';
        // Masquer le total et le bouton de commande si le panier est vide
        const checkoutForm = document.getElementById('checkoutForm');
        if (checkoutForm) checkoutForm.style.display = 'none';
        const totalElement = document.querySelector('.cart-total');
        if (totalElement) totalElement.style.display = 'none';
        return;
    }

    cart.forEach(item => {
        const itemElement = document.createElement('div');
        itemElement.className = 'cart-item';
        
        const price = parseFloat(item.price) || 0;
        const quantity = parseInt(item.quantity) || 1;
        const color = item.color || 'N/A';
        // Gestion de l'image avec chemin par défaut si nécessaire
        let imagePath = '';
        if (item.image && item.image.startsWith('http')) {
            // Si c'est une URL complète, on l'utilise telle quelle
            imagePath = item.image;
        } else if (item.image) {
            // Sinon, on vérifie si le fichier existe dans uploads
            const basePath = window.location.pathname.includes('admin') ? '../uploads/' : 'uploads/';
            const fullPath = basePath + item.image;
            // On essaie de charger l'image, si elle échoue, on utilise l'image par défaut
            imagePath = `onerror="this.src='im0.png';" src="${fullPath}"`;
        } else {
            // Si pas d'image, on utilise l'image par défaut
            imagePath = 'src="im0.png"';
        }

        itemElement.innerHTML = `
            <img ${imagePath} alt="${item.name}" class="cart-item-image">
            <div class="cart-item-details">
                <h3>${item.name}</h3>
                <p>Couleur: ${color}</p>
                <p>Prix: ${price.toFixed(2)} TND</p>
                <div class="quantity-controls">
                    <button type="button" class="quantity-btn minus">-</button>
                    <span class="quantity-display">${quantity}</span>
                    <button type="button" class="quantity-btn plus">+</button>
                </div>
            </div>
            <button type="button" class="remove-item">×</button>
        `;
        cartContainer.appendChild(itemElement);

        // On supprime les gestionnaires d'événements individuels
    });

    // Gérer l'affichage du total et du bouton
    const total = calculateTotal();
    const checkoutForm = document.getElementById('checkoutForm');

    // S'assurer que le total est affiché
    let totalElement = cartContainer.parentNode.querySelector('.cart-total');
    if (!totalElement) {
        totalElement = document.createElement('div');
        totalElement.className = 'cart-total';
        // Placer le total avant le formulaire de checkout
        if(checkoutForm) {
            checkoutForm.parentNode.insertBefore(totalElement, checkoutForm);
        }
    }
    totalElement.innerHTML = `<p>Total: ${total.toFixed(2)} TND</p>`;
    totalElement.style.display = 'block';

    // Afficher le bouton de commande
    if (checkoutForm) {
        checkoutForm.style.display = 'block';
    }
}

// Supprimer les anciennes initialisations
// document.addEventListener('DOMContentLoaded', updateCartDisplay);
// document.addEventListener('DOMContentLoaded', addClearCartButton);

// NOUVELLE GESTION D'ÉVÉNEMENTS (Délégation)
document.addEventListener('DOMContentLoaded', function() {
    const cartItemsContainer = document.getElementById('cart-items');

    if (cartItemsContainer) {
        cartItemsContainer.addEventListener('click', function(event) {
            const target = event.target;
            const itemElement = target.closest('.cart-item');
            if (!itemElement) return;

            // Retrouver les données de l'article
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            // Créer un ID unique pour l'élément dans le DOM pour le retrouver
            const itemIndex = Array.from(itemElement.parentNode.children).indexOf(itemElement);
            const item = cart[itemIndex];
            
            if (!item) return;

            if (target.classList.contains('plus')) {
                // Vérifier le stock avant d'incrémenter
                fetch(`check_stock.php?id=${item.id}`)
                    .then(response => response.json())
                    .then(stockData => {
                        if (item.quantity < stockData.quantity) {
                            updateQuantity(item.id, item.color, item.quantity + 1);
                        } else {
                            showNotification(`Désolé, il ne reste que ${stockData.quantity} pièce${stockData.quantity > 1 ? 's' : ''} en stock.`);
                        }
                    })
                    .catch(error => {
                        console.error('Erreur lors de la vérification du stock:', error);
                        showNotification('Erreur lors de la vérification du stock');
                    });
            } else if (target.classList.contains('minus')) {
                updateQuantity(item.id, item.color, item.quantity - 1);
            } else if (target.classList.contains('remove-item')) {
                removeFromCart(item.id, item.color);
            }
        });
    }

    // Initialisation au chargement
    updateCartDisplay();
    addClearCartButton();
});

// Initialisation directe à la fin du script pour garantir l'exécution
// updateCartDisplay();
// addClearCartButton(); 
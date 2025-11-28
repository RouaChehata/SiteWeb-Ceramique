// Fonctionnalités JavaScript pour l'interface d'administration

document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des fonctionnalités
    initAdminFeatures();
});

function initAdminFeatures() {
    // Gestion du menu mobile
    initMobileMenu();
    
    // Gestion des modals
    initModals();
    
    // Gestion des confirmations
    initConfirmations();
    
    // Gestion des notifications
    initNotifications();
}

// Gestion du menu mobile
function initMobileMenu() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    // Ajouter un bouton hamburger pour mobile
    if (window.innerWidth <= 768) {
        const hamburger = document.createElement('button');
        hamburger.className = 'mobile-menu-btn';
        hamburger.innerHTML = '<i class="fas fa-bars"></i>';
        hamburger.onclick = function() {
            sidebar.classList.toggle('open');
        };
        
        const header = document.querySelector('.admin-header');
        if (header) {
            header.insertBefore(hamburger, header.firstChild);
        }
    }
    
    // Fermer le menu en cliquant en dehors
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768 && 
            !sidebar.contains(e.target) && 
            !e.target.classList.contains('mobile-menu-btn')) {
            sidebar.classList.remove('open');
        }
    });
}

// Gestion des modals
function initModals() {
    // Fermer les modals avec Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (modal.style.display === 'block') {
                    modal.style.display = 'none';
                }
            });
        }
    });
}

// Gestion des confirmations
function initConfirmations() {
    // Intercepter les formulaires de suppression
    const deleteForms = document.querySelectorAll('form[method="POST"]');
    deleteForms.forEach(form => {
        const deleteBtn = form.querySelector('button[type="submit"]');
        if (deleteBtn && deleteBtn.classList.contains('btn-danger')) {
            deleteBtn.addEventListener('click', function(e) {
                const action = form.querySelector('input[name="action"]');
                if (action && action.value === 'delete') {
                    const confirmed = confirm('Êtes-vous sûr de vouloir effectuer cette action ? Cette opération est irréversible.');
                    if (!confirmed) {
                        e.preventDefault();
                    }
                }
            });
        }
    });
}

// Gestion des notifications
function initNotifications() {
    // Auto-fermeture des messages de succès/erreur
    const messages = document.querySelectorAll('.success-message, .error-message');
    messages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => {
                message.remove();
            }, 300);
        }, 5000);
    });
}

// Fonction pour afficher des notifications
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-fermeture
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 5000);
}

// Fonction pour exporter des données
function exportData(data, filename, type = 'csv') {
    let content = '';
    
    if (type === 'csv') {
        // Convertir en CSV
        const headers = Object.keys(data[0]);
        content = headers.join(',') + '\n';
        
        data.forEach(row => {
            const values = headers.map(header => {
                const value = row[header];
                return typeof value === 'string' && value.includes(',') ? `"${value}"` : value;
            });
            content += values.join(',') + '\n';
        });
    }
    
    // Créer et télécharger le fichier
    const blob = new Blob([content], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    window.URL.revokeObjectURL(url);
}

// Fonction pour valider les formulaires
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('error');
            isValid = false;
        } else {
            input.classList.remove('error');
        }
    });
    
    return isValid;
}

// Fonction pour charger les données d'un produit pour édition
function loadProductData(productId) {
    // Simulation de chargement des données
    // En réalité, vous feriez un appel AJAX
    fetch(`get_product.php?id=${productId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('name').value = data.name;
            document.getElementById('description').value = data.description;
            document.getElementById('price').value = data.price;
            document.getElementById('category').value = data.category;
            document.getElementById('image').value = data.image;
            document.getElementById('stock').value = data.stock;
        })
        .catch(error => {
            console.error('Erreur lors du chargement des données:', error);
            showNotification('Erreur lors du chargement des données', 'error');
        });
}

// Fonction pour filtrer les tableaux
function filterTable(tableId, searchTerm) {
    const table = document.getElementById(tableId);
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const match = text.includes(searchTerm.toLowerCase());
        row.style.display = match ? '' : 'none';
    });
}

// Fonction pour trier les tableaux
function sortTable(tableId, columnIndex) {
    const table = document.getElementById(tableId);
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        const aValue = a.cells[columnIndex].textContent;
        const bValue = b.cells[columnIndex].textContent;
        return aValue.localeCompare(bValue);
    });
    
    rows.forEach(row => tbody.appendChild(row));
}

// Fonction pour paginer les tableaux
function paginateTable(tableId, itemsPerPage = 10) {
    const table = document.getElementById(tableId);
    const rows = table.querySelectorAll('tbody tr');
    const totalPages = Math.ceil(rows.length / itemsPerPage);
    
    // Masquer toutes les lignes
    rows.forEach(row => row.style.display = 'none');
    
    // Afficher seulement la première page
    for (let i = 0; i < itemsPerPage && i < rows.length; i++) {
        rows[i].style.display = '';
    }
    
    // Créer les contrôles de pagination
    createPaginationControls(tableId, totalPages, itemsPerPage);
}

function createPaginationControls(tableId, totalPages, itemsPerPage) {
    const table = document.getElementById(tableId);
    const container = table.parentElement;
    
    // Supprimer l'ancienne pagination
    const oldPagination = container.querySelector('.pagination');
    if (oldPagination) {
        oldPagination.remove();
    }
    
    // Créer la nouvelle pagination
    const pagination = document.createElement('div');
    pagination.className = 'pagination';
    
    for (let i = 1; i <= totalPages; i++) {
        const button = document.createElement('button');
        button.textContent = i;
        button.onclick = () => showPage(tableId, i, itemsPerPage);
        if (i === 1) button.classList.add('active');
        pagination.appendChild(button);
    }
    
    container.appendChild(pagination);
}

function showPage(tableId, page, itemsPerPage) {
    const table = document.getElementById(tableId);
    const rows = table.querySelectorAll('tbody tr');
    const start = (page - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    
    // Masquer toutes les lignes
    rows.forEach(row => row.style.display = 'none');
    
    // Afficher les lignes de la page
    for (let i = start; i < end && i < rows.length; i++) {
        rows[i].style.display = '';
    }
    
    // Mettre à jour les boutons de pagination
    const pagination = table.parentElement.querySelector('.pagination');
    if (pagination) {
        const buttons = pagination.querySelectorAll('button');
        buttons.forEach((btn, index) => {
            btn.classList.toggle('active', index + 1 === page);
        });
    }
}

// Fonction pour rafraîchir les données
function refreshData() {
    location.reload();
}

// Fonction pour afficher un loader
function showLoader() {
    const loader = document.createElement('div');
    loader.className = 'loader';
    loader.innerHTML = '<div class="spinner"></div>';
    document.body.appendChild(loader);
}

function hideLoader() {
    const loader = document.querySelector('.loader');
    if (loader) {
        loader.remove();
    }
}

// Fonction pour formater les dates
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Fonction pour formater les montants
function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-TN', {
        style: 'currency',
        currency: 'TND'
    }).format(amount);
}

// Fonction pour valider les emails
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Fonction pour valider les numéros de téléphone
function validatePhone(phone) {
    const re = /^[\+]?[0-9]{8,15}$/;
    return re.test(phone);
}

// Fonction pour générer des IDs uniques
function generateId() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2);
}

// Fonction pour copier dans le presse-papiers
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification('Copié dans le presse-papiers', 'success');
    }).catch(() => {
        showNotification('Erreur lors de la copie', 'error');
    });
}

// Fonction pour imprimer une page
function printPage() {
    window.print();
}

// Fonction pour sauvegarder les préférences
function savePreferences(preferences) {
    localStorage.setItem('admin_preferences', JSON.stringify(preferences));
}

// Fonction pour charger les préférences
function loadPreferences() {
    const preferences = localStorage.getItem('admin_preferences');
    return preferences ? JSON.parse(preferences) : {};
}

// Fonction pour gérer les raccourcis clavier
function initKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + S pour sauvegarder
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            const activeForm = document.querySelector('form:focus-within');
            if (activeForm) {
                activeForm.submit();
            }
        }
        
        // Ctrl/Cmd + N pour nouveau
        if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
            e.preventDefault();
            const addButton = document.querySelector('button[onclick*="openAddModal"]');
            if (addButton) {
                addButton.click();
            }
        }
        
        // Ctrl/Cmd + F pour rechercher
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            e.preventDefault();
            const searchInput = document.querySelector('input[type="search"]');
            if (searchInput) {
                searchInput.focus();
            }
        }
    });
}

// Initialiser les raccourcis clavier
initKeyboardShortcuts(); 
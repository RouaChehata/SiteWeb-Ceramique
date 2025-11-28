# Interface d'Administration - Ceramic Art Nour

## Vue d'ensemble

L'interface d'administration de Ceramic Art Nour est un système complet de gestion de boutique en ligne qui permet aux administrateurs de gérer tous les aspects du site web.

## Accès à l'administration

### URL d'accès
```
http://votre-domaine.com/admin/
```

### Identifiants par défaut
- **Utilisateur :** admin
- **Mot de passe :** admin123

⚠️ **Important :** Changez ces identifiants par défaut en production !

## Fonctionnalités principales

### 1. Tableau de bord (`index.php`)
- **Statistiques en temps réel :**
  - Nombre total d'utilisateurs
  - Nombre total de commandes
  - Chiffre d'affaires total
  - Commandes en attente
- **Dernières commandes** avec statut et actions rapides
- **Graphiques et visualisations** (à implémenter)

### 2. Gestion des utilisateurs (`users.php`)
- **Liste complète des utilisateurs** avec informations détaillées
- **Actions disponibles :**
  - Voir les détails d'un utilisateur
  - Activer/désactiver un compte
  - Supprimer un utilisateur
  - Exporter la liste des utilisateurs
- **Filtres et recherche** (à implémenter)

### 3. Gestion des commandes (`orders.php`)
- **Liste de toutes les commandes** avec statuts
- **Gestion des statuts :**
  - En attente
  - En cours
  - Expédiée
  - Terminée
  - Annulée
- **Actions disponibles :**
  - Voir les détails d'une commande
  - Modifier le statut
  - Supprimer une commande
  - Exporter les commandes

### 4. Gestion des produits (`products.php`)
- **Catalogue complet des produits**
- **Actions disponibles :**
  - Ajouter un nouveau produit
  - Modifier un produit existant
  - Supprimer un produit
  - Gérer le stock
- **Modal d'ajout/modification** avec formulaire complet
- **Catégories de produits :**
  - Vases
  - Assiettes
  - Tasses
  - Décorations
  - Autres

### 5. Paramètres (`settings.php`)
- **Paramètres du site :**
  - Nom du site
  - Description
  - Email de contact
  - Téléphone de contact
  - Devise (TND, EUR, USD)
  - Taux de TVA
  - Coût de livraison
- **Sécurité :**
  - Changement de mot de passe admin
- **Maintenance :**
  - Sauvegarde de la base de données
  - Vidage du cache
  - Mode maintenance
  - Réinitialisation du site
- **Informations système :**
  - Version PHP
  - Version MySQL
  - Espace disque
  - Mémoire utilisée

## Structure des fichiers

```
admin/
├── index.php          # Tableau de bord
├── login.php          # Page de connexion
├── logout.php         # Déconnexion
├── users.php          # Gestion des utilisateurs
├── orders.php         # Gestion des commandes
├── products.php       # Gestion des produits
├── settings.php       # Paramètres
├── admin.css          # Styles CSS
├── admin.js           # JavaScript
└── README.md          # Documentation
```

## Sécurité

### Authentification
- Session PHP sécurisée
- Vérification de l'authentification sur chaque page
- Protection contre l'accès non autorisé

### Validation des données
- Validation côté serveur (PHP)
- Validation côté client (JavaScript)
- Protection contre les injections SQL (PDO)
- Échappement des données affichées

### Recommandations de sécurité
1. **Changez les identifiants par défaut**
2. **Utilisez HTTPS en production**
3. **Limitez les tentatives de connexion**
4. **Sauvegardez régulièrement la base de données**
5. **Maintenez les logiciels à jour**

## Personnalisation

### Couleurs et thème
Les couleurs principales sont définies dans `admin.css` :
```css
:root {
    --primary-color: #b48a92;
    --secondary-color: #e8a7b0;
    --accent-color: #f8e1e4;
    /* ... */
}
```

### Ajout de nouvelles fonctionnalités
1. Créez le fichier PHP dans le dossier `admin/`
2. Ajoutez le lien dans la navigation (sidebar)
3. Incluez les styles CSS nécessaires
4. Ajoutez les fonctionnalités JavaScript si besoin

## Fonctionnalités à implémenter

### Court terme
- [ ] Système de catégories complet
- [ ] Gestion des images (upload)
- [ ] Système de sauvegarde automatique
- [ ] Logs d'activité admin
- [ ] Notifications par email

### Moyen terme
- [ ] Graphiques et statistiques avancées
- [ ] Système de rôles et permissions
- [ ] API REST pour l'administration
- [ ] Mode sombre/clair
- [ ] Export PDF des rapports

### Long terme
- [ ] Application mobile admin
- [ ] Intelligence artificielle pour les recommandations
- [ ] Système de gestion des stocks avancé
- [ ] Intégration avec les réseaux sociaux

## Support et maintenance

### Logs d'erreur
Les erreurs sont enregistrées dans :
- `../db_error.log` (erreurs de base de données)
- Logs PHP du serveur web

### Sauvegarde
- Sauvegardez régulièrement la base de données
- Sauvegardez les fichiers du site
- Testez les restaurations

### Mise à jour
1. Sauvegardez le site
2. Testez les modifications en local
3. Déployez en production
4. Vérifiez le bon fonctionnement

## Contact et support

Pour toute question ou problème :
- Email : contact@ceramicartnour.com
- Documentation technique : voir les commentaires dans le code
- Base de connaissances : à créer

---

**Version :** 1.0  
**Dernière mise à jour :** <?php echo date('d/m/Y'); ?>  
**Auteur :** Équipe de développement Ceramic Art Nour 
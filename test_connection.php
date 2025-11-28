<?php
ob_start();
// Inclure le fichier de configuration
require_once 'config.php';

try {
    // Tester la connexion
    if ($conn) {
        echo "<h2 style='color: green;'>✅ Connexion à la base de données réussie !</h2>";
        ob_flush(); flush();
        
        // Nettoyer les données de test précédentes si elles existent
        $conn->exec("DELETE FROM users WHERE username = 'testuser'");
        $conn->exec("DELETE FROM products WHERE name = 'Vase Test'");

        // Tester la création d'un utilisateur
        $test_user = [
            'username' => 'testuser',
            'fullname' => 'Test User',
            'email' => 'test@example.com',
            'password' => password_hash('test123', PASSWORD_DEFAULT),
            'address' => '123 Test St',
            'phone' => '123-456-7890'
        ];
        
        $sql = "INSERT INTO users (username, fullname, email, password, address, phone) VALUES (:username, :fullname, :email, :password, :address, :phone)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($test_user);
        
        echo "<p style='color: green;'>✅ Test d'insertion d'utilisateur réussi !</p>";
        ob_flush(); flush();
        
        // Tester la lecture des utilisateurs
        $sql = "SELECT * FROM users";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        echo "<h3>Liste des utilisateurs :</h3>";
        echo "<ul>";
        foreach ($users as $user) {
            echo "<li>{$user['fullname']} ({$user['email']}) - Username: {$user['username']}</li>";
        }
        echo "</ul>";
        ob_flush(); flush();
        
        // Tester la création d'un produit
        $test_product = [
            'name' => 'Vase Test',
            'description' => 'Un beau vase en céramique',
            'price' => 29.99,
            'image_url' => 'im1.jpg',
            'stock' => 10
        ];
        
        $sql = "INSERT INTO products (name, description, price, image_url, stock) 
                VALUES (:name, :description, :price, :image_url, :stock)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($test_product);
        
        echo "<p style='color: green;'>✅ Test d'insertion de produit réussi !</p>";
        ob_flush(); flush();
        
        // Afficher les produits
        $sql = "SELECT * FROM products";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $products = $stmt->fetchAll();
        
        echo "<h3>Liste des produits :</h3>";
        echo "<ul>";
        foreach ($products as $product) {
            echo "<li>{$product['name']} - {$product['price']}€ (Stock: {$product['stock']})</li>";
        }
        echo "</ul>";
        ob_flush(); flush();
        
    }
} catch(PDOException $e) {
    $error_message = "❌ Erreur de connexion : " . $e->getMessage() . "\n";
    file_put_contents('db_error.log', $error_message, FILE_APPEND);
    echo "<h2 style='color: red;'>❌ Erreur de connexion. Voir db_error.log pour les détails.</h2>";
}
?> 
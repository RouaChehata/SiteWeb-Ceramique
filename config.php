<?php
$servername = "localhost";
$username = "root";
$password = ""; // À changer pour un mot de passe sécurisé en production
$dbname = "ceramic_shop"; // Changé pour correspondre au nom de la base de données existante

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    // Ensure products table exists
    $sql = "CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10, 2) NOT NULL,
        image_url VARCHAR(255),
        stock INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);

    // Ensure is_active column exists in users table
    $sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT TRUE";
    $conn->exec($sql);

    // Ensure last_login column exists in users table
    $sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login TIMESTAMP NULL";
    $conn->exec($sql);

    // Ensure orders table exists
    $sql = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        total_amount DECIMAL(10, 2) NOT NULL,
        status VARCHAR(50) DEFAULT 'pending',
        fullname VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        delivery_address TEXT NOT NULL,
        phone VARCHAR(20) NOT NULL,
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )";
    $conn->exec($sql);

    // Ensure order_items table exists
    $sql = "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id VARCHAR(255) NOT NULL,
        product_name VARCHAR(255) NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        color VARCHAR(50) NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    )";
    $conn->exec($sql);

} catch(PDOException $e) {
    // Si la base de données n'existe pas, la créer (ce cas ne devrait pas arriver si elle existe déjà)
    if ($e->getCode() == 1049) {
        try {
            $conn = new PDO("mysql:host=$servername", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Créer la base de données
            $sql = "CREATE DATABASE $dbname";
            $conn->exec($sql);
            
            // Créer la table users
            $conn->exec("USE $dbname");
            $sql = "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                fullname VARCHAR(100) NOT NULL,
                address TEXT NOT NULL,
                phone VARCHAR(20) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_login TIMESTAMP NULL,
                is_active BOOLEAN DEFAULT TRUE,
                reset_token VARCHAR(64) NULL,
                reset_expiry TIMESTAMP NULL
            )";
            $conn->exec($sql);
            
            // Reconnecter avec la nouvelle base de données
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch(PDOException $e2) {
            $_SESSION['db_error'] = "Erreur lors de la création de la base de données : " . $e2->getMessage();
            header('Location: login.php');
            exit();
        }
    } else {
        $_SESSION['db_error'] = "Erreur de connexion à la base de données : " . $e->getMessage();
        header('Location: login.php');
        exit();
    }
}
?> 
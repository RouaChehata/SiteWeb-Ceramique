<?php
require_once '../config.php';

try {
    // Création de la table contact_messages
    $sql = "CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_read BOOLEAN DEFAULT FALSE
    )";
    
    $conn->exec($sql);
    echo "La table 'contact_messages' a été créée avec succès.";
    
} catch(PDOException $e) {
    die("ERREUR : Impossible d'exécuter la requête. " . $e->getMessage());
}

// Redirection vers la page des messages après 3 secondes
header('Refresh: 3; URL=messages.php');
echo "<br>Redirection vers la page des messages...";
?>

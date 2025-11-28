<?php
echo "<h1>Vérification de la configuration</h1>";

// Vérifier PHP
echo "<h2>Version PHP : " . phpversion() . "</h2>";

// Vérifier les extensions nécessaires
$required_extensions = ['pdo', 'pdo_mysql', 'mysqli'];
echo "<h2>Extensions PHP :</h2>";
foreach ($required_extensions as $ext) {
    echo $ext . " : " . (extension_loaded($ext) ? "✅" : "❌") . "<br>";
}

// Vérifier la connexion à la base de données
require_once 'config.php';
try {
    $conn->query("SELECT 1");
    echo "<h2>Base de données : ✅ Connecté</h2>";
    
    // Vérifier les tables
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<h2>Tables trouvées :</h2>";
    foreach ($tables as $table) {
        echo "- " . $table . "<br>";
    }
} catch(PDOException $e) {
    echo "<h2>Base de données : ❌ Erreur</h2>";
    echo "Message d'erreur : " . $e->getMessage();
}

// Vérifier les permissions des fichiers
echo "<h2>Permissions des fichiers :</h2>";
$files_to_check = [
    'config.php',
    'create_database.sql',
    'ce.html',
    'login.php',
    'register.php'
];

foreach ($files_to_check as $file) {
    echo $file . " : " . (file_exists($file) ? "✅" : "❌") . "<br>";
}

// Vérifier les chemins
echo "<h2>Chemins :</h2>";
echo "Document Root : " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Chemin actuel : " . __DIR__ . "<br>";

// Vérifier les variables d'environnement
echo "<h2>Variables d'environnement :</h2>";
echo "SERVER_SOFTWARE : " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "SERVER_NAME : " . $_SERVER['SERVER_NAME'] . "<br>";
echo "SERVER_PORT : " . $_SERVER['SERVER_PORT'] . "<br>";
?> 
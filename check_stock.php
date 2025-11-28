<?php
require_once 'config.php';

header('Content-Type: application/json');

$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($productId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de produit invalide']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($product) {
        echo json_encode(['quantity' => (int)$product['stock']]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Produit non trouvé']);
    }
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de base de données']);
}

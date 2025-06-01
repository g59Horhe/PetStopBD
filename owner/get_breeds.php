<?php
// AJAX endpoint to get breeds for a specific pet type
require_once '../includes/auth_functions.php';
session_start();

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}
header('Content-Type: application/json');

if (isset($_GET['pet_type_id']) && is_numeric($_GET['pet_type_id'])) {
    $pet_type_id = (int)$_GET['pet_type_id'];
    
    $query = "SELECT id, name, size_category FROM pet_breeds WHERE pet_type_id = ? ORDER BY name";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $pet_type_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $breeds = [];
    while ($row = $result->fetch_assoc()) {
        $breeds[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'size_category' => $row['size_category']
        ];
    }
    
    echo json_encode($breeds);
} else {
    echo json_encode([]);
}

$conn->close();
?>
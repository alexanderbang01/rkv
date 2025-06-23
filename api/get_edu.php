<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once('../database/db_conn.php');

// Check if eduId is provided
if (!isset($_GET['eduId']) || empty($_GET['eduId'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Education ID is required'
    ]);
    exit;
}

$eduId = intval($_GET['eduId']);

try {
    // Prepare SQL statement to get specializations for the education
    $stmt = $conn->prepare("
        SELECT e.id, e.name, e.length, e.euxLength 
        FROM education e 
        WHERE e.eduId = ? 
        ORDER BY e.name ASC
    ");

    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }

    $stmt->bind_param('i', $eduId);
    $stmt->execute();

    $result = $stmt->get_result();
    $specializations = [];

    while ($row = $result->fetch_assoc()) {
        $specializations[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'length' => $row['length'],
            'euxLength' => $row['euxLength']
        ];
    }

    $stmt->close();

    // Return success response
    echo json_encode([
        'success' => true,
        'specializations' => $specializations,
        'count' => count($specializations)
    ]);
} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} finally {
    // Close database connection
    if (isset($conn)) {
        $conn->close();
    }
}

<?php
session_start();
require_once('../database/db_conn.php');

// Check if user is logged in
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'Ikke autoriseret']);
    exit;
}

header('Content-Type: application/json');

$search = $_GET['search'] ?? '';
$search = '%' . $search . '%';

try {
    // Get education titles with their educations that match the search
    $stmt = $conn->prepare("
        SELECT 
            et.id as title_id, 
            et.title, 
            et.link, 
            et.last_updated,
            e.id as education_id, 
            e.name, 
            e.length, 
            e.euxLength,
            e.updated_at
        FROM educationtitle et 
        LEFT JOIN education e ON et.id = e.eduId 
        WHERE et.title LIKE ? OR e.name LIKE ?
        ORDER BY et.title, e.name
    ");

    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();

    $educationData = [];
    $totalResults = 0;

    while ($row = $result->fetch_assoc()) {
        $titleId = $row['title_id'];
        if (!isset($educationData[$titleId])) {
            $educationData[$titleId] = [
                'title_id' => $titleId,
                'title' => $row['title'],
                'link' => $row['link'],
                'last_updated' => $row['last_updated'],
                'educations' => []
            ];
        }
        if ($row['education_id']) {
            $educationData[$titleId]['educations'][] = [
                'id' => $row['education_id'],
                'name' => $row['name'],
                'length' => $row['length'],
                'euxLength' => $row['euxLength'],
                'updated_at' => $row['updated_at']
            ];
            $totalResults++;
        }
    }

    echo json_encode([
        'success' => true,
        'data' => array_values($educationData),
        'total_results' => $totalResults,
        'search_term' => trim($_GET['search'] ?? '')
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Der opstod en fejl ved søgningen'
    ]);
}

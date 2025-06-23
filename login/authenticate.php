<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once('../database/db_conn.php');

$enteredCode = $_POST['code'] ?? '';

if (empty($enteredCode)) {
    echo json_encode(['success' => false, 'message' => 'Kode er påkrævet']);
    exit;
}

try {
    $query = "SELECT * FROM users WHERE code = ?";
    $statement = mysqli_prepare($conn, $query);

    if (!$statement) {
        throw new Exception('Database fejl');
    }

    mysqli_stmt_bind_param($statement, "s", $enteredCode);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        // Simple session setup
        $_SESSION['loggedIn'] = true;
        $_SESSION['id'] = $row['id'];
        $_SESSION['name'] = $row['name'];
        $_SESSION['department'] = $row['department'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['phone'] = $row['phone'];

        echo json_encode([
            'success' => true,
            'message' => 'Login succesfuldt',
            'redirect' => '../'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Ugyldig kode. Prøv igen.'
        ]);
    }

    mysqli_stmt_close($statement);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Der opstod en fejl. Prøv igen senere.'
    ]);
} finally {
    if (isset($conn)) {
        mysqli_close($conn);
    }
}

<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';

// Only allow DELETE method
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed'], JSON_THROW_ON_ERROR);
    exit;
}

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true, 512, JSON_THROW_ON_ERROR);

    // Validate required fields
    if (empty($data['fullName'])) {
        throw new InvalidArgumentException('Parameter fullName is required');
    }

    $fullName = $data['fullName'];

    // Initialize AWS SSM client
    $ssm = createSsmClient();

    // Delete the parameter
    $result = $ssm->deleteParameter([
        'Name' => $fullName
    ]);

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Parameter deleted successfully'
    ], JSON_THROW_ON_ERROR);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_THROW_ON_ERROR);
}
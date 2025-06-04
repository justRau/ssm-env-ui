<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    sendErrorResponse('Method not allowed', 405);
}

try {
    $input = getJsonInput();

    // Validate required fields (same as Go updateParameter function)
    $requiredFields = ['fullName', 'value', 'type'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field])) {
            sendErrorResponse("Missing required field: $field");
        }
    }

    $fullName = $input['fullName'];
    $value = $input['value'];
    $type = $input['type'];

    $ssmClient = createSsmClient();

    // Update parameter (same as Go PutParameter call with Overwrite: true)
    $result = $ssmClient->putParameter([
        'Name' => $fullName,
        'Value' => $value,
        'Type' => $type,
        'Overwrite' => true  // Overwrite existing parameter
    ]);

    sendJsonResponse([
        'success' => true,
        'message' => 'Parameter updated successfully',
        'parameter' => [
            'fullName' => $fullName,
            'value' => $value,
            'type' => $type
        ]
    ]);

} catch (AwsException $e) {
    sendErrorResponse('AWS Error: ' . $e->getAwsErrorMessage(), 500);
} catch (Exception $e) {
    sendErrorResponse('Error: ' . $e->getMessage(), 500);
}
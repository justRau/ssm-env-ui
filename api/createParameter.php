<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse('Method not allowed', 405);
}

try {
    $input = getJsonInput();

    // Validate required fields (same as Go createNewParameter function)
    $requiredFields = ['prefix', 'name', 'value', 'type'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            sendErrorResponse("Missing required field: $field");
        }
    }

    $prefix = $input['prefix'];
    $name = $input['name'];
    $value = $input['value'];
    $type = $input['type'];

    // Ensure prefix ends with '/'
    if (!str_ends_with($prefix, '/')) {
        $prefix .= '/';
    }

    // Validate parameter type using match expression (PHP 8 feature)
    $isValidType = match ($type) {
        'String', 'StringList', 'SecureString' => true,
        default => false
    };

    if (!$isValidType) {
        sendErrorResponse('Invalid parameter type. Allowed types: String, StringList, SecureString');
    }

    $fullName = $prefix . $name;
    $ssmClient = createSsmClient();

    // Create parameter (same as Go PutParameter call)
    $result = $ssmClient->putParameter([
        'Name' => $fullName,
        'Value' => $value,
        'Type' => $type,
        'Overwrite' => false  // Don't overwrite existing parameters
    ]);

    sendJsonResponse([
        'success' => true,
        'message' => 'Parameter created successfully',
        'parameter' => [
            'name' => $name,
            'fullName' => $fullName,
            'value' => $value,
            'type' => $type
        ]
    ]);

} catch (AwsException $e) {
    $errorCode = $e->getAwsErrorCode();
    if ($errorCode === 'ParameterAlreadyExists') {
        sendErrorResponse('Parameter already exists', 409);
    } else {
        sendErrorResponse('AWS Error: ' . $e->getAwsErrorMessage(), 500);
    }
} catch (Exception $e) {
    sendErrorResponse('Error: ' . $e->getMessage(), 500);
}
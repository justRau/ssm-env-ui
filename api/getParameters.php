<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';

try {
    // Get prefix from query parameter
    $prefix = $_GET['prefix'] ?? '';

    if (empty($prefix)) {
        sendErrorResponse('Missing required parameter: prefix');
    }

    // Ensure prefix ends with '/' (same logic as Go version)
    if (!str_ends_with($prefix, '/')) {
        $prefix .= '/';
    }

    $ssmClient = createSsmClient();
    $parameters = [];
    $nextToken = null;

    // Fetch all parameters with pagination (same as Go fetchParameters function)
    do {
        $params = [
            'Path' => $prefix,
            'Recursive' => false,
            'WithDecryption' => true
        ];

        if ($nextToken) {
            $params['NextToken'] = $nextToken;
        }

        $result = $ssmClient->getParametersByPath($params);

        if (isset($result['Parameters'])) {
            $parameters = array_merge($parameters, $result['Parameters']);
        }

        $nextToken = $result['NextToken'] ?? null;

    } while ($nextToken);

    // Format parameters similar to Go formatParameters function
    $formattedParameters = [];
    foreach ($parameters as $param) {
        $name = $param['Name'];
        $value = $param['Value'];
        $type = $param['Type'];

        // Extract just the parameter name without the full path
        $nameParts = explode('/', $name);
        $shortName = end($nameParts);

        $formattedParameters[] = [
            'name' => $shortName,
            'fullName' => $name,
            'value' => $value,
            'type' => $type
        ];
    }

    // Sort parameters alphabetically by name (same as Go version)
    usort($formattedParameters, function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });

    sendJsonResponse([
        'success' => true,
        'parameters' => $formattedParameters,
        'prefix' => $prefix
    ]);

} catch (AwsException $e) {
    sendErrorResponse('AWS Error: ' . $e->getAwsErrorMessage(), 500);
} catch (Exception $e) {
    sendErrorResponse('Error: ' . $e->getMessage(), 500);
}
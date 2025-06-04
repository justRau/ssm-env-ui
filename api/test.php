<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';

// Simple health check endpoint using PHP 8.3 features
function getSystemInfo(): array {
    return [
        'php_version' => PHP_VERSION,
        'timestamp' => time(),
        'aws_sdk_loaded' => class_exists('Aws\Ssm\SsmClient'),
        'server_info' => [
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]
    ];
}

try {
    $info = getSystemInfo();

    // Test SSM client creation
    $ssmClient = createSsmClient();
    $info['ssm_client_created'] = true;
    $info['aws_region'] = $_ENV['AWS_DEFAULT_REGION'] ?? 'eu-central-1';

    sendJsonResponse([
        'success' => true,
        'message' => 'SSM Parameter Editor PHP Backend is working!',
        'system' => $info
    ]);

} catch (Exception $e) {
    sendErrorResponse('Backend test failed: ' . $e->getMessage(), 500);
}
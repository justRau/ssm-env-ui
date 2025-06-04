<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Aws\Ssm\SsmClient;
use Aws\Exception\AwsException;

// Create SSM client using default credential chain (same as Go tool)
function createSsmClient(): SsmClient {
    return new SsmClient([
        'region' => $_ENV['AWS_DEFAULT_REGION'] ?? 'eu-central-1',
        'version' => 'latest'
    ]);
}

// Send JSON response with hardened encoding
function sendJsonResponse(array $data, int $statusCode = 200): never {
    http_response_code($statusCode);
    header('Content-Type: application/json');

    try {
        echo json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    } catch (JsonException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to encode response'], JSON_THROW_ON_ERROR);
    }

    exit;
}

// Send error response
function sendErrorResponse(string $message, int $statusCode = 400): never {
    sendJsonResponse(['error' => $message], $statusCode);
}

// Get JSON input for POST/PUT requests with hardened decoding
function getJsonInput(): array {
    $input = file_get_contents('php://input');

    if ($input === false || $input === '') {
        sendErrorResponse('No input data provided', 400);
    }

    try {
        $data = json_decode($input, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($data)) {
            sendErrorResponse('Input must be a JSON object', 400);
        }

        return $data;
    } catch (JsonException $e) {
        sendErrorResponse('Invalid JSON input: ' . $e->getMessage(), 400);
    }
}
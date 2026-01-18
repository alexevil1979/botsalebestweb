<?php

/**
 * Git Webhook Handler for Auto-Deploy
 * 
 * Usage:
 * 1. Set up a webhook in your Git repository pointing to this file
 * 2. Set WEBHOOK_SECRET in .env
 * 3. Configure your web server to execute this script
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Config;

Config::load(__DIR__ . '/../.env');

$secret = Config::get('WEBHOOK_SECRET', '');
$headers = getallheaders();
$signature = $headers['X-Hub-Signature-256'] ?? $headers['X-Hub-Signature'] ?? '';

// Verify webhook secret
if ($secret) {
    $payload = file_get_contents('php://input');
    $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);
    
    if (!hash_equals($expectedSignature, $signature)) {
        http_response_code(403);
        echo "Forbidden";
        exit;
    }
}

// Verify it's a push to main/master
$payload = json_decode(file_get_contents('php://input'), true);
$ref = $payload['ref'] ?? '';

if (strpos($ref, 'refs/heads/main') === false && strpos($ref, 'refs/heads/master') === false) {
    http_response_code(200);
    echo "OK - Not main/master branch";
    exit;
}

// Execute deploy script
$deployScript = __DIR__ . '/deploy.sh';
if (file_exists($deployScript)) {
    chmod($deployScript, 0755);
    $output = [];
    $returnVar = 0;
    exec("bash {$deployScript} 2>&1", $output, $returnVar);
    
    http_response_code($returnVar === 0 ? 200 : 500);
    echo implode("\n", $output);
} else {
    http_response_code(500);
    echo "Deploy script not found";
}

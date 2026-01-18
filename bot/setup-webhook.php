<?php

/**
 * Script to set up Telegram webhook
 * Run this once after deployment
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Config;
use Core\TelegramAPI;

Config::load(__DIR__ . '/../.env');

$webhookUrl = Config::get('TELEGRAM_WEBHOOK_URL');
$secret = Config::get('TELEGRAM_WEBHOOK_SECRET', '');

if (!$webhookUrl) {
    echo "❌ TELEGRAM_WEBHOOK_URL not configured in .env\n";
    exit(1);
}

echo "Setting up webhook: {$webhookUrl}\n";

$telegram = new TelegramAPI();
$result = $telegram->setWebhook($webhookUrl, $secret);

if ($result) {
    echo "✅ Webhook set successfully!\n";
    
    // Verify webhook
    $info = $telegram->getWebhookInfo();
    if ($info) {
        echo "\nWebhook info:\n";
        echo "URL: " . ($info['url'] ?? 'N/A') . "\n";
        echo "Pending updates: " . ($info['pending_update_count'] ?? 0) . "\n";
        if (isset($info['last_error_date'])) {
            echo "Last error: " . date('Y-m-d H:i:s', $info['last_error_date']) . "\n";
            echo "Error message: " . ($info['last_error_message'] ?? 'N/A') . "\n";
        }
    }
} else {
    echo "❌ Failed to set webhook\n";
    exit(1);
}

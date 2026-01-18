<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Config;
use Core\Translator;
use Bot\WebhookHandler;

try {
    Config::load(__DIR__ . '/../.env');
    Translator::loadTranslations();
    
    $handler = new WebhookHandler();
    $handler->handle();
} catch (\Exception $e) {
    error_log("Webhook error: " . $e->getMessage());
    http_response_code(500);
    echo "Internal Server Error";
}

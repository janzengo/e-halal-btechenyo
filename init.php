<?php
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables with error checking
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
try {
    $dotenv->load();
    $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USERNAME', 'MAIL_MAILER', 'MAIL_HOST', 'MAIL_PORT', 'MAIL_USERNAME', 'MAIL_PASSWORD'])->notEmpty();
} catch (Exception $e) {
    die('Error loading environment variables: ' . $e->getMessage());
}

// Get the host dynamically
$host = $_SERVER['HTTP_HOST'];

// Define base URL for the application dynamically
if (!defined('BASE_URL')) define('BASE_URL', 'http://' . $host . '/e-halal/');

// Set global timezone for the entire project
date_default_timezone_set('Asia/Manila');

// Protect the config functions from being redeclared
if (!function_exists('config')) {
    function config() {
        return [
            'DB_HOST' => $_ENV['DB_HOST'],
            'DB_NAME' => $_ENV['DB_NAME'],
            'DB_USERNAME' => $_ENV['DB_USERNAME'],
            'DB_PASSWORD' => $_ENV['DB_PASSWORD'] ?? '',
        ];
    }
}

if (!function_exists('mail_config')) {
    function mail_config() {
        return [
            'mailer' => $_ENV['MAIL_MAILER'] ?? 'sendmail',
            'use_smtp' => ($_ENV['MAIL_MAILER'] ?? 'sendmail') === 'smtp',
            'mail_from' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@e-halal.edu.ph',
            'mail_from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'E-Halal BTECHenyo Voting System',
            'mail_reply_to' => $_ENV['MAIL_REPLY_TO'] ?? 'noreply@e-halal.edu.ph',
            'smtp' => [
                'host' => $_ENV['MAIL_HOST'] ?? '',
                'port' => $_ENV['MAIL_PORT'] ?? 587,
                'username' => $_ENV['MAIL_USERNAME'] ?? '',
                'password' => $_ENV['MAIL_PASSWORD'] ?? '',
                'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls'
            ]
        ];
    }
}

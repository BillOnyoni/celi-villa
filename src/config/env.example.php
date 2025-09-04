<?php
// src/config/env.example.php
// Copy this file to env.php and fill in your actual M-Pesa credentials

// M-Pesa Configuration
$_ENV['MPESA_ENVIRONMENT'] = 'sandbox'; // Change to 'production' for live
$_ENV['MPESA_CONSUMER_KEY'] = 'YOUR_CONSUMER_KEY_HERE';
$_ENV['MPESA_CONSUMER_SECRET'] = 'YOUR_CONSUMER_SECRET_HERE';
$_ENV['MPESA_SHORTCODE'] = 'YOUR_PAYBILL_NUMBER'; // Your actual paybill number
$_ENV['MPESA_PASSKEY'] = 'YOUR_PASSKEY_HERE';

// Your website's public URL for callbacks
$_ENV['SITE_URL'] = 'https://yourdomain.com'; // Change to your actual domain

// Database Configuration (if different from db.php)
$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_USER'] = 'root';
$_ENV['DB_PASS'] = '';
$_ENV['DB_NAME'] = 'celica';

// Email Configuration (optional - for better email delivery)
$_ENV['SMTP_HOST'] = 'smtp.gmail.com';
$_ENV['SMTP_PORT'] = '587';
$_ENV['SMTP_USERNAME'] = 'your-email@gmail.com';
$_ENV['SMTP_PASSWORD'] = 'your-app-password';
?>
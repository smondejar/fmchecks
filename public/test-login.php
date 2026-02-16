<?php
// Simple test - directly load login page bypassing router
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Load dependencies
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../src/middleware/Auth.php';
require __DIR__ . '/../src/middleware/Csrf.php';
require __DIR__ . '/../src/models/User.php';
require __DIR__ . '/../src/models/Permission.php';
require __DIR__ . '/../src/models/Setting.php';

// Set page variables
$pageTitle = 'Login Test';

// Include login view
require __DIR__ . '/../src/views/login.php';

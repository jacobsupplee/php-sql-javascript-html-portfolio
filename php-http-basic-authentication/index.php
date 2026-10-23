<?php
// HTTP Basic Authentication demo.
// Keep credentials in auth.local.php, which is excluded from Git.

$configPath = __DIR__ . '/auth.local.php';

if (!is_file($configPath)) {
    http_response_code(500);
    exit('Authentication is not configured. Copy auth.example.php to auth.local.php and set local demo credentials.');
}

try {
    $authConfig = require $configPath;
} catch (Throwable $e) {
    error_log('HTTP Basic Authentication Demo authentication configuration failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Authentication could not be configured. Check the local configuration file.');
}

if (
    !is_array($authConfig) ||
    !isset($authConfig['username'], $authConfig['password']) ||
    !is_string($authConfig['username']) ||
    !is_string($authConfig['password']) ||
    $authConfig['username'] === '' ||
    $authConfig['password'] === ''
) {
    http_response_code(500);
    exit('Authentication configuration is missing a username or password.');
}

$username = $authConfig['username'];
$password = $authConfig['password'];

$providedUsername = $_SERVER['PHP_AUTH_USER'] ?? '';
$providedPassword = $_SERVER['PHP_AUTH_PW'] ?? '';

$isAuthenticated =
    is_string($providedUsername) &&
    is_string($providedPassword) &&
    hash_equals($username, $providedUsername) &&
    hash_equals($password, $providedPassword);

if (!$isAuthenticated) {
    // HTTP Basic Auth makes the browser display its built-in credential prompt.
    header('WWW-Authenticate: Basic realm="Portfolio Authentication Demo", charset="UTF-8"');
    http_response_code(401);
    $pageTitle = 'Authentication required';
    $message = 'Enter the configured demo credentials to continue.';
    $submessage = 'This page demonstrates HTTP Basic Authentication.';
} else {
    http_response_code(200);
    $pageTitle = 'Authentication successful';
    $safeUsername = htmlspecialchars($providedUsername, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $message = 'Welcome, ' . $safeUsername . '.';
    $submessage = 'Your credentials were accepted by the server.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HTTP Basic Authentication Demo</title>
    <link rel="stylesheet" href="../design.css">
</head>
<body>
    <main class="login-box">
        <h1><?= htmlspecialchars($pageTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h1>
        <p><?= $message ?></p>
        <p><?= htmlspecialchars($submessage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
    </main>
</body>
</html>

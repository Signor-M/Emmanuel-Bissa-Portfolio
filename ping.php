<?php
// Endpoint pour garder le serveur web ET la base de données Aiven éveillés via UptimeRobot
header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/includes/config.php';

$dbStatus = 'OK';

try {
    if (isset($pdo)) {
        // Exécution d'une petite requête pour maintenir la connexion active côté Aiven
        $pdo->query("SELECT 1");
    } else {
        $dbStatus = 'PDO Not Initialized';
    }
} catch (Exception $e) {
    http_response_code(500);
    echo "ERROR: DB Connection failed - " . $e->getMessage();
    exit;
}

http_response_code(200);
echo "pong (DB: {$dbStatus})";


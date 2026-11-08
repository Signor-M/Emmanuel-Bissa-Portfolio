<?php

// En production, Render fournit les variables directement via l'environnement.
// En local, les valeurs peuvent être placées dans .env.example (fichier ignoré).
$appEnvironment = strtolower(trim(getenv('APP_ENV') ?: 'local'));
if (!in_array($appEnvironment, ['production', 'prod'], true)) {
    $envFile = dirname(__DIR__) . '/.env.example';
    if (is_readable($envFile)) {
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }

            [$name, $value] = array_pad(explode('=', $line, 2), 2, '');
            $name = trim($name);
            $value = trim($value);

            if ($name !== '' && getenv($name) === false) {
                putenv($name . '=' . trim($value, "\"'"));
            }
        }
    }
}

    $db_host = getenv('DB_HOST') ?: "localhost";
    $db_name = getenv('DB_NAME') ?: "portfolio";
    $db_user = getenv('DB_USER') ?: "admin";
    $db_pass = getenv('DB_PASS') ?: "admin";
    $db_port = getenv('DB_PORT') ?: "3306";
    $db_char = "utf8mb4";

    try {
        $pdo = new PDO("mysql:host={$db_host};port={$db_port};dbname={$db_name};charset={$db_char}", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Récupération des informations de l'administrateur (prénom et nom)
        try {
            $adminStmt = $pdo->query("SELECT firstname, lastname FROM admin ORDER BY id ASC LIMIT 1");
            $adminUser = $adminStmt->fetch(PDO::FETCH_ASSOC);
            $firstname = !empty($adminUser['firstname']) ? $adminUser['firstname'] : 'Emmanuel';
            $lastname = !empty($adminUser['lastname']) ? $adminUser['lastname'] : 'Bissa';
        } catch (Exception $ex) {
            $firstname = 'Emmanuel';
            $lastname = 'Bissa';
        }
    }
    catch(Exception $e){
        die("Erreur : " . $e->getMessage());
    }
?>
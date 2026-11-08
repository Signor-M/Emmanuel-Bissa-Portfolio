<?php

/**
 * Redirige vers une URL spécifiée et arrête l'exécution de la page.
 */
function redirectTo($url) {
    header("Location: {$url}");
    exit();
}

/**
 * Nettoie et sécurise les données saisies par l'utilisateur.
 */
function security($data) {
    if (is_null($data)) return "";
    $temp = trim($data);
    $temp = strip_tags($temp);
    $temp = stripslashes($temp);
    return $temp;
}

/**
 * Affiche les alertes d'erreur et de succès stockées en session puis les réinitialise.
 */
function displayAlerts() {
    if (isset($_SESSION["error"])) {
        echo '<div class="alert-error">' . htmlspecialchars($_SESSION["error"]) . '</div>';
        unset($_SESSION["error"]);
    }

    if (isset($_SESSION["success"])) {
        echo '<div class="alert-success">' . htmlspecialchars($_SESSION["success"]) . '</div>';
        unset($_SESSION["success"]);
    }
}

/**
 * Envoie une image vers Cloudinary et retourne son URL publique HTTPS.
 */
function handleFileUpload(
    $fileArray,
    $targetDir = null,
    $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'],
    $resourceType = 'image',
    $folderName = 'images'
) {
    if (!isset($fileArray) || !isset($fileArray['error']) || $fileArray['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // Aucun fichier fourni
    }

    if ($fileArray['error'] !== UPLOAD_ERR_OK) {
        switch ($fileArray['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $_SESSION['error'] = "Le fichier téléchargé dépasse la taille maximale autorisée.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $_SESSION['error'] = "Le fichier n'a été que partiellement téléchargé.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $_SESSION['error'] = "Échec de l'écriture du fichier temporaire (permissions serveur).";
                break;
            default:
                $_SESSION['error'] = "Erreur lors du téléchargement du fichier (Code : " . $fileArray['error'] . ").";
                break;
        }
        return false;
    }

    $fileName = basename($fileArray['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($fileExt, $allowedExts)) {
        $_SESSION['error'] = "Format d'image non valide. Formats autorisés : " . implode(', ', $allowedExts);
        return false;
    }

    $tmpPath = $fileArray['tmp_name'];
    if (!is_uploaded_file($tmpPath) && !file_exists($tmpPath)) {
        $_SESSION['error'] = "Le fichier image téléchargé est introuvable.";
        return false;
    }

    $cloudName = getenv('CLOUDINARY_CLOUD_NAME') ?: '';
    $apiKey = getenv('CLOUDINARY_API_KEY') ?: '';
    $apiSecret = getenv('CLOUDINARY_API_SECRET') ?: '';

    if ($cloudName === '' || $apiKey === '' || $apiSecret === '') {
        $_SESSION['error'] = "Le stockage d'images Cloudinary n'est pas configuré sur le serveur.";
        return false;
    }

    if (!function_exists('curl_init')) {
        $_SESSION['error'] = "L'extension PHP cURL est nécessaire pour envoyer les images.";
        return false;
    }

    $timestamp = time();
    $appEnvironment = strtolower(trim(getenv('APP_ENV') ?: 'local'));
    $folderEnvironment = in_array($appEnvironment, ['production', 'prod'], true)
        ? 'production'
        : 'local';
    $folder = 'emmanuel-bissa-portfolio/uploads/' . $folderEnvironment . '/' . $folderName;
    $signature = sha1("folder={$folder}&timestamp={$timestamp}{$apiSecret}");
    $curl = curl_init("https://api.cloudinary.com/v1_1/" . rawurlencode($cloudName) . '/' . $resourceType . '/upload');

    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_POSTFIELDS => [
            'file' => new CURLFile($tmpPath, $fileArray['type'] ?? 'application/octet-stream', $fileName),
            'api_key' => $apiKey,
            'timestamp' => $timestamp,
            'folder' => $folder,
            'signature' => $signature,
        ],
    ]);

    $response = curl_exec($curl);
    $statusCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlError = curl_error($curl);
    curl_close($curl);

    if ($response === false || $curlError !== '') {
        $_SESSION['error'] = "Impossible de contacter Cloudinary : " . $curlError;
        return false;
    }

    $result = json_decode($response, true);
    if ($statusCode < 200 || $statusCode >= 300 || empty($result['secure_url'])) {
        $cloudinaryError = $result['error']['message'] ?? "réponse invalide";
        $_SESSION['error'] = "Cloudinary a refusé l'image : " . $cloudinaryError;
        return false;
    }

    return $result['secure_url'];
}
?>
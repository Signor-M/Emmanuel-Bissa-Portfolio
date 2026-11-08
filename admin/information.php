<?php
session_start();
require_once __DIR__ . "/../includes/config.php";
require_once __DIR__ . "/../includes/functions.php";
require_once __DIR__ . "/very_session.php";

$uploadDir = __DIR__ . "/../assets/img/";

// Charger les informations existantes
try {
    $stmt = $pdo->query("SELECT * FROM informations ORDER BY id ASC LIMIT 1");
    $info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$info) {
        $info = [
            'id' => '',
            'description' => '',
            'profile_image' => '',
            'linkedin_link' => '',
            'github_link' => '',
            'facebook_link' => '',
            'instagram_link' => '',
            'twitter_link' => '',
            'tiktok_link' => '',
            'youtube_link' => '',
            'hire_link' => null
        ];
    }
} catch (Exception $e) {
    die("Erreur :" . $e->getMessage());
}

// Enregistrement des informations
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $description = security($_POST["description"] ?? "");
        $linkedin_link = security($_POST["linkedin_link"] ?? "");
        $github_link = security($_POST["github_link"] ?? "");
        $facebook_link = security($_POST["facebook_link"] ?? "");
        $instagram_link = security($_POST["instagram_link"] ?? "");
        $twitter_link = security($_POST["twitter_link"] ?? "");
        $tiktok_link = security($_POST["tiktok_link"] ?? "");
        $youtube_link = security($_POST["youtube_link"] ?? "");
        $cvLink = $info["hire_link"] ?? "";

        $profileImage = $info["profile_image"] ?? "";

        // Gestion du téléchargement de l'image de profil
        if (isset($_FILES["profile_image"]) && $_FILES["profile_image"]["error"] !== UPLOAD_ERR_NO_FILE) {
            $uploadedName = handleFileUpload($_FILES["profile_image"], $uploadDir);
            if ($uploadedName === false) {
                redirectTo("information.php");
            }
            if (!empty($uploadedName)) {
                $profileImage = $uploadedName;
            }
        }

        if (isset($_FILES["cv_file"]) && $_FILES["cv_file"]["error"] !== UPLOAD_ERR_NO_FILE) {
            $uploadedCv = handleFileUpload($_FILES["cv_file"], null, ['pdf'], 'raw', 'cv');
            if ($uploadedCv === false) {
                redirectTo("information.php");
            }
            if (!empty($uploadedCv)) {
                $cvLink = $uploadedCv;
            }
        }

        if (!empty($info["id"])) {
            // UPDATE
            $sql = "UPDATE informations 
                    SET description = :desc, profile_image = :image, linkedin_link = :linkedin, 
                        github_link = :github, facebook_link = :facebook, instagram_link = :instagram, 
                        twitter_link = :twitter, tiktok_link = :tiktok, youtube_link = :youtube,
                        hire_link = :hire, last_update = NOW()
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                "desc" => $description,
                "image" => $profileImage,
                "linkedin" => $linkedin_link,
                "github" => $github_link,
                "facebook" => $facebook_link,
                "instagram" => $instagram_link,
                "twitter" => $twitter_link,
                "tiktok" => $tiktok_link,
                "youtube" => $youtube_link,
                "hire" => $cvLink,
                "id" => $info["id"]
            ]);
        } else {
            // INSERT
            $sql = "INSERT INTO informations (description, profile_image, linkedin_link, github_link, facebook_link, instagram_link, twitter_link, tiktok_link, youtube_link, hire_link, last_update)
                    VALUES (:desc, :image, :linkedin, :github, :facebook, :instagram, :twitter, :tiktok, :youtube, :hire, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                "desc" => $description,
                "image" => $profileImage,
                "linkedin" => $linkedin_link,
                "github" => $github_link,
                "facebook" => $facebook_link,
                "instagram" => $instagram_link,
                "twitter" => $twitter_link,
                "tiktok" => $tiktok_link,
                "youtube" => $youtube_link,
                "hire" => $cvLink
            ]);
        }

        $_SESSION["success"] = "Informations enregistrées avec succès !";
        redirectTo("information.php");
    } catch (Exception $e) {
        die("Erreur :" . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informations - Admin</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
</head>

<body>

    <?php include "includes/header_and_navbar.php"; ?>

    <main>
        <h2>Informations Générales</h2>

        <?php displayAlerts(); ?>

        <form action="information.php" method="POST" enctype="multipart/form-data" class="admin-form">
            <div class="form-group">
                <label for="description">Description (Bio) :</label>
                <textarea name="description" id="description" rows="5" placeholder="Entrez votre description..."><?php echo htmlspecialchars($info['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="profile_image">Photo de Profil :</label>
                <input type="file" name="profile_image" id="profile_image" accept="image/*">
                <?php if (!empty($info['profile_image'])): ?>
                    <div style="margin-top: 5px;">
                        <?php
                        $profileImagePath = $info['profile_image'];
                        $imgSrc = preg_match('/^(https?:\/\/|data:)/i', $profileImagePath)
                            ? $profileImagePath
                            : '../' . $profileImagePath;
                        ?>
                        <img src="<?php echo htmlspecialchars($imgSrc, ENT_QUOTES, 'UTF-8'); ?>" alt="Photo de profil" style="max-height: 100px; border-radius: 50%; border: 2px solid var(--main-color);">
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="linkedin_link">Lien LinkedIn :</label>
                <input type="url" name="linkedin_link" id="linkedin_link" placeholder="https://linkedin.com/in/..." value="<?php echo htmlspecialchars($info['linkedin_link']); ?>">
            </div>

            <div class="form-group">
                <label for="github_link">Lien GitHub :</label>
                <input type="url" name="github_link" id="github_link" placeholder="https://github.com/..." value="<?php echo htmlspecialchars($info['github_link']); ?>">
            </div>

            <div class="form-group">
                <label for="facebook_link">Lien Facebook :</label>
                <input type="url" name="facebook_link" id="facebook_link" placeholder="https://facebook.com/..." value="<?php echo htmlspecialchars($info['facebook_link']); ?>">
            </div>

            <div class="form-group">
                <label for="instagram_link">Lien Instagram :</label>
                <input type="url" name="instagram_link" id="instagram_link" placeholder="https://instagram.com/..." value="<?php echo htmlspecialchars($info['instagram_link']); ?>">
            </div>

            <div class="form-group">
                <label for="twitter_link">Lien Twitter / X :</label>
                <input type="url" name="twitter_link" id="twitter_link" placeholder="https://twitter.com/..." value="<?php echo htmlspecialchars($info['twitter_link']); ?>">
            </div>

            <div class="form-group">
                <label for="tiktok_link">Lien TikTok :</label>
                <input type="url" name="tiktok_link" id="tiktok_link" placeholder="https://www.tiktok.com/@..." value="<?php echo htmlspecialchars($info['tiktok_link'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="form-group">
                <label for="youtube_link">Lien YouTube :</label>
                <input type="url" name="youtube_link" id="youtube_link" placeholder="https://www.youtube.com/@..." value="<?php echo htmlspecialchars($info['youtube_link'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="form-group">
                <label for="cv_file">CV (format PDF) :</label>
                <input type="file" name="cv_file" id="cv_file" accept="application/pdf,.pdf">
                <?php if (!empty($info['hire_link'])): ?>
                    <p><a href="<?php echo htmlspecialchars($info['hire_link'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">Voir le CV actuel</a></p>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn">Enregistrer les modifications</button>
        </form>
    </main>

</body>

</html>
<?php
session_start();
require_once __DIR__ . "/../includes/config.php";
require_once __DIR__ . "/../includes/functions.php";
require_once __DIR__ . "/very_session.php";

$isEditMode = false;
$editSkill = [
    'id' => '',
    'name' => '',
    'category_id' => '',
    'percentage' => 80,
    'display_order' => 0
];

// Ensure skill_categories table exists
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS skill_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            display_order INT DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $catCheck = $pdo->query("SELECT COUNT(*) FROM skill_categories")->fetchColumn();
    if ($catCheck == 0) {
        $pdo->exec("
            INSERT INTO skill_categories (id, name, display_order) VALUES
            (1, 'Front-End', 1),
            (2, 'Back-End', 2),
            (3, 'Base de données', 3),
            (4, 'Software & Game Dev', 4);
        ");
    }

    $colCheck = false;
    try {
        $colCheck = $pdo->query("SHOW COLUMNS FROM skills LIKE 'category_id'")->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $ex) {
        $colCheck = false;
    }

    if (!$colCheck) {
        $pdo->exec("ALTER TABLE skills ADD COLUMN category_id INT NULL AFTER name;");
        $pdo->exec("
            UPDATE skills SET category_id = 1 WHERE name LIKE '%HTML%' OR name LIKE '%CSS%' OR name LIKE '%BOOTSTRAP%' OR name LIKE '%REACT%' OR name LIKE '%FRONT%';
            UPDATE skills SET category_id = 2 WHERE name LIKE '%PHP%' OR name LIKE '%NODE%' OR name LIKE '%BACK%';
            UPDATE skills SET category_id = 3 WHERE name LIKE '%MYSQL%' OR name LIKE '%SQL%' OR name LIKE '%SQLITE%' OR name LIKE '%BASE%';
            UPDATE skills SET category_id = 4 WHERE name LIKE '%GODOT%' OR name LIKE '%GDEVELOP%' OR name LIKE '%GAME%' OR name LIKE '%SOFTWARE%';
            UPDATE skills SET category_id = 1 WHERE category_id IS NULL OR category_id = 0;
        ");
    }
} catch (Exception $e) {
    // Ignore schema check error if already present
}

// Mode Édition
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editId = (int) $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM skills WHERE id = :id");
        $stmt->execute(['id' => $editId]);
        $fetched = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($fetched) {
            $isEditMode = true;
            $editSkill = $fetched;
        } else {
            $_SESSION["error"] = "Compétence introuvable.";
            redirectTo("skills.php");
        }
    } catch (Exception $e) {
        die("Erreur :" . $e->getMessage());
    }
}

// 1. AJOUT (INSERT)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add"])) {
    try {
        $name = security($_POST["name"] ?? "");
        $category_id = !empty($_POST["category_id"]) ? (int) security($_POST["category_id"]) : null;
        $percentage = isset($_POST["percentage"]) ? (int) security($_POST["percentage"]) : 80;
        $display_order = isset($_POST["display_order"]) ? (int) security($_POST["display_order"]) : 0;

        if ($percentage < 0) $percentage = 0;
        if ($percentage > 100) $percentage = 100;

        if (!empty($name)) {
            $sql = "INSERT INTO skills (name, category_id, percentage, display_order, last_update) VALUES (:name, :category_id, :percentage, :order, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                "name" => $name,
                "category_id" => $category_id,
                "percentage" => $percentage,
                "order" => $display_order
            ]);

            $_SESSION["success"] = "Compétence ajoutée avec succès !";
            redirectTo("skills.php");
        } else {
            $_SESSION["error"] = "Le nom de la compétence ne peut pas être vide.";
            redirectTo("skills.php");
        }
    } catch (Exception $e) {
        die("Erreur :" . $e->getMessage());
    }
}

// 2. MODIFICATION (UPDATE)
else if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update"]) && isset($_POST["id"])) {
    try {
        $id = (int) $_POST["id"];
        $name = security($_POST["name"] ?? "");
        $category_id = !empty($_POST["category_id"]) ? (int) security($_POST["category_id"]) : null;
        $percentage = isset($_POST["percentage"]) ? (int) security($_POST["percentage"]) : 80;
        $display_order = isset($_POST["display_order"]) ? (int) security($_POST["display_order"]) : 0;

        if ($percentage < 0) $percentage = 0;
        if ($percentage > 100) $percentage = 100;

        if (!empty($name)) {
            $sql = "UPDATE skills SET name = :name, category_id = :category_id, percentage = :percentage, display_order = :order, last_update = NOW() WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                "name" => $name,
                "category_id" => $category_id,
                "percentage" => $percentage,
                "order" => $display_order,
                "id" => $id
            ]);

            $_SESSION["success"] = "Compétence mise à jour avec succès !";
            redirectTo("skills.php");
        } else {
            $_SESSION["error"] = "Le nom de la compétence ne peut pas être vide.";
            redirectTo("skills.php?action=edit&id=" . $id);
        }
    } catch (Exception $e) {
        die("Erreur :" . $e->getMessage());
    }
}

// 3. SUPPRESSION (DELETE)
else if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"]) && $_GET["action"] === "delete" && isset($_GET["id"])) {
    try {
        $id = (int) $_GET["id"];
        $sql = "DELETE FROM skills WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["id" => $id]);

        $_SESSION["success"] = "Compétence supprimée avec succès !";
        redirectTo("skills.php");
    } catch (Exception $e) {
        die("Erreur :" . $e->getMessage());
    }
}

// CHARGEMENT DES DONNÉES
try {
    $categories = $pdo->query("SELECT * FROM skill_categories ORDER BY display_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
    $maxOrder = $pdo->query("SELECT IFNULL(MAX(display_order), 0) + 1 FROM skills")->fetchColumn();
    $skills = $pdo->query("
        SELECT s.*, c.name as category_name 
        FROM skills s 
        LEFT JOIN skill_categories c ON s.category_id = c.id 
        ORDER BY IFNULL(c.display_order, 999) ASC, s.display_order ASC, s.id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Erreur :" . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compétences - Admin</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
</head>

<body>

    <?php include "includes/header_and_navbar.php"; ?>

    <main>
        <h2><?php echo $isEditMode ? "Modifier la Compétence" : "Gestion des Compétences"; ?></h2>

        <?php displayAlerts(); ?>

        <form action="skills.php" method="POST" class="admin-form">
            <?php if ($isEditMode): ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($editSkill['id']); ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="name">Nom de la compétence (ex: HTML / CSS, REACT...) :</label>
                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($editSkill['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="category_id">Domaine / Catégorie :</label>
                <select name="category_id" id="category_id" style="width: 100%; padding: 1.2rem; background: var(--bg-dark); color: #fff; border: 1px solid var(--border-color); border-radius: 8px; font-size: 1rem;">
                    <option value="">-- Sélectionner un domaine --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($editSkill['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="percentage">Pourcentage de maîtrise (0 à 100%) :</label>
                <input type="number" name="percentage" id="percentage" min="0" max="100" value="<?php echo htmlspecialchars($editSkill['percentage']); ?>" required>
            </div>

            <div class="form-group">
                <label for="display_order">Ordre d'affichage :</label>
                <input type="number" name="display_order" id="display_order" value="<?php echo $isEditMode ? htmlspecialchars($editSkill['display_order']) : $maxOrder; ?>">
            </div>

            <?php if ($isEditMode): ?>
                <button type="submit" name="update" class="btn">Enregistrer les modifications</button>
                <a href="skills.php" class="btn" style="background-color: #555; margin-left: 10px;">Annuler</a>
            <?php else: ?>
                <button type="submit" name="add" class="btn">Ajouter la compétence</button>
            <?php endif; ?>
        </form>

        <h3>Liste des compétences</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Compétence</th>
                    <th>Domaine</th>
                    <th>Niveau (%)</th>
                    <th>Ordre</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($skills)): ?>
                    <?php foreach ($skills as $skill): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($skill["name"]); ?></td>
                            <td>
                                <span class="badge" style="background: rgba(0,255,238,0.15); color: #00ffee; padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.95rem;">
                                    <?php echo htmlspecialchars($skill["category_name"] ?? 'Non classé'); ?>
                                </span>
                            </td>
                            <td><strong><?php echo htmlspecialchars($skill["percentage"]); ?>%</strong></td>
                            <td><?php echo htmlspecialchars($skill["display_order"]); ?></td>
                            <td>
                                <a href="skills.php?action=edit&id=<?php echo $skill["id"]; ?>" class="btn-edit" title="Modifier"><i class="bx bx-edit"></i></a>
                                <a href="skills.php?action=delete&id=<?php echo $skill["id"]; ?>" class="btn-delete" title="Supprimer"><i class="bx bx-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted);">Aucune compétence enregistrée.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

    <script>
        document.querySelectorAll(".btn-delete").forEach(btn => {
            btn.addEventListener("click", (e) => {
                if (!confirm("Voulez-vous vraiment supprimer cette compétence ?")) {
                    e.preventDefault();
                }
            });
        });
    </script>

</body>

</html>

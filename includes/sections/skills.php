<?php
try {
    // Ensure skill_categories table exists with icon column
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS skill_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            icon VARCHAR(255) DEFAULT 'bx bx-code-alt',
            display_order INT DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Add icon column if not existing
    try {
        $iconCol = $pdo->query("SHOW COLUMNS FROM skill_categories LIKE 'icon'")->fetch(PDO::FETCH_ASSOC);
        if (!$iconCol) {
            $pdo->exec("ALTER TABLE skill_categories ADD COLUMN icon VARCHAR(255) DEFAULT 'bx bx-code-alt' AFTER name;");
        }
    } catch (Exception $ex) {}

    // Insert default categories with icons if empty
    $catCheck = $pdo->query("SELECT COUNT(*) FROM skill_categories")->fetchColumn();
    if ($catCheck == 0) {
        $pdo->exec("
            INSERT INTO skill_categories (id, name, icon, display_order) VALUES
            (1, 'Front-End', 'bx bx-layout', 1),
            (2, 'Back-End', 'bx bx-server', 2),
            (3, 'Base de données', 'bx bx-data', 3),
            (4, 'Software & Game Dev', 'bx bx-joystick', 4);
        ");
    } else {
        // Update default icons if missing
        $pdo->exec("UPDATE skill_categories SET icon = 'bx bx-layout' WHERE name LIKE '%Front%' AND (icon IS NULL OR icon = '' OR icon = 'bx bx-code-alt');");
        $pdo->exec("UPDATE skill_categories SET icon = 'bx bx-server' WHERE name LIKE '%Back%' AND (icon IS NULL OR icon = '' OR icon = 'bx bx-code-alt');");
        $pdo->exec("UPDATE skill_categories SET icon = 'bx bx-data' WHERE (name LIKE '%Base%' OR name LIKE '%SQL%') AND (icon IS NULL OR icon = '' OR icon = 'bx bx-code-alt');");
        $pdo->exec("UPDATE skill_categories SET icon = 'bx bx-joystick' WHERE (name LIKE '%Game%' OR name LIKE '%Software%') AND (icon IS NULL OR icon = '' OR icon = 'bx bx-code-alt');");
    }

    // Check if category_id column exists in skills table
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

    // Query skills grouped by category
    $skillsStmt = $pdo->query("
        SELECT s.*, c.name as category_name, c.icon as category_icon 
        FROM skills s 
        LEFT JOIN skill_categories c ON s.category_id = c.id 
        ORDER BY IFNULL(c.display_order, 999) ASC, c.id ASC, s.display_order ASC, s.id ASC
    ");
    $allSkills = $skillsStmt->fetchAll(PDO::FETCH_ASSOC);

    $skillsByCategory = [];
    $categoryIcons = [];

    foreach ($allSkills as $skill) {
        $catName = !empty($skill['category_name']) ? $skill['category_name'] : 'Autres Compétences';
        
        if (!isset($categoryIcons[$catName])) {
            $categoryIcons[$catName] = getCategoryIconHelper($catName, $skill['category_icon'] ?? '');
        }

        $skillsByCategory[$catName][] = $skill;
    }

} catch (Exception $e) {
    $skillsByCategory = [];
    $categoryIcons = [];
}

function getCategoryIconHelper($name, $dbIcon = '') {
    if (!empty($dbIcon) && $dbIcon !== 'bx bx-code-alt') {
        return $dbIcon;
    }
    $lower = mb_strtolower($name);
    if (strpos($lower, 'front') !== false) return 'bx bx-layout';
    if (strpos($lower, 'back') !== false) return 'bx bx-server';
    if (strpos($lower, 'base') !== false || strpos($lower, 'sql') !== false || strpos($lower, 'donné') !== false) return 'bx bx-data';
    if (strpos($lower, 'game') !== false || strpos($lower, 'soft') !== false || strpos($lower, 'jeu') !== false) return 'bx bx-joystick';
    return 'bx bx-code-alt';
}
?>
<section class="skills" id="skills">
    <h2 class="heading">Compétences</h2>

    <?php if (!empty($skillsByCategory)): ?>
      <div class="skills-categories-grid">
        <?php foreach ($skillsByCategory as $categoryName => $skills): ?>
          <div class="skill-category-card">
            <h3 class="skill-category-title">
              <i class="<?php echo htmlspecialchars($categoryIcons[$categoryName] ?? 'bx bx-code-alt'); ?>"></i>
              <?php echo htmlspecialchars($categoryName); ?>
            </h3>
            <div class="skill-list">
              <?php foreach ($skills as $skill): ?>
                <div class="skill-item">
                  <div class="skill-meta">
                    <span><?php echo htmlspecialchars($skill["name"]); ?></span> 
                    <span><?php echo (int) $skill["percentage"]; ?>%</span>
                  </div>
                  <div class="skill-track">
                    <div class="skill-fill" style="--target: <?php echo (int) $skill["percentage"]; ?>%"></div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p style="text-align: center; width: 100%; color: #ccc; font-size: 1.6rem;">Aucune compétence enregistrée pour le moment.</p>
    <?php endif; ?>
</section>

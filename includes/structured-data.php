<?php
$structuredData = [
    '@context' => 'https://schema.org',
    '@type' => 'Person',
    'name' => 'Emmanuel Bissa',
    'url' => $baseUrl . '/',
    'jobTitle' => 'Développeur Full-Stack & Ingénieur Logiciel',
    'knowsAbout' => [
        'Web Development',
        'Mobile Development',
        'Game Development',
        'IoT',
        'React',
        'Node.js',
        'Godot Engine',
    ],
    'alumniOf' => [
        '@type' => 'CollegeOrUniversity',
        'name' => "Institut Universitaire d'Abidjan (IUA)",
    ],
    'sameAs' => [
        'https://github.com/signor-m',
    ],
];
?>
<script type="application/ld+json">
<?php echo json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
</script>

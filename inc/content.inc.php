<?php
$page = isset($_GET['page']) ? $_GET['page'] : 'modifier';

switch ($page) {
    case 'accueil':
        $content = __DIR__ . '/Dashboard/home.inc.php';
        break;
    case 'modifier':
        $content = __DIR__ . '/Dashboard/object.inc.php';
        break;
    case 'objet':
        $content = __DIR__ . '/Dashboard/displayObject.inc.php';
        break;
    case 'settings':
        $content = __DIR__ . '/Dashboard/settings.inc.php';
        break;
    case 'admin':
        $content = __DIR__ . '/Dashboard/admin.inc.php';
        break;
    default:
        $content = 'Page non trouvée.';
        break;
}
?>

<main class="main-content">
    <?php
    if ($content && file_exists($content)) {
        include $content;
    } else {
        echo "<p>Page non trouvée.</p>";
    }
    ?>
</main>

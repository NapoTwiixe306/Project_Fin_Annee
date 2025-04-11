<?php
$page = isset($_GET['page']) ? $_GET['page'] : 'accueil';

switch ($page) {
    case 'accueil':
        $content = __DIR__ . '/Dashboard/home.inc.php';
        break;
    case 'dashboard':
        $content = 'Bienvenue dans le dashboard.';
        break;
    case 'modifier':
        $content = __DIR__ . '/Dashboard/object.inc.php';
        break;
    case 'settings':
        $content = 'Paramètres.';
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

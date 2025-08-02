<?php
session_start();

// Vérifier l'authentification directement
if (!isset($_SESSION['brocanteur_id'])) {
    header('Location: connexion.php');
    exit();
}

// Récupérer les paramètres de la page
$page = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? 'index';
?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Dashboard - Échos de Violon</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/styles.css">
</head>
<body>
    <?php include 'inc/sidebar.inc.php'; ?>
    
    <main class="main-content">
        <?php
        // Routage simple
        switch ($page) {
            case 'objet':
                include 'inc/Dashboard/object.inc.php';
                break;
            case 'settings':
                include 'inc/Dashboard/settings.inc.php';
                break;
            case 'admin':
                include 'inc/Dashboard/admin.inc.php';
                break;
            case 'home':
            default:
                include 'inc/Dashboard/home.inc.php';
                break;
        }
        ?>
    </main>
</body>
</html>

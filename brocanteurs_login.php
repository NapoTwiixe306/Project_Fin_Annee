<?php
    session_start();
    if (!isset($_SESSION['brocanteur_id'])) {
        header("Location: connexion.php");
        exit();
    }
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="../../css/styles.css">
    <title>Dashboard</title>
</head>
<body>
    <div class="dashboard">
        <?php include './inc/sidebar.inc.php'; ?>

        <?php include './inc/content.inc.php'; ?>
    </div>
</body>
</html>

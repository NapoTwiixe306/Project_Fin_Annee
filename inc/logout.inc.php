<?php
session_start();

// Vider les variables de session
$_SESSION = [];

// Supprimer le cookie de session si utilisé
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600,
        '/Project_Fin_Annee/', $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Détruire la session côté serveur
session_destroy();

// Rediriger vers la page d'accueil
header("Location: ../index.php");
exit();
?>

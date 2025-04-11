<?php
global $pdo;
session_start();
require_once 'bdd.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        die("Email et mot de passe requis.");
    }

    // Récupération de l'utilisateur
    $stmt = $pdo->prepare("SELECT id, password_hash FROM brocanteurs WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['brocanteur_id'] = $user['id'];
        header("Location: brocanteurs_login.php");
        exit();
    } else {
        echo "Identifiants incorrects.";
    }
}
?>

<section>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit">Se connecter</button>
    </form>
    <p>vous n'avez pas de compte ? <a href="inscription.php">Inscrivez vous</a> </p>
</section>

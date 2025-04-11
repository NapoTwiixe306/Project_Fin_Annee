<?php
global $pdo;
require_once 'bdd.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
        die("Tous les champs sont requis.");
    }

    // Vérifie si l'email existe déjà
    $stmt = $pdo->prepare("SELECT id FROM brocanteurs WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        die("Cet email est déjà utilisé.");
    }

    // Hash du mot de passe
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Insérer le brocanteur
    $stmt = $pdo->prepare("INSERT INTO brocanteurs (email, password_hash, nom, prenom) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$email, $passwordHash, $nom, $prenom])) {
        header("Location: connexion.php");
        exit();
    } else {
        echo "Erreur lors de l'inscription.";
    }
}
?>

<section>
    <form method="POST">
        <input type="text" name="nom" placeholder="Nom" required>
        <input type="text" name="prenom" placeholder="Prénom" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit">S'inscrire</button>
    </form>
    <p>vous avez déjà un compte ? <a href="connexion.php">Connectez vous</a></p>
</section>

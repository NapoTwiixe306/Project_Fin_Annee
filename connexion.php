<?php
require_once 'src/autoload.php';
use Controllers\BrocanteurController;

$controller = new BrocanteurController();
$result = $controller->login();

if ($result['success']) {
    header("Location: brocanteurs_login.php");
    exit();
}

$errors = $result['errors'];
?>

<section>
    <?php if (!empty($errors)): ?>
        <div class="errors">
            <?php foreach ($errors as $error): ?>
                <p style="color: red;"><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit">Se connecter</button>
    </form>
    <p>vous n'avez pas de compte ? <a href="inscription.php">Inscrivez vous</a> </p>
</section>

<?php
require_once 'inc/email_config.php';
require_once 'src/autoload.php';
use Controllers\BrocanteurController;

$controller = new BrocanteurController();
$token = $_GET['token'] ?? '';

if (empty($token)) {
    header('Location: connexion.php');
    exit();
}

$result = $controller->resetPassword($token);

$success = $result['success'] ?? false;
$errors = $result['errors'] ?? [];
$tokenValid = $result['token_valid'] ?? false;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau mot de passe - Supra Brocante</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <div class="reset-password-form">
            <h1>Nouveau mot de passe</h1>
            
            <?php if ($success): ?>
                <div class="success-message">
                    <p>Votre mot de passe a été réinitialisé avec succès !</p>
                    <a href="connexion.php" class="btn btn-primary">Se connecter</a>
                </div>
            <?php elseif (!$tokenValid): ?>
                <div class="error-message">
                    <p>Le lien de réinitialisation est invalide ou a expiré.</p>
                    <a href="reset_password.php" class="btn btn-primary">Demander un nouveau lien</a>
                </div>
            <?php else: ?>
                <?php if (!empty($errors)): ?>
                    <div class="errors">
                        <?php foreach ($errors as $error): ?>
                            <p class="error"><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="form">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    
                    <div class="form-group">
                        <label for="password">Nouveau mot de passe</label>
                        <input type="password" id="password" name="password" placeholder="Nouveau mot de passe" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirmer le mot de passe" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Réinitialiser le mot de passe</button>
                </form>
                
                <div class="form-links">
                    <p><a href="connexion.php">Retour à la connexion</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 
<?php
require_once 'inc/email_config.php';
require_once 'src/autoload.php';
use Controllers\BrocanteurController;

$controller = new BrocanteurController();
$result = $controller->requestPasswordReset();

// Protection anti-spam : vérifier le taux de demandes de réinitialisation
$resetAttempts = $_SESSION['reset_attempts'] ?? 0;
$lastResetAttempt = $_SESSION['last_reset_attempt'] ?? 0;
$currentTime = time();
$maxResetAttempts = 3; // Maximum 3 demandes
$resetLockoutTime = 1800; // 30 minutes de blocage

// Vérifier si l'utilisateur est temporairement bloqué
if ($resetAttempts >= $maxResetAttempts && ($currentTime - $lastResetAttempt) < $resetLockoutTime) {
    $remainingTime = $resetLockoutTime - ($currentTime - $lastResetAttempt);
    $result['errors'] = ["Trop de demandes de réinitialisation. Veuillez attendre " . ceil($remainingTime / 60) . " minutes."];
} else {
    // Réinitialiser le compteur si le temps de blocage est écoulé
    if (($currentTime - $lastResetAttempt) >= $resetLockoutTime) {
        $_SESSION['reset_attempts'] = 0;
    }
    
    if ($result['success']) {
        // Incrémenter le compteur de demandes
        $_SESSION['reset_attempts'] = $resetAttempts + 1;
        $_SESSION['last_reset_attempt'] = $currentTime;
    }
}

$success = $result['success'] ?? false;
$errors = $result['errors'] ?? [];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation du mot de passe - Supra Brocante</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <div class="reset-password-form">
            <div class="form-header">
                <h1>Réinitialisation du mot de passe</h1>
                <p class="form-description">
                    Entrez votre adresse email pour recevoir un lien de réinitialisation de votre mot de passe.
                </p>
            </div>
            
            <?php if ($success): ?>
                <div class="success-message">
                    <div class="success-icon">✅</div>
                    <div class="success-content">
                        <h3>Email envoyé avec succès !</h3>
                        <p>Un email de réinitialisation a été envoyé à votre adresse email.</p>
                        <p>Vérifiez votre boîte de réception et suivez les instructions dans l'email.</p>
                        <div class="success-actions">
                            <a href="connexion.php" class="btn btn-primary">Retour à la connexion</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php if (!empty($errors)): ?>
                    <div class="error-messages">
                        <div class="error-icon">❌</div>
                        <div class="error-content">
                            <h3>Erreur de réinitialisation</h3>
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="reset-form">
                    <!-- Champ honeypot caché pour la protection anti-spam -->
                    <div style="display: none;">
                        <input type="text" name="website" tabindex="-1" autocomplete="off">
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Adresse email</label>
                        <div class="input-wrapper">
                            <span class="input-icon">📧</span>
                            <input type="email" id="email" name="email" class="form-input email-input" placeholder="Votre adresse email" required>
                        </div>
                        <small class="form-help">
                            Nous vous enverrons un lien sécurisé pour réinitialiser votre mot de passe.
                        </small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-large">
                        <span class="btn-icon">🔐</span>
                        Envoyer le lien de réinitialisation
                    </button>
                </form>
                
                <div class="form-links">
                    <div class="link-group">
                        <a href="connexion.php" class="link-item">
                            <span class="link-icon">←</span>
                            Retour à la connexion
                        </a>
                    </div>
                    <div class="link-group">
                        <p class="link-text">Vous n'avez pas de compte ?</p>
                        <a href="inscription.php" class="link-item">
                            <span class="link-icon">📝</span>
                            Inscrivez-vous
                        </a>
                    </div>
                </div>
                
                <div class="security-info">
                    <h4>🔒 Sécurité</h4>
                    <ul>
                        <li>Le lien de réinitialisation expire dans 1 heure</li>
                        <li>Votre email ne sera pas partagé avec des tiers</li>
                        <li>Vous pouvez demander un nouveau lien si nécessaire</li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 
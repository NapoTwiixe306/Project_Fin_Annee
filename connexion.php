<?php
session_start();

// Configuration de base de données
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/bdd.php';

$errors = [];

// Protection anti-spam : vérifier le taux de tentatives de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Protection anti-spam : vérifier le champ honeypot
    if (!empty($_POST['website'])) {
        // Si le champ honeypot est rempli, c'est probablement un bot
        $errors[] = "Erreur de validation. Veuillez réessayer.";
    } else {
        $loginAttempts = $_SESSION['login_attempts'] ?? 0;
        $lastAttempt = $_SESSION['last_login_attempt'] ?? 0;
        $currentTime = time();
        $maxAttempts = 5; // Maximum 5 tentatives
        $lockoutTime = 900; // 15 minutes de blocage

        // Vérifier si l'utilisateur est temporairement bloqué
        if ($loginAttempts >= $maxAttempts && ($currentTime - $lastAttempt) < $lockoutTime) {
            $remainingTime = $lockoutTime - ($currentTime - $lastAttempt);
            $errors[] = "Trop de tentatives de connexion. Veuillez attendre " . ceil($remainingTime / 60) . " minutes.";
        } else {
            // Réinitialiser le compteur si le temps de blocage est écoulé
            if (($currentTime - $lastAttempt) >= $lockoutTime) {
                $_SESSION['login_attempts'] = 0;
            }
            
            // Logique de connexion simplifiée
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if (empty($email) || empty($password)) {
                $errors[] = "Email et mot de passe requis.";
            } else {
                // Vérifier les identifiants dans la base de données
                $stmt = $pdo->prepare("SELECT id, email, password_hash, nom, prenom FROM brocanteurs WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($password, $user['password_hash'])) {
                    // Connexion réussie
                    $_SESSION['brocanteur_id'] = $user['id'];
                    $_SESSION['login_attempts'] = 0;
                    header("Location: admin_dashboard.php");
                    exit();
                } else {
                    // Échec de connexion
                    $_SESSION['login_attempts'] = $loginAttempts + 1;
                    $_SESSION['last_login_attempt'] = $currentTime;
                    $errors[] = "Identifiants incorrects.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Supra Brocante</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="login-page">
        <div class="login-container">
            <div class="login-header">
                <h1 class="login-title">Connexion</h1>
                <p class="login-subtitle">Accédez à votre compte Supra Brocante</p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="login-errors">
                    <?php foreach ($errors as $error): ?>
                        <div class="error-message"><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
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
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Mot de passe</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input type="password" id="password" name="password" class="form-input password-input" placeholder="Votre mot de passe" required>
                    </div>
                </div>
                
                <button type="submit" class="login-button">
                    Se connecter
                </button>
            </form>
            
            <div class="login-links">
                <a href="reset_password.php" class="login-link forgot-password">
                    Mot de passe oublié ?
                </a>
                
                <div class="login-divider">
                    <span class="divider-text">ou</span>
                </div>
                
                <a href="inscription.php" class="login-link register-link">
                    Créer un compte
                </a>
            </div>
        </div>
    </div>


</body>
</html>

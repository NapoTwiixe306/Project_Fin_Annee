<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and get their email
$userEmail = '';
if (isset($_SESSION['brocanteur_id'])) {
    require_once 'bdd.php';
    $stmt = $pdo->prepare("SELECT email FROM brocanteurs WHERE id = ?");
    $stmt->execute([$_SESSION['brocanteur_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $userEmail = $user['email'];
    }
}

$errors = [];
$success = false;
$formData = [
    'nom' => '',
    'prenom' => '',
    'email' => $userEmail,
    'sujet' => '',
    'message' => ''
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Protection anti-spam : vérifier le champ honeypot
    if (!empty($_POST['website'])) {
        // Si le champ honeypot est rempli, c'est probablement un bot
        $errors[] = "Erreur de validation. Veuillez réessayer.";
    } else {
        // Protection anti-spam : vérifier le temps minimum entre soumissions
        $lastSubmission = $_SESSION['last_contact_submission'] ?? 0;
        $currentTime = time();
        $minInterval = 30; // 30 secondes minimum entre soumissions
        
        if ($currentTime - $lastSubmission < $minInterval) {
            $errors[] = "Veuillez attendre quelques secondes avant de renvoyer un message.";
        } else {
            // Récupérer l'email de l'admin depuis la base de données
            require_once 'bdd.php';
            $stmt = $pdo->prepare("SELECT email FROM brocanteurs WHERE role = 'admin' LIMIT 1");
            $stmt->execute();
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            $adminEmail = $admin ? $admin['email'] : "j.milants@student.helmo.be"; // Fallback
            
            $nom = trim($_POST["nom"] ?? '');
            $prenom = trim($_POST["prenom"] ?? '');
            $email = trim($_POST["email"] ?? '');
            $sujet = trim($_POST["sujet"] ?? '');
            $message = trim($_POST["message"] ?? '');

            // Preserve form data
            $formData = [
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'sujet' => $sujet,
                'message' => $message
            ];

            // Validation
            if (empty($nom)) {
                $errors[] = "Le nom est obligatoire.";
            }
            if (empty($prenom)) {
                $errors[] = "Le prénom est obligatoire.";
            }
            if (empty($email)) {
                $errors[] = "L'adresse email est obligatoire.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Format d'adresse email invalide.";
            }
            if (empty($sujet)) {
                $errors[] = "Le sujet est obligatoire.";
            }
            if (empty($message)) {
                $errors[] = "Le message est obligatoire.";
            }

            // If no errors, send email
            if (empty($errors)) {
                $emailSubject = "[Supra Brocante] $sujet";
                $emailContent = "Message reçu de : $prenom $nom <$email>\n\n";
                $emailContent .= "Sujet: $sujet\n\n";
                $emailContent .= "Message:\n$message";

                // Headers for admin email
                $headers = "From: $email\r\n";
                $headers .= "Reply-To: $email\r\n";
                $headers .= "Cc: $email\r\n"; // Copy to sender
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

                // Send email (will work on production server)
                if (mail($adminEmail, $emailSubject, $emailContent, $headers)) {
                    $success = true;
                    // Enregistrer le temps de soumission pour la protection anti-spam
                    $_SESSION['last_contact_submission'] = $currentTime;
                    $formData = [ // Reset form on success
                        'nom' => '',
                        'prenom' => '',
                        'email' => $userEmail,
                        'sujet' => '',
                        'message' => ''
                    ];
                } else {
                    $errors[] = "Erreur lors de l'envoi du message. Veuillez réessayer plus tard.";
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
    <link rel="stylesheet" href="./css/styles.css">
    <title>Contact</title>
</head>
<body>
    <header>
        <?php include './inc/navbar.inc.php'; ?>
    </header>
    <main class="main">
        <h1>Contactez-nous</h1>
        <section class="contact">
            <article class="red">
                <h2>Contactez-nous</h2>
                <p>Envoyez-nous un message et nous vous répondrons dans les plus brefs délais.</p>

                <?php if ($success): ?>
                    <div class="success-message">
                        <p style="color: green; font-weight: bold;">Votre message a été envoyé avec succès ! Vous recevrez une copie à votre adresse email.</p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="error-messages">
                        <?php foreach ($errors as $error): ?>
                            <p style="color: red; font-weight: bold;"><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"])?>" class="column">
                    <!-- Champ honeypot caché pour la protection anti-spam -->
                    <div style="display: none;">
                        <input type="text" name="website" tabindex="-1" autocomplete="off">
                    </div>
                    
                    <label for="nom">Nom * :</label>
                    <input type="text" name="nom" id="nom" value="<?= htmlspecialchars($formData['nom']) ?>" required>

                    <label for="prenom">Prénom * :</label>
                    <input type="text" name="prenom" id="prenom" value="<?= htmlspecialchars($formData['prenom']) ?>" required>

                    <label for="email">Adresse email * :</label>
                    <input type="email" name="email" id="email" value="<?= htmlspecialchars($formData['email']) ?>" required>

                    <label for="sujet">Sujet * :</label>
                    <input type="text" name="sujet" id="sujet" value="<?= htmlspecialchars($formData['sujet']) ?>" required>

                    <label for="message">Message * :</label>
                    <textarea name="message" id="message" rows="6" required><?= htmlspecialchars($formData['message']) ?></textarea>

                    <button type="submit">Envoyer</button>
                </form>
            </article>
        </section>
    </main>
</body>
</html>

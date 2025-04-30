<?php
$messageEnvoye = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars(trim($_POST["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $sujet = htmlspecialchars(trim($_POST["sujet"]));
    $message = htmlspecialchars(trim($_POST["message"]));

    if (!empty($name) && !empty($email) && !empty($sujet) && !empty($message) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        
        $to = "j.milants@student.helmo.be";
        
        $subject = "Nouveau message de contact : $sujet";

        $body = "Nom : $name\n";
        $body .= "Email : $email\n\n";
        $body .= "Message :\n$message";

        // Headers
        $headers = "From: $name <$email>" . "\r\n" .
                   "Reply-To: $email" . "\r\n" .
                   "Content-Type: text/plain; charset=utf-8";

        // Envoi
        if (mail($to, $subject, $body, $headers)) {
            $messageEnvoye = "Message envoyé avec succès. Merci de nous avoir contactés !";
        } else {
            $messageEnvoye = "❌ Une erreur est survenue lors de l'envoi du message.";
        }

    } else {
        $messageEnvoye = "❌ Veuillez remplir tous les champs correctement.";
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

                <?php if ($messageEnvoye): ?>
                    <p><?= $messageEnvoye ?></p>
                <?php endif; ?>

                <form action="" method="POST">
                    <label for="name">Nom :</label>
                    <input type="text" name="name" id="name" placeholder="Entrez votre Nom..." required>

                    <label for="email">Email :</label>
                    <input type="email" name="email" id="email" placeholder="Entrez votre Email..." required>

                    <label for="sujet">Sujet :</label>
                    <input type="text" name="sujet" id="sujet" placeholder="Entrez votre Sujet..." required>

                    <label for="message">Message :</label>
                    <textarea name="message" id="message" placeholder="Entrez votre Message..." required></textarea>

                    <article>
                        <button type="reset" class="cancel">Annuler</button>
                        <button type="submit" class="send">Envoyer</button>
                    </article>
                </form>
            </article>
        </section>
    </main>
</body>
</html>

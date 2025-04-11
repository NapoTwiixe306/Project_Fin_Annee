<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/styles.css">
    <title>Document</title>
</head>
<body>
    <header>
        <?php include './inc/navbar.inc.php'; ?>
    </header>
    <main class="main">
        <h1>Contacez nous</h1>
        <section class="contact">
            <article class="red">
                <h2>Contactez-nous</h2>
                <p>Envoyez-nous un message et nous vous répondrons dans les plus brefs délais.</p>
                <form action="" methode="POST">
                    <label for="name">Nom : </label>
                    <input type="text" name="name" id="name" placeholder="Entrez votre Nom...">
                    <label for="email">Email : </label>
                    <input type="mail" name="email" id="mail" placeholder="Entrez votre Email...">
                    <label for="sujet">Sujet : </label>
                    <input type="text" name="sujet" id="subject" placeholder="Entrez votre Sujet...">
                    <label for="message">Message : </label>
                    <textarea name="message" id="msg" placeholder="Entrez votre Message..."></textarea>
                    <article>
                        <button type="cancel" class="cancel">Annuler</button>
                        <button type="submit" class="send">Envoyer</button>
                    </article>
                </form>
            </article>
    </main>
</body>
</html>
<?php
include 'bdd.php'; // Inclure la connexion à la base de données

// Récupérer les données des brocanteurs
$sql = "SELECT b.id, b.nom, b.prenom, e.numero AS emplacement, e.zone, b.description
        FROM brocanteurs b
        JOIN emplacements e ON b.emplacement_id = e.id";

$stmt = $pdo->query($sql);
$brocanteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="./css/styles.css">
    <title>FAP - Brocanteurs</title>
</head>
<body>
    <header>
        <?php include './inc/navbar.inc.php'; ?>
    </header>
    <main>
        <section class="brocanteurs">
            <h2>Brocanteurs</h2>
            <section class="form">
                <form>
                    <input type="text" name="search" placeholder="Chercher un brocanteur...">
                    <select name="search" id="filter">
                        <option value="name">Nom</option>
                        <option value="Emplacement">Emplacement</option>
                        <option value="Type de stand">Stand</option>
                    </select>
                    <select name="filter" id="filter">
                        <option value="">Choisissez parmi les zones</option>
                        <option value="A">Zone A</option>
                        <option value="B">Zone B</option>
                        <option value="C">Zone C</option>
                        <option value="D">Zone D</option>
                        <option value="E">Zone E</option>
                    </select>
                </form>
            </section>
            <section class="card_brocanteurs">
                <?php foreach ($brocanteurs as $brocanteur): ?>
                <article class="card">
                    <section class="infos">
                        <img src="https://avatar.iran.liara.run/public/12" alt="" width="50" height="50"/>
                        <div class="text">
                            <p class="title"><?= htmlspecialchars($brocanteur['prenom'] . ' ' . $brocanteur['nom']); ?></p>
                            <p><?= htmlspecialchars($brocanteur['emplacement']); ?></p>
                        </div>
                    </section>
                    <section class="badge">
                        <span class="emplacement">Emplacement <?= htmlspecialchars($brocanteur['emplacement']); ?></span>
                        <span class="zone">Zone <?= htmlspecialchars($brocanteur['zone']); ?></span>
                    </section>
                    <section class="description">
                        <p><?= htmlspecialchars($brocanteur['description']); ?></p>
                    </section>
                    <section class="button">
                        <a href="details.php?id=<?= $brocanteur['id']; ?>" class="details-button">Afficher les détails</a>
                    </section>
                </article>
                <?php endforeach; ?>
            </section>
        </section>
    </main>
</body>
</html>

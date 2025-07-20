<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            <form method="get">
                <input type="text" name="search" placeholder="Chercher un brocanteur..." value="<?= htmlspecialchars($data['filters']['search']) ?>">
                <select name="filter_by" id="filter">
                    <option value="name" <?= $data['filters']['filter_by'] === 'name' ? 'selected' : '' ?>>Nom</option>
                    <option value="emplacement" <?= $data['filters']['filter_by'] === 'emplacement' ? 'selected' : '' ?>>Emplacement</option>
                </select>
                <select name="zone" id="zone">
                    <option value="">Choisissez parmi les zones</option>
                    <?php foreach ($data['zones'] as $zone): ?>
                        <option value="<?= $zone ?>" <?= $data['filters']['zone'] === $zone ? 'selected' : '' ?>>Zone <?= $zone ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Rechercher</button>
            </form>
        </section>
        <section class="card_brocanteurs">
            <?php foreach ($data['brocanteurs'] as $brocanteur): ?>
                <article class="card">
                    <section class="infos">
                        <?php
                        $photo = !empty($brocanteur['photo_profil']) && file_exists('uploads/' . $brocanteur['photo_profil'])
                            ? 'uploads/' . $brocanteur['photo_profil']
                            : 'https://avatar.iran.liara.run/public/12';
                        ?>
                        <img src="<?= htmlspecialchars($photo) ?>" alt="photo de profil" width="50" height="50"/>
                        <div class="text">
                            <p class="title"><?= htmlspecialchars(($brocanteur['prenom'] ?? '') . ' ' . ($brocanteur['nom'] ?? '')) ?></p>
                            <p><?= htmlspecialchars($brocanteur['emplacement'] ?? '') ?></p>
                        </div>
                    </section>
                    <section class="badge">
                        <span class="emplacement">Emplacement <?= htmlspecialchars($brocanteur['emplacement'] ?? '') ?></span>
                        <span class="zone">Zone <?= htmlspecialchars($brocanteur['zone'] ?? '') ?></span>
                    </section>
                    <section class="description">
                        <p><?= htmlspecialchars($brocanteur['description'] ?? '') ?></p>
                    </section>
                    <section class="button">
                        <a href="details.php?id=<?= $brocanteur['id'] ?>" class="details-button">Afficher les détails</a>
                    </section>
                </article>
            <?php endforeach; ?>
        </section>
    </section>
</main>
</body>
</html>

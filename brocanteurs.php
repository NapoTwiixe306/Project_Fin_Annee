<?php
require_once 'inc/config.php';
require_once 'bdd.php';

// Récupérer les filtres
$search = $_GET['search'] ?? '';
$filter_by = $_GET['filter_by'] ?? 'name';
$zone = $_GET['zone'] ?? '';

// Construire la requête
$sql = "SELECT b.*, e.numero as emplacement, e.zone 
        FROM brocanteurs b 
        LEFT JOIN emplacements e ON b.emplacement_id = e.id 
        WHERE b.visible = TRUE";

$params = [];

if (!empty($search)) {
    if ($filter_by === 'name') {
        $sql .= " AND (b.nom LIKE ? OR b.prenom LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    } elseif ($filter_by === 'emplacement') {
        $sql .= " AND e.numero LIKE ?";
        $params[] = "%$search%";
    }
}

if (!empty($zone)) {
    $sql .= " AND e.zone = ?";
    $params[] = $zone;
}

$sql .= " ORDER BY b.nom, b.prenom";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$brocanteurs = $stmt->fetchAll();

// Récupérer les zones pour le filtre
$stmt = $pdo->query("SELECT DISTINCT e.zone FROM emplacements e 
INNER JOIN brocanteurs b ON e.id = b.emplacement_id 
WHERE b.visible = TRUE AND e.zone IS NOT NULL 
ORDER BY e.zone");
$zones = $stmt->fetchAll(PDO::FETCH_COLUMN);

$data = [
    'brocanteurs' => $brocanteurs,
    'zones' => $zones,
    'filters' => [
        'search' => $search,
        'filter_by' => $filter_by,
        'zone' => $zone
    ]
];
?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
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
                        <?php if (!empty($brocanteur['zone'])): ?>
                            <span class="zone">Zone <?= htmlspecialchars($brocanteur['zone']) ?></span>
                        <?php endif; ?>
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

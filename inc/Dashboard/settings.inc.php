<?php
require_once __DIR__ . '/../config.php';
require_once 'bdd.php';

if (!isset($_SESSION['brocanteur_id'])) {
    header('Location: connexion.php');
    exit();
}

$id = $_SESSION['brocanteur_id'];
$error = $success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $description = trim($_POST['description']);
    $visibilite = isset($_POST['visibilite']) && $_POST['visibilite'] === 'on' ? 1 : 0;
    $password_hash = $_POST['password_hash'];
    $password_hash_confirm = $_POST['password_hash_confirm'];

    if (empty($nom) || empty($prenom) || empty($email)) {
        $error = "Nom, prénom et email sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide.";
    } elseif ($password_hash && $password_hash !== $password_hash_confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        $query = "UPDATE brocanteurs SET nom = ?, prenom = ?, email = ?, description = ?, visibilite = ?";
        $params = [$nom, $prenom, $email, $description, $visibilite];

        if (!empty($password_hash)) {
            $query .= ", password_hash = ?";
            $params[] = password_hash($password_hash, PASSWORD_DEFAULT);
        }

        $query .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $pdo->prepare($query);
        if ($stmt->execute($params)) {
            $success = "Profil mis à jour avec succès.";
        } else {
            $error = "Erreur lors de la mise à jour.";
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM brocanteurs WHERE id = ?");
$stmt->execute([$id]);
$brocanteur = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Éditer mon profil</title>
</head>
<body>
<h1>Modifier mon profil</h1>

<?php if ($error): ?><p style="color:red;"><?= htmlspecialchars($error ?? '') ?></p><?php endif; ?>
<?php if ($success): ?><p style="color:green;"><?= htmlspecialchars($success ?? '') ?></p><?php endif; ?>

<form method="post">
    <label>Nom : <input type="text" name="nom" value="<?= htmlspecialchars($brocanteur['nom'] ?? '') ?>" required></label><br>
    <label>Prénom : <input type="text" name="prenom" value="<?= htmlspecialchars($brocanteur['prenom'] ?? '') ?>" required></label><br>
    <label>Email : <input type="email" name="email" value="<?= htmlspecialchars($brocanteur['email'] ?? '') ?>" required></label><br>
    <label>Description : <textarea name="description"><?= htmlspecialchars($brocanteur['description'] ?? '') ?></textarea></label><br>
    <label>Visibilité : 
        <input type="checkbox" name="visibilite" <?= isset($brocanteur['visibilite']) && $brocanteur['visibilite'] ? 'checked' : '' ?>>
    </label><br>

    <label>Nouveau mot de passe : <input type="password" name="password_hash"></label><br>
    <label>Confirmer mot de passe : <input type="password" name="password_hash_confirm"></label><br>
    <button type="submit">Mettre à jour</button>
</form>

</body>
</html>

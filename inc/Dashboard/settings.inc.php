<?php
require_once __DIR__ . '/../config.php';
require_once 'bdd.php';

if (!isset($_SESSION['brocanteur_id'])) {
    header('Location: connexion.php');
    exit();
}

$id = $_SESSION['brocanteur_id'];
$error = $success = "";

// Récupération des infos actuelles du brocanteur
$stmt = $pdo->prepare("SELECT * FROM brocanteurs WHERE id = ?");
$stmt->execute([$id]);
$brocanteur = $stmt->fetch(PDO::FETCH_ASSOC);

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $description = $_POST['description'];
    $visibilite = isset($_POST['visibilite']) ? 1 : 0;
    $password = $_POST['password'];

    $password_hash = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;

    // Gestion de la photo de profil
    $photo_filename = $brocanteur['photo_profil'] ?? null;

    if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['photo_profil']['tmp_name'];
        $fileName = basename($_FILES['photo_profil']['name']);
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = uniqid('photo_', true) . '.' . $fileExtension;
            $uploadDir = __DIR__ . '/../../uploads/';
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $photo_filename = $newFileName;
            } else {
                $error = "Erreur lors de l'enregistrement de l'image.";
            }
        } else {
            $error = "Format d'image non autorisé.";
        }
    }

    if (!$error) {
        $query = "UPDATE brocanteurs SET nom = ?, prenom = ?, email = ?, description = ?, visibilite = ?, photo_profil = ?";
        $params = [$nom, $prenom, $email, $description, $visibilite, $photo_filename];

        if ($password_hash) {
            $query .= ", password_hash = ?";
            $params[] = $password_hash;
        }

        $query .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $pdo->prepare($query);
        if ($stmt->execute($params)) {
            $success = "Profil mis à jour avec succès.";
            header("Location: settings.php");
            exit();
        } else {
            $error = "Erreur lors de la mise à jour.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paramètres</title>
    <link rel="stylesheet" href="../styles/settings.css">
</head>
<body>
    <h1>Paramètres de votre compte</h1>
    <?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
    <?php if ($success) echo "<p style='color:green;'>$success</p>"; ?>
    <form method="post" enctype="multipart/form-data">
        <label>Nom : <input type="text" name="nom" value="<?= htmlspecialchars($brocanteur['nom']) ?>" required></label><br>
        <label>Prénom : <input type="text" name="prenom" value="<?= htmlspecialchars($brocanteur['prenom']) ?>" required></label><br>
        <label>Email : <input type="email" name="email" value="<?= htmlspecialchars($brocanteur['email']) ?>" required></label><br>
        <label>Mot de passe : <input type="password" name="password" placeholder="Laisser vide pour ne pas changer"></label><br>
        <label>Description : <textarea name="description"><?= htmlspecialchars($brocanteur['description']) ?></textarea></label><br>
        <label>Visibilité : <input type="checkbox" name="visibilite" <?= $brocanteur['visibilite'] ? 'checked' : '' ?>></label><br>
        <label>Photo de profil : <input type="file" name="photo_profil" accept="image/*"></label><br>
        <?php if (!empty($brocanteur['photo_profil'])): ?>
            <img src="../../uploads/<?= htmlspecialchars($brocanteur['photo_profil']) ?>" alt="Photo de profil" style="width:80px;height:80px;border-radius:50%;"><br>
        <?php endif; ?>
        <button type="submit">Enregistrer</button>
    </form>
</body>
</html>

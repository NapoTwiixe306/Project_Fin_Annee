<?php
require_once __DIR__ . '/../../src/autoload.php';
use Controllers\BrocanteurController;

$controller = new BrocanteurController();
$controller->requireAuth();

$user = $controller->getCurrentUser();

$errors = [];
$success = false;
$passwordErrors = [];
$passwordSuccess = false;

// Preserve form data
$formData = [
    'nom' => $user['nom'] ?? '',
    'prenom' => $user['prenom'] ?? '',
    'email' => $user['email'] ?? '',
    'description' => $user['description'] ?? '',
    'visibilite' => $user['visibilite'] ?? 0
];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $visibilite = isset($_POST['visibilite']) ? 1 : 0;

    // Preserve form data
    $formData = [
        'nom' => $nom,
        'prenom' => $prenom,
        'email' => $email,
        'description' => $description,
        'visibilite' => $visibilite
    ];

    // Validation
    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire.";
    }
    if (empty($prenom)) {
        $errors[] = "Le prénom est obligatoire.";
    }
    if (empty($email)) {
        $errors[] = "L'email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide.";
    }

    // Check email uniqueness
    require_once __DIR__ . '/../../bdd.php';
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM brocanteurs WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user['id']]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Cet email est déjà utilisé par un autre utilisateur.";
    }

    // Handle photo upload
    $photo_filename = $user['photo_profil'] ?? null;
    if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['photo_profil']['tmp_name'];
        $fileName = basename($_FILES['photo_profil']['name']);
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = uniqid('photo_', true) . '.' . $fileExtension;
            $uploadDir = __DIR__ . '/../../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $photo_filename = $newFileName;
            } else {
                $errors[] = "Erreur lors de l'enregistrement de l'image.";
            }
        } else {
            $errors[] = "Format d'image non autorisé.";
        }
    }

    // Save if no errors
    if (empty($errors)) {
        $query = "UPDATE brocanteurs SET nom = ?, prenom = ?, email = ?, description = ?, visibilite = ?, photo_profil = ? WHERE id = ?";
        $params = [$nom, $prenom, $email, $description, $visibilite, $photo_filename, $user['id']];

        $stmt = $pdo->prepare($query);
        if ($stmt->execute($params)) {
            $success = true;
            // Refresh user data
            $user = $controller->getCurrentUser();
        } else {
            $errors[] = "Erreur lors de la mise à jour.";
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($current_password)) {
        $passwordErrors[] = "Le mot de passe actuel est obligatoire.";
    }
    if (empty($new_password)) {
        $passwordErrors[] = "Le nouveau mot de passe est obligatoire.";
    } elseif (strlen($new_password) < 8) {
        $passwordErrors[] = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
    }
    if ($new_password !== $confirm_password) {
        $passwordErrors[] = "Les mots de passe ne correspondent pas.";
    }

    // Verify current password
    if (empty($passwordErrors) && !password_verify($current_password, $user['password_hash'])) {
        $passwordErrors[] = "Le mot de passe actuel est incorrect.";
    }

    // Update password if no errors
    if (empty($passwordErrors)) {
        require_once __DIR__ . '/../../bdd.php';
        $stmt = $pdo->prepare("UPDATE brocanteurs SET password_hash = ? WHERE id = ?");
        if ($stmt->execute([password_hash($new_password, PASSWORD_DEFAULT), $user['id']])) {
            $passwordSuccess = true;
        } else {
            $passwordErrors[] = "Erreur lors de la mise à jour du mot de passe.";
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
    
    <!-- Profile Update Section -->
    <section class="profile-section">
        <h2>Informations personnelles</h2>
        
        <?php if ($success): ?>
            <p style="color: green; font-weight: bold;">Profil mis à jour avec succès !</p>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="error-messages">
                <?php foreach ($errors as $error): ?>
                    <p style="color: red; font-weight: bold;"><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_profile">
            
            <div class="form-group">
                <label for="nom">Nom *</label>
                <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($formData['nom']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="prenom">Prénom *</label>
                <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($formData['prenom']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($formData['email']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4"><?= htmlspecialchars($formData['description']) ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="visibilite">
                    <input type="checkbox" id="visibilite" name="visibilite" <?= $formData['visibilite'] ? 'checked' : '' ?>>
                    Être visible en ligne
                </label>
            </div>
            
            <div class="form-group">
                <label for="photo_profil">Photo de profil</label>
                <input type="file" id="photo_profil" name="photo_profil" accept="image/*">
                <?php if (!empty($user['photo_profil'])): ?>
                    <div class="current-photo">
                        <img src="../../uploads/<?= htmlspecialchars($user['photo_profil']) ?>" alt="Photo actuelle" style="width:80px;height:80px;border-radius:50%;">
                        <p>Photo actuelle</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <button type="submit">Mettre à jour le profil</button>
        </form>
    </section>
    
    <!-- Password Change Section -->
    <section class="password-section">
        <h2>Changer le mot de passe</h2>
        
        <?php if ($passwordSuccess): ?>
            <p style="color: green; font-weight: bold;">Mot de passe mis à jour avec succès !</p>
        <?php endif; ?>
        
        <?php if (!empty($passwordErrors)): ?>
            <div class="error-messages">
                <?php foreach ($passwordErrors as $error): ?>
                    <p style="color: red; font-weight: bold;"><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="post">
            <input type="hidden" name="action" value="change_password">
            
            <div class="form-group">
                <label for="current_password">Mot de passe actuel *</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            
            <div class="form-group">
                <label for="new_password">Nouveau mot de passe *</label>
                <input type="password" id="new_password" name="new_password" required>
                <small>Au moins 8 caractères</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirmer le nouveau mot de passe *</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit">Changer le mot de passe</button>
        </form>
    </section>
</body>
</html>

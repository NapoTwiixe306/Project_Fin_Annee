<?php
require_once __DIR__ . '/../../src/autoload.php';

use Controllers\DashboardController;

// Vérifier l'authentification
if (!isset($_SESSION['brocanteur_id'])) {
    header('Location: connexion.php');
    exit();
}

$controller = new DashboardController();
$user = $controller->getCurrentUserInfo();

if (!$user) {
    header('Location: connexion.php');
    exit();
}

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
    'visibilite' => $user['visible'] ?? 0
];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $updateData = [
        'nom' => trim($_POST['nom'] ?? ''),
        'prenom' => trim($_POST['prenom'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'visibilite' => isset($_POST['visibilite']) ? 1 : 0
    ];

    // Handle photo upload
    if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = uniqid('photo_', true) . '.' . pathinfo($_FILES['photo_profil']['name'], PATHINFO_EXTENSION);
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['photo_profil']['tmp_name'], $targetPath)) {
            $updateData['photo_profil'] = $fileName;
        }
    }

    $result = $controller->updateProfile($updateData);
    $success = $result['success'];
    $errors = $result['errors'];

    if ($success) {
        // Refresh user data
        $user = $controller->getCurrentUserInfo();
        $formData = [
            'nom' => $user['nom'] ?? '',
            'prenom' => $user['prenom'] ?? '',
            'email' => $user['email'] ?? '',
            'description' => $user['description'] ?? '',
            'visibilite' => $user['visible'] ?? 0
        ];
    } else {
        // Preserve form data on error
        $formData = $updateData;
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $passwordData = [
        'current_password' => $_POST['current_password'] ?? '',
        'new_password' => $_POST['new_password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? ''
    ];

    $result = $controller->changePassword($passwordData);
    $passwordSuccess = $result['success'];
    $passwordErrors = $result['errors'];
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
            <div class="success-message" style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                <strong>✅ Succès !</strong> Votre profil a été mis à jour avec succès.
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="error-messages" style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                <strong>❌ Erreurs de validation :</strong>
                <ul style="margin: 10px 0 0 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
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
            <div class="success-message" style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                <strong>✅ Succès !</strong> Votre mot de passe a été mis à jour avec succès.
            </div>
        <?php endif; ?>
        
        <?php if (!empty($passwordErrors)): ?>
            <div class="error-messages" style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                <strong>❌ Erreurs de validation :</strong>
                <ul style="margin: 10px 0 0 20px;">
                    <?php foreach ($passwordErrors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
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
                <small>Au moins 8 caractères, incluant une minuscule, une majuscule et un chiffre</small>
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
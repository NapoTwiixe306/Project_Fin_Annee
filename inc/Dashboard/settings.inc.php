<?php
// Vérifier l'authentification
if (!isset($_SESSION['brocanteur_id'])) {
    header('Location: connexion.php');
    exit();
}

// Récupérer les informations de l'utilisateur
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../../bdd.php';

$brocanteur_id = $_SESSION['brocanteur_id'];
$stmt = $pdo->prepare("SELECT * FROM brocanteurs WHERE id = ?");
$stmt->execute([$brocanteur_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: connexion.php');
    exit();
}

$errors = [];
$success = false;
$passwordErrors = [];
$passwordSuccess = false;
$passwordFormData = [
    'current_password' => '',
    'new_password' => '',
    'confirm_password' => ''
];

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
    } elseif (strlen($nom) < 2) {
        $errors[] = "Le nom doit contenir au moins 2 caractères.";
    } elseif (strlen($nom) > 50) {
        $errors[] = "Le nom ne peut pas dépasser 50 caractères.";
    }
    
    if (empty($prenom)) {
        $errors[] = "Le prénom est obligatoire.";
    } elseif (strlen($prenom) < 2) {
        $errors[] = "Le prénom doit contenir au moins 2 caractères.";
    } elseif (strlen($prenom) > 50) {
        $errors[] = "Le prénom ne peut pas dépasser 50 caractères.";
    }
    
    if (empty($email)) {
        $errors[] = "L'email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide. Veuillez saisir une adresse email valide.";
    } elseif (strlen($email) > 255) {
        $errors[] = "L'email ne peut pas dépasser 255 caractères.";
    }

    // Check email uniqueness
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
            $stmt = $pdo->prepare("SELECT * FROM brocanteurs WHERE id = ?");
            $stmt->execute([$brocanteur_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
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

    // Preserve password form data in case of errors
    $passwordFormData = [
        'current_password' => $current_password,
        'new_password' => $new_password,
        'confirm_password' => $confirm_password
    ];

    // Validation
    if (empty($current_password)) {
        $passwordErrors[] = "Le mot de passe actuel est obligatoire.";
    }
    
    if (empty($new_password)) {
        $passwordErrors[] = "Le nouveau mot de passe est obligatoire.";
    } elseif (strlen($new_password) < 8) {
        $passwordErrors[] = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $new_password)) {
        $passwordErrors[] = "Le nouveau mot de passe doit contenir au moins une minuscule, une majuscule et un chiffre.";
    }
    
    if (empty($confirm_password)) {
        $passwordErrors[] = "La confirmation du mot de passe est obligatoire.";
    } elseif ($new_password !== $confirm_password) {
        $passwordErrors[] = "Les mots de passe ne correspondent pas.";
    }

    // Verify current password
    if (empty($passwordErrors) && !password_verify($current_password, $user['password_hash'])) {
        $passwordErrors[] = "Le mot de passe actuel est incorrect.";
    }

    // Update password if no errors
    if (empty($passwordErrors)) {
        $stmt = $pdo->prepare("UPDATE brocanteurs SET password_hash = ? WHERE id = ?");
        if ($stmt->execute([password_hash($new_password, PASSWORD_DEFAULT), $user['id']])) {
            $passwordSuccess = true;
            // Clear form data on success
            $passwordFormData = [
                'current_password' => '',
                'new_password' => '',
                'confirm_password' => ''
            ];
        } else {
            $passwordErrors[] = "Erreur lors de la mise à jour du mot de passe.";
        }
    }
}
?>

<div class="dashboard-settings">
    <h1>Paramètres de votre compte</h1>
    
    <!-- Profile Update Section -->
    <section class="profile-section">
        <h2>Informations personnelles</h2>
        
        <?php if ($success): ?>
            <div class="success-message">
                <strong>✅ Succès !</strong> Votre profil a été mis à jour avec succès.
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="error-messages">
                <strong>❌ Erreurs de validation :</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data" class="settings-form">
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
                <textarea id="description" name="description" rows="4" placeholder="Parlez-nous de vous et de ce que vous vendez..."><?= htmlspecialchars($formData['description']) ?></textarea>
            </div>
            
            <div class="form-group checkbox-group">
                <label for="visibilite" class="checkbox-label">
                    <input type="checkbox" id="visibilite" name="visibilite" <?= $formData['visibilite'] ? 'checked' : '' ?>>
                    <span class="checkmark"></span>
                    Être visible en ligne
                </label>
                <small>Autorise l'affichage de votre profil et de vos objets sur le site</small>
            </div>
            
            <div class="form-group">
                <label for="photo_profil">Photo de profil</label>
                <input type="file" id="photo_profil" name="photo_profil" accept="image/*">
                <?php if (!empty($user['photo_profil'])): ?>
                    <div class="current-photo">
                        <img src="../../uploads/<?= htmlspecialchars($user['photo_profil']) ?>" alt="Photo actuelle">
                        <p>Photo actuelle</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn btn-primary">Mettre à jour le profil</button>
        </form>
    </section>
    
    <!-- Password Change Section -->
    <section class="password-section">
        <h2>Changer le mot de passe</h2>
        
        <?php if ($passwordSuccess): ?>
            <div class="success-message">
                <strong>✅ Succès !</strong> Votre mot de passe a été mis à jour avec succès.
            </div>
        <?php endif; ?>
        
        <?php if (!empty($passwordErrors)): ?>
            <div class="error-messages">
                <strong>❌ Erreurs de validation :</strong>
                <ul>
                    <?php foreach ($passwordErrors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="post" class="settings-form">
            <input type="hidden" name="action" value="change_password">
            
            <div class="form-group">
                <label for="current_password">Mot de passe actuel *</label>
                <input type="password" id="current_password" name="current_password" value="<?= htmlspecialchars($passwordFormData['current_password'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="new_password">Nouveau mot de passe *</label>
                <input type="password" id="new_password" name="new_password" value="<?= htmlspecialchars($passwordFormData['new_password'] ?? '') ?>" required>
                <small>Au moins 8 caractères, incluant une minuscule, une majuscule et un chiffre</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirmer le nouveau mot de passe *</label>
                <input type="password" id="confirm_password" name="confirm_password" value="<?= htmlspecialchars($passwordFormData['confirm_password'] ?? '') ?>" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Changer le mot de passe</button>
        </form>
    </section>
</div>

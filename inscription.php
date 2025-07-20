<?php
require_once 'src/autoload.php';
use Controllers\BrocanteurController;

$controller = new BrocanteurController();
$result = $controller->register();

if ($result['success']) {
    $success_message = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
}

$errors = $result['errors'];
$emplacements = $result['emplacements'];

// Vérifier si tous les emplacements sont pris
$all_slots_taken = empty($emplacements);

// Conserver les données du formulaire en cas d'erreur
$form_data = [
    'nom' => $_POST['nom'] ?? '',
    'prenom' => $_POST['prenom'] ?? '',
    'email' => $_POST['email'] ?? '',
    'description' => $_POST['description'] ?? '',
    'visibilite_en_ligne' => $_POST['visibilite_en_ligne'] ?? '0'
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/styles.css">
    <title>Inscription - Échos de Violon</title>
</head>
<body>
    <header>
        <?php include './inc/navbar.inc.php'; ?>
    </header>
    <main class="main">
        <section class="inscription">
            <h1>S'inscrire à la foire aux puces</h1>
            
            <?php if (isset($success_message)): ?>
                <div class="success-message">
                    <p style="color: green;"><?= htmlspecialchars($success_message) ?></p>
                    <p><a href="connexion.php">Se connecter maintenant</a></p>
                </div>
            <?php elseif ($all_slots_taken): ?>
                <div class="error-message">
                    <p style="color: red;">Désolé, tous les emplacements sont déjà attribués. Il n'est plus possible d'effectuer une demande de participation à la foire aux puces.</p>
                    <p><a href="index.php">Retour à l'accueil</a></p>
                </div>
            <?php else: ?>
                <?php if (!empty($errors)): ?>
                    <div class="errors">
                        <?php foreach ($errors as $error): ?>
                            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="nom">Nom *</label>
                        <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($form_data['nom']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="prenom">Prénom *</label>
                        <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($form_data['prenom']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($form_data['email']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Mot de passe *</label>
                        <input type="password" id="password" name="password" required>
                        <small>Au moins 8 caractères</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4" placeholder="Parlez-nous de vous et de ce que vous vendez..."><?= htmlspecialchars($form_data['description']) ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="photo_profil">Photo de profil</label>
                        <input type="file" id="photo_profil" name="photo_profil" accept="image/*">
                    </div>
                    
                    <div class="form-group">
                        <label for="visibilite_en_ligne">
                            <input type="checkbox" id="visibilite_en_ligne" name="visibilite_en_ligne" value="1" <?= $form_data['visibilite_en_ligne'] ? 'checked' : '' ?>>
                            Être visible en ligne
                        </label>
                        <small>Autorise l'affichage de votre profil et de vos objets sur le site</small>
                    </div>
                    
                    <button type="submit">S'inscrire</button>
                </form>
            <?php endif; ?>
            
            <p>vous avez déjà un compte ? <a href="connexion.php">Connectez vous</a></p>
        </section>
    </main>
    <?php include './inc/footer.inc.php'; ?>
</body>
</html>

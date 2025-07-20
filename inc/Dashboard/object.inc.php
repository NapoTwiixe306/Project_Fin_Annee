<?php
require_once __DIR__ . '/../../src/autoload.php';
use Controllers\ObjetController;

$controller = new ObjetController();

// Get the action from URL parameters
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$data = [];
$formData = [
    'titre' => '',
    'description' => '',
    'categorie_id' => 0
];

// Handle different actions
switch ($action) {
    case 'create':
        $data = $controller->create();
        if ($data['success']) {
            // Redirect to avoid resubmission
            header('Location: admin_dashboard.php?page=objet&action=list&success=1');
            exit();
        }
        // Preserve form data on error
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formData = [
                'titre' => $_POST['titre'] ?? '',
                'description' => $_POST['description'] ?? '',
                'categorie_id' => $_POST['categorie_id'] ?? 0
            ];
        }
        break;
        
    case 'edit':
        if ($id > 0) {
            $data = $controller->update($id);
            if ($data['success']) {
                // Redirect to avoid resubmission
                header('Location: admin_dashboard.php?page=objet&action=list&updated=1');
                exit();
            }
            // Preserve form data
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $formData = [
                    'titre' => $_POST['titre'] ?? '',
                    'description' => $_POST['description'] ?? '',
                    'categorie_id' => $_POST['categorie_id'] ?? 0
                ];
            } else {
                // Pre-fill form with existing data
                $objet = $data['objet'];
                if ($objet) {
                    $formData = [
                        'titre' => $objet['titre'],
                        'description' => $objet['description'],
                        'categorie_id' => $objet['categorie_id']
                    ];
                }
            }
        }
        break;
        
    case 'delete':
        if ($id > 0) {
            $data = $controller->delete($id);
            if ($data['success']) {
                header('Location: admin_dashboard.php?page=objet&action=list&deleted=1');
                exit();
            }
        }
        break;
        
    case 'list':
    default:
        $data = $controller->getUserObjects();
        break;
}

// Get categories for forms
if (in_array($action, ['create', 'edit'])) {
    if (!isset($data['categories'])) {
        $categorieModel = new \Models\Categorie();
        $data['categories'] = $categorieModel->getAll();
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des objets</title>
    <link rel="stylesheet" href="../../css/styles.css">
</head>
<body>
<div class="container">
    <?php if ($action === 'list'): ?>
        <h1>Mes objets</h1>
        
        <!-- Success/Error messages -->
        <?php if (isset($_GET['success'])): ?>
            <p class="success">Objet ajouté avec succès !</p>
        <?php endif; ?>
        <?php if (isset($_GET['updated'])): ?>
            <p class="success">Objet mis à jour avec succès !</p>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
            <p class="success">Objet supprimé avec succès !</p>
        <?php endif; ?>
        
        
        <?php if (!empty($data['objets'])): ?>
            <div class="objects-grid">
                <?php foreach ($data['objets'] as $objet): ?>
                    <div class="object-card">
                        <div class="object-image">
                            <?php if (!empty($objet['photo_objet']) && file_exists(__DIR__ . '/../../' . $objet['photo_objet'])): ?>
                                <img src="../../<?= htmlspecialchars($objet['photo_objet']) ?>" alt="<?= htmlspecialchars($objet['titre']) ?>">
                            <?php else: ?>
                                <div class="no-image">Pas d'image</div>
                            <?php endif; ?>
                        </div>
                        <div class="object-info">
                            <h3><?= htmlspecialchars($objet['titre']) ?></h3>
                            <p class="description"><?= htmlspecialchars(substr($objet['description'], 0, 100)) ?><?= strlen($objet['description']) > 100 ? '...' : '' ?></p>
                            <p class="category">Catégorie: <?= htmlspecialchars($objet['categorie_nom'] ?? 'Non définie') ?></p>
                        </div>
                        <div class="object-actions">
                            <a href="admin_dashboard.php?page=objet&action=edit&id=<?= $objet['id'] ?>" class="btn btn-secondary">Modifier</a>
                            <a href="admin_dashboard.php?page=objet&action=delete&id=<?= $objet['id'] ?>" class="btn btn-danger" onclick="return confirm('Voulez-vous vraiment supprimer cet objet ?')">Supprimer</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Vous n'avez pas encore ajouté d'objets.</p>
        <?php endif; ?>
        
    <?php elseif ($action === 'create'): ?>
        <h1>Ajouter un objet</h1>
        
        <?php if (!empty($data['errors'])): ?>
            <div class="error-messages">
                <?php foreach ($data['errors'] as $error): ?>
                    <p class="error"><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="titre">Titre *</label>
                <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($formData['titre']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" name="description" rows="4" required><?= htmlspecialchars($formData['description']) ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="categorie_id">Catégorie *</label>
                <select id="categorie_id" name="categorie_id" required>
                    <option value="">Sélectionnez une catégorie</option>
                    <?php foreach ($data['categories'] as $categorie): ?>
                        <option value="<?= $categorie['id'] ?>" <?= $formData['categorie_id'] == $categorie['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($categorie['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="photo">Photo de l'objet</label>
                <input type="file" id="photo" name="photo" accept="image/*">
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Ajouter l'objet</button>
                <a href="admin_dashboard.php?page=objet&action=list" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
        
    <?php elseif ($action === 'edit'): ?>
        <?php if (isset($data['objet']) && $data['objet']): ?>
            <h1>Modifier l'objet</h1>
            
            <?php if (!empty($data['errors'])): ?>
                <div class="error-messages">
                    <?php foreach ($data['errors'] as $error): ?>
                        <p class="error"><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="titre">Titre *</label>
                <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($formData['titre']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" name="description" rows="4" required><?= htmlspecialchars($formData['description']) ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="categorie_id">Catégorie *</label>
                <select id="categorie_id" name="categorie_id" required>
                    <option value="">Sélectionnez une catégorie</option>
                    <?php foreach ($data['categories'] as $categorie): ?>
                        <option value="<?= $categorie['id'] ?>" <?= $formData['categorie_id'] == $categorie['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($categorie['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="photo">Photo de l'objet</label>
                <input type="file" id="photo" name="photo" accept="image/*">
                <?php if (!empty($data['objet']['photo_objet'])): ?>
                    <div class="current-image">
                        <img src="../../<?= htmlspecialchars($data['objet']['photo_objet']) ?>" alt="Photo actuelle" style="max-width: 200px; max-height: 200px;">
                        <p>Photo actuelle</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                <a href="admin_dashboard.php?page=objet&action=list" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
        <?php else: ?>
            <h1>Objet non trouvé</h1>
            <p>L'objet que vous cherchez n'existe pas ou vous n'avez pas les droits pour le modifier.</p>
            <a href="admin_dashboard.php?page=objet&action=list" class="btn btn-primary">Retour à la liste</a>
        <?php endif; ?>
    
    <?php endif; ?>
</div>
</body>
</html>

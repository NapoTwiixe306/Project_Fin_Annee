<?php
// Vérifier l'authentification
if (!isset($_SESSION['brocanteur_id'])) {
    header('Location: connexion.php');
    exit();
}

// Configuration de base de données
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../../bdd.php';

$brocanteur_id = $_SESSION['brocanteur_id'];

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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre = trim($_POST['titre'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $categorie_id = (int)($_POST['categorie_id'] ?? 0);
            
            if (empty($titre) || empty($description) || $categorie_id === 0) {
                $data['errors'] = ["Tous les champs sont obligatoires."];
            } else {
                // Gestion de l'upload de photo
                $photo_path = null;
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../../uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $fileName = uniqid('objet_', true) . '.' . pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                    $targetPath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
                        $photo_path = 'uploads/' . $fileName;
                    }
                }
                
                $stmt = $pdo->prepare("INSERT INTO objets (brocanteur_id, titre, description, categorie_id, photo_objet) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute([$brocanteur_id, $titre, $description, $categorie_id, $photo_path])) {
                    header('Location: admin_dashboard.php?page=objet&action=list&success=1');
                    exit();
                } else {
                    $data['errors'] = ["Erreur lors de l'ajout de l'objet."];
                }
            }
            
            // Preserve form data on error
            $formData = [
                'titre' => $titre,
                'description' => $description,
                'categorie_id' => $categorie_id
            ];
        }
        break;

    case 'edit':
        if ($id > 0) {
            // Récupérer l'objet existant
            $stmt = $pdo->prepare("SELECT * FROM objets WHERE id = ? AND brocanteur_id = ?");
            $stmt->execute([$id, $brocanteur_id]);
            $data['objet'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($data['objet']) {
                $formData = [
                    'titre' => $data['objet']['titre'],
                    'description' => $data['objet']['description'],
                    'categorie_id' => $data['objet']['categorie_id']
                ];
                
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $titre = trim($_POST['titre'] ?? '');
                    $description = trim($_POST['description'] ?? '');
                    $categorie_id = (int)($_POST['categorie_id'] ?? 0);
                    
                    // Validation
                    if (empty($titre) || empty($description) || $categorie_id === 0) {
                        $data['errors'] = ["Tous les champs sont obligatoires."];
                    } else {
                        // Gestion de l'upload de photo
                        $photo_path = $data['objet']['photo_objet']; // Garder l'ancienne photo par défaut
                        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                            $uploadDir = __DIR__ . '/../../uploads/';
                            if (!is_dir($uploadDir)) {
                                mkdir($uploadDir, 0755, true);
                            }
                            
                            $fileName = uniqid('objet_', true) . '.' . pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                            $targetPath = $uploadDir . $fileName;
                            
                            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
                                // Supprimer l'ancienne photo si elle existe
                                if ($photo_path && file_exists(__DIR__ . '/../../' . $photo_path)) {
                                    unlink(__DIR__ . '/../../' . $photo_path);
                                }
                                $photo_path = 'uploads/' . $fileName;
                            }
                        }
                        
                        $stmt = $pdo->prepare("UPDATE objets SET titre = ?, description = ?, categorie_id = ?, photo_objet = ? WHERE id = ? AND brocanteur_id = ?");
                        if ($stmt->execute([$titre, $description, $categorie_id, $photo_path, $id, $brocanteur_id])) {
                            header('Location: admin_dashboard.php?page=objet&action=list&updated=1');
                            exit();
                        } else {
                            $data['errors'] = ["Erreur lors de la mise à jour de l'objet."];
                        }
                    }
                    
                    // Preserve form data on error
                    $formData = [
                        'titre' => $titre,
                        'description' => $description,
                        'categorie_id' => $categorie_id
                    ];
                }
            } else {
                header('Location: admin_dashboard.php?page=objet&action=list');
                exit();
            }
        } else {
            header('Location: admin_dashboard.php?page=objet&action=list');
            exit();
        }
        break;

    case 'delete':
        if ($id > 0) {
            // Vérifier que l'objet appartient à l'utilisateur
            $stmt = $pdo->prepare("SELECT photo_objet FROM objets WHERE id = ? AND brocanteur_id = ?");
            $stmt->execute([$id, $brocanteur_id]);
            $objet = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($objet) {
                // Supprimer la photo si elle existe
                if ($objet['photo_objet'] && file_exists(__DIR__ . '/../../' . $objet['photo_objet'])) {
                    unlink(__DIR__ . '/../../' . $objet['photo_objet']);
                }
                
                // Supprimer l'objet
                $stmt = $pdo->prepare("DELETE FROM objets WHERE id = ? AND brocanteur_id = ?");
                if ($stmt->execute([$id, $brocanteur_id])) {
                    header('Location: admin_dashboard.php?page=objet&action=list&deleted=1');
                    exit();
                }
            }
        }
        header('Location: admin_dashboard.php?page=objet&action=list');
        exit();

    case 'list':
    default:
        // Récupérer les objets de l'utilisateur
        $stmt = $pdo->prepare("SELECT o.*, c.nom as categorie_nom FROM objets o LEFT JOIN categories c ON o.categorie_id = c.id WHERE o.brocanteur_id = ? ORDER BY o.created_at DESC");
        $stmt->execute([$brocanteur_id]);
        $data['objets'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;
}

// Get categories for forms
if (in_array($action, ['create', 'edit'])) {
    $stmt = $pdo->prepare("SELECT id, nom FROM categories ORDER BY nom");
    $stmt->execute();
    $data['categories'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

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
                            <?php
                                $relativeWebPath = 'uploads/' . basename($objet['photo_objet'] ?? '');

                                $serverPath = __DIR__ . '/../../uploads/' . basename($objet['photo_objet'] ?? '');
                                $fileExists = !empty($objet['photo_objet']) && file_exists($serverPath);
                            ?>
                            <?php if ($fileExists): ?>
                                <img src="<?= htmlspecialchars($relativeWebPath) ?>" alt="<?= htmlspecialchars($objet['titre']) ?>">
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
                        <?php
                            $relativeWebPath = 'uploads/' . basename($data['objet']['photo_objet']);
                            $serverPath = __DIR__ . '/../../uploads/' . basename($data['objet']['photo_objet']);
                            $fileExists = file_exists($serverPath);
                        ?>
                        <div class="current-image" style="margin-top: 10px;">
                            <?php if ($fileExists): ?>
                                <img src="<?= htmlspecialchars($relativeWebPath) ?>" alt="Photo actuelle" style="max-width: 200px; max-height: 200px; border: 1px solid #ccc; padding: 4px;">
                                <p style="margin-top: 5px;">Photo actuelle</p>
                            <?php else: ?>
                                <p style="color: red;">Image introuvable sur le serveur.</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                    <a href="admin_dashboard.php?page=objet&action=list" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        <?php else: ?>
            <p>Objet introuvable.</p>
            <a href="admin_dashboard.php?page=objet&action=list" class="btn btn-secondary">Retour à la liste</a>
        <?php endif; ?>

    <?php else: ?>
        <p>Action inconnue.</p>
    <?php endif; ?>
</div>

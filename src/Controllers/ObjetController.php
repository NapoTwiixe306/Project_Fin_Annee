<?php

namespace Controllers;

use Models\Objet;
use Models\Categorie;
use Services\SessionService;
use Services\CookieService;

class ObjetController
{
    private $objetModel;
    private $categorieModel;
    private $sessionService;
    private $cookieService;

    public function __construct()
    {
        $this->objetModel = new Objet();
        $this->categorieModel = new Categorie();
        $this->sessionService = SessionService::getInstance();
        $this->cookieService = CookieService::getInstance();
    }

    /**
     * Affiche la liste des objets avec filtres
     */
    public function index(): array
    {
        // Récupération des filtres depuis les cookies ou les paramètres GET
        $savedFilters = $this->cookieService->getSearchFilters();
        
        $search = $_GET['search'] ?? $savedFilters['search'] ?? '';
        $filter = $_GET['filter'] ?? $savedFilters['filter'] ?? '';

        // Sauvegarde des filtres actuels dans les cookies
        $currentFilters = [
            'search' => $search,
            'filter' => $filter
        ];
        $this->cookieService->saveSearchFilters($currentFilters);

        // Récupération des données
        $objets = $this->objetModel->search($search, $filter);
        $categories = $this->categorieModel->getAll();

        return [
            'objets' => $objets,
            'categories' => $categories,
            'filters' => $currentFilters
        ];
    }

    /**
     * Affiche le détail d'un objet
     */
    public function show(int $id): ?array
    {
        $objet = $this->objetModel->getById($id);
        
        if (!$objet) {
            return null;
        }

        return ['objet' => $objet];
    }

    /**
     * Crée un nouvel objet
     */
    public function create(): array
    {
        $this->requireAuth();
        
        $errors = [];
        $success = false;
        $userId = $this->sessionService->getUserId();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre = trim($_POST['titre'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $categorieId = intval($_POST['categorie_id'] ?? 0);

            // Validation
            if (empty($titre) || empty($description) || empty($categorieId)) {
                $errors[] = "Tous les champs sont obligatoires.";
            }

            // Vérification que la catégorie existe
            if ($categorieId > 0) {
                $categorie = $this->categorieModel->getById($categorieId);
                if (!$categorie) {
                    $errors[] = "La catégorie sélectionnée n'existe pas.";
                }
            }

            $photoPath = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $photoPath = $this->handleFileUpload($_FILES['photo']);
                if (!$photoPath) {
                    $errors[] = "Erreur lors du téléchargement de l'image.";
                }
            }

            if (empty($errors)) {
                $data = [
                    'brocanteur_id' => $userId,
                    'titre' => $titre,
                    'description' => $description,
                    'categorie_id' => $categorieId,
                    'photo_objet' => $photoPath
                ];

                if ($this->objetModel->create($data)) {
                    $success = true;
                } else {
                    $errors[] = "Erreur lors de l'ajout de l'objet.";
                }
            }
        }

        return [
            'success' => $success,
            'errors' => $errors,
            'categories' => $this->categorieModel->getAll()
        ];
    }

    /**
     * Met à jour un objet existant
     */
    public function update(int $id): array
    {
        $this->requireAuth();
        
        $errors = [];
        $success = false;
        $userId = $this->sessionService->getUserId();

        // Vérifier que l'objet appartient à l'utilisateur
        if (!$this->objetModel->belongsToUser($id, $userId)) {
            $errors[] = "Vous n'avez pas l'autorisation de modifier cet objet.";
            return [
                'success' => false,
                'errors' => $errors,
                'categories' => $this->categorieModel->getAll(),
                'objet' => null
            ];
        }

        $objet = $this->objetModel->getById($id);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre = trim($_POST['titre'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $categorieId = intval($_POST['categorie_id'] ?? 0);

            // Validation
            if (empty($titre) || empty($description) || empty($categorieId)) {
                $errors[] = "Tous les champs sont obligatoires.";
            }

            // Vérification que la catégorie existe
            if ($categorieId > 0) {
                $categorie = $this->categorieModel->getById($categorieId);
                if (!$categorie) {
                    $errors[] = "La catégorie sélectionnée n'existe pas.";
                }
            }

            $photoPath = $objet['photo_objet']; // Garder l'ancienne photo par défaut
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $newPhotoPath = $this->handleFileUpload($_FILES['photo']);
                if ($newPhotoPath) {
                    // Supprimer l'ancienne photo si elle existe
                    if ($photoPath && file_exists(__DIR__ . '/../../' . $photoPath)) {
                        unlink(__DIR__ . '/../../' . $photoPath);
                    }
                    $photoPath = $newPhotoPath;
                } else {
                    $errors[] = "Erreur lors du téléchargement de l'image.";
                }
            }

            if (empty($errors)) {
                $data = [
                    'titre' => $titre,
                    'description' => $description,
                    'categorie_id' => $categorieId,
                    'photo_objet' => $photoPath
                ];

                if ($this->objetModel->update($id, $data)) {
                    $success = true;
                    $objet = $this->objetModel->getById($id); // Récupérer les données mises à jour
                } else {
                    $errors[] = "Erreur lors de la mise à jour de l'objet.";
                }
            }
        }

        return [
            'success' => $success,
            'errors' => $errors,
            'categories' => $this->categorieModel->getAll(),
            'objet' => $objet
        ];
    }

    /**
     * Supprime un objet
     */
    public function delete(int $id): array
    {
        $this->requireAuth();
        
        $errors = [];
        $success = false;
        $userId = $this->sessionService->getUserId();

        // Vérifier que l'objet appartient à l'utilisateur
        if (!$this->objetModel->belongsToUser($id, $userId)) {
            $errors[] = "Vous n'avez pas l'autorisation de supprimer cet objet.";
        } else {
            $objet = $this->objetModel->getById($id);
            
            if ($this->objetModel->delete($id)) {
                // Supprimer le fichier photo si il existe
                if ($objet && $objet['photo_objet'] && file_exists(__DIR__ . '/../../' . $objet['photo_objet'])) {
                    unlink(__DIR__ . '/../../' . $objet['photo_objet']);
                }
                $success = true;
            } else {
                $errors[] = "Erreur lors de la suppression de l'objet.";
            }
        }

        return [
            'success' => $success,
            'errors' => $errors
        ];
    }

    /**
     * Récupère les objets d'un utilisateur
     */
    public function getUserObjects(): array
    {
        $this->requireAuth();
        
        $userId = $this->sessionService->getUserId();
        $objets = $this->objetModel->getByBrocanteur($userId);

        return [
            'objets' => $objets
        ];
    }

    /**
     * Gère le téléchargement de fichiers
     */
    private function handleFileUpload(array $file): ?string
    {
        $uploadDir = __DIR__ . '/../../uploads/';
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Vérifier le type de fichier
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return null;
        }

        // Vérifier la taille (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return null;
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid("objet_", true) . '.' . $ext;
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return 'uploads/' . $filename;
        }

        return null;
    }

    /**
     * Middleware pour vérifier l'authentification
     */
    private function requireAuth(): void
    {
        if (!$this->sessionService->isLoggedIn()) {
            header('Location: connexion.php');
            exit();
        }
    }
}

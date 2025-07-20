<?php

namespace Controllers;

use Models\Brocanteur;
use Models\Emplacement;
use Services\SessionService;
use Services\CookieService;

class BrocanteurController
{
    private $brocanteurModel;
    private $emplacementModel;
    private $sessionService;
    private $cookieService;

    public function __construct()
    {
        $this->brocanteurModel = new Brocanteur();
        $this->emplacementModel = new Emplacement();
        $this->sessionService = SessionService::getInstance();
        $this->cookieService = CookieService::getInstance();
    }

    /**
     * Affiche la liste des brocanteurs avec filtres
     */
    public function index(): array
    {
        // Récupération des filtres depuis les cookies ou les paramètres GET
        $savedFilters = $this->cookieService->getSearchFilters();
        
        $search = $_GET['search'] ?? $savedFilters['search'] ?? '';
        $filterBy = $_GET['filter_by'] ?? $savedFilters['filter_by'] ?? 'name';
        $zone = $_GET['zone'] ?? $savedFilters['zone'] ?? '';

        // Sauvegarde des filtres actuels dans les cookies
        $currentFilters = [
            'search' => $search,
            'filter_by' => $filterBy,
            'zone' => $zone
        ];
        $this->cookieService->saveSearchFilters($currentFilters);

        // Récupération des données
        $brocanteurs = $this->brocanteurModel->search($search, $filterBy, $zone);
        $zones = $this->emplacementModel->getZones();

        return [
            'brocanteurs' => $brocanteurs,
            'zones' => $zones,
            'filters' => $currentFilters
        ];
    }

    /**
     * Affiche le détail d'un brocanteur
     */
    public function show(int $id): ?array
    {
        $brocanteur = $this->brocanteurModel->getById($id);
        
        if (!$brocanteur) {
            return null;
        }

        // Get brocanteur's objects
        $objects = $this->brocanteurModel->getObjectsByBrocanteur($id);
        $brocanteur['objects'] = $objects;

        return ['brocanteur' => $brocanteur];
    }

    /**
     * Connexion d'un brocanteur
     */
    public function login(): array
    {
        $errors = [];
        $success = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $errors[] = "Email et mot de passe requis.";
            } else {
                $user = $this->brocanteurModel->getByEmail($email);
                
                if ($user && password_verify($password, $user['password_hash'])) {
                    $this->sessionService->login($user['id']);
                    $success = true;
                } else {
                    $errors[] = "Identifiants incorrects.";
                }
            }
        }

        return [
            'success' => $success,
            'errors' => $errors
        ];
    }

    /**
     * Inscription d'un nouveau brocanteur
     */
    public function register(): array
    {
        $errors = [];
        $success = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $description = trim($_POST['description'] ?? '');
            $visibiliteEnLigne = isset($_POST['visibilite_en_ligne']) ? 1 : 0;

            // Validation
            if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
                $errors[] = "Tous les champs obligatoires doivent être remplis.";
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Format d'email invalide.";
            }

            if (strlen($password) < 8) {
                $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
            }

            if ($password !== $confirmPassword) {
                $errors[] = "Les mots de passe ne correspondent pas.";
            }

            if ($this->brocanteurModel->emailExists($email)) {
                $errors[] = "Cet email est déjà utilisé.";
            }

            // Gestion de l'upload de photo
            $photoPath = null;
            if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === UPLOAD_ERR_OK) {
                $photoPath = $this->handlePhotoUpload($_FILES['photo_profil']);
                if (!$photoPath) {
                    $errors[] = "Erreur lors de l'upload de la photo.";
                }
            }

            if (empty($errors)) {
                $data = [
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email,
                    'password' => $password,
                    'description' => $description,
                    'visible' => $visibiliteEnLigne,
                    'photo_profil' => $photoPath
                ];

                if ($this->brocanteurModel->create($data)) {
                    $success = true;
                } else {
                    $errors[] = "Erreur lors de l'inscription.";
                }
            }
        }

        return [
            'success' => $success,
            'errors' => $errors,
            'emplacements' => $this->emplacementModel->getAvailable()
        ];
    }

    /**
     * Déconnexion
     */
    public function logout(): void
    {
        $this->sessionService->logout();
    }

    /**
     * Vérifie si l'utilisateur est connecté
     */
    public function isLoggedIn(): bool
    {
        return $this->sessionService->isLoggedIn();
    }

    /**
     * Récupère l'utilisateur connecté
     */
    public function getCurrentUser(): ?array
    {
        $userId = $this->sessionService->getUserId();
        if (!$userId) {
            return null;
        }

        return $this->brocanteurModel->getById($userId);
    }

    /**
     * Middleware pour vérifier l'authentification
     */
    public function requireAuth(): void
    {
        if (!$this->isLoggedIn()) {
            header('Location: connexion.php');
            exit();
        }
    }

    /**
     * Met à jour le profil du brocanteur connecté
     */
    public function updateProfile(): array
    {
        $this->requireAuth();
        
        $errors = [];
        $success = false;
        $userId = $this->sessionService->getUserId();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $emplacementId = $_POST['emplacement_id'] ?? null;
            $description = trim($_POST['description'] ?? '');

            // Validation
            if (empty($nom) || empty($prenom) || empty($email)) {
                $errors[] = "Nom, prénom et email sont obligatoires.";
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Format d'email invalide.";
            }

            if ($this->brocanteurModel->emailExists($email, $userId)) {
                $errors[] = "Cet email est déjà utilisé par un autre utilisateur.";
            }

            if (empty($errors)) {
                $data = [
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email,
                    'emplacement_id' => $emplacementId,
                    'description' => $description
                ];

                if ($this->brocanteurModel->update($userId, $data)) {
                    $success = true;
                } else {
                    $errors[] = "Erreur lors de la mise à jour.";
                }
            }
        }

        return [
            'success' => $success,
            'errors' => $errors,
            'emplacements' => $this->emplacementModel->getAvailable(),
            'user' => $this->getCurrentUser()
        ];
    }

    /**
     * Gère l'upload de photo de profil
     */
    private function handlePhotoUpload(array $file): ?string
    {
        $uploadDir = 'uploads/profils/';
        
        // Créer le dossier s'il n'existe pas
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Vérifier le type de fichier
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            return null;
        }
        
        // Vérifier la taille (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return null;
        }
        
        // Générer un nom unique
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $targetPath = $uploadDir . $filename;
        
        // Déplacer le fichier
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $targetPath;
        }
        
        return null;
    }
}

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

    /**
     * Demande de réinitialisation de mot de passe
     */
    public function requestPasswordReset(): array
    {
        $errors = [];
        $success = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');

            if (empty($email)) {
                $errors[] = "L'adresse email est requise.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Format d'email invalide.";
            } else {
                // Vérifier si l'email existe
                $user = $this->brocanteurModel->getByEmail($email);
                
                if (!$user) {
                    // Pour des raisons de sécurité, on ne révèle pas si l'email existe ou non
                    $success = true;
                } else {
                    // Générer un token de réinitialisation
                    $token = bin2hex(random_bytes(32));
                    $expiresAt = date('Y-m-d H:i:s', strtotime('+' . RESET_TOKEN_EXPIRY_HOURS . ' hour'));
                    
                    if ($this->brocanteurModel->createResetToken($user['id'], $token, $expiresAt)) {
                        // Envoyer l'email
                        $resetLink = APP_URL . "/reset_password_confirm.php?token=" . $token;
                        $this->sendResetEmail($email, $user['nom'], $user['prenom'], $resetLink);
                        $success = true;
                    } else {
                        $errors[] = "Erreur lors de la génération du lien de réinitialisation.";
                    }
                }
            }
        }

        return [
            'success' => $success,
            'errors' => $errors
        ];
    }

    /**
     * Réinitialisation du mot de passe avec token
     */
    public function resetPassword(string $token): array
    {
        $errors = [];
        $success = false;
        $tokenValid = false;

        // Vérifier si le token est valide
        $resetData = $this->brocanteurModel->getResetToken($token);
        
        if (!$resetData) {
            return [
                'success' => false,
                'errors' => [],
                'token_valid' => false
            ];
        }

        $tokenValid = true;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Validation
            if (empty($password)) {
                $errors[] = "Le mot de passe est requis.";
            } elseif (strlen($password) < 8) {
                $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
            } elseif ($password !== $confirmPassword) {
                $errors[] = "Les mots de passe ne correspondent pas.";
            } else {
                // Mettre à jour le mot de passe
                if ($this->brocanteurModel->updatePassword($resetData['brocanteur_id'], $password)) {
                    // Supprimer le token utilisé
                    $this->brocanteurModel->deleteResetToken($token);
                    $success = true;
                } else {
                    $errors[] = "Erreur lors de la réinitialisation du mot de passe.";
                }
            }
        }

        return [
            'success' => $success,
            'errors' => $errors,
            'token_valid' => $tokenValid
        ];
    }

    /**
     * Envoie l'email de réinitialisation
     */
    private function sendResetEmail(string $email, string $nom, string $prenom, string $resetLink): bool
    {
        $subject = "[Supra Brocante] Réinitialisation de votre mot de passe";
        
        $emailContent = "Bonjour $prenom $nom,\n\n";
        $emailContent .= "Vous avez demandé la réinitialisation de votre mot de passe pour votre compte Supra Brocante.\n\n";
        $emailContent .= "Cliquez sur le lien ci-dessous pour définir un nouveau mot de passe :\n";
        $emailContent .= "$resetLink\n\n";
        $emailContent .= "Ce lien expirera dans " . RESET_TOKEN_EXPIRY_HOURS . " heure(s).\n\n";
        $emailContent .= "Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.\n\n";
        $emailContent .= "Cordialement,\nL'équipe Supra Brocante";

        // Headers pour l'email (même système que contact.php)
        $headers = "From: noreply@suprabrocante.be\r\n";
        $headers .= "Reply-To: noreply@suprabrocante.be\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        return mail($email, $subject, $emailContent, $headers);
    }
}

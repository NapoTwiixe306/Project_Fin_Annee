<?php

namespace Controllers;

use Models\Brocanteur;
use Models\Objet;
use Models\Categorie;
use Models\Emplacement;
use Models\Zone;
use Services\SessionService;

class DashboardController
{
    private $brocanteurModel;
    private $objetModel;
    private $categorieModel;
    private $emplacementModel;
    private $zoneModel;
    private $sessionService;

    public function __construct()
    {
        $this->brocanteurModel = new Brocanteur();
        $this->objetModel = new Objet();
        $this->categorieModel = new Categorie();
        $this->emplacementModel = new Emplacement();
        $this->zoneModel = new Zone();
        $this->sessionService = SessionService::getInstance();
    }

    /**
     * Récupère les informations du brocanteur connecté
     */
    public function getCurrentUserInfo(): ?array
    {
        $userId = $this->sessionService->getUserId();
        if (!$userId) {
            return null;
        }

        return $this->brocanteurModel->getById($userId);
    }

    /**
     * Met à jour le profil du brocanteur
     */
    public function updateProfile(array $data): array
    {
        $errors = [];
        $success = false;
        $userId = $this->sessionService->getUserId();

        if (!$userId) {
            return ['success' => false, 'errors' => ['Utilisateur non connecté']];
        }

        // Validation
        if (empty($data['nom'])) {
            $errors[] = "Le nom est obligatoire.";
        } elseif (strlen($data['nom']) < 2) {
            $errors[] = "Le nom doit contenir au moins 2 caractères.";
        }

        if (empty($data['prenom'])) {
            $errors[] = "Le prénom est obligatoire.";
        } elseif (strlen($data['prenom']) < 2) {
            $errors[] = "Le prénom doit contenir au moins 2 caractères.";
        }

        if (empty($data['email'])) {
            $errors[] = "L'email est obligatoire.";
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Format d'email invalide.";
        }

        // Vérifier l'unicité de l'email
        if ($this->brocanteurModel->emailExists($data['email'], $userId)) {
            $errors[] = "Cet email est déjà utilisé par un autre utilisateur.";
        }

        if (empty($errors)) {
            $updateData = [
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'email' => $data['email'],
                'description' => $data['description'] ?? '',
                'visible' => $data['visibilite'] ?? 0,
                'photo_profil' => $data['photo_profil'] ?? null
            ];

            if ($this->brocanteurModel->update($userId, $updateData)) {
                $success = true;
            } else {
                $errors[] = "Erreur lors de la mise à jour.";
            }
        }

        return [
            'success' => $success,
            'errors' => $errors
        ];
    }

    /**
     * Change le mot de passe du brocanteur
     */
    public function changePassword(array $data): array
    {
        $errors = [];
        $success = false;
        $userId = $this->sessionService->getUserId();

        if (!$userId) {
            return ['success' => false, 'errors' => ['Utilisateur non connecté']];
        }

        $user = $this->brocanteurModel->getById($userId);

        // Validation
        if (empty($data['current_password'])) {
            $errors[] = "Le mot de passe actuel est obligatoire.";
        }

        if (empty($data['new_password'])) {
            $errors[] = "Le nouveau mot de passe est obligatoire.";
        } elseif (strlen($data['new_password']) < 8) {
            $errors[] = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
        }

        if (empty($data['confirm_password'])) {
            $errors[] = "La confirmation du mot de passe est obligatoire.";
        } elseif ($data['new_password'] !== $data['confirm_password']) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }

        // Vérifier le mot de passe actuel
        if (empty($errors) && !password_verify($data['current_password'], $user['password_hash'])) {
            $errors[] = "Le mot de passe actuel est incorrect.";
        }

        if (empty($errors)) {
            if ($this->brocanteurModel->updatePassword($userId, $data['new_password'])) {
                $success = true;
            } else {
                $errors[] = "Erreur lors de la mise à jour du mot de passe.";
            }
        }

        return [
            'success' => $success,
            'errors' => $errors
        ];
    }

    /**
     * Récupère les objets du brocanteur
     */
    public function getUserObjects(): array
    {
        $userId = $this->sessionService->getUserId();
        if (!$userId) {
            return [];
        }

        return $this->objetModel->getByBrocanteur($userId);
    }

    /**
     * Crée un nouvel objet
     */
    public function createObject(array $data): array
    {
        $errors = [];
        $success = false;
        $userId = $this->sessionService->getUserId();

        if (!$userId) {
            return ['success' => false, 'errors' => ['Utilisateur non connecté']];
        }

        // Validation
        if (empty($data['titre'])) {
            $errors[] = "Le titre est obligatoire.";
        }

        if (empty($data['description'])) {
            $errors[] = "La description est obligatoire.";
        }

        if (empty($data['categorie_id'])) {
            $errors[] = "La catégorie est obligatoire.";
        }

        if (empty($errors)) {
            $objectData = [
                'brocanteur_id' => $userId,
                'titre' => $data['titre'],
                'description' => $data['description'],
                'categorie_id' => $data['categorie_id'],
                'photo_objet' => $data['photo_objet'] ?? null
            ];

            if ($this->objetModel->create($objectData)) {
                $success = true;
            } else {
                $errors[] = "Erreur lors de l'ajout de l'objet.";
            }
        }

        return [
            'success' => $success,
            'errors' => $errors
        ];
    }

    /**
     * Met à jour un objet
     */
    public function updateObject(int $objectId, array $data): array
    {
        $errors = [];
        $success = false;
        $userId = $this->sessionService->getUserId();

        if (!$userId) {
            return ['success' => false, 'errors' => ['Utilisateur non connecté']];
        }

        // Vérifier que l'objet appartient à l'utilisateur
        if (!$this->objetModel->belongsToUser($objectId, $userId)) {
            return ['success' => false, 'errors' => ['Vous n\'avez pas l\'autorisation de modifier cet objet.']];
        }

        // Validation
        if (empty($data['titre'])) {
            $errors[] = "Le titre est obligatoire.";
        }

        if (empty($data['description'])) {
            $errors[] = "La description est obligatoire.";
        }

        if (empty($data['categorie_id'])) {
            $errors[] = "La catégorie est obligatoire.";
        }

        if (empty($errors)) {
            $objectData = [
                'titre' => $data['titre'],
                'description' => $data['description'],
                'categorie_id' => $data['categorie_id'],
                'photo_objet' => $data['photo_objet'] ?? null
            ];

            if ($this->objetModel->update($objectId, $objectData)) {
                $success = true;
            } else {
                $errors[] = "Erreur lors de la mise à jour de l'objet.";
            }
        }

        return [
            'success' => $success,
            'errors' => $errors
        ];
    }

    /**
     * Supprime un objet
     */
    public function deleteObject(int $objectId): array
    {
        $errors = [];
        $success = false;
        $userId = $this->sessionService->getUserId();

        if (!$userId) {
            return ['success' => false, 'errors' => ['Utilisateur non connecté']];
        }

        // Vérifier que l'objet appartient à l'utilisateur
        if (!$this->objetModel->belongsToUser($objectId, $userId)) {
            return ['success' => false, 'errors' => ['Vous n\'avez pas l\'autorisation de supprimer cet objet.']];
        }

        if ($this->objetModel->delete($objectId)) {
            $success = true;
        } else {
            $errors[] = "Erreur lors de la suppression de l'objet.";
        }

        return [
            'success' => $success,
            'errors' => $errors
        ];
    }

    /**
     * Récupère un objet par ID
     */
    public function getObject(int $objectId): ?array
    {
        $userId = $this->sessionService->getUserId();
        if (!$userId) {
            return null;
        }

        $object = $this->objetModel->getById($objectId);
        
        if ($object && $this->objetModel->belongsToUser($objectId, $userId)) {
            return $object;
        }

        return null;
    }

    /**
     * Récupère toutes les catégories
     */
    public function getCategories(): array
    {
        return $this->categorieModel->getAll();
    }

    /**
     * Récupère tous les emplacements
     */
    public function getAllEmplacements(): array
    {
        return $this->emplacementModel->getAll();
    }

    /**
     * Récupère les emplacements disponibles
     */
    public function getAvailableEmplacements(): array
    {
        return $this->emplacementModel->getAvailable();
    }

    /**
     * Récupère toutes les zones
     */
    public function getAllZones(): array
    {
        return $this->zoneModel->getAll();
    }
} 
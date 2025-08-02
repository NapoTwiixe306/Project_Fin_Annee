<?php

namespace Controllers;

use Models\Brocanteur;
use Models\Emplacement;
use Models\Zone;
use Services\SessionService;

class AdminController
{
    private $brocanteurModel;
    private $emplacementModel;
    private $zoneModel;
    private $sessionService;

    public function __construct()
    {
        $this->brocanteurModel = new Brocanteur();
        $this->emplacementModel = new Emplacement();
        $this->zoneModel = new Zone();
        $this->sessionService = SessionService::getInstance();
    }

    /**
     * Récupère tous les brocanteurs avec leurs informations
     */
    public function getAllBrocanteurs(): array
    {
        return $this->brocanteurModel->getAll();
    }

    /**
     * Attribue un emplacement à un brocanteur
     */
    public function assignEmplacement(int $brocanteurId, int $emplacementId): array
    {
        $errors = [];
        $success = false;

        // Vérifier que l'emplacement existe et est disponible
        $emplacement = $this->emplacementModel->getById($emplacementId);
        if (!$emplacement) {
            $errors[] = "Emplacement introuvable.";
        } elseif (!$emplacement['disponible']) {
            $errors[] = "Cet emplacement n'est pas disponible.";
        }

        // Vérifier que le brocanteur existe
        $brocanteur = $this->brocanteurModel->getById($brocanteurId);
        if (!$brocanteur) {
            $errors[] = "Brocanteur introuvable.";
        }

        if (empty($errors)) {
            if ($this->emplacementModel->assignToBrocanteur($emplacementId, $brocanteurId)) {
                $success = true;
            } else {
                $errors[] = "Erreur lors de l'attribution de l'emplacement.";
            }
        }

        return [
            'success' => $success,
            'errors' => $errors
        ];
    }

    /**
     * Annule l'emplacement d'un brocanteur
     */
    public function unassignEmplacement(int $brocanteurId): array
    {
        $errors = [];
        $success = false;

        // Vérifier que le brocanteur existe
        $brocanteur = $this->brocanteurModel->getById($brocanteurId);
        if (!$brocanteur) {
            $errors[] = "Brocanteur introuvable.";
        }

        if (empty($errors)) {
            if ($this->emplacementModel->unassignFromBrocanteur($brocanteurId)) {
                $success = true;
            } else {
                $errors[] = "Erreur lors de l'annulation de l'emplacement.";
            }
        }

        return [
            'success' => $success,
            'errors' => $errors
        ];
    }

    /**
     * Supprime un brocanteur et ses objets associés
     */
    public function deleteBrocanteur(int $brocanteurId): array
    {
        $errors = [];
        $success = false;

        // Vérifier que le brocanteur existe
        $brocanteur = $this->brocanteurModel->getById($brocanteurId);
        if (!$brocanteur) {
            $errors[] = "Brocanteur introuvable.";
        } elseif ($brocanteur['emplacement_id'] !== null) {
            $errors[] = "Impossible de supprimer un brocanteur qui a un emplacement attribué.";
        }

        if (empty($errors)) {
            if ($this->brocanteurModel->delete($brocanteurId)) {
                $success = true;
            } else {
                $errors[] = "Erreur lors de la suppression du brocanteur.";
            }
        }

        return [
            'success' => $success,
            'errors' => $errors
        ];
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

    /**
     * Récupère les statistiques d'administration
     */
    public function getAdminStats(): array
    {
        $stats = [
            'total_brocanteurs' => 0,
            'brocanteurs_avec_emplacement' => 0,
            'brocanteurs_sans_emplacement' => 0,
            'total_emplacements' => 0,
            'emplacements_occupes' => 0,
            'emplacements_disponibles' => 0,
            'total_zones' => 0
        ];

        // Statistiques des brocanteurs
        $brocanteurs = $this->brocanteurModel->getAll();
        $stats['total_brocanteurs'] = count($brocanteurs);
        
        foreach ($brocanteurs as $brocanteur) {
            if ($brocanteur['emplacement_id']) {
                $stats['brocanteurs_avec_emplacement']++;
            } else {
                $stats['brocanteurs_sans_emplacement']++;
            }
        }

        // Statistiques des emplacements
        $emplacements = $this->emplacementModel->getAll();
        $stats['total_emplacements'] = count($emplacements);
        
        foreach ($emplacements as $emplacement) {
            if ($emplacement['disponible']) {
                $stats['emplacements_disponibles']++;
            } else {
                $stats['emplacements_occupes']++;
            }
        }

        // Statistiques des zones
        $zones = $this->zoneModel->getAll();
        $stats['total_zones'] = count($zones);

        return $stats;
    }
} 
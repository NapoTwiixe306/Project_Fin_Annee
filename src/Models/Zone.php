<?php

namespace Models;

use Database\DatabaseConnection;
use PDO;

class Zone
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = DatabaseConnection::getInstance()->getPdo();
    }

    /**
     * Récupère toutes les zones
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM zones ORDER BY nom";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère une zone par ID
     */
    public function getById(int $id): ?array
    {
        $sql = "SELECT * FROM zones WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Récupère une zone par nom
     */
    public function getByNom(string $nom): ?array
    {
        $sql = "SELECT * FROM zones WHERE nom = :nom";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['nom' => $nom]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Crée une nouvelle zone
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO zones (nom) VALUES (:nom)";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            'nom' => $data['nom']
        ]);
    }

    /**
     * Met à jour une zone
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE zones SET nom = :nom WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            'id' => $id,
            'nom' => $data['nom']
        ]);
    }

    /**
     * Supprime une zone
     */
    public function delete(int $id): bool
    {
        // Vérifier qu'aucun emplacement n'utilise cette zone
        $sql = "SELECT COUNT(*) FROM emplacements WHERE zone_id = :zone_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['zone_id' => $id]);
        
        if ($stmt->fetchColumn() > 0) {
            return false; // Impossible de supprimer une zone utilisée
        }
        
        $sql = "DELETE FROM zones WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Vérifie si une zone existe par nom
     */
    public function nomExists(string $nom, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM zones WHERE nom = :nom";
        $params = ['nom' => $nom];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Récupère les zones avec le nombre d'emplacements
     */
    public function getAllWithEmplacementCount(): array
    {
        $sql = "SELECT z.*, COUNT(e.id) as emplacement_count
                FROM zones z
                LEFT JOIN emplacements e ON z.id = e.zone_id
                GROUP BY z.id
                ORDER BY z.nom";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les zones disponibles (avec emplacements libres)
     */
    public function getAvailableZones(): array
    {
        $sql = "SELECT DISTINCT z.*
                FROM zones z
                INNER JOIN emplacements e ON z.id = e.zone_id
                LEFT JOIN brocanteurs b ON e.id = b.emplacement_id
                WHERE b.emplacement_id IS NULL
                ORDER BY z.nom";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 
<?php

namespace Models;

use Database\DatabaseConnection;
use PDO;

class Emplacement
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = DatabaseConnection::getInstance()->getPdo();
    }

    /**
     * Récupère tous les emplacements
     */
    public function getAll(): array
    {
        $sql = "SELECT e.id, e.numero, e.zone, 
                       (CASE WHEN b.emplacement_id IS NULL THEN 1 ELSE 0 END) as disponible
                FROM emplacements e
                LEFT JOIN brocanteurs b ON e.id = b.emplacement_id
                ORDER BY e.zone, e.numero";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les emplacements disponibles
     */
    public function getAvailable(): array
    {
        $sql = "SELECT e.id, e.numero, e.zone
                FROM emplacements e
                LEFT JOIN brocanteurs b ON e.id = b.emplacement_id
                WHERE b.emplacement_id IS NULL
                ORDER BY e.zone, e.numero";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un emplacement par ID
     */
    public function getById(int $id): ?array
    {
        $sql = "SELECT e.id, e.numero, e.zone, 
                       (CASE WHEN b.emplacement_id IS NULL THEN 1 ELSE 0 END) as disponible
                FROM emplacements e
                LEFT JOIN brocanteurs b ON e.id = b.emplacement_id
                WHERE e.id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Récupère les emplacements par zone
     */
    public function getByZone(string $zone): array
    {
        $sql = "SELECT e.id, e.numero, e.zone, 
                       (CASE WHEN b.emplacement_id IS NULL THEN 1 ELSE 0 END) as disponible
                FROM emplacements e
                LEFT JOIN brocanteurs b ON e.id = b.emplacement_id
                WHERE e.zone = :zone 
                ORDER BY e.numero";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['zone' => $zone]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crée un nouvel emplacement
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO emplacements (numero, zone) VALUES (:numero, :zone)";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            'numero' => $data['numero'],
            'zone' => $data['zone']
        ]);
    }

    /**
     * Met à jour un emplacement
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE emplacements SET numero = :numero, zone = :zone WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            'id' => $id,
            'numero' => $data['numero'],
            'zone' => $data['zone']
        ]);
    }

    /**
     * Supprime un emplacement
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM emplacements WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Vérifie si un emplacement existe
     */
    public function exists(string $numero, string $zone, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM emplacements WHERE numero = :numero AND zone = :zone";
        $params = ['numero' => $numero, 'zone' => $zone];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Récupère toutes les zones distinctes
     */
    public function getZones(): array
    {
        $sql = "SELECT DISTINCT zone FROM emplacements ORDER BY zone";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Assigne un emplacement à un brocanteur
     */
    public function assignToBrocanteur(int $emplacementId, int $brocanteurId): bool
    {
        $sql = "UPDATE brocanteurs SET emplacement_id = :emplacement_id WHERE id = :brocanteur_id";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            'emplacement_id' => $emplacementId,
            'brocanteur_id' => $brocanteurId
        ]);
    }

    /**
     * Libère un emplacement (retire l'assignation)
     */
    public function unassignFromBrocanteur(int $brocanteurId): bool
    {
        $sql = "UPDATE brocanteurs SET emplacement_id = NULL WHERE id = :brocanteur_id";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute(['brocanteur_id' => $brocanteurId]);
    }
}

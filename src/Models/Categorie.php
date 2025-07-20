<?php

namespace Models;

use Database\DatabaseConnection;
use PDO;

class Categorie
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = DatabaseConnection::getInstance()->getPdo();
    }

    /**
     * Récupère toutes les catégories
     */
    public function getAll(): array
    {
        $sql = "SELECT id, nom FROM categories ORDER BY nom ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère une catégorie par ID
     */
    public function getById(int $id): ?array
    {
        $sql = "SELECT * FROM categories WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Récupère une catégorie par nom
     */
    public function getByNom(string $nom): ?array
    {
        $sql = "SELECT * FROM categories WHERE nom = :nom";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['nom' => $nom]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Crée une nouvelle catégorie
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO categories (nom) VALUES (:nom)";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            'nom' => $data['nom']
        ]);
    }

    /**
     * Met à jour une catégorie
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE categories SET nom = :nom WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            'id' => $id,
            'nom' => $data['nom']
        ]);
    }

    /**
     * Supprime une catégorie
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM categories WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Vérifie si une catégorie existe par nom
     */
    public function nomExists(string $nom, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM categories WHERE nom = :nom";
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
     * Compte le nombre d'objets dans une catégorie
     */
    public function countObjets(int $id): int
    {
        $sql = "SELECT COUNT(*) FROM objets WHERE categorie_id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return (int)$stmt->fetchColumn();
    }
}

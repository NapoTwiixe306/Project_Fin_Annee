<?php

namespace Models;

use Database\DatabaseConnection;
use PDO;

class Objet
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = DatabaseConnection::getInstance()->getPdo();
    }

    /**
     * Récupère tous les objets avec leurs catégories et informations brocanteur
     */
    public function getAll(): array
    {
        $sql = "SELECT o.id, o.titre, o.description, o.photo_objet, o.created_at,
                       c.nom AS categorie, c.id AS categorie_id,
                       b.nom AS brocanteur_nom, b.prenom AS brocanteur_prenom, 
                       b.photo_profil, b.id AS brocanteur_id
                FROM objets o
                JOIN categories c ON o.categorie_id = c.id
                JOIN brocanteurs b ON o.brocanteur_id = b.id
                WHERE b.visible = TRUE AND b.emplacement_id IS NOT NULL
                ORDER BY o.created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recherche des objets avec filtres
     */
    public function search(string $search = '', string $filter = ''): array
    {
        $sql = "SELECT o.id, o.titre, o.description, o.photo_objet, o.created_at,
                       c.nom AS categorie, c.id AS categorie_id,
                       b.nom AS brocanteur_nom, b.prenom AS brocanteur_prenom, 
                       b.photo_profil, b.id AS brocanteur_id
                FROM objets o
                JOIN categories c ON o.categorie_id = c.id
                JOIN brocanteurs b ON o.brocanteur_id = b.id
                WHERE b.visible = TRUE AND b.emplacement_id IS NOT NULL";

        $params = [];

        if (!empty($search)) {
            $sql .= " AND (o.titre LIKE :search OR c.nom LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        if (!empty($filter)) {
            $sql .= " AND c.nom = :filter";
            $params['filter'] = $filter;
        }

        $sql .= " ORDER BY o.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un objet par ID
     */
    public function getById(int $id): ?array
    {
        $sql = "SELECT o.*, c.nom AS categorie, b.nom AS brocanteur_nom, b.prenom AS brocanteur_prenom
                FROM objets o
                JOIN categories c ON o.categorie_id = c.id
                JOIN brocanteurs b ON o.brocanteur_id = b.id
                WHERE o.id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Récupère les objets d'un brocanteur
     */
    public function getByBrocanteur(int $brocanteurId): array
    {
        $sql = "SELECT o.*, c.nom AS categorie_nom
                FROM objets o
                LEFT JOIN categories c ON o.categorie_id = c.id
                WHERE o.brocanteur_id = :brocanteur_id
                ORDER BY o.created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['brocanteur_id' => $brocanteurId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crée un nouvel objet
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO objets (brocanteur_id, titre, description, categorie_id, photo_objet, created_at) 
                VALUES (:brocanteur_id, :titre, :description, :categorie_id, :photo_objet, NOW())";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            'brocanteur_id' => $data['brocanteur_id'],
            'titre' => $data['titre'],
            'description' => $data['description'],
            'categorie_id' => $data['categorie_id'],
            'photo_objet' => $data['photo_objet'] ?? null
        ]);
    }

    /**
     * Met à jour un objet
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE objets 
                SET titre = :titre, description = :description, 
                    categorie_id = :categorie_id, photo_objet = :photo_objet, 
                    updated_at = NOW()
                WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            'id' => $id,
            'titre' => $data['titre'],
            'description' => $data['description'],
            'categorie_id' => $data['categorie_id'],
            'photo_objet' => $data['photo_objet'] ?? null
        ]);
    }

    /**
     * Supprime un objet
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM objets WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Vérifie si un objet appartient à un brocanteur
     */
    public function belongsToUser(int $objetId, int $brocanteurId): bool
    {
        $sql = "SELECT COUNT(*) FROM objets WHERE id = :objet_id AND brocanteur_id = :brocanteur_id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'objet_id' => $objetId,
            'brocanteur_id' => $brocanteurId
        ]);
        
        return $stmt->fetchColumn() > 0;
    }
}

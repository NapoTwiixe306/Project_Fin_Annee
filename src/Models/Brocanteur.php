<?php

namespace Models;

use Database\DatabaseConnection;
use PDO;

class Brocanteur
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = DatabaseConnection::getInstance()->getPdo();
    }

    /**
     * Récupère tous les brocanteurs avec leurs emplacements
     */
    public function getAll(): array
    {
        $sql = "SELECT b.id, b.nom, b.prenom, b.email, b.photo_profil, b.description,
                       e.numero AS emplacement, e.zone
                FROM brocanteurs b
                LEFT JOIN emplacements e ON b.emplacement_id = e.id
                ORDER BY b.nom, b.prenom";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recherche des brocanteurs avec filtres
     */
    public function search(string $search = '', string $filterBy = 'name', string $zone = ''): array
    {
        $sql = "SELECT b.id, b.nom, b.prenom, b.photo_profil, e.numero AS emplacement, e.zone, b.description
                FROM brocanteurs b
                JOIN emplacements e ON b.emplacement_id = e.id
                WHERE 1=1";

        $params = [];

        // Filtre par nom ou emplacement
        if (!empty($search)) {
            if ($filterBy === 'emplacement') {
                $sql .= " AND e.numero LIKE :search";
            } else {
                $sql .= " AND (b.nom LIKE :search OR b.prenom LIKE :search)";
            }
            $params['search'] = '%' . $search . '%';
        }

        // Filtre par zone
        if (!empty($zone)) {
            $sql .= " AND e.zone = :zone";
            $params['zone'] = $zone;
        }

        $sql .= " ORDER BY b.nom, b.prenom";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un brocanteur par ID
     */
    public function getById(int $id): ?array
    {
        $sql = "SELECT b.*, e.numero AS emplacement, e.zone
                FROM brocanteurs b
                LEFT JOIN emplacements e ON b.emplacement_id = e.id
                WHERE b.id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Récupère un brocanteur par email
     */
    public function getByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM brocanteurs WHERE email = :email";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Crée un nouveau brocanteur
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO brocanteurs (nom, prenom, email, password_hash, emplacement_id, description, photo_profil, visible, created_at) 
                VALUES (:nom, :prenom, :email, :password_hash, :emplacement_id, :description, :photo_profil, :visible, NOW())";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'emplacement_id' => $data['emplacement_id'] ?? null,
            'description' => $data['description'] ?? null,
            'photo_profil' => $data['photo_profil'] ?? null,
            'visible' => $data['visible'] ?? 1
        ]);
    }

    /**
     * Met à jour un brocanteur
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE brocanteurs 
                SET nom = :nom, prenom = :prenom, email = :email, 
                    emplacement_id = :emplacement_id, description = :description, 
                    photo_profil = :photo_profil, updated_at = NOW()
                WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            'id' => $id,
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => $data['email'],
            'emplacement_id' => $data['emplacement_id'] ?? null,
            'description' => $data['description'] ?? null,
            'photo_profil' => $data['photo_profil'] ?? null
        ]);
    }

    /**
     * Supprime un brocanteur
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM brocanteurs WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Vérifie si un email existe déjà
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM brocanteurs WHERE email = :email";
        $params = ['email' => $email];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Récupère les objets d'un brocanteur
     */
    public function getObjectsByBrocanteur(int $brocanteurId): array
    {
        $sql = "SELECT o.titre, o.description, o.photo_objet, c.nom as categorie
                FROM objets o
                LEFT JOIN categories c ON o.categorie_id = c.id
                WHERE o.brocanteur_id = :brocanteur_id ORDER BY o.titre";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['brocanteur_id' => $brocanteurId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}

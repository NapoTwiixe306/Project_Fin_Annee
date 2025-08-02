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
     * Récupère tous les brocanteurs visibles avec leurs emplacements
     */
    public function getAll(): array
    {
        $sql = "SELECT b.id, b.nom, b.prenom, b.email, b.photo_profil, b.description,
                       e.numero AS emplacement, e.zone
                FROM brocanteurs b
                LEFT JOIN emplacements e ON b.emplacement_id = e.id
                WHERE b.visible = TRUE
                ORDER BY b.nom, b.prenom";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recherche des brocanteurs visibles avec filtres
     */
    public function search(string $search = '', string $filterBy = 'name', string $zone = ''): array
    {
        $sql = "SELECT b.id, b.nom, b.prenom, b.photo_profil, e.numero AS emplacement, e.zone, b.description
                FROM brocanteurs b
                JOIN emplacements e ON b.emplacement_id = e.id
                WHERE b.visible = TRUE";

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
     * Récupère un brocanteur visible par ID
     */
    public function getById(int $id): ?array
    {
        $sql = "SELECT b.*, e.numero AS emplacement, e.zone
                FROM brocanteurs b
                LEFT JOIN emplacements e ON b.emplacement_id = e.id
                WHERE b.id = :id AND b.visible = TRUE";
        
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
        $sql = "SELECT o.id, o.titre, o.description, o.photo_objet, c.nom as categorie
                FROM objets o
                LEFT JOIN categories c ON o.categorie_id = c.id
                WHERE o.brocanteur_id = :brocanteur_id ORDER BY o.titre";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['brocanteur_id' => $brocanteurId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crée un token de réinitialisation de mot de passe
     */
    public function createResetToken(int $brocanteurId, string $token, string $expiresAt): bool
    {
        // Supprimer les anciens tokens pour ce brocanteur
        $this->deleteResetTokensByBrocanteur($brocanteurId);
        
        $sql = "INSERT INTO reset_tokens (brocanteur_id, token, expires_at) VALUES (:brocanteur_id, :token, :expires_at)";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            'brocanteur_id' => $brocanteurId,
            'token' => $token,
            'expires_at' => $expiresAt
        ]);
    }

    /**
     * Récupère un token de réinitialisation
     */
    public function getResetToken(string $token): ?array
    {
        $sql = "SELECT * FROM reset_tokens WHERE token = :token AND expires_at > NOW()";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['token' => $token]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Supprime un token de réinitialisation
     */
    public function deleteResetToken(string $token): bool
    {
        $sql = "DELETE FROM reset_tokens WHERE token = :token";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute(['token' => $token]);
    }

    /**
     * Supprime tous les tokens d'un brocanteur
     */
    public function deleteResetTokensByBrocanteur(int $brocanteurId): bool
    {
        $sql = "DELETE FROM reset_tokens WHERE brocanteur_id = :brocanteur_id";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute(['brocanteur_id' => $brocanteurId]);
    }

    /**
     * Met à jour le mot de passe d'un brocanteur
     */
    public function updatePassword(int $brocanteurId, string $password): bool
    {
        $sql = "UPDATE brocanteurs SET password_hash = :password_hash WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            'id' => $brocanteurId,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT)
        ]);
    }

    /**
     * Nettoie les tokens expirés
     */
    public function cleanExpiredTokens(): int
    {
        $sql = "DELETE FROM reset_tokens WHERE expires_at < NOW()";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->rowCount();
    }
}

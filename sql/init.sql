-- Table des zones géographiques
CREATE TABLE IF NOT EXISTS zones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) UNIQUE NOT NULL
);

-- Table des emplacements
CREATE TABLE IF NOT EXISTS emplacements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(50) UNIQUE NOT NULL,
    zone_id INT NOT NULL,
    FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE CASCADE
);

-- Table des brocanteurs
CREATE TABLE IF NOT EXISTS brocanteurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    description TEXT,
    visible BOOLEAN DEFAULT TRUE,
    role ENUM('brocanteur', 'admin') DEFAULT 'brocanteur',
    emplacement_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (emplacement_id) REFERENCES emplacements(id) ON DELETE SET NULL
);

-- Table des administrateurs
CREATE TABLE IF NOT EXISTS administrateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL
);

-- Table des catégories d'objets
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) UNIQUE NOT NULL
);

-- Table des objets en vente
CREATE TABLE IF NOT EXISTS objets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brocanteur_id INT NOT NULL,
    titre VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    categorie_id INT NOT NULL,  -- Assurez-vous que cette colonne existe bien
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (brocanteur_id) REFERENCES brocanteurs(id) ON DELETE CASCADE,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE CASCADE  -- C'est bien categorie_id et non categorie
);



-- Table des demandes d'inscription
CREATE TABLE IF NOT EXISTS inscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brocanteur_id INT NOT NULL,
    statut ENUM('en attente', 'validé', 'refusé') DEFAULT 'en attente',
    motif_refus TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (brocanteur_id) REFERENCES brocanteurs(id) ON DELETE CASCADE
);

-- Table pour les tokens de réinitialisation de mot de passe
CREATE TABLE IF NOT EXISTS reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brocanteur_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (brocanteur_id) REFERENCES brocanteurs(id) ON DELETE CASCADE
);

-- Table pour l'historique des actions administratives
CREATE TABLE IF NOT EXISTS historique_admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES administrateurs(id) ON DELETE CASCADE
);


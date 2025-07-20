-- Script pour insérer des catégories de base dans la table categories
USE Q240237;

INSERT INTO categories (nom) VALUES 
('Violons'),
('Violoncelles'),
('Partitions'),
('Accessoires'),
('Archets'),
('Étuis et housses'),
('Métronomes'),
('Cordes'),
('Lutrins'),
('Objets de collection')
ON DUPLICATE KEY UPDATE nom = VALUES(nom);

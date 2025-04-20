# ✅ To-Do List – Projet Foire aux Puces

## 🏗️ STRUCTURE GÉNÉRALE
- [x] `config.php` pour la connexion PDO  
- [x] `bdd.php` pour les requêtes réutilisables  
- [x] Dossier `/inc/` pour composants communs  
- [ ] `navbar.inc.php` pour menu commun  
- [ ] `footer.inc.php` pour le pied de page  

## 👥 AUTHENTIFICATION
- [x] Connexion (`connexion.php`)  
- [x] Déconnexion fonctionnelle (`logout.php`)  
- [x] Inscription brocanteur (`inscription.php`)  
- [ ] Réinitialisation mot de passe (`reset_password.php`)  

## 👤 BROCANTEUR – ESPACE PRIVÉ
- [x] Ajouter un objet  
- [x] Supprimer un objet  
- [x] Modifier un objet  
- [x] Éditer son profil (infos, visibilité, mot de passe)  
- [ ] Afficher le statut d’inscription (emplacement attribué ou pas)  

## 🛍️ OBJETS – PUBLIC
- [x] Affichage d’objets par catégorie  
- [ ] Affichage **de 3 objets aléatoires** en page d’accueil  
- [ ] Page d’un objet individuel (`objet.php`)  
- [ ] Recherche :
  - [ ] Par mot-clé (titre/description)  
  - [ ] Par brocanteur  
  - [ ] Sauvegarde des filtres avec cookies  

## 📣 BROCANTEURS – PUBLIC
- [x] Liste des brocanteurs  
- [ ] Tri par nom  
- [ ] Affichage par zone  
- [ ] Affichage fiche brocanteur complète  
- [ ] Affichage objets associés à un brocanteur  

## 🛠️ ADMINISTRATEUR – ESPACE PRIVÉ
- [x] Page dashboard (`admin_dashboard.php`)  
- [ ] Liste des brocanteurs inscrits  
- [ ] Attribution des emplacements  
- [ ] Modification / annulation d’emplacement  
- [ ] Suppression d’un brocanteur (sans emplacement uniquement)  
- [ ] Vue d’ensemble des statuts d’inscription  

## 📐 BASE DE DONNÉES
- [x] Tables : `brocanteurs`, `objets`, `zones`, `catégories`, `emplacements`  
- [x] Données de test  
- [ ] Contraintes (foreign keys, `NOT NULL`, etc.)  
- [ ] Suppression en cascade  

## 💅 DESIGN & RESPONSIVE
- [ ] SCSS propre et compilé  
- [ ] Responsive mobile/tablette  
- [ ] Styliser :
  - [ ] Formulaires  
  - [ ] Messages de feedback  
  - [ ] Cartes objet & brocanteur  

## 🧪 SÉCURITÉ & TESTS
- [ ] `htmlspecialchars` sur toutes les sorties  
- [ ] Protection des pages selon rôle (`$_SESSION`)  
- [ ] Validation serveur  
- [ ] Redirections sécurisées  
- [ ] Tests :
  - [ ] Login invalide  
  - [ ] Accès sans session  
  - [ ] Insertion malveillante  
  - [ ] Suppression logique et cohérente
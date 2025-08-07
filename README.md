# 🏪 Foire aux Puces - Supra Brocante

## 📋 Vue d'ensemble

**Foire aux Puces** est une plateforme web permettant aux brocanteurs de gérer leurs objets en vente et aux visiteurs de découvrir les stands et articles disponibles. Le projet est développé en PHP avec une architecture MVC et utilise SCSS pour le styling.

## 🎯 Fonctionnalités

### Utilisateurs

- **Visiteurs** peuvent :
  - Parcourir les objets en vente.
  - Voir les détails des brocanteurs.
  - Contacter les administrateurs via une page dédiée.

- **Brocanteurs** peuvent :
  - S'inscrire et se connecter pour gérer leurs objets de vente.
  - Modifier leur profil et leurs objets.
  - Visualiser les emplacements occupés.

- **Administrateurs** peuvent :
  - Gérer les comptes brocanteurs et les emplacements.
  - Gérer les catégories et objets en vente.

## 🏗️ Architecture Technique

### Technologies utilisées
- **Backend** : PHP 8.0+
- **Base de données** : MySQL
- **Frontend** : HTML5, CSS3, SCSS
- **Architecture** : MVC (Model-View-Controller)
- **Sécurité** : Sessions PHP, hachage des mots de passe

### Structure du projet
```
Project_Fin_Annee/
├── src/                    # Code source MVC
│   ├── Controllers/        # Contrôleurs
│   ├── Models/            # Modèles de données
│   ├── Services/          # Services (Session, Cookie)
│   └── Views/             # Vues
├── inc/                   # Composants inclus
│   ├── Dashboard/         # Pages du dashboard
│   └── config.php         # Configuration
├── scss/                  # Styles SCSS
├── css/                   # CSS compilé
├── uploads/               # Fichiers uploadés
├── sql/                   # Scripts SQL
└── docs/                  # Documentation
```

## 📊 Évaluation des fonctionnalités
- **Note globale : 7.5/10**
- Fonctionnalités essentielles implémentées, cependant, certaines fonctionnalités avancées comme les filtres de recherche et l'interface responsive complète sont manquantes.

## 🔧 Installation et configuration

### Prérequis
- PHP 8.0 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web (Apache/Nginx)
- Composer (pour l'autoload)

### Installation

1. **Cloner le projet**
   ```bash
   git clone [url-du-repo]
   cd Project_Fin_Annee
   ```

2. **Configurer la base de données**
   ```bash
   mysql -u root -p
   CREATE DATABASE Q240237;
   USE Q240237;
   ```

3. **Configurer la connexion**
   Modifier `inc/config.php` avec les paramètres de votre base de données.

4. **Compiler les styles SCSS**
   ```bash
   npm install -g sass
   sass scss/App.scss css/styles.css
   ```

5. **Configurer les permissions**
   ```bash
   chmod 755 uploads/
   chmod 755 uploads/profils/
   ```

## 🚀 Utilisation
- **Accueil** : `index.php`
- **Brocanteurs** : `brocanteurs.php`
- **Objets en vente** : `objet.php`
- **Contact** : `contact.php`

## 🔒 Sécurité
- Hachage des mots de passe avec bcrypt.
- Protection contre l'injection SQL avec PDO.
- Hébergement sécurisé, avec priorité à l'échappement des sorties HTML.

## 🎨 Design et Interface
- Framework CSS : SCSS personnalisé.
- Composants : Boutons, formulaires, cartes.
- Responsive : Partiellement implémenté.

## 🔄 Maintenance
- **Scripts de maintenance**
  - `maintenance/cleanup_tokens.php` : Nettoyage des tokens expirés.

---

*Dernière mise à jour : Août 2025*
*Version : 2.0*
❌ **Fonctionnalités de sécurité avancées**
❌ **Tests et documentation**

**Note finale : 7.5/10** - Projet fonctionnel avec une base solide, nécessitant des améliorations pour atteindre un niveau production.

---

*Dernière mise à jour : Décembre 2024*
*Version : 1.0* 
# 🏪 Foire aux Puces - Supra Brocante

## 📋 Vue d'ensemble

**Foire aux Puces** est une plateforme web permettant aux brocanteurs de gérer leurs objets en vente et aux visiteurs de découvrir les stands et articles disponibles. Le projet est développé en PHP avec une architecture MVC et utilise SCSS pour le styling.

## 🎯 Cahier des charges

Le projet est basé sur le cahier des charges version 1.3 (21-11-2024) qui définit les fonctionnalités attendues pour la gestion d'une foire aux puces.

## 🏗️ Architecture technique

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

### 🎯 Note globale : **7.5/10**

### ✅ Fonctionnalités complètement implémentées (9/10)

#### **UC-A.2 Réinitialiser mot de passe**
- ✅ Vérification de l'existence de l'email
- ✅ Génération de tokens sécurisés
- ✅ Envoi d'email avec lien de réinitialisation
- ✅ Validation du token et expiration (1 heure)
- ✅ Interface utilisateur complète
- ✅ Messages d'erreur appropriés

#### **UC-A.4 Consulter les brocanteurs**
- ✅ Liste des brocanteurs visibles
- ✅ Fiche complète avec nom, prénom, photo, description
- ✅ Affichage de l'emplacement attribué (numéro + zone)
- ✅ Aperçus des objets proposés avec liens
- ✅ Filtrage par visibilité

#### **UC-B.1 Éditer profil**
- ✅ Validation complète des champs (email unique, format valide)
- ✅ Messages d'erreur explicites
- ✅ Persistance des données en cas d'erreur
- ✅ Formulaire de modification de mot de passe complet
- ✅ Validation de complexité des mots de passe

#### **Fonctionnalités d'administration**
- ✅ Dashboard administrateur
- ✅ Attribution/annulation d'emplacements
- ✅ Suppression sécurisée des brocanteurs
- ✅ Gestion des objets

### ⚠️ Fonctionnalités partiellement implémentées (6/10)

#### **UC-A.1 Connexion/Inscription**
- ✅ Connexion fonctionnelle
- ✅ Inscription avec validation
- ❌ Gestion des rôles utilisateur limitée

#### **UC-B.2 Gestion des objets**
- ✅ Ajout, modification, suppression d'objets
- ✅ Upload de photos
- ✅ Catégorisation
- ❌ Recherche avancée limitée

#### **UC-C.1 Interface publique**
- ✅ Page d'accueil avec objets aléatoires
- ✅ Liste des brocanteurs
- ✅ Page de contact
- ❌ Recherche et filtres avancés

### ❌ Fonctionnalités manquantes ou incomplètes (4/10)

#### **Recherche et filtres**
- ❌ Recherche par mot-clé avancée
- ❌ Filtres par zone/emplacement
- ❌ Sauvegarde des filtres avec cookies
- ❌ Tri par différents critères

#### **Interface utilisateur**
- ❌ Design responsive complet
- ❌ Navigation mobile optimisée
- ❌ Messages de feedback uniformes

#### **Sécurité et validation**
- ❌ Protection CSRF complète
- ❌ Validation côté client
- ❌ Gestion des erreurs 404/500

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
# Créer la base de données
mysql -u root -p
CREATE DATABASE Q240237;
USE Q240237;

# Importer le schéma
mysql -u root -p Q240237 < sql/init.sql
mysql -u root -p Q240237 < sql/insert_categories.sql
```

3. **Configurer la connexion**
```php
// inc/config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'Q240237');
define('DB_USER', 'votre_utilisateur');
define('DB_PASS', 'votre_mot_de_passe');
```

4. **Compiler les styles SCSS**
```bash
# Installer Sass si nécessaire
npm install -g sass

# Compiler les styles
sass scss/App.scss css/styles.css
```

5. **Configurer les permissions**
```bash
chmod 755 uploads/
chmod 755 uploads/profils/
```

## 🚀 Utilisation

### Accès public
- **Accueil** : `index.php`
- **Brocanteurs** : `brocanteurs.php`
- **Objets en vente** : `objet.php`
- **Contact** : `contact.php`

### Espace brocanteur
- **Connexion** : `connexion.php`
- **Inscription** : `inscription.php`
- **Dashboard** : `admin_dashboard.php`

### Administration
- **Dashboard admin** : `admin_dashboard.php?page=admin`
- **Gestion des emplacements** : Interface intégrée

## 📁 Structure de la base de données

### Tables principales
- `brocanteurs` : Utilisateurs brocanteurs
- `objets` : Articles en vente
- `categories` : Catégories d'objets
- `emplacements` : Emplacements de la foire
- `zones` : Zones géographiques
- `reset_tokens` : Tokens de réinitialisation

### Relations
- Un brocanteur peut avoir plusieurs objets
- Un objet appartient à une catégorie
- Un brocanteur peut avoir un emplacement
- Un emplacement appartient à une zone

## 🔒 Sécurité

### Implémenté
- ✅ Hachage des mots de passe (bcrypt)
- ✅ Protection contre l'injection SQL (PDO)
- ✅ Échappement des sorties HTML
- ✅ Validation des sessions
- ✅ Tokens sécurisés pour réinitialisation

### À améliorer
- ❌ Protection CSRF
- ❌ Rate limiting
- ❌ Validation côté client
- ❌ Logs de sécurité

## 🎨 Design et interface

### Système de design
- **Framework CSS** : SCSS personnalisé
- **Composants** : Boutons, formulaires, cartes
- **Responsive** : Partiellement implémenté
- **Accessibilité** : Basique

### Pages principales
- **Accueil** : Présentation + objets aléatoires
- **Brocanteurs** : Liste avec filtres
- **Objets** : Catalogue avec recherche
- **Dashboard** : Interface de gestion

## 📈 Fonctionnalités avancées

### Système de mailing
- ✅ Envoi d'emails de contact
- ✅ Réinitialisation de mot de passe
- ✅ Notifications automatiques

### Gestion des fichiers
- ✅ Upload de photos de profil
- ✅ Upload de photos d'objets
- ✅ Validation des types de fichiers

### Système de sessions
- ✅ Authentification sécurisée
- ✅ Gestion des rôles
- ✅ Déconnexion automatique

## 🐛 Problèmes connus

### Fonctionnalités manquantes
1. **Recherche avancée** : Filtres par prix, date, zone
2. **Pagination** : Pour les listes longues
3. **Notifications** : Système de notifications en temps réel
4. **API** : Interface REST pour applications mobiles

### Améliorations techniques
1. **Performance** : Cache des requêtes fréquentes
2. **SEO** : URLs optimisées, meta tags
3. **Tests** : Tests unitaires et d'intégration
4. **Documentation** : Documentation API

## 🔄 Maintenance

### Scripts de maintenance
- `maintenance/cleanup_tokens.php` : Nettoyage des tokens expirés

### Tâches cron recommandées
```bash
# Nettoyer les tokens expirés (toutes les heures)
0 * * * * php /path/to/project/maintenance/cleanup_tokens.php
```

## 📝 Notes de développement

### Architecture MVC
Le projet suit une architecture MVC avec :
- **Models** : Logique métier et accès aux données
- **Views** : Interface utilisateur
- **Controllers** : Gestion des requêtes et coordination

### Services
- **SessionService** : Gestion des sessions utilisateur
- **CookieService** : Gestion des cookies de préférences

### Sécurité
- Validation côté serveur pour toutes les entrées
- Échappement HTML pour toutes les sorties
- Hachage sécurisé des mots de passe

## 🎯 Conclusion

Le projet **Foire aux Puces** présente une base solide avec les fonctionnalités essentielles implémentées. Les points forts incluent :

✅ **Fonctionnalités core complètes** (authentification, gestion des objets, administration)
✅ **Architecture MVC bien structurée**
✅ **Sécurité de base implémentée**
✅ **Interface utilisateur fonctionnelle**

Les principales améliorations à apporter concernent :
❌ **Recherche et filtres avancés**
❌ **Interface responsive complète**
❌ **Fonctionnalités de sécurité avancées**
❌ **Tests et documentation**

**Note finale : 7.5/10** - Projet fonctionnel avec une base solide, nécessitant des améliorations pour atteindre un niveau production.

---

*Dernière mise à jour : Décembre 2024*
*Version : 1.0* 
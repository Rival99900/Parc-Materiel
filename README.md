# 📦 Parc Matériel SLAM-PHP

Application PHP de gestion du parc matériel informatique.

## 📁 Structure du projet

```
SLAM-PHP/
├── index.php              # Point d'entrée (redirige vers Public/)
│
├── 📁 Public/             # Pages publiques (sans authentification)
│   ├── index.php          # Accueil - Liste tous les matériels
│   ├── details.php        # Page de détails d'un matériel
│   ├── recherche.php      # Page de recherche multicritères
│   ├── login.php          # Formulaire de connexion admin
│   └── stats.php          # Statistiques du parc
│
├── 📁 Admin/              # Pages d'administration (authentification requise)
│   ├── index.php          # Dashboard admin
│   ├── ajouter.php        # Formulaire pour ajouter un matériel
│   ├── modifier.php       # Formulaire pour modifier un matériel
│   ├── supprimer.php      # Formulaire pour supprimer un matériel
│   └── logout.php         # Déconnexion
│
├── 📁 Config/             # Fichiers de configuration
│   └── credentials.php    # Identifiants MySQL
│
├── 📁 Database/           # Scripts de base de données
│   └── creation.sql       # Script SQL de création des tables
│
└── README.md              # Ce fichier

```

## 🚀 Installation

### 1. Créer la base de données
Exécutez le script SQL situé dans `Database/creation.sql` pour créer les tables :
- **UTILISATEURS** : Stocké les comptes créer par les admins dans la base de donnée
- **TYPEE** : Types de matériel (PC, Écran, CPU, RAM, etc.)
- **MATERIEL** : Inventaire des équipements

### 2. Configurer les identifiants
Modifiez le fichier `Config/credentials.php` avec vos identifiants MySQL :
```php
$host = "127.0.0.1";       // Serveur MySQL
$dbname = "parc_materiel";  // Nom de la base
$user = "";             // Utilisateur
$password = "";             // Mot de passe
```

### 3. Accéder à l'application
- **Accueil public** : `http://localhost/SLAM-PHP/`
- **Connexion admin** : `http://localhost/SLAM-PHP/Public/login.php`
- **Admin panel** : `http://localhost/SLAM-PHP/Admin/index.php`

## 📝 Description

Voici la base de données permettant de représenter l'ensemble du matériel présent dans le parc informatique.
Elle s'appuie sur un Modèle Conceptuel de Données (MCD) afin de structurer : les différents types de matériel, leurs caractéristiques, leurs relations hiérarchiques (notamment la composition d'un PC et de ses composants). Une application PHP accompagne cette base de données.
Elle permet de se connecter au serveur MySQL et d'afficher sous forme de tableau l'ensemble du parc matériel, en récupérant les informations directement depuis la base.
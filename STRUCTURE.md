# 🏗️ Structure organisée du projet SLAM-PHP
```
SLAM-PHP/ (racine)
│
├── index.php              🚀 Point d'entrée simple
├── README.md              📖 Documentation complète
│
├── 📁 Public/             📦 Pages publiques (sans auth)
│   ├── index.php          🏠 Accueil principal
│   ├── details.php        📋 Détails d'un matériel
│   ├── recherche.php      🔍 Recherche avancée
│   ├── stats.php          📊 Statistiques
│   └── login.php          🔐 Connexion admin
│
├── 📁 Admin/              🛡️ Pages protégées (auth requise)
│   ├── index.php          ⚙️ Dashboard
│   ├── ajouter.php        ➕ Ajouter matériel
│   ├── modifier.php       ✏️ Modifier matériel
│   ├── supprimer.php      🗑️ Supprimer matériel
│   └── logout.php         🚪 Déconnexion
│
├── 📁 Config/             ⚙️ Configuration applicative
│   └── credentials.php    🔑 Identifiants MySQL
│
└── 📁 Database/           📚 Scripts et données
    └── creation.sql       🗄️ DDL création tables
```
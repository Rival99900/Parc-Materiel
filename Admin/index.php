<?php
session_start();

// Vérifie que l'utilisateur est connecté avant d'accéder au panel admin
if (!isset($_SESSION['user_connected']) || $_SESSION['user_connected'] !== true) {
    // Redirection vers la page de connexion si pas connecté
    header("Location: ../Public/login.php");
    exit();
}

// Charge les identifiants de la base de données
require "../Config/credentials.php";

try {
    // Crée une connexion PDO avec la base de données MySQL
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $password);
} catch (PDOException $e) {
    // Affiche un message d'erreur si la connexion échoue
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupère tous les types de matériels disponibles pour les listes
$sqlTypes = "
    SELECT 
        idType,   
        libelle   
    FROM TYPEE 
    ORDER BY libelle
";
$stmtTypes = $pdo->query($sqlTypes);
$types = [];
// Stocke tous les types dans un tableau
foreach ($stmtTypes as $type) {
    $types[] = $type;
}
?>

<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <title>Administration - Parc Matériel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, serif;
            background: #f4f4f4;
            padding: 20px;
        }
        .header {
            background: #333;
            color: white;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            font-size: 24px;
        }
        .user-info {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        .logout-btn {
            background: #d9534f;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 3px;
        }
        .logout-btn:hover {
            background: #c9302c;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .menu {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .menu a {
            display: inline-block;
            margin-right: 15px;
            padding: 10px 20px;
            background: #333;
            color: white;
            text-decoration: none;
            border-radius: 3px;
        }
        .menu a:hover {
            background: #555;
        }
        .content {
            background: white;
            padding: 20px;
            border-radius: 5px;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .back-link {
            margin-bottom: 20px;
        }
        .back-link a {
            color: #0066cc;
            text-decoration: none;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Administration - Parc Matériel</h1>
    <div class="user-info">
        <span>Connecté en tant que : <?php print($_SESSION['user_login']); ?></span>
        <a href="logout.php" class="logout-btn">Déconnexion</a>
    </div>
</div>

<div class="container">
    <div class="menu">
        <a href="ajouter.php">Ajouter un matériel</a>
        <a href="modifier.php">Modifier un matériel</a>
        <a href="supprimer.php">Supprimer un matériel</a>
        <a href="../Public/index.php">Retour à l'accueil public</a>
    </div>
    
    <div class="content">
        <h2>Bienvenue dans l'administration</h2>
        <p>Sélectionnez une action dans le menu ci-dessus pour gérer le parc matériel.</p>
    </div>
</div>

</body>
</html>

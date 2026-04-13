<?php
require "../Config/credentials.php";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $password);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Requête pour compter le nombre total de matériels dans la base de données
$sqlCount = "
    SELECT COUNT(*) as total
    FROM MATERIEL
";
$stmtCount = $pdo->query($sqlCount);
$count = $stmtCount->fetch();

/* 
Requête pour obtenir les statistiques par type
Affiche chaque type et le nombre de matériels de ce type
LEFT JOIN pour inclure les types même s'ils ont 0 matériel
*/
$sqlTypes = "
    SELECT 
        idType,                                
        libelle,                               
        COUNT(m.idMateriel) as nb              
    FROM TYPEE t 
    LEFT JOIN MATERIEL m ON t.idType = m.idType 
    GROUP BY t.idType, t.libelle 
    ORDER BY t.libelle
";
$stmtTypes = $pdo->query($sqlTypes);
$types = [];
// Stocke toutes les statistiques par type
foreach ($stmtTypes as $type) {
    $types[] = $type;
}
?>

<!DOCTYPE html>
<html lang='fr'>
<head>
    <link rel="icon" type="image/png" href="Logo.png">
    <meta charset='UTF-8'>
    <title>À propos - Parc Matériel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, serif;
            background: #f4f4f4;
            padding: 0;
        }
        .header {
            background: #333;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            font-size: 24px;
        }
        .header-links {
            display: flex;
            gap: 15px;
        }
        .header-links a {
            color: white;
            text-decoration: none;
            padding: 8px 12px;
            background: rgba(255,255,255,0.1);
            border-radius: 3px;
        }
        .header-links a:hover {
            background: rgba(255,255,255,0.2);
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .box {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .box h2 {
            color: #333;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        th {
            background: #333;
            color: white;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #0066cc;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
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
    <h1>Statistiques</h1>
    <div class="header-links">
        <a href="index.php">Accueil</a>
        <a href="recherche.php">Recherche</a>
    </div>
</div>

<div class="container">
    <div class="box">
        <h2>Total d'équipements</h2>
        <div class="stat-value"><?php print($count['total']); ?></div>
    </div>
    
    <div class="box">
        <h2>Équipements par type</h2>
        <table>
            <tr>
                <th>Type</th>
                <th>Nombre</th>
            </tr>
            <?php 
            foreach ($types as $type) {
                print("<tr>");
                print("<td>" . $type['libelle'] . "</td>");
                print("<td>" . $type['nb'] . "</td>");
                print("</tr>");
            }
            ?>
        </table>
    </div>
    
    <div class="back-link">
        <a href="index.php">Retour à l'accueil</a>
    </div>
</div>

</body>
</html>

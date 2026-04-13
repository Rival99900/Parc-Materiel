<?php
session_start();

require "../Config/credentials.php";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $password);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Requête SQL pour récupérer tous les matériels avec leurs informations complètes
$sql = "
    SELECT 
        m.idMateriel,           
        m.nom,                  
        m.annee,                
        m.details,              
        t.libelle AS type,      
        p.nom AS parent         
    FROM MATERIEL m
    JOIN TYPEE t ON m.idType = t.idType
    LEFT JOIN MATERIEL p ON m.idParent = p.idMateriel
    ORDER BY m.idMateriel
";

// Exécute la requête et récupère tous les résultats
$stmt = $pdo->query($sql);
$materiels = [];
// Boucle à travers tous les résultats et les stocke dans un tableau
foreach ($stmt as $m) {
    $materiels[] = $m;
}
?>

<!DOCTYPE html>
<html lang='fr'>
<head>
    <link rel="icon" type="image/png" href="../Logo.png">
    <meta charset='UTF-8'>
    <title>Parc matériel</title>
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
        .nav {
            background: #555;
            padding: 10px 20px;
            display: flex;
            gap: 20px;
        }
        .nav a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }
        .nav a:hover {
            color: #ffc107;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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
        tr:hover {
            background: #f0f0f0;
        }
        h1 {
            margin-bottom: 20px;
            color: #333;
        }
        a {
            color: #0066cc;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>📦 Parc Matériel</h1>
    <div class="header-links">
        <a href="recherche.php">Recherche</a>
        <?php 
        if (isset($_SESSION['user_connected']) && $_SESSION['user_connected'] === true) {
            print("<a href='../Admin/index.php'>Administration</a>");
            print("<a href='../Admin/logout.php'>Déconnexion</a>");
        } else {
            print("<a href='login.php'>Connexion Admin</a>");
        }
        ?>
    </div>
</div>

<div class="nav">
    <a href="index.php">Tous les équipements</a>
</div>

<div class="container">
<h1>Liste du parc matériel</h1>

<table>
    <tr>
        <th>ID</th>
        <th>Nom</th>
        <th>Année</th>
        <th>Détails</th>
        <th>Type</th>
        <th>Appartient à</th>
        <th>Action</th>
    </tr>

    <?php 
    foreach ($materiels as $m) {
        print("<tr>\n");
        print("<td>" . $m['idMateriel'] . "</td>\n");
        print("<td>" . $m['nom'] . "</td>\n");
        print("<td>" . (isset($m['annee']) ? $m['annee'] : "—") . "</td>\n");
        
        
        if (isset($m['details'])) {
            print("<td>" . $m['details'] . "</td>\n");
        } else {
            print("<td>—</td>\n");
        }
        
        print("<td>" . $m['type'] . "</td>\n");
        
        if (isset($m['parent'])) {
            print("<td>" . $m['parent'] . "</td>\n");
        } else {
            print("<td>—</td>\n");
        }
        
        print("<td><a href='details.php?id=" . $m['idMateriel'] . "'>Voir détails</a></td>\n");
        print("</tr>\n");
    } 
    ?>

</table>

</div>

</body>
</html>

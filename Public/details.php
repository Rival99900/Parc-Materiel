<?php
session_start();

require "../Config/credentials.php";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $password);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}


// Vérifie que l'utilisateur a fourni un ID de matériel
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    /* 
    Requête pour récupérer les informations complètes du matériel sélectionné
    Récupère le matériel ET son type associé via une jointure
    */
    $sql = "
        SELECT 
            m.*,                
            t.libelle           
        FROM MATERIEL m 
        JOIN TYPEE t ON m.idType = t.idType 
        WHERE m.idMateriel = :id
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $materiel = $stmt->fetch();

    if ($materiel) {
?>

<!DOCTYPE html>
<html lang='fr'>
<head>
    <link rel="icon" type="image/png" href="../Logo.png">
    <meta charset='UTF-8'>
    <title>Détails - Parc Matériel</title>
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
        .details-box {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .details-box h1 {
            color: #333;
            margin-bottom: 15px;
        }
        .detail-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: bold;
            color: #333;
            min-width: 150px;
        }
        .detail-value {
            color: #666;
        }
        h3 {
            color: #333;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        ul {
            list-style-position: inside;
            color: #666;
        }
        ul li {
            padding: 5px 0;
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
        .no-data {
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>📦 Détails du Matériel</h1>
    <div class="header-links">
        <a href="index.php">Accueil</a>
        <a href="recherche.php">Recherche</a>
    </div>
</div>

<div class="container">
    <div class="details-box">
        <h1><?php print($materiel['nom']); ?></h1>
        
        <div class="detail-row">
            <div class="detail-label">ID :</div>
            <div class="detail-value"><?php print($materiel['idMateriel']); ?></div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">Année :</div>
            <div class="detail-value"><?php print(isset($materiel['annee']) ? $materiel['annee'] : "Non renseignée"); ?></div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">Type :</div>
            <div class="detail-value"><?php print($materiel['libelle']); ?></div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">Détails :</div>
            <div class="detail-value"><?php print(isset($materiel['details']) && $materiel['details'] !== "" ? $materiel['details'] : "Aucun détail"); ?></div>
        </div>
        
        <?php 
        // Si le matériel a un parent, on récupère ses informations
        if (isset($materiel['idParent']) && $materiel['idParent'] !== null) {
            // Requête pour récupérer le nom du matériel parent
            $sqlParent = "
                SELECT nom 
                FROM MATERIEL 
                WHERE idMateriel = :id
            ";
            $stmtParent = $pdo->prepare($sqlParent);
            $stmtParent->execute(['id' => $materiel['idParent']]);
            $parent = $stmtParent->fetch();
            if ($parent) {
                print("<div class='detail-row'>");
                print("<div class='detail-label'>Appartient à :</div>");
                print("<div class='detail-value'><a href='details.php?id=" . $materiel['idParent'] . "'>" . $parent['nom'] . "</a></div>");
                print("</div>");
            }
        }
        ?>
    </div>
    
    <div class="details-box">
        <?php 
        /* 
        Requête pour récupérer tous les composants associés (enfants) du matériel actuel
        Cherche tous les matériels qui ont cet ID comme parent
        */
        $sqlEnfants = "
            SELECT 
                idMateriel,  
                nom          
            FROM MATERIEL 
            WHERE idParent = :id
        ";
        $stmtEnfants = $pdo->prepare($sqlEnfants);
        $stmtEnfants->execute(['id' => $id]);
        $composants = [];
        // Stocke tous les composants dans un tableau
        foreach ($stmtEnfants as $comp) {
            $composants[] = $comp;
        }

        // Affiche le titre de la section
        print("<h3>Composants associés :</h3>");
        // Vérifie s'il y a des composants
        if (count($composants) > 0) {
            print("<ul>");
            // Affiche chaque composant avec un lien vers ses détails
            foreach ($composants as $comp) {
                print("<li><a href='details.php?id=" . $comp['idMateriel'] . "'>" . $comp['nom'] . "</a></li>");
            }
            print("</ul>");
        } else {
            // Message si aucun composant n'existe
            print("<p class='no-data'>Aucun composant pour cet équipement.</p>");
        }
        ?>
    </div>
    
    <div class="back-link">
        <a href="index.php">Retour à la liste</a>
    </div>
</div>

</body>
</html>

<?php 
    } else {
        print("Matériel non trouvé.");
    }
} else {
    print("Aucun matériel sélectionné.");
}
?>

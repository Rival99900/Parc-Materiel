<?php
require "../Config/credentials.php";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $password);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Stocke le statut de la recherche pour afficher ou non les résultats
$resultats = [];
$recherche_effectuee = false;

// Traite la recherche si le formulaire est soumis
if (isset($_POST['rechercher'])) {
    $recherche_effectuee = true;
    // Récupère les paramètres de recherche saisis par l'utilisateur
    $nom = isset($_POST['nom']) ? trim($_POST['nom']) : "";
    $type = isset($_POST['type']) ? $_POST['type'] : "";
    $annee = isset($_POST['annee']) ? $_POST['annee'] : "";
    
    // Requête SQL de base pour la recherche multicritères
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
        WHERE 1=1
    ";
    
    $params = [];
    
    // Ajoute condition si recherche par nom
    if ($nom !== "") {
        $sql .= " AND m.nom LIKE :nom";
        $params[':nom'] = "%" . $nom . "%";
    }
    
    // Ajoute condition si recherche par type
    if ($type !== "") {
        $sql .= " AND m.idType = :type";
        $params[':type'] = $type;
    }
    
    // Ajoute condition si recherche par année
    if ($annee !== "") {
        $sql .= " AND m.annee = :annee";
        $params[':annee'] = $annee;
    }
    
    // Trie les résultats par nom
    $sql .= " ORDER BY m.nom";
    
    // Exécute la requête avec les paramètres
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    // Stocke tous les résultats dans un tableau
    foreach ($stmt as $row) {
        $resultats[] = $row;
    }
}

// Récupère tous les types pour le filtre de recherche
$sqlTypes = "
    SELECT 
        idType,  
        libelle  
    FROM TYPEE 
    ORDER BY libelle
";
$stmtTypes = $pdo->query($sqlTypes);
$types = [];
// Stocke tous les types disponibles
foreach ($stmtTypes as $type) {
    $types[] = $type;
}

// Récupère toutes les années disponibles pour le filtre
$sqlAnnees = "
    SELECT DISTINCT 
        annee  
    FROM MATERIEL 
    WHERE annee IS NOT NULL 
    ORDER BY annee DESC
";
$stmtAnnees = $pdo->query($sqlAnnees);
$annees = [];
// Stocke toutes les années
foreach ($stmtAnnees as $annee) {
    $annees[] = $annee;
}
?>

<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <title>Recherche multicritère - Parc Matériel</title>
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
        .home-link a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
        }
        .home-link a:hover {
            text-decoration: underline;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .search-form {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .search-form h2 {
            margin-bottom: 15px;
            color: #333;
        }
        .form-group {
            display: inline-block;
            margin-right: 20px;
            margin-bottom: 15px;
            vertical-align: top;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
            font-size: 14px;
        }
        .form-group input,
        .form-group select {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 3px;
            font-family: Arial, serif;
            font-size: 14px;
            min-width: 150px;
        }
        .form-group button {
            padding: 8px 20px;
            background: #333;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 23px;
        }
        .form-group button:hover {
            background: #555;
        }
        .results {
            background: white;
            padding: 20px;
            border-radius: 5px;
        }
        .results h2 {
            margin-bottom: 15px;
            color: #333;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            background: white;
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
        .no-results {
            color: #666;
            font-style: italic;
            padding: 20px;
            text-align: center;
        }
        .back-link {
            margin-top: 20px;
            text-align: center;
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
    <h1>Recherche multicritère</h1>
    <div class="home-link">
        <a href="index.php">Accueil</a>
    </div>
</div>

<div class="container">
    <div class="search-form">
        <h2>Critères de recherche</h2>
        <form method="post">
            <div class="form-group">
                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" placeholder="Contient...">
            </div>
            
            <div class="form-group">
                <label for="type">Type :</label>
                <select id="type" name="type">
                    <option value="">-- Tous les types --</option>
                    <?php 
                    foreach ($types as $t) {
                        print("<option value='" . $t['idType'] . "'>" . $t['libelle'] . "</option>");
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="annee">Année :</label>
                <select id="annee" name="annee">
                    <option value="">-- Toutes les années --</option>
                    <?php 
                    foreach ($annees as $a) {
                        if (isset($a['annee']) && $a['annee'] !== null) {
                            print("<option value='" . $a['annee'] . "'>" . $a['annee'] . "</option>");
                        }
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <button type="submit" name="rechercher">Rechercher</button>
            </div>
        </form>
    </div>
    
    <?php 
    if ($recherche_effectuee) {
        print("<div class='results'>");
        print("<h2>Résultats (" . count($resultats) . " résultat(s))</h2>");
        
        if (count($resultats) > 0) {
            print("<table>");
            print("<tr>");
            print("<th>ID</th>");
            print("<th>Nom</th>");
            print("<th>Année</th>");
            print("<th>Type</th>");
            print("<th>Détails</th>");
            print("<th>Appartient à</th>");
            print("</tr>");
            
            foreach ($resultats as $m) {
                print("<tr>");
                print("<td>" . $m['idMateriel'] . "</td>");
                print("<td><a href='details.php?id=" . $m['idMateriel'] . "'>" . $m['nom'] . "</a></td>");
                print("<td>" . (isset($m['annee']) ? $m['annee'] : "—") . "</td>");
                print("<td>" . $m['type'] . "</td>");
                print("<td>" . (isset($m['details']) ? $m['details'] : "—") . "</td>");
                print("<td>" . (isset($m['parent']) ? $m['parent'] : "—") . "</td>");
                print("</tr>");
            }
            
            print("</table>");
        } else {
            print("<div class='no-results'>Aucun matériel ne correspond à votre recherche.</div>");
        }
        
        print("</div>");
    }
    ?>
    
    <div class="back-link">
        <a href="index.php">Retour à l'accueil</a>
    </div>
</div>

</body>
</html>

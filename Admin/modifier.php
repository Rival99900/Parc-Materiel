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

// Variables pour gérer l'état du formulaire
$message = "";
$success = false;
$materiel = null;
// Récupère l'ID du matériel à modifier depuis l'URL
$id_select = isset($_GET['id']) ? $_GET['id'] : null;

// Traite la modification si le formulaire est soumis
if (isset($_POST['id']) && isset($_POST['nom']) && isset($_POST['idType'])) {
    // Récupère les données du formulaire
    $id = $_POST['id'];
    $nom = $_POST['nom'];
    $annee = $_POST['annee'];
    $idType = $_POST['idType'];
    $details = isset($_POST['details']) ? $_POST['details'] : null;
    $idParent = isset($_POST['idParent']) && $_POST['idParent'] !== "" ? $_POST['idParent'] : null;
    
    // Vérifie que le nom n'est pas vide
    if ($nom !== "") {
        // Requête pour mettre à jour le matériel
        $sql = "
            UPDATE MATERIEL 
            SET nom = :nom, 
                annee = :annee, 
                details = :details, 
                idType = :idType, 
                idParent = :idParent 
            WHERE idMateriel = :id
        ";
        $stmt = $pdo->prepare($sql);
        
        // Exécute la requête avec les paramètres sécurisés
        if ($stmt->execute([
            ':id' => $id,
            ':nom' => $nom,
            ':annee' => $annee,
            ':details' => $details,
            ':idType' => $idType,
            ':idParent' => $idParent
        ])) {
            $message = "Matériel modifié avec succès !";
            $success = true;
        } else {
            $message = "Erreur lors de la modification du matériel.";
        }
    } else {
        $message = "Le nom est obligatoire !";
    }
}

// Si un ID est fourni en GET, récupère le matériel correspondant
if ($id_select !== null && $id_select !== "") {
    // Requête pour récupérer les informations du matériel
    $sql = "
        SELECT * 
        FROM MATERIEL 
        WHERE idMateriel = :id
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id_select]);
    $materiel = $stmt->fetch();
}

// Récupère tous les types disponibles
$sqlTypes = "
    SELECT 
        idType,   
        libelle   
    FROM TYPEE 
    ORDER BY libelle
";
$stmtTypes = $pdo->query($sqlTypes);
$types = [];
// Stocke tous les types
foreach ($stmtTypes as $type) {
    $types[] = $type;
}

// Récupère tous les matériels possibles comme parent
$sqlMateriel = "
    SELECT 
        idMateriel,  
        nom          
    FROM MATERIEL 
    ORDER BY nom
";
$stmtMateriel = $pdo->query($sqlMateriel);
$materiels = [];
// Stocke tous les matériels disponibles
foreach ($stmtMateriel as $mat) {
    $materiels[] = $mat;
}

// Récupère la liste de tous les matériels pour la sélection initiale
$sqlAll = "
    SELECT 
        idMateriel,  
        nom          
    FROM MATERIEL 
    ORDER BY nom
";
$stmtAll = $pdo->query($sqlAll);
$allMateriels = [];
// Stocke tous les matériels
foreach ($stmtAll as $m) {
    $allMateriels[] = $m;
}
?>

<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <title>Modifier un matériel - Administration</title>
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
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
        }
        h1 {
            margin-bottom: 20px;
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
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-top: 15px;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        input, select, textarea {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
            font-family: Arial, serif;
            font-size: 14px;
        }
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        button {
            margin-top: 20px;
            padding: 10px;
            background: #333;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #555;
        }
        .message {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 3px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .selection-section {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ccc;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Modifier un matériel</h1>
</div>

<div class="container">
    <div class="back-link">
        <a href="index.php">Retour à l'administration</a>
    </div>
    
    <?php 
    if ($message !== "") {
        $class = $success ? "success" : "error";
        print("<div class='message " . $class . "'>" . $message . "</div>");
    }
    ?>
    
    <div class="selection-section">
        <h2>Sélectionner un matériel à modifier</h2>
        <form method="get">
            <label for="id">Matériel :</label>
            <select id="id" name="id" required onchange="this.form.submit()">
                <option value="">-- Choisir un matériel --</option>
                <?php 
                foreach ($allMateriels as $m) {
                    $selected = ($m['idMateriel'] == $id_select) ? "selected" : "";
                    print("<option value='" . $m['idMateriel'] . "' " . $selected . ">" . $m['nom'] . "</option>");
                }
                ?>
            </select>
        </form>
    </div>
    
    <?php 
    if ($materiel !== null) {
        print("<form method='post'>");
        print("<input type='hidden' name='id' value='" . $materiel['idMateriel'] . "'>");
        
        print("<label for='nom'>Nom du matériel :</label>");
        print("<input type='text' id='nom' name='nom' value='" . $materiel['nom'] . "' required>");
        
        print("<label for='annee'>Année :</label>");
        $annee_val = isset($materiel['annee']) ? $materiel['annee'] : "";
        print("<input type='number' id='annee' name='annee' min='1900' max='2099' value='" . $annee_val . "'>");
        
        print("<label for='idType'>Type :</label>");
        print("<select id='idType' name='idType' required>");
        foreach ($types as $type) {
            $selected = ($type['idType'] == $materiel['idType']) ? "selected" : "";
            print("<option value='" . $type['idType'] . "' " . $selected . ">" . $type['libelle'] . "</option>");
        }
        print("</select>");
        
        print("<label for='details'>Détails :</label>");
        $details_val = isset($materiel['details']) ? $materiel['details'] : "";
        print("<textarea id='details' name='details'>" . $details_val . "</textarea>");
        
        print("<label for='idParent'>Appartient à (optionnel) :</label>");
        print("<select id='idParent' name='idParent'>");
        print("<option value=''>-- Aucun --</option>");
        foreach ($materiels as $mat) {
            if ($mat['idMateriel'] != $materiel['idMateriel']) {
                $selected = ($mat['idMateriel'] == $materiel['idParent']) ? "selected" : "";
                print("<option value='" . $mat['idMateriel'] . "' " . $selected . ">" . $mat['nom'] . "</option>");
            }
        }
        print("</select>");
        
        print("<button type='submit'>Modifier le matériel</button>");
        print("</form>");
    }
    ?>
</div>

</body>
</html>

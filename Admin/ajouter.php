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

// Variable pour stocker les messages de feedback utilisateur
$message = "";
$success = false;

// Traite l'ajout si le formulaire est soumis
if (isset($_POST['nom']) && isset($_POST['annee']) && isset($_POST['idType'])) {
    // Récupère les données du formulaire
    $nom = $_POST['nom'];
    $annee = $_POST['annee'];
    $idType = $_POST['idType'];
    $details = isset($_POST['details']) ? $_POST['details'] : null;
    $idParent = isset($_POST['idParent']) && $_POST['idParent'] !== "" ? $_POST['idParent'] : null;
    
    // Vérifie que le nom n'est pas vide
    if ($nom !== "") {
        // Requête pour insérer un nouveau matériel dans la base de données
        $sql = "
            INSERT INTO MATERIEL (nom, annee, details, idType, idParent) 
            VALUES (:nom, :annee, :details, :idType, :idParent)
        ";
        $stmt = $pdo->prepare($sql);
        
        // Exécute la requête avec les paramètres sécurisés
        if ($stmt->execute([
            ':nom' => $nom,
            ':annee' => $annee,
            ':details' => $details,
            ':idType' => $idType,
            ':idParent' => $idParent
        ])) {
            $message = "Matériel ajouté avec succès !";
            $success = true;
        } else {
            $message = "Erreur lors de l'ajout du matériel.";
        }
    } else {
        $message = "Le nom est obligatoire !";
    }
}

// Récupère tous les types disponibles pour le formulaire
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

// Récupère tous les matériels existants (pour choisir un parent optionnel)
$sqlMateriel = "
    SELECT 
        idMateriel,  
        nom          
    FROM MATERIEL 
    ORDER BY nom
";
$stmtMateriel = $pdo->query($sqlMateriel);
$materiels = [];
// Stocke tous les matériels disponibles comme parent
foreach ($stmtMateriel as $mat) {
    $materiels[] = $mat;
}
?>

<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <title>Ajouter un matériel - Administration</title>
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
    </style>
</head>
<body>

<div class="header">
    <h1>Ajouter un matériel</h1>
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
    
    <form method="post">
        <label for="nom">Nom du matériel :</label>
        <input type="text" id="nom" name="nom" required>
        
        <label for="annee">Année :</label>
        <input type="number" id="annee" name="annee" min="1900" max="2099">
        
        <label for="idType">Type :</label>
        <select id="idType" name="idType" required>
            <option value="">-- Sélectionner un type --</option>
            <?php 
            foreach ($types as $type) {
                print("<option value='" . $type['idType'] . "'>" . $type['libelle'] . "</option>");
            }
            ?>
        </select>
        
        <label for="details">Détails :</label>
        <textarea id="details" name="details"></textarea>
        
        <label for="idParent">Appartient à (optionnel) :</label>
        <select id="idParent" name="idParent">
            <option value="">-- Aucun --</option>
            <?php 
            foreach ($materiels as $mat) {
                print("<option value='" . $mat['idMateriel'] . "'>" . $mat['nom'] . "</option>");
            }
            ?>
        </select>
        
        <button type="submit">Ajouter le matériel</button>
    </form>
</div>

</body>
</html>

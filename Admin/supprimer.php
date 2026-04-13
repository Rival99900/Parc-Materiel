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
// Récupère l'ID du matériel à supprimer depuis l'URL
$id_select = isset($_GET['id']) ? $_GET['id'] : null;

// Traite la suppression si le formulaire est confirmé
if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === "oui") {
    $id = $_POST['id'];
    
    // Vérification : récupère le nombre de composants associés
    $sqlCheck = "
        SELECT COUNT(*) as count 
        FROM MATERIEL 
        WHERE idParent = :id
    ";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute([':id' => $id]);
    $result = $stmtCheck->fetch();
    
    // Si des composants existent, refuse la suppression
    if ($result['count'] > 0) {
        $message = "Ce matériel a des composants associés. Supprimez-les d'abord.";
    } else {
        // Requête pour supprimer le matériel de la base de données
        $sql = "
            DELETE FROM MATERIEL 
            WHERE idMateriel = :id
        ";
        $stmt = $pdo->prepare($sql);
        
        // Exécute la suppression
        if ($stmt->execute([':id' => $id])) {
            $message = "Matériel supprimé avec succès !";
            $success = true;
            $id_select = null;
            $materiel = null;
        } else {
            $message = "Erreur lors de la suppression du matériel.";
        }
    }
}

// Si un ID est fourni en GET, récupère le matériel correspondant
if ($id_select !== null && $id_select !== "") {
    /* 
    Requête pour récupérer les informations du matériel à supprimer
    Récupère aussi le type du matériel via une jointure
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
    $stmt->execute([':id' => $id_select]);
    $materiel = $stmt->fetch();
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
    <title>Supprimer un matériel - Administration</title>
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
        select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
            font-family: Arial, serif;
            font-size: 14px;
        }
        button {
            margin-top: 20px;
            padding: 10px;
            background: #d9534f;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #c9302c;
        }
        button.cancel {
            background: #5cb85c;
            margin-top: 10px;
        }
        button.cancel:hover {
            background: #449d44;
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
        .materiel-info {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 3px;
            margin-bottom: 20px;
            border-left: 4px solid #d9534f;
        }
        .materiel-info p {
            margin: 5px 0;
        }
        .materiel-info strong {
            color: #333;
        }
        .confirmation-section {
            background: #fff3cd;
            padding: 15px;
            border-radius: 3px;
            border-left: 4px solid #ffc107;
            margin-bottom: 20px;
        }
        .confirmation-section p {
            margin-bottom: 10px;
            color: #856404;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Supprimer un matériel</h1>
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
        <h2>Sélectionner un matériel à supprimer</h2>
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
        print("<div class='materiel-info'>");
        print("<h3>" . $materiel['nom'] . "</h3>");
        print("<p><strong>ID :</strong> " . $materiel['idMateriel'] . "</p>");
        print("<p><strong>Année :</strong> " . (isset($materiel['annee']) ? $materiel['annee'] : "—") . "</p>");
        print("<p><strong>Type :</strong> " . $materiel['libelle'] . "</p>");
        if (isset($materiel['details']) && $materiel['details'] !== "") {
            print("<p><strong>Détails :</strong> " . $materiel['details'] . "</p>");
        }
        print("</div>");
        
        print("<div class='confirmation-section'>");
        print("<p><strong>⚠️ Attention !</strong></p>");
        print("<p>Cette action est irréversible. Êtes-vous sûr de vouloir supprimer ce matériel ?</p>");
        print("</div>");
        
        print("<form method='post'>");
        print("<input type='hidden' name='id' value='" . $materiel['idMateriel'] . "'>");
        print("<input type='hidden' name='confirm_delete' value='oui'>");
        print("<button type='submit'>Supprimer définitivement</button>");
        print("</form>");
        
        print("<form method='get'>");
        print("<button type='button' class='cancel' onclick='window.location.href=\"index.php\"'>Annuler</button>");
        print("</form>");
    }
    ?>
</div>

</body>
</html>

<?php
session_start();

// Vérifie si l'utilisateur a déjà une session active
if (isset($_SESSION['user_connected']) && $_SESSION['user_connected'] === true) {
    // Redirection automatique vers le pannel admin s'il est déjà connecté
    header("Location: ../Admin/index.php");
    exit();
}

// Charge les identifiants de la base de données
require "../Config/credentials.php";

// Variable pour stocker les messages d'erreur
$error = "";

// Traite le formulaire de connexion si soumis
if (isset($_POST['login']) && isset($_POST['password'])) {
    // Récupère les identifiants saisis par l'utilisateur
    $login = $_POST['login'];
    $pwd = $_POST['password'];
    
    try {
        // Crée une connexion PDO avec la base de données MySQL
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $password);
        
        // Requête pour récupérer l'utilisateur
        $sql = "
            SELECT idUtilisateur, login, password 
            FROM UTILISATEURS 
            WHERE login = :login
            ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':login' => $login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Vérifie si l'utilisateur existe et le mot de passe est correct
        if ($user && password_verify($pwd, $user['password'])) {
            // Crée les variables de session pour l'utilisateur connecté
            $_SESSION['user_connected'] = true;
            $_SESSION['user_login'] = $user['login'];
            $_SESSION['user_id'] = $user['idUtilisateur'];
            // Redirection vers le panel d'administration
            header("Location: ../Admin/index.php");
            exit();
        } else {
            // Message d'erreur si les identifiants sont incorrects
            $error = "Identifiants incorrects !";
        }
    } catch (PDOException $e) {
        $error = "Erreur de connexion à la base de données : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <title>Connexion - Parc Matériel</title>
    <style>
        body {
            font-family: Arial, serif;
            background: #f4f4f4;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 300px;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            box-sizing: border-box;
            font-size: 14px;
        }
        button {
            width: 100%;
            padding: 10px;
            margin-top: 20px;
            background: #333;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #555;
        }
        .error {
            color: red;
            margin-bottom: 20px;
            text-align: center;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #333;
            text-decoration: none;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        .back-link p {
            margin-bottom: 15px;
            font-size: 14px;
        }
        .back-link p a {
            color: #0066cc;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h1>Connexion Admin</h1>
    
    <?php 
    if ($error !== "") {
        print("<div class='error'>" . $error . "</div>");
    }
    ?>
    
    <form method="post">
        <input type="text" name="login" placeholder="Identifiant" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit">Se connecter</button>
    </form>
    
    <div class="back-link">
        <p>Pas encore inscrit ? <a href="../Admin/register.php">Créer un compte</a></p>
        <a href="index.php">Retour à l'accueil</a>
    </div>
</div>

</body>
</html>

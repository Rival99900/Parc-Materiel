<?php
session_start();

// Vérifie si l'utilisateur est déjà connecté
if (isset($_SESSION['user_connected']) && $_SESSION['user_connected'] === true) {
    // Redirection vers le panel admin s'il est déjà connecté
    header("Location: index.php");
    exit();
}

// Charge les identifiants de la base de données
require "../Config/credentials.php";

// Variables pour stocker les messages
$message = "";
$error = "";

// Traite le formulaire d'inscription si soumis
if (isset($_POST['login']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['password_confirm'])) {
    // Récupère les données du formulaire
    $login = trim($_POST['login']);
    $email = trim($_POST['email']);
    $pwd = $_POST['password'];
    $pwd_confirm = $_POST['password_confirm'];
    
    // Validation des données
    if (empty($login) || empty($email) || empty($pwd) || empty($pwd_confirm)) {
        $error = "Tous les champs sont obligatoires !";
    } elseif (strlen($login) < 5) {
        $error = "L'identifiant doit contenir au moins 5 caractères !";
    } elseif (strlen($pwd) < 15) {
        $error = "Le mot de passe doit contenir au moins 15 caractères !";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email est invalide !";
    } elseif ($pwd !== $pwd_confirm) {
        $error = "Les mots de passe ne correspondent pas !";
    } else {
        try {
            // Crée une connexion PDO avec la base de données MySQL
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $password);
            
            // Vérifie si le login existe déjà
            $sqlCheck = "
                SELECT COUNT(*) 
                FROM UTILISATEURS 
                WHERE login = :login OR email = :email
                ";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute([':login' => $login, ':email' => $email]);
            $exists = $stmtCheck->fetchColumn();
            
            if ($exists > 0) {
                $error = "Cet identifiant ou email existe déjà !";
            } else {
                // Hash du mot de passe
                $hashed_password = password_hash($pwd, PASSWORD_DEFAULT);
                
                // Requête pour insérer l'utilisateur
                $sqlInsert = "
                    INSERT INTO UTILISATEURS (login, email, password, date_creation) 
                    VALUES (:login, :email, :password, NOW())
                ";
                $stmtInsert = $pdo->prepare($sqlInsert);
                
                if ($stmtInsert->execute([
                    ':login' => $login,
                    ':email' => $email,
                    ':password' => $hashed_password
                ])) {
                    $message = "Inscription réussie ! Redirection vers la connexion...";
                    // Redirection après 2 secondes
                    header("refresh:2;url=../Public/login.php");
                } else {
                    $error = "Erreur lors de l'inscription. Veuillez réessayer.";
                }
            }
        } catch (PDOException $e) {
            $error = "Erreur de connexion à la base de données : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <title>Créer un compte - Parc Matériel</title>
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
        input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
            font-family: Arial, serif;
            font-size: 14px;
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
        .info-text {
            margin-top: 15px;
            padding: 10px;
            background: #e7f3ff;
            border-left: 4px solid #0066cc;
            color: #004085;
            font-size: 13px;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Créer un compte administrateur</h1>
</div>

<div class="container">
    <div class="back-link">
        <a href="../Public/login.php">Retour à la connexion</a>
    </div>
    
    <?php 
    if ($message !== "") {
        print("<div class='message success'>" . htmlspecialchars($message) . "</div>");
    }
    if ($error !== "") {
        print("<div class='message error'>" . htmlspecialchars($error) . "</div>");
    }
    ?>
    
    <form method="post">
        <label for="login">Identifiant :</label>
        <input type="text" id="login" name="login" placeholder="Minimum 5 caractères" minlength="5" required>
        
        <label for="email">Adresse email :</label>
        <input type="email" id="email" name="email" placeholder="exemple@domaine.fr" required>
        
        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" placeholder="Minimum 15 caractères" minlength="15" required>
        
        <label for="password_confirm">Confirmer le mot de passe :</label>
        <input type="password" id="password_confirm" name="password_confirm" placeholder="Confirmer le mot de passe" minlength="6" required>
        
        <button type="submit">Créer le compte</button>
    </form>
    
    <div class="info-text">
        <strong>Information :</strong> Veuillez conserver vos identifiants en sécurité. 
        Vous pourrez les utiliser pour accéder à l'administration du parc matériel.
    </div>
</div>

</body>
</html>

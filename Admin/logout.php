<?php
// Démarre la session pour pouvoir la détruire
session_start();

// Détruit complètement la session actuelle
// Supprime toutes les variables de session stockées
session_destroy();

// Redirige l'utilisateur vers la page d'accueil publique
header("Location: ../Public/index.php");
exit();
?>

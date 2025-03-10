<?php
require_once 'includes/db.php';

// Déconnecter l'utilisateur
logoutUser();

// Rediriger vers la page d'accueil
header("Location: index.php");
exit;
?>
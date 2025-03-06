<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Démarrer la session
session_start();

// Inclure les fonctions et la connexion à la base de données
require_once 'includes/functions.php';
require_once 'includes/db.php';

// Définir le titre par défaut du site
$pageTitle = $pageTitle ?? "ImmoAgence - Votre spécialiste immobilier";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <!-- CSS Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSS personnalisé -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- VueJS via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">ImmoAgence</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="properties.php">Propriétés</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="add-property.php">Déposer une annonce</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<div class="container mt-4">
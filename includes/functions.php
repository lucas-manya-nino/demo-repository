<?php
/**
 * Fonctions utilitaires pour l'application
 */

/**
 * Formate un prix en euros
 *
 * @param float $price Prix à formater
 * @return string Prix formaté
 */
function formatPrice($price) {
    return number_format($price, 0, ',', ' ') . ' €';
}

/**
 * Formate une superficie en m²
 *
 * @param float $area Superficie en m²
 * @return string Superficie formatée
 */
function formatArea($area) {
    return $area . ' m²';
}

/**
 * Génère un slug à partir d'un texte
 *
 * @param string $text Texte à transformer en slug
 * @return string Slug généré
 */
function slugify($text) {
    // Remplacer les caractères non alphanumériques par des tirets
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);

    // Translittération
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

    // Supprimer les caractères indésirables
    $text = preg_replace('~[^-\w]+~', '', $text);

    // Trim tirets
    $text = trim($text, '-');

    // Remplacer les tirets multiples
    $text = preg_replace('~-+~', '-', $text);

    // Mettre en minuscules
    $text = strtolower($text);

    return $text;
}

/**
 * Génère une URL conviviale pour une propriété
 *
 * @param array $property Données de la propriété
 * @return string URL de la propriété
 */
function getPropertyUrl($property) {
    return 'property.php?id=' . $property['id'] . '&slug=' . slugify($property['title']);
}

/**
 * Vérifie si une chaîne commence par une autre chaîne
 *
 * @param string $haystack Chaîne à vérifier
 * @param string $needle Préfixe à rechercher
 * @return bool True si $haystack commence par $needle
 */
function startsWith($haystack, $needle) {
    return substr($haystack, 0, strlen($needle)) === $needle;
}

/**
 * Tronque un texte à une longueur maximale
 *
 * @param string $text Texte à tronquer
 * @param int $length Longueur maximale
 * @param string $suffix Suffixe à ajouter si le texte est tronqué
 * @return string Texte tronqué
 */
function truncateText($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }

    return substr($text, 0, $length) . $suffix;
}

/**
 * Récupère l'URL de l'image d'une propriété
 * Si l'image n'existe pas, renvoie une image par défaut
 *
 * @param string $imageName Nom de l'image
 * @return string URL de l'image
 */
function getPropertyImageUrl($imageName) {
    $imagePath = 'assets/images/' . $imageName;

    if (file_exists($imagePath)) {
        return $imagePath;
    }

    // Image par défaut si l'image spécifiée n'existe pas
    return 'assets/images/default-property.jpg';
}

/**
 * Valide les données d'une propriété
 *
 * @param array $data Données à valider
 * @return array Erreurs de validation (tableau vide si pas d'erreurs)
 */
function validatePropertyData($data) {
    $errors = [];

    // Validation du titre
    if (empty($data['title'])) {
        $errors['title'] = 'Le titre est obligatoire';
    } elseif (strlen($data['title']) < 5) {
        $errors['title'] = 'Le titre doit comporter au moins 5 caractères';
    }

    // Validation de la description
    if (empty($data['description'])) {
        $errors['description'] = 'La description est obligatoire';
    } elseif (strlen($data['description']) < 20) {
        $errors['description'] = 'La description doit comporter au moins 20 caractères';
    }

    // Validation du type
    if (empty($data['type'])) {
        $errors['type'] = 'Le type de bien est obligatoire';
    }

    // Validation du prix
    if (empty($data['price'])) {
        $errors['price'] = 'Le prix est obligatoire';
    } elseif (!is_numeric($data['price']) || $data['price'] <= 0) {
        $errors['price'] = 'Le prix doit être un nombre positif';
    }

    // Validation de la superficie
    if (empty($data['area'])) {
        $errors['area'] = 'La superficie est obligatoire';
    } elseif (!is_numeric($data['area']) || $data['area'] <= 0) {
        $errors['area'] = 'La superficie doit être un nombre positif';
    }

    // Validation du nombre de chambres
    if (!isset($data['bedrooms']) || !is_numeric($data['bedrooms']) || $data['bedrooms'] < 0) {
        $errors['bedrooms'] = 'Le nombre de chambres doit être un nombre positif ou nul';
    }

    // Validation de la ville
    if (empty($data['city'])) {
        $errors['city'] = 'La ville est obligatoire';
    }

    return $errors;
}
<?php

function formatPrice($price) {
    return number_format($price, 0, ',', ' ') . ' €';
}

function formatArea($area) {
    return $area . ' m²';
}

// (merci l'IA pour celle là)
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);

    return $text;
}

function getPropertyUrl($property) {
    return 'property.php?id=' . $property['id'] . '&slug=' . slugify($property['title']);
}

function truncateText($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }

    return substr($text, 0, $length) . $suffix;
}

function getPropertyImageUrl($imageName) {
    $imagePath = 'images/' . $imageName;

    if (file_exists($imagePath)) {
        return $imagePath;
    }

    // Image par défaut si l'image spécifiée n'existe pas
    return 'assets/images/default-property.jpg';
}

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
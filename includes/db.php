<?php
/**
 * Connexion à la base de données MySQL
 */

// Paramètres de connexion à la base de données
const DB_HOST = 'sql313.infinityfree.com';
const DB_USER = 'if0_38459457';
const DB_PASS = 'dnUvhB9pGmSx';
const DB_NAME = 'if0_38459457_immo';
const DB_PORT = 3306;

/**
 * Établit une connexion à la base de données
 *
 * @return mysqli Objet de connexion à la base de données
 */

function getDbConnection() {
    try{
        static $conn = null;

        if ($conn === null) {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

            // Vérifier la connexion
            if ($conn->connect_error) {
                die('Erreur de connexion à la base de données: ' . $conn->connect_error);
            }

            // Définir le jeu de caractères
            $conn->set_charset('utf8mb4');
        }

        return $conn;
    }catch(Exception $e){
        var_dump($e->getMessage());
        die();
    }
}

/**
 * Exécute une requête SQL et retourne le résultat
 *
 * @param string $sql Requête SQL à exécuter
 * @param array $params Paramètres pour la requête préparée
 * @return mysqli_result|bool Résultat de la requête
 */
function executeQuery($sql, $params = []) {
    $conn = getDbConnection();
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die('Erreur de préparation de la requête: ' . $conn->error);
    }

    if (!empty($params)) {
        // Construire les types de paramètres (s = string, i = integer, d = double)
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }

        // Lier les paramètres
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();

    return $stmt->get_result();
}

/**
 * Récupère toutes les propriétés
 *
 * @return array Tableau des propriétés
 */
function getAllProperties() {
    $sql = "SELECT * FROM properties";
    $result = executeQuery($sql);

    $properties = [];
    while ($row = $result->fetch_assoc()) {
        // Convertir les features depuis le format JSON
        if (isset($row['features'])) {
            $row['features'] = json_decode($row['features'], true) ?: [];
        } else {
            $row['features'] = [];
        }

        $properties[] = $row;
    }

    return $properties;
}

/**
 * Récupère les propriétés récentes
 *
 * @param int $limit Nombre de propriétés à récupérer
 * @return array Tableau des propriétés récentes
 */
function getRecentProperties($limit = 3) {
    $sql = "SELECT * FROM properties ORDER BY date_added DESC LIMIT ?";
    $result = executeQuery($sql, [$limit]);

    $properties = [];
    while ($row = $result->fetch_assoc()) {
        // Convertir les features depuis le format JSON
        if (isset($row['features'])) {
            $row['features'] = json_decode($row['features'], true) ?: [];
        } else {
            $row['features'] = [];
        }

        $properties[] = $row;
    }

    return $properties;
}

/**
 * Récupère une propriété par son ID
 *
 * @param int $id ID de la propriété
 * @return array|null Données de la propriété ou null si non trouvée
 */
function getPropertyById($id) {
    $sql = "SELECT * FROM properties WHERE id = ?";
    $result = executeQuery($sql, [$id]);

    if ($row = $result->fetch_assoc()) {
        // Convertir les features depuis le format JSON
        if (isset($row['features'])) {
            $row['features'] = json_decode($row['features'], true) ?: [];
        } else {
            $row['features'] = [];
        }

        return $row;
    }

    return null;
}

/**
 * Ajoute une nouvelle propriété
 *
 * @param array $propertyData Données de la propriété
 * @return int|false ID de la nouvelle propriété ou false en cas d'échec
 */
function addProperty($propertyData) {
    // Convertir les features en JSON
    if (isset($propertyData['features']) && is_array($propertyData['features'])) {
        $propertyData['features'] = json_encode($propertyData['features']);
    } else {
        $propertyData['features'] = json_encode([]);
    }

    // Ajouter la date d'ajout si elle n'est pas définie
    if (!isset($propertyData['date_added'])) {
        $propertyData['date_added'] = date('Y-m-d');
    }

    // Créer la requête SQL d'insertion
    $columns = implode(', ', array_keys($propertyData));
    $placeholders = implode(', ', array_fill(0, count($propertyData), '?'));

    $sql = "INSERT INTO properties ($columns) VALUES ($placeholders)";

    $conn = getDbConnection();
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die('Erreur de préparation de la requête: ' . $conn->error);
    }

    // Construire les types de paramètres
    $types = '';
    foreach ($propertyData as $param) {
        if (is_int($param)) {
            $types .= 'i';
        } elseif (is_float($param)) {
            $types .= 'd';
        } else {
            $types .= 's';
        }
    }

    // Lier les paramètres
    $stmt->bind_param($types, ...array_values($propertyData));

    // Exécuter la requête
    if ($stmt->execute()) {
        return $conn->insert_id;
    }

    return false;
}

/**
 * Filtre les propriétés selon certains critères
 *
 * @param array $filters Critères de filtrage
 * @return array Propriétés filtrées
 */
function filterProperties($filters) {
    $sql = "SELECT * FROM properties WHERE 1=1";
    $params = [];

    // Filtrer par type
    if (!empty($filters['type'])) {
        $sql .= " AND type = ?";
        $params[] = $filters['type'];
    }

    // Filtrer par prix min
    if (!empty($filters['price_min'])) {
        $sql .= " AND price >= ?";
        $params[] = $filters['price_min'];
    }

    // Filtrer par prix max
    if (!empty($filters['price_max'])) {
        $sql .= " AND price <= ?";
        $params[] = $filters['price_max'];
    }

    // Filtrer par nombre de chambres
    if (!empty($filters['bedrooms'])) {
        $sql .= " AND bedrooms >= ?";
        $params[] = $filters['bedrooms'];
    }

    // Filtrer par ville
    if (!empty($filters['city'])) {
        $sql .= " AND city LIKE ?";
        $params[] = '%' . $filters['city'] . '%';
    }

    // Ajouter l'ordre de tri
    $sql .= " ORDER BY date_added DESC";

    $result = executeQuery($sql, $params);

    $properties = [];
    while ($row = $result->fetch_assoc()) {
        // Convertir les features depuis le format JSON
        if (isset($row['features'])) {
            $row['features'] = json_decode($row['features'], true) ?: [];
        } else {
            $row['features'] = [];
        }

        $properties[] = $row;
    }

    return $properties;
}

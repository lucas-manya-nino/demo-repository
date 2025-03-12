<?php
/**
 * Connexion à la base de données MySQL
 */

const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = 'root';
const DB_NAME = 'real_estate';
const DB_PORT = 3306;

function getDbConnection() {
    try{
        static $conn = null;

        if ($conn === null) {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

            if ($conn->connect_error) {
                die('Erreur de connexion à la base de données: ' . $conn->connect_error);
            }

            $conn->set_charset('utf8mb4');
        }

        return $conn;
    }catch(Exception $e){
        var_dump($e->getMessage());
        die();
    }
}
function executeQuery($sql, $params = [])
{
    $conn = getDbConnection();
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die('Erreur de préparation de la requête: ' . $conn->error);
    }

    if (!empty($params)) {
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

        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();

    return $stmt->get_result();
}

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

function getPropertyById($id) {
    $sql = "SELECT * FROM properties WHERE id = ?";
    $result = executeQuery($sql, [$id]);

    if ($row = $result->fetch_assoc()) {
        if (isset($row['features'])) {
            $row['features'] = json_decode($row['features'], true) ?: [];
        } else {
            $row['features'] = [];
        }

        return $row;
    }

    return null;
}

function addProperty($propertyData) {
    if (isset($propertyData['features']) && is_array($propertyData['features'])) {
        $propertyData['features'] = json_encode($propertyData['features']);
    } else {
        $propertyData['features'] = json_encode([]);
    }

    if (!isset($propertyData['date_added'])) {
        $propertyData['date_added'] = date('Y-m-d');
    }

    $columns = implode(', ', array_keys($propertyData));
    $placeholders = implode(', ', array_fill(0, count($propertyData), '?'));

    $sql = "INSERT INTO properties ($columns) VALUES ($placeholders)";

    $conn = getDbConnection();
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die('Erreur de préparation de la requête: ' . $conn->error);
    }

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

    $stmt->bind_param($types, ...array_values($propertyData));

    if ($stmt->execute()) {
        return $conn->insert_id;
    }

    return false;
}

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


function verifyUserCredentials($username, $password) {
    $conn = getDbConnection();

    $hashedPassword = hash('sha256', $password);

    $sql = "SELECT * FROM clients WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $hashedPassword);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        unset($row['password']);
        return $row;
    }

    return false;
}


function verifyUsernameAvailable($username) {
    $conn = getDbConnection();

    $sql = "SELECT COUNT(*) as count FROM clients WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return $row['count'] == 0;
    }

    return false;
}

function verifyEmailAvailable($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $conn = getDbConnection();

    $sql = "SELECT COUNT(*) as count FROM clients WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return $row['count'] == 0;
    }

    return false;
}

function verifyPhoneNumAvailable($phoneNum) {
    $phoneNum = preg_replace('/[\s\-\(\)]/', '', $phoneNum);

    if (!preg_match('/^(\+?\d{1,3})?\d{9,12}$/', $phoneNum)) {
        return false;
    }

    $conn = getDbConnection();

    $sql = "SELECT COUNT(*) as count FROM clients WHERE phone_num = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $phoneNum);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return $row['count'] == 0;
    }

    return false;
}


function registerNewClient($clientData) {
    $conn = getDbConnection();

    if (!verifyUsernameAvailable($clientData['username']) ||
        !verifyEmailAvailable($clientData['email']) ||
        !verifyPhoneNumAvailable($clientData['phone_num'])) {
        return false;
    }

    $clientData['password'] = hash('sha256', $clientData['password']);
    $clientData['registration_date'] = date('Y-m-d');

    $columns = implode(', ', array_keys($clientData));
    $placeholders = implode(', ', array_fill(0, count($clientData), '?'));
    $sql = "INSERT INTO clients ($columns) VALUES ($placeholders)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die('Erreur de préparation de la requête: ' . $conn->error);
    }

    $types = '';
    foreach ($clientData as $param) {
        $types .= is_int($param) ? 'i' : (is_float($param) ? 'd' : 's');
    }

    $stmt->bind_param($types, ...array_values($clientData));

    if ($stmt->execute()) {
        return $conn->insert_id;
    }

    return false;
}

function loginUser($username, $password) {
    $user = verifyUserCredentials($username, $password);

    if ($user) {
        session_start();
        $_SESSION['user'] = $user;
        return true;
    }

    return false;
}


function logoutUser()
{
    session_start();
    $_SESSION = [];
    session_destroy();
}

function isFavorite($clientId, $propertyId) {
    $conn = getDbConnection();
    
    $sql = "SELECT COUNT(*) as count FROM favorites WHERE fk_id_client = ? AND fk_id_property = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $clientId, $propertyId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    return $result['count'] > 0;
}

function getUserFavorites($clientId) {
        $conn = getDbConnection();

        $query = "
            SELECT properties.* 
            FROM properties 
            JOIN favorites ON properties.id = favorites.fk_id_property 
            WHERE favorites.fk_id_client = ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $clientId);
        $stmt->execute();
        $result = $stmt->get_result();
        $favorites = $result->fetch_all(MYSQLI_ASSOC);
        return $favorites;
}
<?php
/**
 * Connexion à la base de données MySQL
 */

// Paramètres de connexion à la base de données
const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'real_estate';
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

/**
 * Vérifie les informations de connexion de l'utilisateur
 *
 * @param string $username Nom d'utilisateur
 * @param string $password Mot de passe
 * @return array|false Tableau des informations de l'utilisateur si correct, false sinon
 */
function verifyUserCredentials($username, $password) {
    // Hacher le mot de passe fourni
    $hashedPassword = hash('sha256', $password);

    // Requête SQL pour vérifier les informations de connexion
    $sql = "SELECT * FROM clients WHERE username = ? AND password = ?";
    $result = executeQuery($sql, [$username, $hashedPassword]);

    if ($row = $result->fetch_assoc()) {
        // Retirer le mot de passe du tableau des résultats pour des raisons de sécurité
        unset($row['password']);
        return $row;
    }

    return false;
}

/**
 * Vérifie si un nom d'utilisateur est disponible
 *
 * @param string $username Nom d'utilisateur à vérifier
 * @return bool True si le nom d'utilisateur est disponible, False sinon
 */
function verifyUsernameAvailable($username) {
    // Requête SQL pour vérifier si le nom d'utilisateur existe déjà
    $sql = "SELECT COUNT(*) as count FROM clients WHERE username = ?";
    $result = executeQuery($sql, [$username]);

    if ($row = $result->fetch_assoc()) {
        return $row['count'] == 0;
    }

    return false;
}

/**
 * Vérifie si une adresse email est valide et disponible
 *
 * @param string $email Adresse email à vérifier
 * @return bool True si l'adresse email est valide et disponible, False sinon
 */
function verifyEmailAvailable($email) {
    // Vérifier si l'email est valide
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    // Requête SQL pour vérifier si l'adresse email existe déjà
    $sql = "SELECT COUNT(*) as count FROM clients WHERE email = ?";
    $result = executeQuery($sql, [$email]);

    if ($row = $result->fetch_assoc()) {
        return $row['count'] == 0;
    }

    return false;
}

/**
 * Vérifie si un numéro de téléphone est valide et disponible
 *
 * @param string $phoneNum Numéro de téléphone à vérifier
 * @return bool True si le numéro est valide et disponible, False sinon
 */
function verifyPhoneNumAvailable($phoneNum) {
    // Nettoyage du numéro (suppression des espaces, tirets, parenthèses)
    $phoneNum = preg_replace('/[\s\-\(\)]/', '', $phoneNum);

    // Vérification du format du numéro (ex: +33 612345678 ou 0612345678)
    if (!preg_match('/^(\+?\d{1,3})?\d{9,12}$/', $phoneNum)) {
        return false;
    }

    // Requête SQL pour vérifier si le numéro existe déjà
    $sql = "SELECT COUNT(*) as count FROM clients WHERE phone_num = ?";
    $result = executeQuery($sql, [$phoneNum]);

    if ($row = $result->fetch_assoc()) {
        return $row['count'] == 0;
    }

    return false;
}

/**
 * Enregistre un nouveau client dans la base de données
 *
 * @param array $clientData Données du client
 * @return int|false ID du nouveau client ou false en cas d'échec
 */
function registerNewClient($clientData) {
    // Vérifier si le nom d'utilisateur est disponible
    if (!verifyUsernameAvailable($clientData['username'])) {
        return false;
    }

    // Vérifier si l'adresse email est disponible
    if (!verifyEmailAvailable($clientData['email'])) {
        return false;
    }

    // Vérifier si le numéro de téléphone est disponible
    if (!verifyPhoneNumAvailable($clientData['phone_num'])) {
        return false;
    }

    // Hacher le mot de passe
    $clientData['password'] = hash('sha256', $clientData['password']);

    // Ajouter la date d'enregistrement
    $clientData['registration_date'] = date('Y-m-d');

    // Créer la requête SQL d'insertion
    $columns = implode(', ', array_keys($clientData));
    $placeholders = implode(', ', array_fill(0, count($clientData), '?'));

    $sql = "INSERT INTO clients ($columns) VALUES ($placeholders)";

    $conn = getDbConnection();
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die('Erreur de préparation de la requête: ' . $conn->error);
    }

    // Construire les types de paramètres
    $types = '';
    foreach ($clientData as $param) {
        if (is_int($param)) {
            $types .= 'i';
        } elseif (is_float($param)) {
            $types .= 'd';
        } else {
            $types .= 's';
        }
    }

    // Lier les paramètres
    $stmt->bind_param($types, ...array_values($clientData));

    // Exécuter la requête
    if ($stmt->execute()) {
        return $conn->insert_id;
    }

    return false;
}

/**
 * Connecte le client s'il a entré le bon nom d'utilisateur et mot de passe
 *
 * @param string $username Nom d'utilisateur
 * @param string $password Mot de passe
 * @return bool True si la connexion est réussie, False sinon
 */
function loginUser($username, $password) {
    // Vérifier les informations de connexion de l'utilisateur
    $user = verifyUserCredentials($username, $password);

    if ($user) {
        // Démarrer une session
        session_start();

        // Stocker les informations de l'utilisateur dans la session
        $_SESSION['user'] = $user;

        return true;
    }

    return false;
}

/**
 * Déconnecte le client en détruisant la session
 */
function logoutUser() {
    session_start();
    
    // Supprimer toutes les variables de session
    $_SESSION = [];

    // Détruire la session
    session_destroy();
}

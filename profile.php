<?php
// Inclure l'en-tête
include 'includes/start.php';

// Inclure la fonction pour la connexion à la base de données et la fonction getUserFavorites
require_once 'includes/db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
    header('Location: loginpage.html');
    exit;
}

// Récupérer l'id de l'utilisateur connecté
$userId = $_SESSION['user']['id_client'];  // Utilisation de 'user' dans la session

// Obtenir la connexion à la base de données
$conn = getDbConnection();

// Récupérer les informations de l'utilisateur avec MySQLi
$query = "SELECT * FROM clients WHERE id_client = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $userId); // 'i' pour integer
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Vérifier si l'utilisateur existe
if (!$user) {
    // Rediriger vers une page d'erreur si l'utilisateur n'existe pas
    header('Location: errorpage.html');
    exit;
}

// Récupérer les propriétés favorites de l'utilisateur
$favorites = getUserFavorites($userId);
?>

<?php
// Définir le titre de la page
$pageTitle = "Profil de l'utilisateur - ImmoAgence";


?>

<div id="profile-app">
    <!-- Section Profil Utilisateur -->
    <section class="hero-section py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-4">Profil de <?php echo htmlspecialchars($user['username']); ?></h1>
                    <p><strong>Email :</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Téléphone :</strong> <?php echo htmlspecialchars($user['phone_num']); ?></p>
                    <p><strong>Date d'inscription :</strong> <?php echo htmlspecialchars($user['registration_date']); ?></p>
                </div>
                <div class="col-md-4 d-none d-md-block">
                    <img src="assets/images/profile-illustration.png" alt="Profil utilisateur" class="img-fluid">
                </div>
            </div>
        </div>
    </section>

    <!-- Section Propriétés en Favoris -->
    <section class="mt-5 bg-light py-5">
        <div class="container">
            <h2 class="mb-4">Propriétés en Favoris</h2>
            <?php if ($favorites): ?>
                <div class="row">
                    <?php foreach ($favorites as $property): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card property-card">
                                <img src="images/<?php echo htmlspecialchars($property['image']); ?>" class="card-img-top property-image" alt="<?php echo htmlspecialchars($property['title']); ?>" style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($property['title']); ?></h5>
                                    <p class="property-price"><?php echo number_format($property['price'], 2, ',', ' '); ?> €</p>
                                    <p class="card-text"><?php echo truncateText($property['description'], 100); ?></p>
                                    <div class="property-features mb-3">
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($property['type']); ?></span>
                                        <span class="badge bg-secondary"><?php echo formatArea($property['area']); ?></span>
                                        <span class="badge bg-secondary"><?php echo $property['bedrooms']; ?> chambre(s)</span>
                                    </div>
                                    <a href="property-details.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">Voir détails</a>
                                </div>
                                <div class="card-footer">
                                    <small class="property-date">Ajouté le <?php echo date('d/m/Y', strtotime($property['date_added'])); ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Aucune propriété en favori.</p>
            <?php endif; ?>
        </div>
    </section>

</div>

<?php
// Inclure le pied de page
include 'includes/end.php';
?>

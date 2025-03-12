<?php
include 'includes/start.php';

require_once 'includes/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: loginpage.html');
    exit;
}

$userId = $_SESSION['user']['id'];

$conn = getDbConnection();

$query = "SELECT * FROM clients WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header('Location: errorpage.html');
    exit;
}

$favorites = getUserFavorites($userId);
?>

<?php
$pageTitle = "Profil de l'utilisateur - ImmoAgence";


?>

<div id="profile-app">
    <section class="hero-section py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-4">Profil de <?php echo htmlspecialchars($user['username']); ?></h1>
                    <p><strong>Email :</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Téléphone :</strong> <?php echo htmlspecialchars($user['phone_num']); ?></p>
                    <p><strong>Date d'inscription :</strong> <?php echo htmlspecialchars($user['registration_date']); ?></p>
                </div>
            </div>
        </div>
    </section>

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
include 'includes/end.php';
?>

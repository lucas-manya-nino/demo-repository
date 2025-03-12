<?php
include 'includes/start.php';
include_once 'includes/db.php';

$conn = getDbConnection();
$propertyId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($propertyId <= 0) {
    header('Location: properties.php');
    exit;
}

$property = getPropertyById($propertyId);

if (!$property) {
    header('Location: properties.php');
    exit;
}

$pageTitle = "ImmoAgence - " . htmlspecialchars($property['title']);

$isLoggedIn = isset($_SESSION['user']);

if (isset($_POST['add_to_favorites'])) {
    $userId = $_SESSION['user']['id'];

    $query = "INSERT INTO favorites (fk_id_client, fk_id_property) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $userId, $propertyId);
    $stmt->execute();

    header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $propertyId);
    exit;
}

$isFavorite = isFavorite($_SESSION['user']['id'], $propertyId);

?>

    <style>
        .sticky-info {
            position: -webkit-sticky;
            position: sticky;
            top: 20px;
            background-color: #fff;
            z-index: 100;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .content-wrapper {
            min-height: 1000px;
        }

        @media (max-width: 767px) {
            .sticky-info {
                position: static;
                box-shadow: none;
            }
        }

        .row{
            position: relative;
        }

    </style>

    <div id="property-detail-app">
        <div class="mb-4">
            <a href="properties.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Retour aux propriétés
            </a>
        </div>

        <div class="content-wrapper">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-4">
                        <img src="<?php echo getPropertyImageUrl($property['image']); ?>" class="img-fluid property-detail-image" alt="<?php echo htmlspecialchars($property['title']); ?>">
                    </div>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h2>Description</h2>
                        </div>
                        <div class="card-body">
                            <p><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>
                        </div>
                    </div>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h2>Caractéristiques</h2>
                        </div>
                        <div class="card-body">
                            <?php if (empty($property['features'])): ?>
                                <p>Aucune caractéristique spécifiée pour ce bien.</p>
                            <?php else: ?>
                                <ul class="property-detail-features">
                                    <?php foreach ($property['features'] as $feature): ?>
                                        <li>
                                            <i class="fas fa-check text-success me-2"></i>
                                            <?php echo htmlspecialchars($feature); ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 sticky-container">
                    <div class="sticky-info">
                        <h1><?php echo htmlspecialchars($property['title']); ?></h1>
                        <p class="property-price fs-3"><?php echo formatPrice($property['price']); ?></p>

                        <div class="mt-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Type:</span>
                                <strong><?php echo ucfirst(htmlspecialchars($property['type'])); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Superficie:</span>
                                <strong><?php echo formatArea($property['area']); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Chambres:</span>
                                <strong><?php echo $property['bedrooms']; ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Salles de bain:</span>
                                <strong><?php echo $property['bathrooms']; ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Adresse:</span>
                                <strong><?php echo htmlspecialchars($property['address']); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Code postal:</span>
                                <strong><?php echo htmlspecialchars($property['postal_code']); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Ville:</span>
                                <strong><?php echo htmlspecialchars($property['city']); ?></strong>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h5>Contactez-nous</h5>
                            <a href="tel:+33123456789" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-phone"></i> Appeler
                            </a>
                            <a href="mailto:contact@immoagence.fr" class="btn btn-outline-primary w-100">
                                <i class="fas fa-envelope"></i> Envoyer un email
                            </a>
                        </div>

                        <?php if($isLoggedIn && !$isFavorite){ ?>
                            <form method="POST" action="">
                                <button type="submit" name="add_to_favorites" class="btn btn-warning w-100 mt-4">
                                    <i class="fas fa-heart"></i> Ajouter aux favoris
                                </button>
                            </form>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('property-detail-app')) {
                const app = new Vue({
                    el: '#property-detail-app',
                    data: {
                        property: <?php echo json_encode($property); ?>
                    }
                });
            }
        });
    </script>

<?php
// Inclure le pied de page
include 'includes/end.php';
?>
<?php
// Récupérer l'ID de la propriété
$propertyId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Vérifier que l'ID est valide
if ($propertyId <= 0) {
    // Rediriger vers la page des propriétés si l'ID n'est pas valide
    header('Location: properties.php');
    exit;
}

// Récupérer les données de la propriété
$property = getPropertyById($propertyId);

// Vérifier que la propriété existe
if (!$property) {
    // Rediriger vers la page des propriétés si la propriété n'existe pas
    header('Location: properties.php');
    exit;
}

// Définir le titre de la page avec le titre de la propriété
$pageTitle = "ImmoAgence - " . htmlspecialchars($property['title']);

// Inclure l'en-tête
include 'includes/start.php';
?>

    <div id="property-detail-app">
        <div class="mb-4">
            <a href="properties.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Retour aux propriétés
            </a>
        </div>

        <div class="row">
            <!-- Image principale -->
            <div class="col-md-8 mb-4">
                <img src="<?php echo getPropertyImageUrl($property['image']); ?>" class="img-fluid property-detail-image" alt="<?php echo htmlspecialchars($property['title']); ?>">
            </div>

            <!-- Informations rapides -->
            <div class="col-md-4 mb-4">
                <div class="property-detail-info">
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
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Description -->
            <div class="col-md-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h2>Description</h2>
                    </div>
                    <div class="card-body">
                        <p><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>
                    </div>
                </div>

                <!-- Caractéristiques -->
                <div class="card mt-4">
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

            <!-- Propriétés similaires -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h3>Biens similaires</h3>
                    </div>
                    <div class="card-body p-0">
                        <?php
                        // Récupérer des propriétés du même type
                        $similarProperties = [];
                        $allProperties = getAllProperties();
                        $count = 0;

                        foreach ($allProperties as $similarProperty) {
                            if ($similarProperty['id'] != $property['id'] && $similarProperty['type'] == $property['type']) {
                                $similarProperties[] = $similarProperty;
                                $count++;

                                // Limiter à 3 propriétés similaires
                                if ($count >= 3) {
                                    break;
                                }
                            }
                        }
                        ?>

                        <?php if (empty($similarProperties)): ?>
                            <div class="p-3">
                                <p>Aucun bien similaire disponible pour le moment.</p>
                            </div>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($similarProperties as $similarProperty): ?>
                                    <li class="list-group-item">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0">
                                                <img src="<?php echo getPropertyImageUrl($similarProperty['image']); ?>" alt="<?php echo htmlspecialchars($similarProperty['title']); ?>" class="img-thumbnail" style="width: 80px; height: 60px; object-fit: cover;">
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-0"><?php echo htmlspecialchars(truncateText($similarProperty['title'], 40)); ?></h6>
                                                <p class="property-price mb-1"><?php echo formatPrice($similarProperty['price']); ?></p>
                                                <a href="<?php echo getPropertyUrl($similarProperty); ?>" class="btn btn-sm btn-outline-primary">Voir</a>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
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
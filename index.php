<?php
// Définir le titre de la page
$pageTitle = "ImmoAgence - Votre spécialiste immobilier";



// Inclure l'en-tête
include 'includes/start.php';

// Récupérer les propriétés récentes (3 dernières)
$recentProperties = getRecentProperties(3);

?>

    <div id="home-app">
        <!-- Section Hero -->
        <section class="hero-section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1>Trouvez votre bien immobilier idéal</h1>
                        <p class="lead">Des milliers d'annonces de vente et de location disponibles partout en France.</p>
                        <a href="properties.php" class="btn btn-light btn-lg mt-3">Consulter nos annonces</a>
                    </div>
                    <div class="col-md-4 d-none d-md-block">
                        <img src="assets/images/house-illustration.png" alt="Maison" class="img-fluid">
                    </div>
                </div>
            </div>
        </section>

        <!-- Section Propriétés récentes -->
        <section class="mt-5">
            <div class="container">
                <h2 class="mb-4">Propriétés récentes</h2>
                <div class="row">
                    <?php foreach ($recentProperties as $property): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card property-card">
                                <img src="<?php echo getPropertyImageUrl($property['image']); ?>" class="card-img-top property-image" alt="<?php echo htmlspecialchars($property['title']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($property['title']); ?></h5>
                                    <p class="property-price"><?php echo formatPrice($property['price']); ?></p>
                                    <p class="card-text"><?php echo truncateText($property['description'], 100); ?></p>
                                    <div class="property-features mb-3">
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($property['type']); ?></span>
                                        <span class="badge bg-secondary"><?php echo formatArea($property['area']); ?></span>
                                        <span class="badge bg-secondary"><?php echo $property['bedrooms']; ?> chambre(s)</span>
                                    </div>
                                    <a href="<?php echo getPropertyUrl($property); ?>" class="btn btn-primary">Voir détails</a>
                                </div>
                                <div class="card-footer">
                                    <small class="property-date">Ajouté le <?php echo date('d/m/Y', strtotime($property['date_added'])); ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-4">
                    <a href="properties.php" class="btn btn-outline-primary">Voir toutes les propriétés</a>
                </div>
            </div>
        </section>

        <!-- Section Avantages -->
        <section class="mt-5">
            <div class="container">
                <h2 class="text-center mb-4">Pourquoi choisir ImmoAgence ?</h2>
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="feature-box">
                            <i class="fas fa-home"></i>
                            <h3>Large choix de biens</h3>
                            <p>Des milliers de propriétés à vendre ou à louer dans toute la France pour tous les budgets.</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="feature-box">
                            <i class="fas fa-search"></i>
                            <h3>Recherche intelligente</h3>
                            <p>Utilisez nos filtres avancés pour trouver rapidement le bien qui correspond à vos critères.</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="feature-box">
                            <i class="fas fa-handshake"></i>
                            <h3>Service personnalisé</h3>
                            <p>Nos agents immobiliers vous accompagnent à chaque étape de votre projet immobilier.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

<?php
// Inclure le pied de page
include 'includes/end.php';
?>
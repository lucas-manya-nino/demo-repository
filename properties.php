<?php
include 'includes/start.php';

$pageTitle = "ImmoAgence - Toutes nos propriétés";

$filters = [
    'type' => $_GET['type'] ?? '',
    'price_min' => $_GET['price_min'] ?? '',
    'price_max' => $_GET['price_max'] ?? '',
    'bedrooms' => $_GET['bedrooms'] ?? '',
    'city' => $_GET['city'] ?? ''
];

$properties = filterProperties($filters);
?>

    <div id="properties-app">
        <h1 class="mb-4">Rechercher une propriété</h1>
        <div class="filter-form mb-4">
            <form id="filter-form" method="GET" action="properties.php">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="type" class="form-label">Type de bien</label>
                        <select class="form-select" id="type" name="type" v-model="filters.type">
                            <option value="">Tous les types</option>
                            <option value="appartement">Appartement</option>
                            <option value="maison">Maison</option>
                            <option value="studio">Studio</option>
                            <option value="terrain">Terrain</option>
                            <option value="commerce">Commerce</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="price_min" class="form-label">Prix min</label>
                        <input type="number" class="form-control" id="price_min" name="price_min" placeholder="Min" v-model="filters.price_min">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="price_max" class="form-label">Prix max</label>
                        <input type="number" class="form-control" id="price_max" name="price_max" placeholder="Max" v-model="filters.price_max">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="bedrooms" class="form-label">Chambres min</label>
                        <input type="number" class="form-control" id="bedrooms" name="bedrooms" placeholder="Min" v-model="filters.bedrooms">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="city" class="form-label">Ville</label>
                        <input type="text" class="form-control" id="city" name="city" placeholder="Ville" v-model="filters.city">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Rechercher</button>
                        <button type="button" class="btn btn-outline-secondary ms-2" @click="resetFilters">Réinitialiser</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="mb-3">
            <h2><?php echo count($properties); ?> résultat(s) trouvé(s)</h2>
        </div>

        <div class="row">
            <?php if (empty($properties)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        Aucune propriété ne correspond à vos critères de recherche.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($properties as $property): ?>
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
            <?php endif; ?>
        </div>
    </div>

    <script>

        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('properties-app')) {
                const app = new Vue({
                    el: '#properties-app',
                    data: {
                        properties: <?php echo json_encode($properties); ?>,
                        filters: {
                            type: '<?php echo htmlspecialchars($filters['type']); ?>',
                            price_min: '<?php echo htmlspecialchars($filters['price_min']); ?>',
                            price_max: '<?php echo htmlspecialchars($filters['price_max']); ?>',
                            bedrooms: '<?php echo htmlspecialchars($filters['bedrooms']); ?>',
                            city: '<?php echo htmlspecialchars($filters['city']); ?>'
                        },
                        isLoading: false,
                        sortBy: 'date',
                        sortOrder: 'desc'
                    },
                    methods: {
                        resetFilters() {
                            this.filters = {
                                type: '',
                                price_min: '',
                                price_max: '',
                                bedrooms: '',
                                city: ''
                            };

                            this.$nextTick(() => {
                                document.getElementById('filter-form').submit();
                            });
                        }
                    }
                });
            }
        });
    </script>

<?php
include 'includes/end.php';
?>
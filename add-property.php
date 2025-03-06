<?php
// Définir le titre de la page
$pageTitle = "ImmoAgence - Déposer une annonce";

include 'includes/start.php';

// Variables pour le formulaire
$errors = [];
$success = false;
$property = [
    'title' => '',
    'description' => '',
    'type' => '',
    'price' => '',
    'area' => '',
    'bedrooms' => '',
    'bathrooms' => '',
    'city' => '',
    'address' => '',
    'postal_code' => '',
    'features' => []
];

// Traiter le formulaire soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les données du formulaire
    $property = [
        'title' => $_POST['title'] ?? '',
        'description' => $_POST['description'] ?? '',
        'type' => $_POST['type'] ?? '',
        'price' => $_POST['price'] ?? '',
        'area' => $_POST['area'] ?? '',
        'bedrooms' => $_POST['bedrooms'] ?? '',
        'bathrooms' => $_POST['bathrooms'] ?? '',
        'city' => $_POST['city'] ?? '',
        'address' => $_POST['address'] ?? '',
        'postal_code' => $_POST['postal_code'] ?? '',
        'features' => isset($_POST['features']) ? json_decode($_POST['features'], true) : []
    ];

    // Gérer l'upload d'image
    $imageName = 'default-property.jpg'; // Image par défaut

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Vérifier l'extension
        if (in_array($fileExt, $allowed)) {
            // Générer un nom de fichier unique
            $newName = uniqid() . '.' . $fileExt;
            $uploadPath = 'assets/images/' . $newName;

            // Déplacer le fichier
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $imageName = $newName;
            }
        } else {
            $errors['image'] = 'Format d\'image non autorisé. Les formats acceptés sont: ' . implode(', ', $allowed);
        }
    }

    $property['image'] = $imageName;

    // Valider les données
    $errors = validatePropertyData($property);

    // Si pas d'erreurs, ajouter la propriété
    if (empty($errors)) {
        $propertyId = addProperty($property);

        if ($propertyId) {
            $success = true;
            // Réinitialiser le formulaire après l'ajout réussi
            $property = [
                'title' => '',
                'description' => '',
                'type' => '',
                'price' => '',
                'area' => '',
                'bedrooms' => '',
                'bathrooms' => '',
                'city' => '',
                'address' => '',
                'postal_code' => '',
                'features' => []
            ];
        } else {
            $errors['general'] = 'Une erreur est survenue lors de l\'ajout de la propriété.';
        }
    }
}

// Inclure l'en-tête

?>

    <div id="add-property-app">
        <h1 class="mb-4">Déposer une annonce</h1>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <strong>Succès !</strong> Votre annonce a été ajoutée avec succès.
                <a href="properties.php" class="alert-link">Voir toutes les propriétés</a>
            </div>
        <?php endif; ?>

        <?php if (isset($errors['general'])): ?>
            <div class="alert alert-danger">
                <?php echo $errors['general']; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2>Informations sur le bien</h2>
            </div>
            <div class="card-body">
                <form class="add-property-form" method="POST" action="add-property.php" enctype="multipart/form-data" @submit="validateForm">
                    <!-- Informations de base -->
                    <div class="mb-4">
                        <h4>Informations générales</h4>

                        <div class="mb-3">
                            <label for="title" class="form-label required">Titre de l'annonce</label>
                            <input type="text" class="form-control <?php echo isset($errors['title']) ? 'is-invalid' : ''; ?>" id="title" name="title" value="<?php echo htmlspecialchars($property['title']); ?>" required>
                            <?php if (isset($errors['title'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['title']; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label required">Description</label>
                            <textarea class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>" id="description" name="description" rows="5" required><?php echo htmlspecialchars($property['description']); ?></textarea>
                            <?php if (isset($errors['description'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['description']; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label required">Type de bien</label>
                            <select class="form-select <?php echo isset($errors['type']) ? 'is-invalid' : ''; ?>" id="type" name="type" required>
                                <option value="" disabled <?php echo empty($property['type']) ? 'selected' : ''; ?>>Sélectionnez un type</option>
                                <option value="appartement" <?php echo $property['type'] == 'appartement' ? 'selected' : ''; ?>>Appartement</option>
                                <option value="maison" <?php echo $property['type'] == 'maison' ? 'selected' : ''; ?>>Maison</option>
                                <option value="studio" <?php echo $property['type'] == 'studio' ? 'selected' : ''; ?>>Studio</option>
                                <option value="terrain" <?php echo $property['type'] == 'terrain' ? 'selected' : ''; ?>>Terrain</option>
                                <option value="commerce" <?php echo $property['type'] == 'commerce' ? 'selected' : ''; ?>>Commerce</option>
                            </select>
                            <?php if (isset($errors['type'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['type']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Caractéristiques du bien -->
                    <div class="mb-4">
                        <h4>Caractéristiques du bien</h4>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label required">Prix (€)</label>
                                <input type="number" class="form-control <?php echo isset($errors['price']) ? 'is-invalid' : ''; ?>" id="price" name="price" value="<?php echo htmlspecialchars($property['price']); ?>" min="0" required>
                                <?php if (isset($errors['price'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo $errors['price']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="area" class="form-label required">Superficie (m²)</label>
                                <input type="number" class="form-control <?php echo isset($errors['area']) ? 'is-invalid' : ''; ?>" id="area" name="area" value="<?php echo htmlspecialchars($property['area']); ?>" min="0" step="0.01" required>
                                <?php if (isset($errors['area'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo $errors['area']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="bedrooms" class="form-label">Nombre de chambres</label>
                                <input type="number" class="form-control <?php echo isset($errors['bedrooms']) ? 'is-invalid' : ''; ?>" id="bedrooms" name="bedrooms" value="<?php echo htmlspecialchars($property['bedrooms']); ?>" min="0">
                                <?php if (isset($errors['bedrooms'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo $errors['bedrooms']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="bathrooms" class="form-label">Nombre de salles de bain</label>
                                <input type="number" class="form-control <?php echo isset($errors['bathrooms']) ? 'is-invalid' : ''; ?>" id="bathrooms" name="bathrooms" value="<?php echo htmlspecialchars($property['bathrooms']); ?>" min="0">
                                <?php if (isset($errors['bathrooms'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo $errors['bathrooms']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Adresse -->
                    <div class="mb-4">
                        <h4>Adresse</h4>

                        <div class="mb-3">
                            <label for="address" class="form-label">Adresse</label>
                            <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($property['address']); ?>">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="postal_code" class="form-label">Code postal</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($property['postal_code']); ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label required">Ville</label>
                                <input type="text" class="form-control <?php echo isset($errors['city']) ? 'is-invalid' : ''; ?>" id="city" name="city" value="<?php echo htmlspecialchars($property['city']); ?>" required>
                                <?php if (isset($errors['city'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo $errors['city']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Caractéristiques supplémentaires -->
                    <div class="mb-4">
                        <h4>Caractéristiques supplémentaires</h4>

                        <div class="mb-3">
                            <label class="form-label">Équipements et services</label>
                            <div class="row">
                                <div class="col-md-4 mb-2" v-for="feature in availableFeatures">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" :value="feature" :id="'feature-' + feature" @change="toggleFeature(feature)" :checked="property.features.includes(feature)">
                                        <label class="form-check-label" :for="'feature-' + feature">{{ feature }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="new-feature" class="form-label">Ajouter une caractéristique personnalisée</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="new-feature" v-model="newFeature" placeholder="Ex: Piscine chauffée">
                                <button type="button" class="btn btn-outline-primary" @click="addCustomFeature">Ajouter</button>
                            </div>
                        </div>

                        <div class="mb-3" v-if="property.features.length > 0">
                            <label class="form-label">Caractéristiques sélectionnées</label>
                            <div class="d-flex flex-wrap gap-2">
                            <span v-for="(feature, index) in property.features" :key="index" class="badge bg-primary">
                                {{ feature }}
                                <button type="button" class="btn-close btn-close-white ms-1" @click="removeFeature(index)" style="font-size: 0.5rem;"></button>
                            </span>
                            </div>
                            <input type="hidden" name="features" :value="JSON.stringify(property.features)">
                        </div>
                    </div>

                    <!-- Image -->
                    <div class="mb-4">
                        <h4>Image</h4>

                        <div class="mb-3">
                            <label for="image" class="form-label">Photo du bien</label>
                            <input type="file" class="form-control <?php echo isset($errors['image']) ? 'is-invalid' : ''; ?>" id="image" name="image" accept="image/*">
                            <div class="form-text">Formats acceptés: JPG, JPEG, PNG, GIF. Taille maximale: 5 Mo.</div>
                            <?php if (isset($errors['image'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['image']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Bouton de soumission -->
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary btn-lg" :disabled="isSubmitting">
                        <span v-if="isSubmitting">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Envoi en cours...
                        </span>
                            <span v-else>Publier l'annonce</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('add-property-app')) {
                const app = new Vue({
                    el: '#add-property-app',
                    data: {
                        property: {
                            title: '<?php echo addslashes($property['title']); ?>',
                            description: '<?php echo addslashes($property['description']); ?>',
                            type: '<?php echo addslashes($property['type']); ?>',
                            price: '<?php echo addslashes($property['price']); ?>',
                            area: '<?php echo addslashes($property['area']); ?>',
                            bedrooms: '<?php echo addslashes($property['bedrooms']); ?>',
                            bathrooms: '<?php echo addslashes($property['bathrooms']); ?>',
                            city: '<?php echo addslashes($property['city']); ?>',
                            address: '<?php echo addslashes($property['address']); ?>',
                            postal_code: '<?php echo addslashes($property['postal_code']); ?>',
                            features: <?php echo json_encode($property['features']); ?>
                        },
                        availableFeatures: [
                            'Balcon', 'Terrasse', 'Jardin', 'Piscine', 'Garage',
                            'Parking', 'Ascenseur', 'Cave', 'Cheminée', 'Climatisation'
                        ],
                        newFeature: '',
                        errors: <?php echo json_encode($errors); ?>,
                        isSubmitting: false,
                        submitSuccess: <?php echo $success ? 'true' : 'false'; ?>
                    },
                    methods: {
                        addCustomFeature() {
                            if (this.newFeature.trim() !== '' && !this.property.features.includes(this.newFeature)) {
                                this.property.features.push(this.newFeature);
                                this.newFeature = '';
                            }
                        },
                        removeFeature(index) {
                            this.property.features.splice(index, 1);
                        },
                        toggleFeature(feature) {
                            const index = this.property.features.indexOf(feature);
                            if (index === -1) {
                                this.property.features.push(feature);
                            } else {
                                this.property.features.splice(index, 1);
                            }
                        },
                        validateForm() {
                            this.isSubmitting = true;
                            // La validation côté serveur prendra le relais
                            return true;
                        }
                    }
                });
            }
        });
    </script>

<?php
// Inclure le pied de page
include 'includes/end.php';
?>
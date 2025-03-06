document.addEventListener('DOMContentLoaded', function() {
    // Initialisation de Vue uniquement si l'élément avec l'ID correspondant existe

    // App Vue pour la page d'accueil
    if (document.getElementById('home-app')) {
        new Vue({
            el: '#home-app',
            data: {
                featuredProperties: [],
                isLoading: true
            },
            mounted() {
                // Les données seront déjà injectées en PHP
                this.isLoading = false;
            }
        });
    }

    // App Vue pour la liste des propriétés
    if (document.getElementById('properties-app')) {
        new Vue({
            el: '#properties-app',
            data: {
                properties: [],
                filters: {
                    type: '',
                    price_min: '',
                    price_max: '',
                    bedrooms: '',
                    city: ''
                },
                isLoading: false,
                sortBy: 'date',
                sortOrder: 'desc'
            },
            computed: {
                sortedProperties() {
                    // Le tri se fait côté PHP, mais on peut appliquer un tri supplémentaire côté client
                    return this.properties;
                }
            },
            methods: {
                // Réinitialiser les filtres
                resetFilters() {
                    this.filters = {
                        type: '',
                        price_min: '',
                        price_max: '',
                        bedrooms: '',
                        city: ''
                    };
                    document.getElementById('filter-form').submit();
                }
            }
        });
    }

    // App Vue pour la page de détail d'une propriété
    if (document.getElementById('property-detail-app')) {
        new Vue({
            el: '#property-detail-app',
            data: {
                property: {},
                isLoading: false
            }
        });
    }

    // App Vue pour le formulaire d'ajout de propriété
    if (document.getElementById('add-property-app')) {
        new Vue({
            el: '#add-property-app',
            data: {
                property: {
                    title: '',
                    description: '',
                    type: '',
                    price: '',
                    area: '',
                    bedrooms: '',
                    bathrooms: '',
                    city: '',
                    address: '',
                    postal_code: '',
                    features: []
                },
                availableFeatures: [
                    'Balcon', 'Terrasse', 'Jardin', 'Piscine', 'Garage',
                    'Parking', 'Ascenseur', 'Cave', 'Cheminée', 'Climatisation'
                ],
                newFeature: '',
                errors: {},
                isSubmitting: false,
                submitSuccess: false
            },
            methods: {
                // Ajouter une caractéristique personnalisée
                addCustomFeature() {
                    if (this.newFeature.trim() !== '' && !this.property.features.includes(this.newFeature)) {
                        this.property.features.push(this.newFeature);
                        this.newFeature = '';
                    }
                },
                // Supprimer une caractéristique
                removeFeature(index) {
                    this.property.features.splice(index, 1);
                },
                // Basculer une caractéristique (ajouter/supprimer)
                toggleFeature(feature) {
                    const index = this.property.features.indexOf(feature);
                    if (index === -1) {
                        this.property.features.push(feature);
                    } else {
                        this.property.features.splice(index, 1);
                    }
                },
                // Valider le formulaire côté client
                validateForm() {
                    this.errors = {};
                    let isValid = true;

                    // Validation du titre
                    if (!this.property.title) {
                        this.errors.title = 'Le titre est obligatoire';
                        isValid = false;
                    } else if (this.property.title.length < 5) {
                        this.errors.title = 'Le titre doit comporter au moins 5 caractères';
                        isValid = false;
                    }

                    // Validation de la description
                    if (!this.property.description) {
                        this.errors.description = 'La description est obligatoire';
                        isValid = false;
                    } else if (this.property.description.length < 20) {
                        this.errors.description = 'La description doit comporter au moins 20 caractères';
                        isValid = false;
                    }

                    // Validation du type
                    if (!this.property.type) {
                        this.errors.type = 'Le type de bien est obligatoire';
                        isValid = false;
                    }

                    // Validation du prix
                    if (!this.property.price) {
                        this.errors.price = 'Le prix est obligatoire';
                        isValid = false;
                    } else if (isNaN(this.property.price) || this.property.price <= 0) {
                        this.errors.price = 'Le prix doit être un nombre positif';
                        isValid = false;
                    }

                    // Validation de la superficie
                    if (!this.property.area) {
                        this.errors.area = 'La superficie est obligatoire';
                        isValid = false;
                    } else if (isNaN(this.property.area) || this.property.area <= 0) {
                        this.errors.area = 'La superficie doit être un nombre positif';
                        isValid = false;
                    }

                    // Validation de la ville
                    if (!this.property.city) {
                        this.errors.city = 'La ville est obligatoire';
                        isValid = false;
                    }

                    return isValid;
                }
            }
        });
    }
});
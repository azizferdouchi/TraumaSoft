# TraumaSoft - Gestion de Cabinet de Traumatologie

**TraumaSoft** est une plateforme complète de gestion pour les cabinets de traumatologie, intégrant l'intelligence artificielle pour améliorer les diagnostics, optimiser les traitements et améliorer l'efficacité globale.

## 📋 Table des Matières

- [🚀 Fonctionnalités](#-fonctionnalités)
- [🛠️ Technologies Utilisées](#️-technologies-utilisées)
- [📦 Installation](#-installation)
- [🔧 Configuration](#-configuration)
- [🏃‍♂️ Démarrage](#-démarrage)
- [🎯 Architecture](#-architecture)
- [🤖 Module IA](#-module-ia)
- [📊 Fonctionnalités du Backend](#-fonctionnalités-du-backend)
- [🎨 Fonctionnalités du Frontend](#-fonctionnalités-du-frontend)
- [🔒 Sécurité](#-sécurité)
- [📈 Performances](#-performances)
- [🤝 Contribution](#-contribution)
- [📄 Licence](#-licence)

---

## 🚀 Fonctionnalités

### 🏥 Gestion Médicale
- **Gestion des patients** : Dossiers médicaux complets avec historique
- **Rendez-vous** : Planification, gestion et suivi des consultations
- **Diagnostics** : Classification et suivi des pathologies
- **Prescriptions** : Génération et suivi des traitements
- **Opérations** : Planification et suivi des interventions chirurgicales
- **Certificats médicaux** : Génération de certificats officiels

### 💰 Gestion Financière
- **Facturation** : Génération automatique des factures
- **Paiements** : Suivi des paiements et des créances
- **Rapports financiers** : Analyse des revenus et des dépenses
- **Assurances** : Gestion des remboursements

### 📊 Tableau de Bord
- **Statistiques en temps réel** : Vue d'ensemble de l'activité
- **Salle d'attente virtuelle** : Suivi des patients en attente
- **Calendrier** : Visualisation des rendez-vous
- **Notifications** : Alertes et rappels

### 🤖 Intelligence Artificielle
- **Analyse des diagnostics** : Détection automatique des pathologies
- **Suggestions de traitements** : Protocoles personnalisés
- **Prédiction de récupération** : Estimation des durées de convalescence
- **Optimisation du planning** : Organisation intelligente des rendez-vous
- **Analyse des images DICOM** : Détection d'anomalies (intégration future)

### 📈 Rapports et Analyses
- **Statistiques médicales** : Analyse des pathologies les plus fréquentes
- **Performance du cabinet** : Indicateurs clés de performance
- **Historique des patients** : Suivi complet des dossiers
- **Analyse des tendances** : Identification des patterns

---

## 🛠️ Technologies Utilisées

### Backend
- **PHP 8.0+** : Langage principal du backend
- **MySQL** : Base de données relationnelle
- **PDO** : Accès sécurisé à la base de données
- **JWT (Firebase PHP JWT)** : Authentification sécurisée
- **REST API** : Architecture moderne d'API

### Frontend
- **HTML5/CSS3** : Structure et style
- **Bootstrap 5** : Framework CSS pour l'interface
- **JavaScript (ES6+)** : Logique frontale
- **Font Awesome** : Icônes
- **Fetch API** : Communication avec le backend

### IA
- **Algorithmes de classification** : Détection des pathologies
- **Système expert** : Suggestions de traitements
- **Analyse statistique** : Prédictions de récupération

---

## 📦 Installation

### Prérequis
- PHP 8.0 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web (Apache, Nginx)
- Composer (pour les dépendances PHP)
- Node.js (optionnel, pour le développement frontend)

### Étapes d'installation

1. **Cloner le dépôt**
```bash
cd /workspace
git clone https://github.com/azizferdouchi/TraumaSoft.git
cd TraumaSoft
```

2. **Installer les dépendances PHP**
```bash
composer install
```

3. **Configurer la base de données**
   - Créez une base de données MySQL : `traumasoft`
   - Importez le schema :
```bash
mysql -u root -p traumasoft < database/traumasoft.sql
```
   - Ou utilisez le script d'import :
```bash
php database/import_database.php
```

4. **Configurer l'application**
   - Modifiez `backend/config/database.php` avec vos identifiants MySQL
   - Configurez le `composer.json` si nécessaire

5. **Configurer le serveur web**
   - Pointez votre serveur vers le dossier `backend/` pour l'API
   - Ou configurez le dossier racine sur `/workspace/github__azizferdouchi__TraumaSoft`

---

## 🔧 Configuration

### Configuration de la base de données
Éditez le fichier `backend/config/database.php` :

```php
<?php
class Database {
    private $host = "localhost";
    private $db_name = "traumasoft";
    private $username = "votre_utilisateur";
    private $password = "votre_mot_de_passe";
    // ...
}
```

### Configuration de l'API
Le backend est configuré pour fonctionner avec :
- CORS activé pour toutes les origines
- Format JSON pour les requêtes et réponses
- Authentification JWT

### Configuration du Frontend
Modifiez le fichier `frontend/js/api.js` pour configurer l'URL de base :

```javascript
class TraumaSoftAPI {
    constructor() {
        this.baseUrl = 'http://votre-domaine.com/traumasoft/backend';
        // ...
    }
}
```

---

## 🏃‍♂️ Démarrage

### Démarrer le serveur de développement

#### Avec PHP intégré
```bash
cd backend
php -S localhost:8000
```

#### Avec Apache/Nginx
1. Configurez votre serveur pour pointer vers le dossier `backend/`
2. Accédez à `http://localhost/traumasoft/backend`

### Accéder à l'application
- **Frontend** : Ouvrez `frontend/index.html` dans votre navigateur
- **Backend API** : `http://localhost:8000` (ou votre configuration)
- **Documentation API** : `http://localhost:8000/api`

### Identifiants par défaut
Après l'import de la base de données, vous pouvez vous connecter avec :

| Email | Mot de passe | Rôle |
|-------|--------------|------|
| admin@traumasoft.com | password | Admin |
| j.dupont@traumasoft.com | password | Médecin |
| m.martin@traumasoft.com | password | Secrétaire |

---

## 🎯 Architecture

### Structure des dossiers

```
TraumaSoft/
├── backend/
│   ├── config/
│   │   └── database.php        # Configuration de la base de données
│   ├── controllers/            # Contrôleurs de l'API
│   │   ├── PatientController.php
│   │   ├── AppointmentController.php
│   │   ├── DiagnosisController.php
│   │   ├── AIController.php     # Contrôleur IA
│   │   └── AuthController.php   # Authentification
│   ├── models/                 # Modèles de données
│   │   ├── Patient.php
│   │   ├── Appointment.php
│   │   ├── Diagnosis.php
│   │   └── User.php
│   ├── ai/                      # Module d'Intelligence Artificielle
│   │   └── AIAnalyzer.php        # Analyseur IA
│   └── index.php                # Point d'entrée de l'API
│
├── frontend/
│   ├── css/                    # Styles CSS
│   ├── js/                     # Scripts JavaScript
│   │   ├── api.js               # Client API
│   │   ├── traumasoft.js        # Logique principale
│   │   └── utils.js             # Fonctions utilitaires
│   ├── sections/               # Sections HTML
│   ├── index.html              # Page principale
│   └── login.html              # Page de connexion
│
├── database/
│   ├── traumasoft.sql          # Schema de la base de données
│   └── import_database.php     # Script d'import
│
├── composer.json              # Dépendances PHP
├── .htaccess                   # Configuration Apache
└── README.md                   # Documentation
```

### Architecture de l'API

L'API suit le pattern **MVC** (Modèle-Vue-Contrôleur) :

1. **Requête** → Routage → **Contrôleur**
2. **Contrôleur** → **Modèle** (accès aux données)
3. **Modèle** → Base de données
4. **Contrôleur** → Réponse JSON

### Routage

Le système de routage est basé sur les URL :

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/patients` | Liste des patients |
| GET | `/patients/:id` | Détails d'un patient |
| POST | `/patients` | Créer un patient |
| PUT | `/patients/:id` | Mettre à jour un patient |
| DELETE | `/patients/:id` | Supprimer un patient |
| GET | `/ai/analyze/diagnostic/:id` | Analyser un diagnostic avec IA |
| GET | `/ai/performance` | Analyse des performances |

Voir la documentation complète : `GET /api`

---

## 🤖 Module IA

Le module d'Intelligence Artificielle de TraumaSoft offre des fonctionnalités avancées :

### 🔍 Analyse des Diagnostics
- **Détection automatique** : Identification des types de pathologies à partir des descriptions
- **Classification** : Catégorisation des diagnostics (fractures, entorses, hernies, etc.)
- **Évaluation de la sévérité** : Détermination automatique du niveau de gravité

### 💊 Suggestions de Traitements
- **Protocoles standardisés** : Suggestions basées sur les bonnes pratiques
- **Médicaments recommandés** : Posologies et durées adaptées
- **Protocoles de rééducation** : Programmes de récupération personnalisés

### ⏱️ Prédiction de Récupération
- **Estimation des durées** : Calcul basé sur le type de pathologie, la sévérité et l'âge
- **Facteurs pris en compte** : Type, localisation, sévérité, âge du patient
- **Recommandations** : Conseils pour optimiser la récupération

### 📅 Optimisation du Planning
- **Tri par priorité** : Organisation intelligente des rendez-vous
- **Allocation des ressources** : Attribution des salles et du personnel
- **Équilibrage de la charge** : Répartition optimale des consultations

### 📊 Analyse des Performances
- **Statistiques médicales** : Analyse des pathologies les plus fréquentes
- **Performance financière** : Suivi des revenus et des coûts
- **Indicateurs clés** : Temps moyen de consultation, taux d'occupation, etc.

### Exemple d'utilisation de l'API IA

```javascript
// Analyser un diagnostic
const response = await api.analyzeDiagnostic(123);
console.log(response.data.analyse.type_detecte); // "fracture"
console.log(response.data.suggestions.traitements); // ["immobilisation", "plâtre", ...]

// Prédire la durée de récupération
const prediction = await api.predictRecovery(123);
console.log(prediction.data.duree_estimee); // "6 semaines"

// Analyser les performances du cabinet
const performance = await api.getPerformanceAnalysis();
console.log(performance.data.nouveaux_patients); // 45
```

---

## 📊 Fonctionnalités du Backend

### Modèles de Données

| Modèle | Description |
|--------|-------------|
| `User` | Utilisateurs (médecins, secrétaires, etc.) |
| `Patient` | Patients avec historique médical |
| `Appointment` | Rendez-vous et consultations |
| `Diagnosis` | Diagnostics et examens |
| `Prescription` | Prescriptions médicales |
| `Operation` | Opérations chirurgicales |
| `Certificate` | Certificats médicaux |
| `Payment` | Paiements et factures |
| `AIAnalysis` | Analyses effectuées par l'IA |

### Contrôleurs Principaux

| Contrôleur | Responsabilités |
|------------|------------------|
| `PatientController` | Gestion des patients |
| `AppointmentController` | Gestion des rendez-vous |
| `DiagnosisController` | Gestion des diagnostics |
| `PrescriptionController` | Gestion des prescriptions |
| `OperationController` | Gestion des opérations |
| `CertificateController` | Gestion des certificats |
| `FinanceController` | Gestion financière |
| `DashboardController` | Données du tableau de bord |
| `AIController` | Fonctionnalités d'IA |
| `AuthController` | Authentification |

---

## 🎨 Fonctionnalités du Frontend

### Pages Principales

| Page | Description |
|------|-------------|
| `login.html` | Connexion des utilisateurs |
| `index.html` | Interface principale avec toutes les sections |

### Sections de l'Interface

| Section | Description |
|---------|-------------|
| Tableau de bord | Vue d'ensemble avec statistiques |
| Patients | Liste et gestion des patients |
| Rendez-vous | Calendrier et gestion des consultations |
| Salle d'attente | Suivi des patients en attente |
| Opérations | Planification et suivi des interventions |
| Certificats | Génération et gestion des certificats |
| Finance | Gestion financière et facturation |
| Rapports | Analyses et statistiques |

### Composants Réutilisables

- **Modals** : Fenêtres modales pour les formulaires
- **Tableaux** : Affichage des données en tableaux
- **Cartes** : Cartes d'information et de statistiques
- **Notifications** : Messages d'alerte et de succès
- **Chargement** : Indicateurs de progression

---

## 🔒 Sécurité

### Authentification
- **JWT (JSON Web Tokens)** : Tokens sécurisés pour l'authentification
- **Durée limitée** : Tokens valides pendant 1 heure
- **Rafraîchissement** : Mécanisme de refresh token

### Protection des Données
- **Chiffrement des mots de passe** : Utilisation de `password_hash()` et `password_verify()`
- **Validation des entrées** : Protection contre les injections SQL
- **Requêtes préparées** : Utilisation de PDO avec des paramètres

### Sécurité de l'API
- **CORS** : Configuration des origines autorisées
- **Headers de sécurité** : Protection contre les attaques courantes
- **Authentification requise** : Accès protégé pour les endpoints sensibles

### Bonnes Pratiques
- **Ne jamais stocker de mots de passe en clair**
- **Utiliser HTTPS en production**
- **Limiter les permissions des utilisateurs**
- **Journaliser les activités**

---

## 📈 Performances

### Optimisations Backend
- **Cache des requêtes** : Réduction des accès à la base de données
- **Indexation** : Optimisation des requêtes SQL
- **Pagination** : Chargement des données par pages
- **Compression** : Réduction de la taille des réponses

### Optimisations Frontend
- **Chargement paresseux** : Chargement des données à la demande
- **Cache local** : Stockage des données fréquemment utilisées
- **Minification** : Réduction de la taille des fichiers
- **Bundle** : Regroupement des fichiers CSS/JS

### Optimisations IA
- **Cache des analyses** : Stockage des résultats pour éviter les recalculs
- **Algorithmes efficaces** : Optimisation des calculs de prédiction
- **Base de connaissances** : Accès rapide aux informations médicales

---

## 🤝 Contribution

Les contributions sont les bienvenues ! Voici comment contribuer :

1. **Forker le dépôt**
2. **Créer une branche** (`git checkout -b feature/nouvelle-fonctionnalite`)
3. **Faire vos modifications**
4. **Tester vos modifications**
5. **Committer vos changements** (`git commit -m 'Ajout de la fonctionnalité X'`)
6. **Pousser vers la branche** (`git push origin feature/nouvelle-fonctionnalite`)
7. **Ouvrir une Pull Request**

### Règles de Contribution
- Respecter le style de code existant
- Ajouter des commentaires explicatifs
- Tester toutes les modifications
- Documenter les nouvelles fonctionnalités
- Suivre les conventions de nommage

---

## 📄 Licence

Ce projet est sous licence **MIT**. Vous êtes libre de :

- Utiliser ce logiciel à des fins personnelles ou commerciales
- Modifier le code source
- Distribuer le logiciel
- Utiliser le logiciel dans vos propres projets

Voir le fichier [LICENSE](LICENSE) pour plus de détails.

---

## 🙏 Remerciements

- **Bootstrap** : Framework CSS pour l'interface
- **Font Awesome** : Icônes de qualité
- **Firebase PHP JWT** : Implémentation JWT pour PHP
- **Toute la communauté Open Source** : Pour les outils et bibliothèques utilisés

---

## 📞 Support

Pour toute question ou problème, vous pouvez :

- **Ouvrir une Issue** sur GitHub
- **Consulter la documentation**
- **Contacter l'équipe** : contact@traumasoft.com

---

**TraumaSoft © 2024** - Gestion Intelligente de Cabinet de Traumatologie

*"Améliorer les soins grâce à la technologie et à l'intelligence artificielle"*

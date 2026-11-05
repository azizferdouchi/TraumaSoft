-- Base de données TraumaSoft - Script SQL
-- Version: 1.0.0
-- Description: Schema complet pour la gestion d'un cabinet de traumatologie

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS traumasoft;
USE traumasoft;

-- Table des utilisateurs (médecins et personnel)
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('admin', 'medecin', 'secretaire', 'infirmier') NOT NULL DEFAULT 'medecin',
    specialite VARCHAR(100),
    telephone VARCHAR(20),
    adresse TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME ON UPDATE CURRENT_TIMESTAMP,
    statut ENUM('actif', 'inactif') DEFAULT 'actif',
    dernier_connexion DATETIME,
    token_reset_password VARCHAR(255),
    token_expiration DATETIME
);

-- Table des patients
CREATE TABLE IF NOT EXISTS patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    date_naissance DATE,
    sexe ENUM('Homme', 'Femme', 'Autre') DEFAULT 'Homme',
    adresse TEXT,
    ville VARCHAR(100),
    code_postal VARCHAR(20),
    telephone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    profession VARCHAR(100),
    situation_familiale ENUM('Célibataire', 'Marié(e)', 'Divorcé(e)', 'Veuf(ve)', 'Autre'),
    assurance_compagnie VARCHAR(100),
    assurance_numero VARCHAR(50),
    assurance_type ENUM('CNOPS', 'CNSS', 'Assurance privée', 'Aucune', 'Autre'),
    assurance_expiration DATE,
    groupe_sanguin VARCHAR(10),
    allergies TEXT,
    antecedents_medicaux TEXT,
    antecedents_chirurgicaux TEXT,
    antecedents_familiaux TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME ON UPDATE CURRENT_TIMESTAMP,
    statut ENUM('actif', 'inactif', 'archivé') DEFAULT 'actif',
    photo_profil VARCHAR(255),
    notes TEXT
);

-- Table des rendez-vous
CREATE TABLE IF NOT EXISTS rendez_vous (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    medecin_id INT,
    date_rendez_vous DATETIME NOT NULL,
    date_fin DATETIME,
    motif TEXT NOT NULL,
    statut ENUM('planifié', 'confirmé', 'en cours', 'terminé', 'annulé', 'reporté', 'sans rendez-vous') DEFAULT 'planifié',
    priorite ENUM('normale', 'urgente', 'très urgente') DEFAULT 'normale',
    type_consultation ENUM('consultation', 'suivi', 'urgence', 'contrôle', 'opération', 'autre'),
    notes TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (medecin_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
);

-- Table des diagnostics
CREATE TABLE IF NOT EXISTS diagnostics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    rendez_vous_id INT,
    medecin_id INT,
    date_diagnostic DATETIME DEFAULT CURRENT_TIMESTAMP,
    type_diagnostic ENUM('fracture', 'entorse', 'luxeation', 'tendinite', 'rupture ligamentaire', 'arthrose', 'hernie discale', 'scolios', 'autre') NOT NULL,
    description TEXT NOT NULL,
    localisation VARCHAR(100),
    severite ENUM('léger', 'modéré', 'sévère', 'critique') DEFAULT 'modéré',
    examen_clinique TEXT,
    examen_complementaire TEXT,
    conclusion TEXT,
    recommandations TEXT,
    date_modification DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (rendez_vous_id) REFERENCES rendez_vous(id) ON DELETE SET NULL,
    FOREIGN KEY (medecin_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
);

-- Table des prescriptions
CREATE TABLE IF NOT EXISTS prescriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    diagnostic_id INT,
    medecin_id INT,
    date_prescription DATETIME DEFAULT CURRENT_TIMESTAMP,
    type_prescription ENUM('médicament', 'rééducation', 'examen', 'chirurgie', 'autre') NOT NULL,
    nom_medicament VARCHAR(200),
    dosage VARCHAR(100),
    frequence VARCHAR(100),
    duree VARCHAR(100),
    quantite INT,
    instructions TEXT,
    statut ENUM('active', 'terminée', 'annulée') DEFAULT 'active',
    date_fin DATETIME,
    date_modification DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (diagnostic_id) REFERENCES diagnostics(id) ON DELETE SET NULL,
    FOREIGN KEY (medecin_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
);

-- Table des opérations
CREATE TABLE IF NOT EXISTS operations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    medecin_id INT,
    diagnostic_id INT,
    date_operation DATETIME NOT NULL,
    type_operation VARCHAR(200) NOT NULL,
    description TEXT,
    lieu VARCHAR(200) NOT NULL,
    duree_estimee VARCHAR(50),
    duree_reelle VARCHAR(50),
    anesthesie_type ENUM('locale', 'régionale', 'générale') DEFAULT 'locale',
    anesthesiste_id INT,
    statut ENUM('planifiée', 'confirmée', 'en cours', 'terminée', 'reportée', 'annulée') DEFAULT 'planifiée',
    complication BOOLEAN DEFAULT FALSE,
    details_complication TEXT,
    protocole_operatoire TEXT,
    materiel_utilise TEXT,
    notes_pre_operatoires TEXT,
    notes_post_operatoires TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (medecin_id) REFERENCES utilisateurs(id) ON DELETE SET NULL,
    FOREIGN KEY (diagnostic_id) REFERENCES diagnostics(id) ON DELETE SET NULL,
    FOREIGN KEY (anesthesiste_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
);

-- Table des certificats médicaux
CREATE TABLE IF NOT EXISTS certificats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    medecin_id INT,
    diagnostic_id INT,
    type_certificat ENUM('arrêt de travail', 'certificat médical', 'certificat d\'accident', 'certificat de guérison', 'certificat de contre-indication', 'autre') NOT NULL,
    titre VARCHAR(200) NOT NULL,
    contenu TEXT NOT NULL,
    date_debut DATE,
    date_fin DATE,
    duree_arret INT,
    statut ENUM('brouillon', 'validé', 'imprimé', 'envoyé', 'archivé') DEFAULT 'brouillon',
    reference VARCHAR(100),
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (medecin_id) REFERENCES utilisateurs(id) ON DELETE SET NULL,
    FOREIGN KEY (diagnostic_id) REFERENCES diagnostics(id) ON DELETE SET NULL
);

-- Table des examens complémentaires
CREATE TABLE IF NOT EXISTS examens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    rendez_vous_id INT,
    diagnostic_id INT,
    type_examen ENUM('radiographie', 'IRM', 'scanner', 'échographie', 'analyse sanguine', 'autre') NOT NULL,
    description TEXT,
    date_examen DATETIME,
    lieu VARCHAR(200),
    resultat TEXT,
    interpretation TEXT,
    fichier_resultat VARCHAR(255),
    statut ENUM('prescrit', 'planifié', 'réalisé', 'annulé') DEFAULT 'prescrit',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (rendez_vous_id) REFERENCES rendez_vous(id) ON DELETE SET NULL,
    FOREIGN KEY (diagnostic_id) REFERENCES diagnostics(id) ON DELETE SET NULL
);

-- Table des paiements et factures
CREATE TABLE IF NOT EXISTS paiements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    rendez_vous_id INT,
    operation_id INT,
    certificat_id INT,
    type_paiement ENUM('consultation', 'opération', 'examen', 'certificat', 'autre') NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    montant_paye DECIMAL(10,2) DEFAULT 0,
    montant_restant DECIMAL(10,2) DEFAULT 0,
    mode_paiement ENUM('espèces', 'carte', 'virement', 'chèque', 'assurance', 'autre') DEFAULT 'espèces',
    statut ENUM('en attente', 'partiel', 'payé', 'annulé', 'remboursé') DEFAULT 'en attente',
    date_paiement DATETIME,
    date_echeance DATETIME,
    reference_facture VARCHAR(100),
    notes TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (rendez_vous_id) REFERENCES rendez_vous(id) ON DELETE SET NULL,
    FOREIGN KEY (operation_id) REFERENCES operations(id) ON DELETE SET NULL,
    FOREIGN KEY (certificat_id) REFERENCES certificats(id) ON DELETE SET NULL
);

-- Table des notes et historique médical
CREATE TABLE IF NOT EXISTS historique_medical (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    utilisateur_id INT,
    type_entree ENUM('consultation', 'diagnostic', 'prescription', 'operation', 'examen', 'certificat', 'note', 'autre') NOT NULL,
    titre VARCHAR(200),
    description TEXT,
    date_evenement DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
);

-- Table des notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT,
    patient_id INT,
    type_notification ENUM('rendez_vous', 'operation', 'paiement', 'certificat', 'urgence', 'autre') NOT NULL,
    titre VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    statut ENUM('non lu', 'lu', 'archivé') DEFAULT 'non lu',
    priorite ENUM('basse', 'normale', 'haute', 'urgente') DEFAULT 'normale',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_lecture DATETIME,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
);

-- Table des paramètres du système
CREATE TABLE IF NOT EXISTS parametres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cle VARCHAR(100) NOT NULL UNIQUE,
    valeur TEXT,
    description TEXT,
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table des logs d'activité
CREATE TABLE IF NOT EXISTS logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT,
    action VARCHAR(200) NOT NULL,
    table_concernee VARCHAR(100),
    id_entree INT,
    details TEXT,
    ip_address VARCHAR(50),
    user_agent TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
);

-- Table des analyses IA (pour l'intégration de l'intelligence artificielle)
CREATE TABLE IF NOT EXISTS analyses_ia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT,
    diagnostic_id INT,
    type_analyse ENUM('diagnostic', 'prescription', 'pronostic', 'recommandation', 'analyse_image') NOT NULL,
    input_data TEXT NOT NULL,
    output_data TEXT,
    score_confiance DECIMAL(5,2),
    model_utilise VARCHAR(100),
    date_analyse DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('en attente', 'en cours', 'terminé', 'erreur') DEFAULT 'en attente',
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (diagnostic_id) REFERENCES diagnostics(id) ON DELETE CASCADE
);

-- Table des images DICOM et fichiers médicaux
CREATE TABLE IF NOT EXISTS fichiers_medicaux (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    rendez_vous_id INT,
    diagnostic_id INT,
    type_fichier ENUM('dicom', 'pdf', 'image', 'document', 'autre') NOT NULL,
    nom_fichier VARCHAR(255) NOT NULL,
    chemin_fichier VARCHAR(500) NOT NULL,
    description TEXT,
    taille_fichier INT,
    date_upload DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (rendez_vous_id) REFERENCES rendez_vous(id) ON DELETE SET NULL,
    FOREIGN KEY (diagnostic_id) REFERENCES diagnostics(id) ON DELETE SET NULL
);

-- Table des spécialités médicales
CREATE TABLE IF NOT EXISTS specialites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table des types de diagnostics
CREATE TABLE IF NOT EXISTS types_diagnostics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    categorie VARCHAR(100),
    code_icd10 VARCHAR(50),
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table des protocoles de traitement
CREATE TABLE IF NOT EXISTS protocoles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(200) NOT NULL,
    description TEXT,
    type_diagnostic_id INT,
    etapes TEXT,
    duree_estimee VARCHAR(100),
    taux_reussite DECIMAL(5,2),
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (type_diagnostic_id) REFERENCES types_diagnostics(id) ON DELETE SET NULL
);

-- Index pour optimiser les requêtes
CREATE INDEX idx_patients_nom ON patients(nom);
CREATE INDEX idx_patients_telephone ON patients(telephone);
CREATE INDEX idx_patients_assurance ON patients(assurance_numero);
CREATE INDEX idx_rendez_vous_date ON rendez_vous(date_rendez_vous);
CREATE INDEX idx_rendez_vous_patient ON rendez_vous(patient_id);
CREATE INDEX idx_rendez_vous_statut ON rendez_vous(statut);
CREATE INDEX idx_diagnostics_patient ON diagnostics(patient_id);
CREATE INDEX idx_diagnostics_type ON diagnostics(type_diagnostic);
CREATE INDEX idx_prescriptions_patient ON prescriptions(patient_id);
CREATE INDEX idx_operations_patient ON operations(patient_id);
CREATE INDEX idx_operations_date ON operations(date_operation);
CREATE INDEX idx_paiements_patient ON paiements(patient_id);
CREATE INDEX idx_paiements_statut ON paiements(statut);

-- Données initiales pour les types de diagnostics
INSERT INTO types_diagnostics (nom, description, categorie, code_icd10) VALUES
('Fracture du radius', 'Fracture de l\'os radius', 'Fractures', 'S52.5'),
('Fracture de la clavicule', 'Fracture de la clavicule', 'Fractures', 'S42.0'),
('Entorse de la cheville', 'Entorse des ligaments de la cheville', 'Entorses', 'S93.4'),
('Entorse du genou', 'Entorse des ligaments du genou', 'Entorses', 'S83.5'),
('Luxation de l\'épaule', 'Luxation de l\'articulation de l\'épaule', 'Luxations', 'S43.0'),
('Rupture du ligament croisé', 'Rupture du ligament croisé antérieur', 'Lésions ligamentaires', 'S83.5'),
('Tendinite de l\'épaule', 'Inflammation des tendons de l\'épaule', 'Tendinopathies', 'M75.0'),
('Tendinite du coude', 'Inflammation des tendons du coude (épicondylite)', 'Tendinopathies', 'M77.0'),
('Arthrose du genou', 'Usure du cartilage du genou', 'Arthrose', 'M17'),
('Arthrose de la hanche', 'Usure du cartilage de la hanche', 'Arthrose', 'M16'),
('Hernie discale lombaire', 'Hernie discale dans la région lombaire', 'Colonne vertébrale', 'M51.2'),
('Hernie discale cervicale', 'Hernie discale dans la région cervicale', 'Colonne vertébrale', 'M50.2'),
('Scoliose', 'Déviation latérale de la colonne vertébrale', 'Colonne vertébrale', 'M41'),
('Fracture du col du fémur', 'Fracture de la partie supérieure du fémur', 'Fractures', 'S72.0'),
('Fracture du poignet', 'Fracture des os du poignet', 'Fractures', 'S62'),
('Syndrome du canal carpien', 'Compression du nerf médian au poignet', 'Syndromes canalaires', 'G56.0');

-- Données initiales pour les spécialités
INSERT INTO specialites (nom, description) VALUES
('Traumatologie', 'Spécialité médicale qui traite les traumatismes et les lésions de l\'appareil locomoteur'),
('Orthopédie', 'Spécialité chirurgicale qui traite les maladies et les lésions de l\'appareil locomoteur'),
('Chirurgie orthopédique', 'Chirurgie spécialisée dans le traitement des affections de l\'appareil locomoteur'),
('Médecine du sport', 'Spécialité qui traite les blessures liées à la pratique du sport'),
('Rhumatologie', 'Spécialité qui traite les maladies des articulations, des muscles et des os'),
('Rééducation fonctionnelle', 'Spécialité qui vise à restaurer les fonctions physiques après une blessure ou une maladie');

-- Données initiales pour les paramètres
INSERT INTO parametres (cle, valeur, description) VALUES
('nom_cabinet', 'TraumaSoft - Cabinet de Traumatologie', 'Nom du cabinet médical'),
('adresse_cabinet', '123 Rue de la Santé, 75000 Paris', 'Adresse physique du cabinet'),
('telephone_cabinet', '+33 1 23 45 67 89', 'Numéro de téléphone principal'),
('email_cabinet', 'contact@traumasoft.com', 'Email de contact du cabinet'),
('tarif_consultation', '150', 'Tarif standard d\'une consultation en DH'),
('tarif_urgence', '250', 'Tarif pour les consultations d\'urgence en DH'),
('tarif_operation', '5000', 'Tarif de base pour une opération en DH'),
('taux_tva', '20', 'Taux de TVA applicable en pourcentage'),
('devise', 'DH', 'Devise utilisée pour les paiements'),
('nombre_rendez_vous_par_page', '10', 'Nombre de rendez-vous affichés par page');

-- Création d'un utilisateur administrateur par défaut
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, specialite, telephone) VALUES
('Admin', 'TraumaSoft', 'admin@traumasoft.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Traumatologie', '+33 1 23 45 67 89');

-- Création d'un médecin par défaut
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, specialite, telephone) VALUES
('Dupont', 'Jean', 'j.dupont@traumasoft.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'medecin', 'Traumatologie', '+33 1 23 45 67 90');

-- Création d'une secrétaire par défaut
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, telephone) VALUES
('Martin', 'Marie', 'm.martin@traumasoft.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'secretaire', '+33 1 23 45 67 91');

-- Création de quelques patients d'exemple
INSERT INTO patients (nom, prenom, date_naissance, sexe, adresse, telephone, email, assurance_compagnie, assurance_numero, assurance_type) VALUES
('Martin', 'Jean', '1985-03-15', 'Homme', '12 Rue de Paris, 75001 Paris', '0612345678', 'jean.martin@email.com', 'CNOPS', 'CNOPS123456', 'CNOPS'),
('Dubois', 'Marie', '1990-07-22', 'Femme', '45 Avenue des Champs, 75008 Paris', '0623456789', 'marie.dubois@email.com', 'CNSS', 'CNSS789012', 'CNSS'),
('Leroy', 'Pierre', '1978-11-05', 'Homme', '78 Boulevard de la République, 75010 Paris', '0634567890', 'pierre.leroy@email.com', 'Assurance privée', 'ASS123456', 'Assurance privée'),
('Moreau', 'Sophie', '1995-02-18', 'Femme', '32 Rue de Lyon, 75012 Paris', '0645678901', 'sophie.moreau@email.com', 'CNOPS', 'CNOPS654321', 'CNOPS'),
('Petit', 'Luc', '1982-09-30', 'Homme', '15 Rue de Marseille, 75015 Paris', '0656789012', 'luc.petit@email.com', 'CNSS', 'CNSS345678', 'CNSS');

-- Création de rendez-vous d'exemple
INSERT INTO rendez_vous (patient_id, medecin_id, date_rendez_vous, date_fin, motif, statut, priorite, type_consultation) VALUES
(1, 2, '2023-05-15 09:00:00', '2023-05-15 09:30:00', 'Fracture radius contrôle', 'terminé', 'normale', 'suivi'),
(2, 2, '2023-05-15 09:30:00', '2023-05-15 10:00:00', 'Entorse cheville', 'terminé', 'normale', 'consultation'),
(3, 2, '2023-05-15 10:00:00', '2023-05-15 10:30:00', 'Lombalgie aiguë', 'en cours', 'normale', 'consultation'),
(4, 2, '2023-05-15 10:30:00', '2023-05-15 11:00:00', 'Fracture clavicule', 'à venir', 'normale', 'consultation'),
(5, 2, '2023-05-15 11:00:00', '2023-05-15 11:30:00', 'Tendinite épaule', 'à venir', 'normale', 'consultation'),
(1, 2, '2023-05-16 08:30:00', '2023-05-16 09:00:00', 'Contrôle post-opératoire', 'planifié', 'normale', 'suivi'),
(3, 2, '2023-05-16 10:00:00', '2023-05-16 12:00:00', 'Ostéosynthèse radius', 'planifié', 'normale', 'opération');

-- Création de diagnostics d'exemple
INSERT INTO diagnostics (patient_id, rendez_vous_id, medecin_id, type_diagnostic, description, localisation, severite, examen_clinique, examen_complementaire, conclusion) VALUES
(1, 1, 2, 'fracture', 'Fracture du radius distal avec déplacement', 'Poignet droit', 'modéré', 'Douleur intense, déformation visible, œdème important', 'Radiographie: Fracture confirmée avec déplacement de 5mm', 'Fracture du radius distal nécessitant une réduction et immobilisation'),
(2, 2, 2, 'entorse', 'Entorse du ligament talo-fibulaire antérieur', 'Cheville droite', 'léger', 'Douleur à la palpation, gonflement modéré, pas de déformation', 'Aucun examen complémentaire nécessaire', 'Entorse de grade 2, traitement conservateur recommandé'),
(3, 3, 2, 'autre', 'Lombalgie mécanique avec irradiation dans la jambe droite', 'Région lombaire', 'sévère', 'Douleur à la mobilisation, signe de Lasegue positif, pas de déficit neurologique', 'IRM: Hernie discale L4-L5', 'Hernie discale lombaire avec compression radiculaire'),
(4, 4, 2, 'fracture', 'Fracture de la clavicule gauche', 'Clavicule gauche', 'modéré', 'Douleur à la palpation, déformation visible, ecchymose', 'Radiographie: Fracture confirmée sans déplacement', 'Fracture de la clavicule sans déplacement, traitement orthopédique'),
(5, 5, 2, 'tendinite', 'Tendinite de la coiffe des rotateurs', 'Épaule droite', 'léger', 'Douleur à l\'abduction et à la rotation externe, pas de faiblesse musculaire', 'Échographie: Tendinopathie confirmée sans rupture', 'Tendinite de la coiffe des rotateurs, traitement médical et kinésithérapie');

-- Création de prescriptions d'exemple
INSERT INTO prescriptions (patient_id, diagnostic_id, medecin_id, type_prescription, nom_medicament, dosage, frequence, duree, quantite, instructions, date_fin) VALUES
(1, 1, 2, 'médicament', 'Paracétamol', '1g', '3 fois par jour', '10 jours', 30, 'À prendre après les repas', '2023-05-25'),
(1, 1, 2, 'médicament', 'Anti-inflammatoire', '200mg', '2 fois par jour', '5 jours', 10, 'À prendre avec les repas', '2023-05-20'),
(1, 1, 2, 'rééducation', 'Kinésithérapie', '', '3 séances par semaine', '6 semaines', 0, 'Rééducation du poignet et renforcement musculaire', '2023-06-30'),
(2, 2, 2, 'médicament', 'Anti-inflammatoire', '200mg', '2 fois par jour', '5 jours', 10, 'À prendre avec les repas', '2023-05-20'),
(2, 2, 2, 'rééducation', 'Kinésithérapie', '', '2 séances par semaine', '4 semaines', 0, 'Renforcement des ligaments de la cheville', '2023-06-15'),
(3, 3, 2, 'médicament', 'Anti-inflammatoire', '200mg', '2 fois par jour', '7 jours', 14, 'À prendre avec les repas', '2023-05-22'),
(3, 3, 2, 'médicament', 'Décontractant musculaire', '50mg', '1 fois par jour', '7 jours', 7, 'À prendre le soir', '2023-05-22'),
(3, 3, 2, 'examen', 'IRM lombaire', '', '', '', 0, 'IRM de contrôle dans 1 mois', '2023-06-15'),
(4, 4, 2, 'médicament', 'Paracétamol', '1g', '3 fois par jour', '5 jours', 15, 'À prendre après les repas', '2023-05-20'),
(5, 5, 2, 'médicament', 'Anti-inflammatoire', '200mg', '2 fois par jour', '10 jours', 20, 'À prendre avec les repas', '2023-05-25');

-- Création d'opérations d'exemple
INSERT INTO operations (patient_id, medecin_id, diagnostic_id, date_operation, type_operation, description, lieu, duree_estimee, duree_reelle, anesthesie_type, statut) VALUES
(2, 2, 2, '2023-05-16 10:00:00', 'Ostéosynthèse radius', 'Ostéosynthèse par plaque et vis pour fracture du radius distal', 'Clinique Saint-Louis', '1h 30min', '1h 25min', 'régionale', 'planifiée'),
(3, 2, NULL, '2023-05-20 09:00:00', 'Discectomie L4-L5', 'Ablation de la hernie discale L4-L5 par voie postérieure', 'Clinique Saint-Louis', '2h 00min', NULL, 'générale', 'planifiée');

-- Création de certificats d'exemple
INSERT INTO certificats (patient_id, medecin_id, diagnostic_id, type_certificat, titre, contenu, date_debut, date_fin, duree_arret, statut) VALUES
(1, 2, 1, 'arrêt de travail', 'Arrêt de travail pour fracture du radius', 'Arrêt de travail de 15 jours pour fracture du radius droit avec immobilisation', '2023-05-15', '2023-05-30', 15, 'validé'),
(3, 2, 3, 'arrêt de travail', 'Arrêt de travail pour hernie discale', 'Arrêt de travail de 30 jours pour hernie discale L4-L5', '2023-05-15', '2023-06-14', 30, 'validé');

-- Création de paiements d'exemple
INSERT INTO paiements (patient_id, rendez_vous_id, type_paiement, montant, montant_paye, mode_paiement, statut, date_paiement, reference_facture) VALUES
(1, 1, 'consultation', 150.00, 150.00, 'carte', 'payé', '2023-05-15 09:30:00', 'FACT-2023-001'),
(2, 2, 'consultation', 150.00, 150.00, 'espèces', 'payé', '2023-05-15 10:00:00', 'FACT-2023-002'),
(3, 3, 'consultation', 150.00, 100.00, 'carte', 'partiel', '2023-05-15 10:30:00', 'FACT-2023-003'),
(4, 4, 'consultation', 150.00, 0.00, 'espèces', 'en attente', NULL, 'FACT-2023-004'),
(5, 5, 'consultation', 150.00, 0.00, 'espèces', 'en attente', NULL, 'FACT-2023-005'),
(2, 6, 'opération', 5000.00, 2500.00, 'assurance', 'partiel', '2023-05-16 10:00:00', 'FACT-2023-006');

-- Création d'examens d'exemple
INSERT INTO examens (patient_id, rendez_vous_id, diagnostic_id, type_examen, description, date_examen, lieu, resultat, statut) VALUES
(1, 1, 1, 'radiographie', 'Radiographie du poignet droit', '2023-05-15 08:30:00', 'Clinique Saint-Louis', 'Fracture du radius distal avec déplacement de 5mm', 'réalisé'),
(2, 2, 2, 'radiographie', 'Radiographie de la cheville droite', '2023-05-15 09:00:00', 'Clinique Saint-Louis', 'Entorse du ligament talo-fibulaire antérieur sans fracture', 'réalisé'),
(3, 3, 3, 'IRM', 'IRM lombaire', '2023-05-15 09:00:00', 'Clinique Saint-Louis', 'Hernie discale L4-L5 avec compression radiculaire', 'réalisé'),
(4, 4, 4, 'radiographie', 'Radiographie de la clavicule gauche', '2023-05-15 09:30:00', 'Clinique Saint-Louis', 'Fracture de la clavicule gauche sans déplacement', 'réalisé'),
(5, 5, 5, 'échographie', 'Échographie de l\'épaule droite', '2023-05-15 09:45:00', 'Clinique Saint-Louis', 'Tendinopathie de la coiffe des rotateurs sans rupture', 'réalisé');

-- Création d'entrées dans l'historique médical
INSERT INTO historique_medical (patient_id, utilisateur_id, type_entree, titre, description, date_evenement) VALUES
(1, 2, 'diagnostic', 'Fracture du radius', 'Diagnostic de fracture du radius distal avec déplacement', '2023-05-15 09:00:00'),
(1, 2, 'prescription', 'Traitement fracture radius', 'Prescription de paracétamol et anti-inflammatoires', '2023-05-15 09:15:00'),
(1, 2, 'consultation', 'Consultation pour fracture', 'Première consultation pour fracture du radius', '2023-05-15 09:00:00'),
(2, 2, 'diagnostic', 'Entorse cheville', 'Diagnostic d\'entorse du ligament talo-fibulaire antérieur', '2023-05-15 09:30:00'),
(3, 2, 'diagnostic', 'Hernie discale', 'Diagnostic de hernie discale L4-L5', '2023-05-15 10:00:00'),
(3, 2, 'examen', 'IRM lombaire', 'Réalisation d\'une IRM lombaire', '2023-05-15 09:00:00'),
(4, 2, 'diagnostic', 'Fracture clavicule', 'Diagnostic de fracture de la clavicule gauche', '2023-05-15 10:30:00'),
(5, 2, 'diagnostic', 'Tendinite épaule', 'Diagnostic de tendinite de la coiffe des rotateurs', '2023-05-15 11:00:00');

-- Création de notifications d'exemple
INSERT INTO notifications (utilisateur_id, patient_id, type_notification, titre, message, priorite) VALUES
(2, 1, 'rendez_vous', 'Rendez-vous terminé', 'Le rendez-vous avec Jean Martin a été marqué comme terminé', 'normale'),
(2, 3, 'rendez_vous', 'Rendez-vous en cours', 'Le rendez-vous avec Pierre Leroy est en cours', 'haute'),
(2, 4, 'rendez_vous', 'Rendez-vous à venir', 'Rendez-vous avec Sophie Moreau à 10:30', 'normale'),
(2, 5, 'rendez_vous', 'Rendez-vous à venir', 'Rendez-vous avec Luc Petit à 11:00', 'normale'),
(1, NULL, 'urgence', 'Nouveau patient urgent', 'Un nouveau patient urgent est en salle d\'attente', 'urgente'),
(3, NULL, 'paiement', 'Paiement partiel', 'Le paiement pour Pierre Leroy est partiel (100/150 DH)', 'normale');

-- Création de logs d'exemple
INSERT INTO logs (utilisateur_id, action, table_concernee, id_entree, details, ip_address) VALUES
(2, 'Connexion', 'utilisateurs', 2, 'Connexion réussie', '192.168.1.100'),
(2, 'Création diagnostic', 'diagnostics', 1, 'Diagnostic de fracture du radius pour patient 1', '192.168.1.100'),
(2, 'Création prescription', 'prescriptions', 1, 'Prescription de paracétamol pour patient 1', '192.168.1.100'),
(2, 'Mise à jour rendez-vous', 'rendez_vous', 1, 'Rendez-vous marqué comme terminé', '192.168.1.100'),
(1, 'Configuration système', 'parametres', NULL, 'Mise à jour du tarif de consultation', '192.168.1.101');

COMMIT;

CREATE DATABASE IF NOT EXISTS traumasoft;
USE traumasoft;

-- Table des patients
CREATE TABLE patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    date_naissance DATE NOT NULL,
    genre ENUM('M', 'F'),
    adresse TEXT,
    telephone VARCHAR(20),
    email VARCHAR(100),
    assurance VARCHAR(100),
    numero_securite_sociale VARCHAR(20),
    mutuelle VARCHAR(100),
    numero_mutuelle VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des rendez-vous
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT,
    date_rdv DATETIME NOT NULL,
    motif TEXT,
    statut ENUM('planifié', 'confirmé', 'en_cours', 'terminé', 'annulé') DEFAULT 'planifié',
    duree INT DEFAULT 30,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
);

-- Table des diagnostics
CREATE TABLE diagnoses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT,
    date_diagnostic DATE NOT NULL,
    diagnostic TEXT NOT NULL,
    code_cim VARCHAR(20),
    traitement TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
);

-- Table des ordonnances
CREATE TABLE prescriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT,
    date_prescription DATE NOT NULL,
    medicaments JSON,
    instructions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
);

-- Table des imageries
CREATE TABLE imaging (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT,
    type_examen VARCHAR(100),
    date_examen DATE,
    description TEXT,
    fichier_dicom VARCHAR(255),
    observations TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
);

-- Table des certificats
CREATE TABLE certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT,
    date_certificat DATE NOT NULL,
    diagnostic TEXT,
    duree_jours INT,
    recommandations TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
);

-- Table des opérations
CREATE TABLE operations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT,
    date_operation DATETIME NOT NULL,
    type_operation VARCHAR(200),
    lieu VARCHAR(100),
    duree_minutes INT,
    statut ENUM('planifiée', 'réalisée', 'annulée') DEFAULT 'planifiée',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
);

-- Table des paiements
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT,
    date_paiement DATE NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    type_paiement ENUM('espèces', 'chèque', 'virement', 'carte') NOT NULL,
    mode_reglement ENUM('caisse', 'banque') NOT NULL,
    reference VARCHAR(100),
    statut ENUM('réglé', 'en_attente', 'annulé') DEFAULT 'réglé',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
);

-- Insertion de données de démonstration avec des noms arabes
INSERT INTO patients (nom, prenom, date_naissance, genre, adresse, telephone, email, assurance, numero_securite_sociale, mutuelle) VALUES
('الفضالي', 'أحمد', '1985-03-15', 'M', '15 شارع الحسن الثاني، الدار البيضاء', '+212612345678', 'ahmed@email.com', 'CNOPS', 'J85123456789', 'أسيط'),
('العلوي', 'فاطمة', '1990-07-22', 'F', '22 شارع محمد الخامس، الرباط', '+212698765432', 'fatima@email.com', 'CNSS', 'K90765432198', 'صحة'),
('المريني', 'يوسف', '1978-11-30', 'M', '8 شارع فلسطين، الدار البيضاء', '+212600112233', 'youssef@email.com', 'CNOPS', 'H78332211007', 'أسيط'),
('العبادي', 'خديجة', '1982-05-14', 'F', '12 شارع الجيش الملكي، فاس', '+212644556677', 'khadija@email.com', 'CNSS', 'J82554433661', 'صحة'),
('الغزاوي', 'محمد', '1995-12-08', 'M', '5 شارع الأنصار، طنجة', '+212655443322', 'mohamed@email.com', 'CNOPS', 'L95112233445', 'أسيط');

INSERT INTO appointments (patient_id, date_rdv, motif, statut, duree) VALUES
(1, '2024-01-15 09:00:00', 'كسر في الرسغ - متابعة', 'terminé', 30),
(2, '2024-01-15 10:00:00', 'التواء في الكاحل', 'terminé', 30),
(3, '2024-01-15 11:00:00', 'آلام حادة في أسفل الظهر', 'en_cours', 45),
(4, '2024-01-15 14:00:00', 'كسر في الترقوة', 'planifié', 60),
(5, '2024-01-15 15:00:00', 'التهاب في الأوتار بالكتف', 'planifié', 30);

INSERT INTO diagnoses (patient_id, date_diagnostic, diagnostic, code_cim, traitement) VALUES
(1, '2024-01-10', 'كسر في النهاية البعيدة للكعبرة', 'S52.5', 'تجبير وتثبيت'),
(2, '2024-01-08', 'التواء في مفصل الكاحل', 'S93.4', 'راحة وعلاج فيزيائي'),
(3, '2024-01-05', 'ألم حاد في أسفل الظهر', 'M54.5', 'مسكنات وعلاج فيزيائي');

INSERT INTO prescriptions (patient_id, date_prescription, medicaments, instructions) VALUES
(1, '2024-01-10', '[{"nom": "باراسيتامول 1000 مجم", "dose": "1 حبة 3 مرات يوميا", "duree": "5 أيام"}, {"nom": "إيبوبروفين 400 مجم", "dose": "1 حبة مرتين يوميا", "duree": "3 أيام"}]', 'تناول بعد الأكل'),
(2, '2024-01-08', '[{"nom": "ديكلوفيناك 50 مجم", "dose": "1 حبة مرتين يوميا", "duree": "7 أيام"}]', 'مع الطعام');

INSERT INTO certificates (patient_id, date_certificat, diagnostic, duree_jours, recommandations) VALUES
(1, '2024-01-10', 'كسر في النهاية البعيدة للكعبرة', 15, 'راحة تامة وتجنب حمل الأشياء'),
(2, '2024-01-08', 'التواء في مفصل الكاحل', 10, 'استخدام العكاز وتجنب المشي الطويل');

INSERT INTO operations (patient_id, date_operation, type_operation, lieu, duree_minutes, statut) VALUES
(1, '2024-01-20 08:00:00', 'تثبيت العظم بالمسامير', 'مستشفى الشيخ زايد', 120, 'planifiée'),
(4, '2024-01-18 10:00:00', 'تجبير الكسر', 'العيادة', 45, 'planifiée');

INSERT INTO payments (patient_id, date_paiement, montant, type_paiement, mode_reglement, reference) VALUES
(1, '2024-01-10', 300.00, 'espèces', 'caisse', 'P001'),
(2, '2024-01-08', 250.00, 'chèque', 'banque', 'CHK001'),
(3, '2024-01-05', 400.00, 'virement', 'banque', 'VIR001');
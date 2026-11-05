<?php
/**
 * Module d'Intelligence Artificielle pour TraumaSoft
 * Analyse des diagnostics, suggestions de traitements, optimisation des performances
 */

class AIAnalyzer {
    private $db;
    private $knowledge_base = [];
    private $diagnostic_patterns = [];
    private $treatment_protocols = [];
    
    public function __construct($db) {
        $this->db = $db;
        $this->loadKnowledgeBase();
        $this->loadDiagnosticPatterns();
        $this->loadTreatmentProtocols();
    }
    
    /**
     * Charger la base de connaissances depuis la base de données
     */
    private function loadKnowledgeBase() {
        try {
            $stmt = $this->db->query("SELECT * FROM types_diagnostics");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->knowledge_base['diagnostics'][$row['nom']] = $row;
            }
            
            $stmt = $this->db->query("SELECT * FROM protocoles");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->knowledge_base['protocoles'][$row['nom']] = $row;
            }
        } catch (PDOException $e) {
            // Base de connaissances vide si erreur
            $this->knowledge_base = [];
        }
    }
    
    /**
     * Charger les motifs de diagnostic
     */
    private function loadDiagnosticPatterns() {
        $this->diagnostic_patterns = [
            // Fractures
            'fracture' => [
                'mots_cles' => ['fracture', 'cassure', 'os cassé', 'fracturé'],
                'localisations' => ['radius', 'clavicule', 'fémur', 'tibia', 'péroné', 'humérus', 'poignet', 'cheville', 'genou'],
                'severite' => ['léger', 'modéré', 'sévère', 'ouvert', 'fermé', 'déplacé'],
                'traitements' => ['immobilisation', 'plâtre', 'attelle', 'ostéosynthèse', 'réduction']
            ],
            
            // Entorses
            'entorse' => [
                'mots_cles' => ['entorse', 'ligament', 'étirement', 'déchirure ligamentaire'],
                'localisations' => ['cheville', 'genou', 'poignet', 'épaule', 'coude'],
                'severite' => ['grade 1', 'grade 2', 'grade 3'],
                'traitements' => ['repos', 'glace', 'compression', 'élévation', 'kinésithérapie', 'attelle']
            ],
            
            // Luxations
            'luxation' => [
                'mots_cles' => ['luxation', 'déboîtement', 'articulation déplacée'],
                'localisations' => ['épaule', 'hanche', 'genou', 'coude', 'doigt'],
                'traitements' => ['réduction', 'immobilisation', 'rééducation']
            ],
            
            // Tendinites
            'tendinite' => [
                'mots_cles' => ['tendinite', 'tendon', 'inflammation tendon', 'douleur tendineuse'],
                'localisations' => ['épaule', 'coude', 'poignet', 'genou', 'tendon d\'achille'],
                'traitements' => ['repos', 'anti-inflammatoires', 'glace', 'kinésithérapie', 'infiltration']
            ],
            
            // Arthrose
            'arthrose' => [
                'mots_cles' => ['arthrose', 'usure cartilage', 'dégénérescence articulaire'],
                'localisations' => ['genou', 'hanche', 'épaule', 'colonne vertébrale', 'mains'],
                'traitements' => ['médicaments', 'kinésithérapie', 'perte de poids', 'prothèse', 'infiltrations']
            ],
            
            // Hernies discales
            'hernie discale' => [
                'mots_cles' => ['hernie discale', 'sciatique', 'lombalgie', 'compression nerveuse'],
                'localisations' => ['lombaire', 'cervicale', 'dorsale'],
                'traitements' => ['repos', 'kinésithérapie', 'médicaments', 'infiltration', 'chirurgie']
            ],
            
            // Ruptures ligamentaires
            'rupture ligamentaire' => [
                'mots_cles' => ['rupture', 'ligament croisé', 'déchirure complète'],
                'localisations' => ['genou', 'épaule', 'cheville'],
                'traitements' => ['chirurgie', 'rééducation', 'attelle']
            ]
        ];
    }
    
    /**
     * Charger les protocoles de traitement
     */
    private function loadTreatmentProtocols() {
        $this->treatment_protocols = [
            'fracture_radius' => [
                'nom' => 'Protocole Fracture Radius',
                'description' => 'Traitement standard pour les fractures du radius',
                'etapes' => [
                    ['action' => 'Réduction', 'description' => 'Réduction de la fracture si déplacement', 'duree' => '1 jour'],
                    ['action' => 'Immobilisation', 'description' => 'Application d\'un plâtre ou attelle', 'duree' => '4-6 semaines'],
                    ['action' => 'Contrôle radiologique', 'description' => 'Radiographie de contrôle à J8 et J30', 'duree' => '1 jour'],
                    ['action' => 'Rééducation', 'description' => 'Kinésithérapie pour récupération fonctionnelle', 'duree' => '4-8 semaines']
                ],
                'medicaments' => [
                    ['nom' => 'Paracétamol', 'dosage' => '1g', 'frequence' => '3x/jour', 'duree' => '5-10 jours'],
                    ['nom' => 'Anti-inflammatoires', 'dosage' => '200mg', 'frequence' => '2x/jour', 'duree' => '5 jours']
                ]
            ],
            
            'entorse_cheville' => [
                'nom' => 'Protocole Entorse Cheville',
                'description' => 'Traitement standard pour les entorses de cheville',
                'etapes' => [
                    ['action' => 'Repos', 'description' => 'Repos fonctionnel avec béquilles si nécessaire', 'duree' => '2-7 jours'],
                    ['action' => 'Glace', 'description' => 'Application de glace 15 min toutes les 2-3 heures', 'duree' => '48-72 heures'],
                    ['action' => 'Compression', 'description' => 'Bandage compressif ou strapping', 'duree' => '1-2 semaines'],
                    ['action' => 'Élévation', 'description' => 'Surélever le pied au-dessus du cœur', 'duree' => '48 heures'],
                    ['action' => 'Rééducation', 'description' => 'Kinésithérapie pour renforcement', 'duree' => '3-6 semaines']
                ],
                'medicaments' => [
                    ['nom' => 'Paracétamol', 'dosage' => '1g', 'frequence' => '3x/jour', 'duree' => '5 jours'],
                    ['nom' => 'Anti-inflammatoires', 'dosage' => '200mg', 'frequence' => '2x/jour', 'duree' => '5 jours']
                ]
            ],
            
            'hernie_discale' => [
                'nom' => 'Protocole Hernie Discale',
                'description' => 'Traitement standard pour les hernies discales',
                'etapes' => [
                    ['action' => 'Repos relatif', 'description' => 'Éviter les efforts et positions douloureuses', 'duree' => '2-4 semaines'],
                    ['action' => 'Médicaments', 'description' => 'Antalgiques et anti-inflammatoires', 'duree' => '7-14 jours'],
                    ['action' => 'Kinésithérapie', 'description' => 'Rééducation lombaire et renforcement', 'duree' => '6-12 semaines'],
                    ['action' => 'Infiltration', 'description' => 'Infiltration épidurale si douleur persistante', 'duree' => '1 séance'],
                    ['action' => 'Chirurgie', 'description' => 'Discectomie si échec du traitement médical', 'duree' => '2-3 mois']
                ],
                'medicaments' => [
                    ['nom' => 'Paracétamol', 'dosage' => '1g', 'frequence' => '3x/jour', 'duree' => '14 jours'],
                    ['nom' => 'Anti-inflammatoires', 'dosage' => '200mg', 'frequence' => '2x/jour', 'duree' => '10 jours'],
                    ['nom' => 'Décontractants musculaires', 'dosage' => '50mg', 'frequence' => '1x/jour', 'duree' => '7 jours']
                ]
            ]
        ];
    }
    
    /**
     * Analyser un diagnostic et suggérer des traitements
     */
    public function analyzeDiagnostic($diagnostic_data) {
        $result = [
            'diagnostic' => $diagnostic_data,
            'analyse' => [],
            'suggestions' => [],
            'protocole' => null,
            'score_confiance' => 0,
            'recommandations' => []
        ];
        
        // Extraire les informations
        $description = strtolower($diagnostic_data['description'] ?? '');
        $localisation = strtolower($diagnostic_data['localisation'] ?? '');
        $type = strtolower($diagnostic_data['type_diagnostic'] ?? '');
        
        // Détecter le type de pathologie
        $detected_type = $this->detectDiagnosticType($description, $type);
        $result['analyse']['type_detecte'] = $detected_type;
        
        // Déterminer la sévérité
        $severity = $this->assessSeverity($description, $diagnostic_data['severite'] ?? '');
        $result['analyse']['severite'] = $severity;
        
        // Trouver le protocole correspondant
        $protocole = $this->findProtocol($detected_type, $localisation, $severity);
        $result['protocole'] = $protocole;
        
        // Générer des suggestions de traitement
        $suggestions = $this->generateTreatmentSuggestions($detected_type, $localisation, $severity, $protocole);
        $result['suggestions'] = $suggestions;
        
        // Générer des recommandations
        $recommandations = $this->generateRecommendations($detected_type, $localisation, $severity, $diagnostic_data);
        $result['recommandations'] = $recommandations;
        
        // Calculer le score de confiance
        $result['score_confiance'] = $this->calculateConfidenceScore($detected_type, $description);
        
        // Sauvegarder l'analyse dans la base de données
        $this->saveAnalysis($diagnostic_data, $result);
        
        return $result;
    }
    
    /**
     * Détecter le type de diagnostic à partir de la description
     */
    private function detectDiagnosticType($description, $type) {
        // Si le type est déjà spécifié, le retourner
        if (!empty($type) && $type != 'autre') {
            return $type;
        }
        
        $max_score = 0;
        $detected_type = 'autre';
        
        foreach ($this->diagnostic_patterns as $type => $pattern) {
            $score = 0;
            
            // Vérifier les mots-clés
            foreach ($pattern['mots_cles'] as $mot) {
                if (strpos($description, $mot) !== false) {
                    $score += 2;
                }
            }
            
            // Vérifier les localisations
            foreach ($pattern['localisations'] as $loc) {
                if (strpos($description, $loc) !== false) {
                    $score += 1;
                }
            }
            
            // Vérifier les traitements mentionnés
            foreach ($pattern['traitements'] as $traitement) {
                if (strpos($description, $traitement) !== false) {
                    $score += 1;
                }
            }
            
            if ($score > $max_score) {
                $max_score = $score;
                $detected_type = $type;
            }
        }
        
        return $detected_type;
    }
    
    /**
     * Évaluer la sévérité du diagnostic
     */
    private function assessSeverity($description, $severity) {
        // Si la sévérité est déjà spécifiée, la retourner
        if (!empty($severity)) {
            return $severity;
        }
        
        // Détecter à partir de la description
        $severe_keywords = ['sévère', 'critique', 'urgent', 'grave', 'compliqué', 'déplacé', 'ouvert'];
        $moderate_keywords = ['modéré', 'moyen', 'partiel', 'grade 2'];
        $mild_keywords = ['léger', 'simple', 'grade 1', 'minime'];
        
        foreach ($severe_keywords as $keyword) {
            if (strpos($description, $keyword) !== false) {
                return 'sévère';
            }
        }
        
        foreach ($moderate_keywords as $keyword) {
            if (strpos($description, $keyword) !== false) {
                return 'modéré';
            }
        }
        
        foreach ($mild_keywords as $keyword) {
            if (strpos($description, $keyword) !== false) {
                return 'léger';
            }
        }
        
        return 'modéré'; // Par défaut
    }
    
    /**
     * Trouver le protocole de traitement approprié
     */
    private function findProtocol($type, $localisation, $severity) {
        $key = strtolower($type . '_' . $localisation);
        
        if (isset($this->treatment_protocols[$key])) {
            return $this->treatment_protocols[$key];
        }
        
        // Essayer avec juste le type
        if (isset($this->treatment_protocols[$type])) {
            return $this->treatment_protocols[$type];
        }
        
        // Chercher dans la base de données
        try {
            $stmt = $this->db->prepare("SELECT * FROM protocoles WHERE LOWER(nom) LIKE ? OR LOWER(description) LIKE ?");
            $search = '%' . strtolower($type) . '%';
            $stmt->execute([$search, $search]);
            
            if ($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            // Ignorer l'erreur
        }
        
        return null;
    }
    
    /**
     * Générer des suggestions de traitement
     */
    private function generateTreatmentSuggestions($type, $localisation, $severity, $protocole) {
        $suggestions = [];
        
        // Suggestions basées sur le type
        if (isset($this->diagnostic_patterns[$type])) {
            $pattern = $this->diagnostic_patterns[$type];
            $suggestions['traitements'] = $pattern['traitements'];
        }
        
        // Suggestions basées sur le protocole
        if ($protocole && isset($protocole['etapes'])) {
            $suggestions['protocole_etapes'] = $protocole['etapes'];
        }
        
        // Suggestions de médicaments
        if ($protocole && isset($protocole['medicaments'])) {
            $suggestions['medicaments'] = $protocole['medicaments'];
        }
        
        // Suggestions basées sur la sévérité
        switch ($severity) {
            case 'sévère':
            case 'critique':
                $suggestions['urgence'] = true;
                $suggestions['specialiste'] = true;
                $suggestions['hospitalisation'] = ($type == 'fracture' || $type == 'rupture ligamentaire');
                break;
            case 'modéré':
                $suggestions['suivi_rapproche'] = true;
                break;
            case 'léger':
                $suggestions['traitement_ambulatoire'] = true;
                break;
        }
        
        // Suggestions spécifiques selon la localisation
        if (strpos($localisation, 'genou') !== false) {
            $suggestions['examen_complementaire'] = ['IRM', 'radiographie'];
        }
        
        if (strpos($localisation, 'colonne') !== false || strpos($localisation, 'lombaire') !== false) {
            $suggestions['examen_complementaire'] = ['IRM', 'scanner'];
        }
        
        return $suggestions;
    }
    
    /**
     * Générer des recommandations personnalisées
     */
    private function generateRecommendations($type, $localisation, $severity, $diagnostic_data) {
        $recommandations = [];
        $patient_age = $this->getPatientAge($diagnostic_data['patient_id'] ?? null);
        
        // Recommandations générales
        $recommandations[] = "Repos relatif et éviction des activités douloureuses";
        
        // Recommandations selon le type
        switch ($type) {
            case 'fracture':
                $recommandations[] = "Immobilisation immédiate de la zone affectée";
                $recommandations[] = "Application de glace pendant les premières 48 heures";
                if ($severity == 'sévère' || $severity == 'critique') {
                    $recommandations[] = "Consultation urgente avec un spécialiste en orthopédie";
                    $recommandations[] = "Radiographie de contrôle recommandée";
                }
                break;
                
            case 'entorse':
                $recommandations[] = "Application du protocole RICE (Repos, Ice, Compression, Elevation)";
                $recommandations[] = "Éviter de forcer sur l'articulation pendant 2-6 semaines";
                if ($severity == 'sévère') {
                    $recommandations[] = "Consultation spécialisée pour évaluation ligamentaire";
                }
                break;
                
            case 'hernie discale':
                $recommandations[] = "Éviter les efforts de soulèvement lourds";
                $recommandations[] = "Maintenir une bonne posture en position assise et debout";
                $recommandations[] = "Exercices de renforcement du tronc (après la phase aiguë)";
                if ($severity == 'sévère') {
                    $recommandations[] = "Consultation neurochirurgicale si symptômes neurologiques";
                }
                break;
                
            case 'tendinite':
                $recommandations[] = "Éviter les mouvements répétitifs responsables";
                $recommandations[] = "Application de chaleur locale après la phase aiguë";
                $recommandations[] = "Étirements doux de la zone affectée";
                break;
        }
        
        // Recommandations selon l'âge
        if ($patient_age < 18) {
            $recommandations[] = "Surveillance accrue chez les enfants (risque de croissance anormale)";
        } elseif ($patient_age > 65) {
            $recommandations[] = "Évaluation du risque de chute et prévention";
            $recommandations[] = "Vérification de l'ostéoporose si fracture";
        }
        
        // Recommandations selon la localisation
        if (strpos($localisation, 'colonne') !== false) {
            $recommandations[] = "Éviter la position assise prolongée";
            $recommandations[] = "Utiliser un coussin lombaire si nécessaire";
        }
        
        if (strpos($localisation, 'genou') !== false || strpos($localisation, 'cheville') !== false) {
            $recommandations[] = "Utilisation de béquilles si la marche est douloureuse";
        }
        
        return $recommandations;
    }
    
    /**
     * Calculer le score de confiance de l'analyse
     */
    private function calculateConfidenceScore($type, $description) {
        $score = 50; // Score de base
        
        // Bonus si le type est clairement identifié
        if (isset($this->diagnostic_patterns[$type])) {
            $score += 20;
        }
        
        // Bonus pour les mots-clés
        foreach ($this->diagnostic_patterns as $pattern_type => $pattern) {
            foreach ($pattern['mots_cles'] as $mot) {
                if (strpos($description, $mot) !== false) {
                    $score += 5;
                }
            }
        }
        
        // Bonus pour les localisations
        foreach ($this->diagnostic_patterns as $pattern_type => $pattern) {
            foreach ($pattern['localisations'] as $loc) {
                if (strpos($description, $loc) !== false) {
                    $score += 3;
                }
            }
        }
        
        // Limiter à 100
        return min(100, $score);
    }
    
    /**
     * Obtenir l'âge du patient
     */
    private function getPatientAge($patient_id) {
        if (!$patient_id) {
            return 30; // Âge par défaut
        }
        
        try {
            $stmt = $this->db->prepare("SELECT date_naissance FROM patients WHERE id = ?");
            $stmt->execute([$patient_id]);
            
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row['date_naissance']) {
                    $birth_date = new DateTime($row['date_naissance']);
                    $today = new DateTime();
                    return $today->diff($birth_date)->y;
                }
            }
        } catch (PDOException $e) {
            // Retourner un âge par défaut
        }
        
        return 30;
    }
    
    /**
     * Sauvegarder l'analyse dans la base de données
     */
    private function saveAnalysis($diagnostic_data, $result) {
        try {
            $stmt = $this->db->prepare("INSERT INTO analyses_ia 
                (patient_id, diagnostic_id, type_analyse, input_data, output_data, score_confiance, model_utilise, statut)
                VALUES (?, ?, 'diagnostic', ?, ?, ?, 'TraumaSoft AI v1.0', 'terminé')");
            
            $patient_id = $diagnostic_data['patient_id'] ?? null;
            $diagnostic_id = $diagnostic_data['id'] ?? null;
            $input_data = json_encode($diagnostic_data);
            $output_data = json_encode($result);
            $score = $result['score_confiance'];
            
            $stmt->execute([$patient_id, $diagnostic_id, $input_data, $output_data, $score]);
            
        } catch (PDOException $e) {
            // Ignorer l'erreur de sauvegarde
        }
    }
    
    /**
     * Analyser une image DICOM (simulation)
     */
    public function analyzeDicomImage($image_data, $patient_id, $diagnostic_id = null) {
        // Dans une version réelle, cela utiliserait une API d'IA pour analyser l'image
        // Pour l'instant, nous simulons une analyse
        
        $result = [
            'patient_id' => $patient_id,
            'diagnostic_id' => $diagnostic_id,
            'analyse' => [],
            'suggestions' => [],
            'score_confiance' => 0
        ];
        
        // Simulation d'analyse d'image
        $findings = [];
        
        // Détecter des anomalies courantes
        $anomalies = [
            'fracture' => ['probabilite' => rand(0, 100), 'localisation' => 'os long'],
            'hernie discale' => ['probabilite' => rand(0, 100), 'localisation' => 'L4-L5'],
            'arthrose' => ['probabilite' => rand(0, 100), 'localisation' => 'genou'],
            'normal' => ['probabilite' => rand(0, 100)]
        ];
        
        // Sélectionner l'anomalie avec la plus haute probabilité
        $max_prob = 0;
        $detected_anomaly = 'normal';
        foreach ($anomalies as $anomaly => $data) {
            if ($data['probabilite'] > $max_prob) {
                $max_prob = $data['probabilite'];
                $detected_anomaly = $anomaly;
            }
        }
        
        $result['analyse']['anomalie_detectee'] = $detected_anomaly;
        $result['analyse']['probabilite'] = $max_prob;
        $result['analyse']['localisation'] = $anomalies[$detected_anomaly]['localisation'] ?? null;
        
        // Générer des suggestions
        if ($detected_anomaly != 'normal') {
            $result['suggestions'] = $this->generateTreatmentSuggestions(
                $detected_anomaly, 
                $anomalies[$detected_anomaly]['localisation'] ?? '', 
                'modéré',
                null
            );
        }
        
        $result['score_confiance'] = $max_prob;
        
        // Sauvegarder l'analyse
        try {
            $stmt = $this->db->prepare("INSERT INTO analyses_ia 
                (patient_id, diagnostic_id, type_analyse, input_data, output_data, score_confiance, model_utilise, statut)
                VALUES (?, ?, 'analyse_image', ?, ?, ?, 'DICOM AI v1.0', 'terminé')");
            
            $input_data = json_encode(['image_data' => '[BINARY DATA]', 'patient_id' => $patient_id]);
            $output_data = json_encode($result);
            
            $stmt->execute([$patient_id, $diagnostic_id, $input_data, $output_data, $max_prob]);
            
        } catch (PDOException $e) {
            // Ignorer l'erreur
        }
        
        return $result;
    }
    
    /**
     * Optimiser le planning des rendez-vous
     */
    public function optimizeSchedule($appointments) {
        $optimized = [];
        $current_time = new DateTime();
        
        // Trier les rendez-vous par priorité et heure
        usort($appointments, function($a, $b) {
            // Priorité: urgente > très urgente > normale
            $priority_order = ['urgente' => 0, 'très urgente' => 1, 'normale' => 2];
            $a_priority = $priority_order[$a['priorite']] ?? 2;
            $b_priority = $priority_order[$b['priorite']] ?? 2;
            
            if ($a_priority != $b_priority) {
                return $a_priority - $b_priority;
            }
            
            // Puis par heure
            $a_time = new DateTime($a['date_rendez_vous']);
            $b_time = new DateTime($b['date_rendez_vous']);
            return $a_time <=> $b_time;
        });
        
        // Réorganiser avec des créneaux optimaux
        $slot_duration = 30; // minutes
        $current_slot = clone $current_time;
        
        foreach ($appointments as $appointment) {
            // Calculer la durée estimée
            $duration = $slot_duration;
            if (isset($appointment['type_consultation']) && $appointment['type_consultation'] == 'opération') {
                $duration = 120; // 2 heures pour une opération
            }
            
            // Créer un créneau optimisé
            $optimized[] = [
                'id' => $appointment['id'],
                'patient_id' => $appointment['patient_id'],
                'date_rendez_vous' => $current_slot->format('Y-m-d H:i:s'),
                'date_fin' => $current_slot->modify("+$duration minutes")->format('Y-m-d H:i:s'),
                'priorite' => $appointment['priorite'],
                'type_consultation' => $appointment['type_consultation'],
                'salle' => $this->assignRoom($appointment['type_consultation'])
            ];
            
            // Passer au créneau suivant
            $current_slot->modify("+$duration minutes");
        }
        
        return $optimized;
    }
    
    /**
     * Assigner une salle en fonction du type de consultation
     */
    private function assignRoom($type) {
        $rooms = [
            'consultation' => ['Salle 1', 'Salle 2', 'Salle 3'],
            'opération' => ['Bloc 1', 'Bloc 2'],
            'urgence' => ['Salle d\'urgence'],
            'examen' => ['Salle de radiologie', 'Salle d\'échographie']
        ];
        
        $room_list = $rooms[$type] ?? $rooms['consultation'];
        return $room_list[array_rand($room_list)];
    }
    
    /**
     * Prédire la durée de récupération
     */
    public function predictRecoveryTime($diagnostic_data) {
        $type = strtolower($diagnostic_data['type_diagnostic'] ?? '');
        $severity = strtolower($diagnostic_data['severite'] ?? 'modéré');
        $localisation = strtolower($diagnostic_data['localisation'] ?? '');
        $patient_age = $this->getPatientAge($diagnostic_data['patient_id'] ?? null);
        
        // Durées de base selon le type
        $base_durations = [
            'fracture' => ['min' => 4, 'max' => 12, 'unite' => 'semaines'],
            'entorse' => ['min' => 2, 'max' => 8, 'unite' => 'semaines'],
            'luxation' => ['min' => 3, 'max' => 10, 'unite' => 'semaines'],
            'tendinite' => ['min' => 2, 'max' => 6, 'unite' => 'semaines'],
            'arthrose' => ['min' => 4, 'max' => 12, 'unite' => 'mois'],
            'hernie discale' => ['min' => 6, 'max' => 24, 'unite' => 'semaines'],
            'rupture ligamentaire' => ['min' => 8, 'max' => 26, 'unite' => 'semaines']
        ];
        
        $duration = $base_durations[$type] ?? ['min' => 2, 'max' => 6, 'unite' => 'semaines'];
        
        // Ajuster selon la sévérité
        switch ($severity) {
            case 'léger':
                $duration['min'] = (int)($duration['min'] * 0.7);
                $duration['max'] = (int)($duration['max'] * 0.7);
                break;
            case 'sévère':
            case 'critique':
                $duration['min'] = (int)($duration['min'] * 1.5);
                $duration['max'] = (int)($duration['max'] * 1.5);
                break;
        }
        
        // Ajuster selon l'âge
        if ($patient_age > 60) {
            $duration['min'] = (int)($duration['min'] * 1.2);
            $duration['max'] = (int)($duration['max'] * 1.2);
        } elseif ($patient_age < 18) {
            $duration['min'] = (int)($duration['min'] * 0.8);
            $duration['max'] = (int)($duration['max'] * 0.8);
        }
        
        // Ajuster selon la localisation
        if (strpos($localisation, 'genou') !== false || strpos($localisation, 'hanche') !== false) {
            $duration['min'] = (int)($duration['min'] * 1.1);
            $duration['max'] = (int)($duration['max'] * 1.1);
        }
        
        // Calculer une estimation
        $estimated_min = $duration['min'];
        $estimated_max = $duration['max'];
        $estimated_avg = (int)(($estimated_min + $estimated_max) / 2);
        
        return [
            'type' => $type,
            'severite' => $severity,
            'age_patient' => $patient_age,
            'localisation' => $localisation,
            'duree_minimale' => $estimated_min,
            'duree_maximale' => $estimated_max,
            'duree_estimee' => $estimated_avg,
            'unite' => $duration['unite'],
            'recommandations' => [
                "Suivi régulier recommandé",
                "Rééducation progressive",
                "Éviter les activités à risque pendant la période de récupération"
            ]
        ];
    }
    
    /**
     * Analyser les performances du cabinet
     */
    public function analyzeCabinetPerformance($start_date, $end_date) {
        $performance = [];
        
        try {
            // Nombre de patients
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM patients WHERE date_creation BETWEEN ? AND ?");
            $stmt->execute([$start_date, $end_date]);
            $performance['nouveaux_patients'] = (int)$stmt->fetchColumn();
            
            // Nombre de consultations
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM rendez_vous WHERE date_rendez_vous BETWEEN ? AND ? AND type_consultation = 'consultation'");
            $stmt->execute([$start_date, $end_date]);
            $performance['consultations'] = (int)$stmt->fetchColumn();
            
            // Nombre d'opérations
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM operations WHERE date_operation BETWEEN ? AND ?");
            $stmt->execute([$start_date, $end_date]);
            $performance['operations'] = (int)$stmt->fetchColumn();
            
            // Revenu total
            $stmt = $this->db->prepare("SELECT SUM(montant_paye) FROM paiements WHERE date_paiement BETWEEN ? AND ? AND statut = 'payé'");
            $stmt->execute([$start_date, $end_date]);
            $performance['revenu_total'] = (float)$stmt->fetchColumn();
            
            // Temps moyen de consultation
            $stmt = $this->db->prepare("SELECT AVG(TIMESTAMPDIFF(MINUTE, date_rendez_vous, date_fin)) FROM rendez_vous WHERE date_fin IS NOT NULL AND date_rendez_vous BETWEEN ? AND ?");
            $stmt->execute([$start_date, $end_date]);
            $performance['temps_moyen_consultation'] = (int)$stmt->fetchColumn();
            
            // Taux d'occupation
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM rendez_vous WHERE date_rendez_vous BETWEEN ? AND ? AND statut != 'annulé'");
            $stmt->execute([$start_date, $end_date]);
            $total_appointments = (int)$stmt->fetchColumn();
            
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM rendez_vous WHERE date_rendez_vous BETWEEN ? AND ? AND statut = 'terminé'");
            $stmt->execute([$start_date, $end_date]);
            $completed_appointments = (int)$stmt->fetchColumn();
            
            $performance['taux_occupation'] = $total_appointments > 0 ? round(($completed_appointments / $total_appointments) * 100, 2) : 0;
            
            // Diagnostics les plus fréquents
            $stmt = $this->db->prepare("SELECT type_diagnostic, COUNT(*) as count FROM diagnostics WHERE date_diagnostic BETWEEN ? AND ? GROUP BY type_diagnostic ORDER BY count DESC LIMIT 5");
            $stmt->execute([$start_date, $end_date]);
            $performance['diagnostics_frequents'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Médecins les plus actifs
            $stmt = $this->db->prepare("SELECT u.id, u.nom, u.prenom, COUNT(r.id) as consultations FROM utilisateurs u LEFT JOIN rendez_vous r ON u.id = r.medecin_id AND r.date_rendez_vous BETWEEN ? AND ? WHERE u.role = 'medecin' GROUP BY u.id ORDER BY consultations DESC");
            $stmt->execute([$start_date, $end_date]);
            $performance['medecins_actifs'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            // Retourner des valeurs par défaut en cas d'erreur
            $performance['erreur'] = $e->getMessage();
        }
        
        return $performance;
    }
    
    /**
     * Générer un rapport complet pour un patient
     */
    public function generatePatientReport($patient_id) {
        $report = [];
        
        try {
            // Informations du patient
            $stmt = $this->db->prepare("SELECT * FROM patients WHERE id = ?");
            $stmt->execute([$patient_id]);
            $patient = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$patient) {
                return ['erreur' => 'Patient non trouvé'];
            }
            
            $report['patient'] = $patient;
            
            // Historique des diagnostics
            $stmt = $this->db->prepare("SELECT * FROM diagnostics WHERE patient_id = ? ORDER BY date_diagnostic DESC");
            $stmt->execute([$patient_id]);
            $report['diagnostics'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Historique des traitements
            $stmt = $this->db->prepare("SELECT * FROM prescriptions WHERE patient_id = ? ORDER BY date_prescription DESC");
            $stmt->execute([$patient_id]);
            $report['prescriptions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Historique des rendez-vous
            $stmt = $this->db->prepare("SELECT * FROM rendez_vous WHERE patient_id = ? ORDER BY date_rendez_vous DESC");
            $stmt->execute([$patient_id]);
            $report['rendez_vous'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Historique des opérations
            $stmt = $this->db->prepare("SELECT * FROM operations WHERE patient_id = ? ORDER BY date_operation DESC");
            $stmt->execute([$patient_id]);
            $report['operations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Paiements
            $stmt = $this->db->prepare("SELECT * FROM paiements WHERE patient_id = ? ORDER BY date_paiement DESC");
            $stmt->execute([$patient_id]);
            $report['paiements'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Analyse IA
            $stmt = $this->db->prepare("SELECT * FROM analyses_ia WHERE patient_id = ? ORDER BY date_analyse DESC");
            $stmt->execute([$patient_id]);
            $report['analyses_ia'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Statistiques
            $report['statistiques'] = [
                'nombre_diagnostics' => count($report['diagnostics']),
                'nombre_prescriptions' => count($report['prescriptions']),
                'nombre_rendez_vous' => count($report['rendez_vous']),
                'nombre_operations' => count($report['operations']),
                'montant_total_paye' => array_reduce($report['paiements'], function($carry, $paiement) {
                    return $carry + ($paiement['montant_paye'] ?? 0);
                }, 0)
            ];
            
            // Suggestions IA
            if (!empty($report['diagnostics'])) {
                $last_diagnostic = $report['diagnostics'][0];
                $analysis = $this->analyzeDiagnostic($last_diagnostic);
                $report['analyse_ia'] = $analysis;
            }
            
        } catch (PDOException $e) {
            $report['erreur'] = $e->getMessage();
        }
        
        return $report;
    }
}

// Exemple d'utilisation
if (php_sapi_name() === 'cli') {
    echo "Module IA TraumaSoft - Test\n";
    echo "=============================\n\n";
    
    try {
        include_once __DIR__ . '/../config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        
        $ai = new AIAnalyzer($db);
        
        // Test avec un diagnostic d'exemple
        $diagnostic = [
            'id' => 1,
            'patient_id' => 1,
            'type_diagnostic' => 'fracture',
            'description' => 'Fracture du radius distal avec déplacement de 5mm',
            'localisation' => 'Poignet droit',
            'severite' => 'modéré',
            'examen_clinique' => 'Douleur intense, déformation visible, œdème important'
        ];
        
        echo "Analyse du diagnostic:\n";
        $result = $ai->analyzeDiagnostic($diagnostic);
        echo "Type détecté: " . ($result['analyse']['type_detecte'] ?? 'inconnu') . "\n";
        echo "Sévérité: " . ($result['analyse']['severite'] ?? 'inconnu') . "\n";
        echo "Score de confiance: " . $result['score_confiance'] . "%\n";
        echo "\nSuggestions:\n";
        print_r($result['suggestions']);
        
        echo "\n\nPrédiction de récupération:\n";
        $recovery = $ai->predictRecoveryTime($diagnostic);
        echo "Durée estimée: " . $recovery['duree_estimee'] . " " . $recovery['unite'] . "\n";
        
    } catch (Exception $e) {
        echo "Erreur: " . $e->getMessage() . "\n";
    }
}
?>"
<?php
/**
 * Contrôleur pour les fonctionnalités d'IA de TraumaSoft
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../models/Patient.php';
include_once '../models/Diagnosis.php';
include_once '../models/Prescription.php';
include_once '../models/Appointment.php';
include_once '../models/Operation.php';
include_once '../config/database.php';
include_once '../ai/AIAnalyzer.php';

class AIController {
    private $db;
    private $ai_analyzer;
    private $patient;
    private $diagnosis;
    private $prescription;
    private $appointment;
    private $operation;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->ai_analyzer = new AIAnalyzer($this->db);
        $this->patient = new Patient($this->db);
        $this->diagnosis = new Diagnosis($this->db);
        $this->prescription = new Prescription($this->db);
        $this->appointment = new Appointment($this->db);
        $this->operation = new Operation($this->db);
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Extraire les paramètres de l'URL
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = str_replace('/traumasoft/backend/controllers/AIController.php', '', $path);
        $path = trim($path, '/');
        
        switch ($method) {
            case 'GET':
                if (strpos($path, 'analyze/diagnostic/') === 0) {
                    $diagnostic_id = (int)str_replace('analyze/diagnostic/', '', $path);
                    $this->analyzeDiagnostic($diagnostic_id);
                } elseif (strpos($path, 'analyze/patient/') === 0) {
                    $patient_id = (int)str_replace('analyze/patient/', '', $path);
                    $this->analyzePatient($patient_id);
                } elseif (strpos($path, 'predict/recovery/') === 0) {
                    $diagnostic_id = (int)str_replace('predict/recovery/', '', $path);
                    $this->predictRecovery($diagnostic_id);
                } elseif (strpos($path, 'performance') === 0) {
                    $this->getPerformanceAnalysis();
                } elseif (strpos($path, 'schedule/optimize') === 0) {
                    $this->optimizeSchedule();
                } elseif (strpos($path, 'analyses') === 0) {
                    $this->getAllAnalyses();
                } else {
                    $this->getAISuggestions();
                }
                break;
            
            case 'POST':
                if (strpos($path, 'analyze/dicom') === 0) {
                    $this->analyzeDicomImage();
                } elseif (strpos($path, 'analyze') === 0) {
                    $this->createAnalysis();
                } else {
                    $this->createAISuggestion();
                }
                break;
            
            case 'PUT':
                // Mise à jour non applicable pour l'IA
                http_response_code(405);
                echo json_encode(['message' => 'Method not allowed for AI endpoints']);
                break;
            
            case 'DELETE':
                // Suppression non applicable pour l'IA
                http_response_code(405);
                echo json_encode(['message' => 'Method not allowed for AI endpoints']);
                break;
            
            default:
                http_response_code(405);
                echo json_encode(['message' => 'Method not allowed']);
        }
    }

    /**
     * Analyser un diagnostic spécifique
     */
    private function analyzeDiagnostic($diagnostic_id) {
        try {
            $this->diagnosis->id = $diagnostic_id;
            $stmt = $this->diagnosis->readOne();
            
            if ($stmt && $stmt->rowCount() > 0) {
                $diagnostic = $stmt->fetch(PDO::FETCH_ASSOC);
                $result = $this->ai_analyzer->analyzeDiagnostic($diagnostic);
                
                echo json_encode([
                    'success' => true,
                    'data' => $result
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Diagnostic not found']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Analyser un patient complet
     */
    private function analyzePatient($patient_id) {
        try {
            $report = $this->ai_analyzer->generatePatientReport($patient_id);
            
            if (isset($report['erreur'])) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => $report['erreur']]);
            } else {
                echo json_encode([
                    'success' => true,
                    'data' => $report
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Prédire la durée de récupération
     */
    private function predictRecovery($diagnostic_id) {
        try {
            $this->diagnosis->id = $diagnostic_id;
            $stmt = $this->diagnosis->readOne();
            
            if ($stmt && $stmt->rowCount() > 0) {
                $diagnostic = $stmt->fetch(PDO::FETCH_ASSOC);
                $result = $this->ai_analyzer->predictRecoveryTime($diagnostic);
                
                echo json_encode([
                    'success' => true,
                    'data' => $result
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Diagnostic not found']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Obtenir l'analyse des performances du cabinet
     */
    private function getPerformanceAnalysis() {
        try {
            $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
            $end_date = $_GET['end_date'] ?? date('Y-m-d');
            
            $result = $this->ai_analyzer->analyzeCabinetPerformance($start_date, $end_date);
            
            echo json_encode([
                'success' => true,
                'data' => $result,
                'period' => [
                    'start_date' => $start_date,
                    'end_date' => $end_date
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Optimiser le planning des rendez-vous
     */
    private function optimizeSchedule() {
        try {
            $stmt = $this->db->query("SELECT * FROM rendez_vous WHERE date_rendez_vous >= NOW() AND statut != 'annulé' ORDER BY date_rendez_vous");
            $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $result = $this->ai_analyzer->optimizeSchedule($appointments);
            
            echo json_encode([
                'success' => true,
                'data' => $result,
                'original_count' => count($appointments),
                'optimized_count' => count($result)
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Obtenir toutes les analyses IA
     */
    private function getAllAnalyses() {
        try {
            $patient_id = $_GET['patient_id'] ?? null;
            $type = $_GET['type'] ?? null;
            $limit = $_GET['limit'] ?? 50;
            
            $query = "SELECT * FROM analyses_ia";
            $params = [];
            
            if ($patient_id) {
                $query .= " WHERE patient_id = ?";
                $params[] = $patient_id;
            }
            
            if ($type) {
                $query .= ($patient_id ? " AND" : " WHERE") . " type_analyse = ?";
                $params[] = $type;
            }
            
            $query .= " ORDER BY date_analyse DESC LIMIT ?";
            $params[] = $limit;
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            $analyses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $analyses,
                'count' => count($analyses)
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Analyser une image DICOM
     */
    private function analyzeDicomImage() {
        try {
            $data = json_decode(file_get_contents("php://input"));
            
            if (!isset($data->patient_id) || empty($data->patient_id)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Patient ID is required']);
                return;
            }
            
            $patient_id = $data->patient_id;
            $diagnostic_id = $data->diagnostic_id ?? null;
            $image_data = $data->image_data ?? null;
            
            $result = $this->ai_analyzer->analyzeDicomImage($image_data, $patient_id, $diagnostic_id);
            
            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Créer une nouvelle analyse
     */
    private function createAnalysis() {
        try {
            $data = json_decode(file_get_contents("php://input"));
            
            if (!isset($data->diagnostic_id) || empty($data->diagnostic_id)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Diagnostic ID is required']);
                return;
            }
            
            $this->diagnosis->id = $data->diagnostic_id;
            $stmt = $this->diagnosis->readOne();
            
            if ($stmt && $stmt->rowCount() > 0) {
                $diagnostic = $stmt->fetch(PDO::FETCH_ASSOC);
                $result = $this->ai_analyzer->analyzeDiagnostic($diagnostic);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Analysis created successfully',
                    'data' => $result
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Diagnostic not found']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Obtenir des suggestions IA générales
     */
    private function getAISuggestions() {
        try {
            $type = $_GET['type'] ?? null;
            $patient_id = $_GET['patient_id'] ?? null;
            
            if ($type === 'diagnostic' && $patient_id) {
                // Obtenir le dernier diagnostic du patient
                $stmt = $this->db->prepare("SELECT * FROM diagnostics WHERE patient_id = ? ORDER BY date_diagnostic DESC LIMIT 1");
                $stmt->execute([$patient_id]);
                
                if ($stmt->rowCount() > 0) {
                    $diagnostic = $stmt->fetch(PDO::FETCH_ASSOC);
                    $result = $this->ai_analyzer->analyzeDiagnostic($diagnostic);
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $result['suggestions']
                    ]);
                    return;
                }
            }
            
            // Retourner des suggestions générales
            $suggestions = [
                'diagnostic' => [
                    'Fracture' => ['traitement' => 'Immobilisation, réduction si déplacement', 'urgence' => true],
                    'Entorse' => ['traitement' => 'Protocole RICE (Repos, Ice, Compression, Elevation)', 'urgence' => false],
                    'Hernie discale' => ['traitement' => 'Repos, kinésithérapie, médicaments', 'urgence' => false]
                ],
                'prescription' => [
                    'Fracture' => ['Paracétamol 1g 3x/jour', 'Anti-inflammatoires 200mg 2x/jour'],
                    'Entorse' => ['Paracétamol 1g 3x/jour', 'Anti-inflammatoires 200mg 2x/jour'],
                    'Tendinite' => ['Anti-inflammatoires 200mg 2x/jour', 'Décontractants musculaires']
                ]
            ];
            
            echo json_encode([
                'success' => true,
                'data' => $suggestions
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Créer une suggestion IA
     */
    private function createAISuggestion() {
        try {
            $data = json_decode(file_get_contents("php://input"));
            
            if (!isset($data->type) || !isset($data->data)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Type and data are required']);
                return;
            }
            
            $type = $data->type;
            $input_data = $data->data;
            
            $suggestion = null;
            
            switch ($type) {
                case 'diagnostic':
                    $suggestion = $this->ai_analyzer->analyzeDiagnostic($input_data);
                    break;
                case 'prescription':
                    // Analyser et suggérer des prescriptions
                    $diagnostic = $input_data;
                    $analysis = $this->ai_analyzer->analyzeDiagnostic($diagnostic);
                    $suggestion = ['prescriptions' => $analysis['suggestions']['medicaments'] ?? []];
                    break;
                case 'recovery':
                    $suggestion = $this->ai_analyzer->predictRecoveryTime($input_data);
                    break;
                default:
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid suggestion type']);
                    return;
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Suggestion created successfully',
                'data' => $suggestion
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}

// Instancier et exécuter le contrôleur
$controller = new AIController();
$controller->handleRequest();

// Fonction utilitaire pour les tests
if (php_sapi_name() === 'cli') {
    echo "AI Controller - Test Mode\n";
    echo "==========================\n\n";
    
    // Exemple de test
    $data = json_encode([
        'type' => 'diagnostic',
        'data' => [
            'id' => 1,
            'patient_id' => 1,
            'type_diagnostic' => 'fracture',
            'description' => 'Fracture du radius distal',
            'localisation' => 'Poignet droit',
            'severite' => 'modéré'
        ]
    ]);
    
    file_put_contents('php://input', $data);
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    $controller = new AIController();
    $controller->createAISuggestion();
}
?>"
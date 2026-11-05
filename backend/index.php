<?php
/**
 * Point d'entrée principal de l'API TraumaSoft
 * Version améliorée avec routage intelligent et gestion des erreurs
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-API-Key");
header("Access-Control-Max-Age: 86400");

// Gérer les requêtes preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Configuration de base
define('APP_ROOT', __DIR__);
define('APP_VERSION', '2.0.0');

// Autoload des classes (simplifié)
spl_autoload_register(function ($class_name) {
    $paths = [
        APP_ROOT . '/models/',
        APP_ROOT . '/controllers/',
        APP_ROOT . '/ai/',
        APP_ROOT . '/config/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Gestion des erreurs
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Logger simple
class Logger {
    public static function log($message, $level = 'INFO') {
        $log_file = APP_ROOT . '/logs/' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        
        // Créer le dossier logs s'il n'existe pas
        if (!file_exists(APP_ROOT . '/logs')) {
            mkdir(APP_ROOT . '/logs', 0755, true);
        }
        
        file_put_contents($log_file, "[$timestamp] [$level] $message\n", FILE_APPEND);
    }
    
    public static function error($message) {
        self::log($message, 'ERROR');
    }
    
    public static function info($message) {
        self::log($message, 'INFO');
    }
    
    public static function debug($message) {
        self::log($message, 'DEBUG');
    }
}

// Vérifier la connexion à la base de données
try {
    include_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Impossible de se connecter à la base de données");
    }
    
    Logger::info("Connexion à la base de données établie");
} catch (Exception $e) {
    Logger::error("Erreur de connexion à la base de données: " . $e->getMessage());
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'error' => 'Service unavailable',
        'message' => 'Impossible de se connecter à la base de données',
        'details' => $e->getMessage()
    ]);
    exit();
}

// Router amélioré
class Router {
    private $routes = [];
    private $base_path = '';
    
    public function __construct() {
        // Déterminer le chemin de base
        $request_uri = $_SERVER['REQUEST_URI'];
        $script_name = $_SERVER['SCRIPT_NAME'];
        
        // Extraire le chemin relatif
        $this->base_path = str_replace('\\', '/', dirname($script_name));
        $path = substr($request_uri, strlen($this->base_path));
        
        // Nettoyer le chemin
        $path = '/' . trim($path, '/');
        $path = parse_url($path, PHP_URL_PATH);
        
        // Définir les routes
        $this->defineRoutes();
        
        // Trouver et exécuter la route
        $this->dispatch($path, $_SERVER['REQUEST_METHOD']);
    }
    
    private function defineRoutes() {
        // Routes pour les patients
        $this->addRoute('GET', '/patients', 'PatientController', 'getAllPatients');
        $this->addRoute('GET', '/patients/:id', 'PatientController', 'getPatient');
        $this->addRoute('POST', '/patients', 'PatientController', 'createPatient');
        $this->addRoute('PUT', '/patients/:id', 'PatientController', 'updatePatient');
        $this->addRoute('DELETE', '/patients/:id', 'PatientController', 'deletePatient');
        $this->addRoute('GET', '/patients/search/:term', 'PatientController', 'searchPatients');
        
        // Routes pour les rendez-vous
        $this->addRoute('GET', '/appointments', 'AppointmentController', 'getAllAppointments');
        $this->addRoute('GET', '/appointments/:id', 'AppointmentController', 'getAppointment');
        $this->addRoute('POST', '/appointments', 'AppointmentController', 'createAppointment');
        $this->addRoute('PUT', '/appointments/:id', 'AppointmentController', 'updateAppointment');
        $this->addRoute('DELETE', '/appointments/:id', 'AppointmentController', 'deleteAppointment');
        $this->addRoute('GET', '/appointments/today', 'AppointmentController', 'getTodayAppointments');
        $this->addRoute('GET', '/appointments/upcoming', 'AppointmentController', 'getUpcomingAppointments');
        
        // Routes pour les diagnostics
        $this->addRoute('GET', '/diagnoses', 'DiagnosisController', 'getAllDiagnoses');
        $this->addRoute('GET', '/diagnoses/:id', 'DiagnosisController', 'getDiagnosis');
        $this->addRoute('POST', '/diagnoses', 'DiagnosisController', 'createDiagnosis');
        $this->addRoute('PUT', '/diagnoses/:id', 'DiagnosisController', 'updateDiagnosis');
        $this->addRoute('DELETE', '/diagnoses/:id', 'DiagnosisController', 'deleteDiagnosis');
        $this->addRoute('GET', '/diagnoses/patient/:id', 'DiagnosisController', 'getByPatient');
        
        // Routes pour les prescriptions
        $this->addRoute('GET', '/prescriptions', 'PrescriptionController', 'getAllPrescriptions');
        $this->addRoute('GET', '/prescriptions/:id', 'PrescriptionController', 'getPrescription');
        $this->addRoute('POST', '/prescriptions', 'PrescriptionController', 'createPrescription');
        $this->addRoute('PUT', '/prescriptions/:id', 'PrescriptionController', 'updatePrescription');
        $this->addRoute('DELETE', '/prescriptions/:id', 'PrescriptionController', 'deletePrescription');
        $this->addRoute('GET', '/prescriptions/patient/:id', 'PrescriptionController', 'getByPatient');
        
        // Routes pour les opérations
        $this->addRoute('GET', '/operations', 'OperationController', 'getAllOperations');
        $this->addRoute('GET', '/operations/:id', 'OperationController', 'getOperation');
        $this->addRoute('POST', '/operations', 'OperationController', 'createOperation');
        $this->addRoute('PUT', '/operations/:id', 'OperationController', 'updateOperation');
        $this->addRoute('DELETE', '/operations/:id', 'OperationController', 'deleteOperation');
        $this->addRoute('GET', '/operations/upcoming', 'OperationController', 'getUpcomingOperations');
        
        // Routes pour les certificats
        $this->addRoute('GET', '/certificates', 'CertificateController', 'getAllCertificates');
        $this->addRoute('GET', '/certificates/:id', 'CertificateController', 'getCertificate');
        $this->addRoute('POST', '/certificates', 'CertificateController', 'createCertificate');
        $this->addRoute('PUT', '/certificates/:id', 'CertificateController', 'updateCertificate');
        $this->addRoute('DELETE', '/certificates/:id', 'CertificateController', 'deleteCertificate');
        $this->addRoute('GET', '/certificates/patient/:id', 'CertificateController', 'getByPatient');
        
        // Routes pour la finance
        $this->addRoute('GET', '/finance/payments', 'FinanceController', 'getAllPayments');
        $this->addRoute('GET', '/finance/payments/:id', 'FinanceController', 'getPayment');
        $this->addRoute('POST', '/finance/payments', 'FinanceController', 'createPayment');
        $this->addRoute('PUT', '/finance/payments/:id', 'FinanceController', 'updatePayment');
        $this->addRoute('DELETE', '/finance/payments/:id', 'FinanceController', 'deletePayment');
        $this->addRoute('GET', '/finance/revenue', 'FinanceController', 'getRevenueReport');
        $this->addRoute('GET', '/finance/outstanding', 'FinanceController', 'getOutstandingPayments');
        
        // Routes pour le tableau de bord
        $this->addRoute('GET', '/dashboard/stats', 'DashboardController', 'getStatistics');
        $this->addRoute('GET', '/dashboard/waiting-room', 'DashboardController', 'getWaitingRoom');
        $this->addRoute('GET', '/dashboard/today', 'DashboardController', 'getTodaySummary');
        $this->addRoute('GET', '/dashboard/activity', 'DashboardController', 'getRecentActivity');
        
        // Routes pour l'IA
        $this->addRoute('GET', '/ai/analyze/diagnostic/:id', 'AIController', 'analyzeDiagnostic');
        $this->addRoute('GET', '/ai/analyze/patient/:id', 'AIController', 'analyzePatient');
        $this->addRoute('GET', '/ai/predict/recovery/:id', 'AIController', 'predictRecovery');
        $this->addRoute('GET', '/ai/performance', 'AIController', 'getPerformanceAnalysis');
        $this->addRoute('GET', '/ai/schedule/optimize', 'AIController', 'optimizeSchedule');
        $this->addRoute('GET', '/ai/analyses', 'AIController', 'getAllAnalyses');
        $this->addRoute('POST', '/ai/analyze/dicom', 'AIController', 'analyzeDicomImage');
        $this->addRoute('POST', '/ai/analyze', 'AIController', 'createAnalysis');
        $this->addRoute('POST', '/ai/suggestions', 'AIController', 'createAISuggestion');
        
        // Routes pour l'authentification (à implémenter)
        $this->addRoute('POST', '/auth/login', 'AuthController', 'login');
        $this->addRoute('POST', '/auth/register', 'AuthController', 'register');
        $this->addRoute('POST', '/auth/refresh', 'AuthController', 'refreshToken');
        $this->addRoute('POST', '/auth/logout', 'AuthController', 'logout');
        
        // Route pour la documentation
        $this->addRoute('GET', '/api', 'Router', 'getApiInfo');
        $this->addRoute('GET', '/', 'Router', 'getApiInfo');
    }
    
    private function addRoute($method, $path, $controller, $action) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
    }
    
    private function dispatch($path, $method) {
        foreach ($this->routes as $route) {
            if ($route['method'] === $method) {
                $pattern = $this->pathToPattern($route['path']);
                
                if (preg_match($pattern, $path, $matches)) {
                    // Extraire les paramètres
                    $params = $this->extractParams($route['path'], $matches);
                    
                    // Vérifier si le contrôleur existe
                    $controller_file = APP_ROOT . '/controllers/' . $route['controller'] . '.php';
                    
                    if (file_exists($controller_file)) {
                        try {
                            include_once $controller_file;
                            
                            if (class_exists($route['controller'])) {
                                $controller = new $route['controller']();
                                
                                // Vérifier si l'action existe
                                if (method_exists($controller, $route['action'])) {
                                    // Appeler l'action avec les paramètres
                                    call_user_func_array([$controller, $route['action']], $params);
                                    return;
                                } else {
                                    Logger::error("Action non trouvée: {$route['controller']}::{$route['action']}");
                                    http_response_code(404);
                                    echo json_encode(['
                                        success' => false,
                                        'error' => 'Not found',
                                        'message' => "Action {$route['action']} non trouvée dans le contrôleur {$route['controller']}"
                                    ]);
                                    return;
                                }
                            } else {
                                Logger::error("Contrôleur non trouvé: {$route['controller']}");
                                http_response_code(404);
                                echo json_encode(['
                                    success' => false,
                                    'error' => 'Not found',
                                    'message' => "Contrôleur {$route['controller']} non trouvé"
                                ]);
                                return;
                            }
                        } catch (Exception $e) {
                            Logger::error("Erreur dans le contrôleur {$route['controller']}: " . $e->getMessage());
                            http_response_code(500);
                            echo json_encode(['
                                success' => false,
                                'error' => 'Internal server error',
                                'message' => $e->getMessage()
                            ]);
                            return;
                        }
                    } else {
                        Logger::error("Fichier du contrôleur non trouvé: $controller_file");
                        http_response_code(404);
                        echo json_encode(['
                            success' => false,
                            'error' => 'Not found',
                            'message' => "Contrôleur {$route['controller']} non trouvé"
                        ]);
                        return;
                    }
                }
            }
        }
        
        // Aucune route trouvée
        Logger::error("Route non trouvée: $method $path");
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Not found',
            'message' => "Route $method $path non trouvée",
            'available_routes' => $this->getAvailableRoutes()
        ]);
    }
    
    private function pathToPattern($path) {
        // Convertir le chemin en regex
        $pattern = preg_replace('/\:([a-zA-Z0-9_]+)/', '(?P<$1>[^/]+)', $path);
        return '@^' . $pattern . '$@';
    }
    
    private function extractParams($path, $matches) {
        $params = [];
        
        // Extraire les noms des paramètres du chemin
        if (preg_match_all('/\:([a-zA-Z0-9_]+)/', $path, $param_matches)) {
            foreach ($param_matches[1] as $param_name) {
                if (isset($matches[$param_name])) {
                    $params[] = $matches[$param_name];
                }
            }
        }
        
        return $params;
    }
    
    public function getApiInfo() {
        $info = [
            'name' => 'TraumaSoft API',
            'version' => APP_VERSION,
            'description' => 'API REST pour la gestion d\'un cabinet de traumatologie avec intégration IA',
            'endpoints' => $this->getAvailableRoutes(),
            'features' => [
                'Gestion des patients',
                'Gestion des rendez-vous',
                'Gestion des diagnostics',
                'Gestion des prescriptions',
                'Gestion des opérations',
                'Gestion des certificats',
                'Gestion financière',
                'Tableau de bord',
                'Analyse IA des diagnostics',
                'Prédiction de récupération',
                'Optimisation du planning',
                'Analyse des performances'
            ],
            'ai_features' => [
                'Analyse automatique des diagnostics',
                'Suggestions de traitements intelligentes',
                'Prédiction de la durée de récupération',
                'Optimisation du planning des rendez-vous',
                'Analyse des performances du cabinet',
                'Détection d\'anomalies dans les images DICOM'
            ]
        ];
        
        echo json_encode($info, JSON_PRETTY_PRINT);
    }
    
    private function getAvailableRoutes() {
        $routes = [];
        
        foreach ($this->routes as $route) {
            $routes[] = [
                'method' => $route['method'],
                'path' => $route['path'],
                'controller' => $route['controller'],
                'action' => $route['action']
            ];
        }
        
        return $routes;
    }
}

// Démarrer le router
try {
    Logger::info("Démarrage de l'API TraumaSoft v" . APP_VERSION);
    Logger::info("Requête: {$_SERVER['REQUEST_METHOD']} {$_SERVER['REQUEST_URI']}");
    
    new Router();
} catch (Exception $e) {
    Logger::error("Erreur fatale: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
}

// Fermer la connexion à la base de données
if (isset($db)) {
    $db = null;
}

// Fonction utilitaire pour les tests CLI
if (php_sapi_name() === 'cli') {
    echo "TraumaSoft API - Mode Test\n";
    echo "==========================\n\n";
    
    // Tester la connexion à la base de données
    try {
        include_once 'config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        
        if ($db) {
            echo "✓ Connexion à la base de données: OK\n";
        } else {
            echo "✗ Connexion à la base de données: ÉCHEC\n";
        }
    } catch (Exception $e) {
        echo "✗ Erreur de connexion: " . $e->getMessage() . "\n";
    }
    
    // Tester le router
    echo "\nRoutes disponibles:\n";
    $router = new Router();
    $routes = $router->getAvailableRoutes();
    
    foreach ($routes as $route) {
        echo "  {$route['method']} {$route['path']}\n";
    }
    
    echo "\nTotal: " . count($routes) . " routes\n";
}
?>"
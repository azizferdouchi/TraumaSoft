<?php
// Point d'entrée principal de l'API
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Gérer les requêtes preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Router basique
$request_uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Extraire le chemin de la requête
$path = parse_url($request_uri, PHP_URL_PATH);
$path = str_replace('/traumasoft/backend', '', $path);

// Routing des requêtes API
if (strpos($path, '/controllers/') === 0) {
    // Appel direct au contrôleur
    $controller_name = str_replace('/controllers/', '', $path);
    $controller_name = str_replace('.php', '', $controller_name);
    
    $controller_file = __DIR__ . '/controllers/' . $controller_name . '.php';
    
    if (file_exists($controller_file)) {
        require_once $controller_file;
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "Controller not found: " . $controller_name));
    }
} else {
    // Route par défaut
    switch($path) {
        case '/patients':
        case '/patients/':
            require_once 'controllers/PatientController.php';
            break;
        case '/appointments':
        case '/appointments/':
            require_once 'controllers/AppointmentController.php';
            break;
        case '/operations':
        case '/operations/':
            require_once 'controllers/OperationController.php';
            break;
        case '/certificates':
        case '/certificates/':
            require_once 'controllers/CertificateController.php';
            break;
        case '/diagnoses':
        case '/diagnoses/':
            require_once 'controllers/DiagnosisController.php';
            break;
        case '/prescriptions':
        case '/prescriptions/':
            require_once 'controllers/PrescriptionController.php';
            break;
        case '/finance':
        case '/finance/':
            require_once 'controllers/FinanceController.php';
            break;
        case '/dashboard':
        case '/dashboard/':
            require_once 'controllers/DashboardController.php';
            break;
        default:
            http_response_code(404);
            echo json_encode(array(
                "message" => "API endpoint not found",
                "available_endpoints" => [
                    "/patients",
                    "/appointments", 
                    "/operations",
                    "/certificates",
                    "/diagnoses",
                    "/prescriptions",
                    "/finance",
                    "/dashboard"
                ]
            ));
    }
}
?>
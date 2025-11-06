<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../models/Patient.php';
include_once '../models/Appointment.php';
include_once '../models/Operation.php';
include_once '../models/Payment.php';
include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if($method == 'GET') {
    $type = $_GET['type'] ?? 'monthly';
    $startDate = $_GET['start'] ?? date('Y-m-01');
    $endDate = $_GET['end'] ?? date('Y-m-t');
    
    switch($type) {
        case 'monthly':
            // Statistiques mensuelles
            $patient = new Patient($db);
            $stmtPatients = $patient->read();
            $totalPatients = $stmtPatients->rowCount();
            
            $appointment = new Appointment($db);
            $query = "SELECT COUNT(*) as total FROM appointments WHERE DATE(date_rdv) BETWEEN ? AND ?";
            $stmt = $appointment->conn->prepare($query);
            $stmt->execute([$startDate, $endDate]);
            $appointmentsCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $operation = new Operation($db);
            $query = "SELECT COUNT(*) as total FROM operations WHERE DATE(date_operation) BETWEEN ? AND ?";
            $stmt = $operation->conn->prepare($query);
            $stmt->execute([$startDate, $endDate]);
            $operationsCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $payment = new Payment($db);
            $query = "SELECT SUM(montant) as total FROM payments WHERE date_paiement BETWEEN ? AND ? AND statut = 'réglé'";
            $stmt = $payment->conn->prepare($query);
            $stmt->execute([$startDate, $endDate]);
            $revenue = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            $report = array(
                "periode" => "Du " . $startDate . " au " . $endDate,
                "total_patients" => $totalPatients,
                "rendez_vous" => $appointmentsCount,
                "operations" => $operationsCount,
                "chiffre_affaires" => $revenue,
                "type" => "rapport_mensuel"
            );
            break;
            
        case 'patients':
            // Rapport patients
            $patient = new Patient($db);
            $stmt = $patient->read();
            $patients = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $patients[] = $row;
            }
            $report = array(
                "type" => "rapport_patients",
                "total" => count($patients),
                "patients" => $patients
            );
            break;
            
        case 'finance':
            // Rapport financier
            $payment = new Payment($db);
            $stmt = $payment->read();
            $payments = array();
            $total = 0;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $payments[] = $row;
                $total += $row['montant'];
            }
            
            $statsStmt = $payment->getStats();
            $stats = array();
            while ($row = $statsStmt->fetch(PDO::FETCH_ASSOC)) {
                $stats[] = $row;
            }
            
            $report = array(
                "type" => "rapport_financier",
                "total_revenus" => $total,
                "nombre_paiements" => count($payments),
                "repartition" => $stats,
                "paiements" => $payments
            );
            break;
            
        default:
            $report = array("message" => "Type de rapport non supporté");
            break;
    }
    
    http_response_code(200);
    echo json_encode($report);
} else {
    http_response_code(405);
    echo json_encode(array("message" => "Méthode non autorisée."));
}
?>
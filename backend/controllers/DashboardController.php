<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';

class DashboardController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getStats() {
        $stats = array();

        // Total patients
        $query = "SELECT COUNT(*) as total FROM patients";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['totalPatients'] = $row['total'];

        // Today's appointments
        $query = "SELECT COUNT(*) as total FROM appointments WHERE date_rdv = CURDATE()";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['todayAppointments'] = $row['total'];

        // Waiting patients
        $query = "SELECT COUNT(*) as total FROM waiting_room WHERE statut = 'en attente'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['waitingPatients'] = $row['total'];

        // Monthly revenue
        $query = "SELECT SUM(montant) as total FROM payments 
                 WHERE MONTH(date_paiement) = MONTH(CURDATE()) 
                 AND YEAR(date_paiement) = YEAR(CURDATE())";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['monthRevenue'] = $row['total'] ? $row['total'] : 0;

        echo json_encode($stats);
    }
}

if(isset($_GET['action']) && $_GET['action'] == 'stats') {
    $controller = new DashboardController();
    $controller->getStats();
}
?>
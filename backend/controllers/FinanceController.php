<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../models/Payment.php';
include_once '../config/database.php';

class FinanceController {
    private $db;
    private $payment;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->payment = new Payment($this->db);
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch($method) {
            case 'GET':
                $this->getPayments();
                break;
            case 'POST':
                $this->createPayment();
                break;
            case 'PUT':
                $this->updatePayment();
                break;
            case 'DELETE':
                $this->deletePayment();
                break;
            default:
                http_response_code(405);
                echo json_encode(array("message" => "Method not allowed"));
        }
    }

    private function getPayments() {
        $stmt = $this->payment->read();
        $num = $stmt->rowCount();

        if($num > 0) {
            $payments_arr = array();
            $payments_arr["records"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $payment_item = array(
                    "id" => $id,
                    "patient_id" => $patient_id,
                    "patient_nom" => $patient_nom,
                    "patient_prenom" => $patient_prenom,
                    "date_paiement" => $date_paiement,
                    "montant" => $montant,
                    "mode_paiement" => $mode_paiement,
                    "statut" => $statut,
                    "facture_numero" => $facture_numero,
                    "notes" => $notes
                );
                array_push($payments_arr["records"], $payment_item);
            }
            echo json_encode($payments_arr);
        } else {
            echo json_encode(array("message" => "No payments found."));
        }
    }

    private function createPayment() {
        $data = json_decode(file_get_contents("php://input"));
        
        $this->payment->patient_id = $data->patient_id;
        $this->payment->date_paiement = $data->date_paiement;
        $this->payment->montant = $data->montant;
        $this->payment->mode_paiement = $data->mode_paiement;
        $this->payment->statut = $data->statut;
        $this->payment->facture_numero = $data->facture_numero;
        $this->payment->notes = $data->notes;

        if($this->payment->create()) {
            http_response_code(201);
            echo json_encode(array("message" => "Paiement créé avec succès."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Impossible de créer le paiement."));
        }
    }

    private function updatePayment() {
        $data = json_decode(file_get_contents("php://input"));
        
        $this->payment->id = $data->id;
        $this->payment->patient_id = $data->patient_id;
        $this->payment->date_paiement = $data->date_paiement;
        $this->payment->montant = $data->montant;
        $this->payment->mode_paiement = $data->mode_paiement;
        $this->payment->statut = $data->statut;
        $this->payment->facture_numero = $data->facture_numero;
        $this->payment->notes = $data->notes;

        if($this->payment->update()) {
            echo json_encode(array("message" => "Paiement mis à jour avec succès."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Impossible de mettre à jour le paiement."));
        }
    }

    private function deletePayment() {
        $data = json_decode(file_get_contents("php://input"));
        $this->payment->id = $data->id;

        if($this->payment->delete()) {
            echo json_encode(array("message" => "Paiement supprimé avec succès."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Impossible de supprimer le paiement."));
        }
    }

    public function getStats() {
        $stats = array();

        // Total revenue
        $query = "SELECT SUM(montant) as total FROM payments WHERE statut = 'payé'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['totalRevenue'] = $row['total'] ? $row['total'] : 0;

        // Monthly revenue
        $query = "SELECT SUM(montant) as total FROM payments 
                 WHERE statut = 'payé' AND MONTH(date_paiement) = MONTH(CURDATE()) 
                 AND YEAR(date_paiement) = YEAR(CURDATE())";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['monthRevenue'] = $row['total'] ? $row['total'] : 0;

        // Pending payments
        $query = "SELECT SUM(montant) as total FROM payments WHERE statut = 'en attente'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['pendingPayments'] = $row['total'] ? $row['total'] : 0;

        echo json_encode($stats);
    }
}

// Gérer les requêtes avec action
if(isset($_GET['action'])) {
    $controller = new FinanceController();
    switch($_GET['action']) {
        case 'stats':
            $controller->getStats();
            break;
        default:
            $controller->handleRequest();
    }
} else {
    $controller = new FinanceController();
    $controller->handleRequest();
}
?>
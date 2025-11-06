<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../models/Prescription.php';
include_once '../config/database.php';

class PrescriptionController {
    private $db;
    private $prescription;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->prescription = new Prescription($this->db);
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch($method) {
            case 'GET':
                $this->getPrescriptions();
                break;
            case 'POST':
                $this->createPrescription();
                break;
            case 'PUT':
                $this->updatePrescription();
                break;
            case 'DELETE':
                $this->deletePrescription();
                break;
            default:
                http_response_code(405);
                echo json_encode(array("message" => "Method not allowed"));
        }
    }

    private function getPrescriptions() {
        if(isset($_GET['patient_id'])) {
            $this->prescription->patient_id = $_GET['patient_id'];
            $stmt = $this->prescription->getByPatient();
        } else {
            $stmt = $this->prescription->read();
        }
        
        $num = $stmt->rowCount();

        if($num > 0) {
            $prescriptions_arr = array();
            $prescriptions_arr["records"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $prescription_item = array(
                    "id" => $id,
                    "patient_id" => $patient_id,
                    "date_prescription" => $date_prescription,
                    "medicaments" => $medicaments,
                    "instructions" => $instructions,
                    "duree_traitement" => $duree_traitement,
                    "medecin_prescripteur" => $medecin_prescripteur,
                    "created_at" => $created_at
                );
                array_push($prescriptions_arr["records"], $prescription_item);
            }
            echo json_encode($prescriptions_arr);
        } else {
            echo json_encode(array("message" => "No prescriptions found."));
        }
    }

    private function createPrescription() {
        $data = json_decode(file_get_contents("php://input"));
        
        $this->prescription->patient_id = $data->patient_id;
        $this->prescription->date_prescription = $data->date_prescription;
        $this->prescription->medicaments = json_encode($data->medicaments);
        $this->prescription->instructions = $data->instructions;
        $this->prescription->duree_traitement = $data->duree_traitement;
        $this->prescription->medecin_prescripteur = $data->medecin_prescripteur;

        if($this->prescription->create()) {
            http_response_code(201);
            echo json_encode(array("message" => "Ordonnance créée avec succès."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Impossible de créer l'ordonnance."));
        }
    }

    private function updatePrescription() {
        $data = json_decode(file_get_contents("php://input"));
        
        $this->prescription->id = $data->id;
        $this->prescription->patient_id = $data->patient_id;
        $this->prescription->date_prescription = $data->date_prescription;
        $this->prescription->medicaments = json_encode($data->medicaments);
        $this->prescription->instructions = $data->instructions;
        $this->prescription->duree_traitement = $data->duree_traitement;
        $this->prescription->medecin_prescripteur = $data->medecin_prescripteur;

        if($this->prescription->update()) {
            echo json_encode(array("message" => "Ordonnance mise à jour avec succès."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Impossible de mettre à jour l'ordonnance."));
        }
    }

    private function deletePrescription() {
        $data = json_decode(file_get_contents("php://input"));
        $this->prescription->id = $data->id;
        $this->prescription->patient_id = $data->patient_id;

        if($this->prescription->delete()) {
            echo json_encode(array("message" => "Ordonnance supprimée avec succès."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Impossible de supprimer l'ordonnance."));
        }
    }
}

$controller = new PrescriptionController();
$controller->handleRequest();
?>
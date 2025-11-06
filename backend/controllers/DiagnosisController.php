<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../models/Diagnosis.php';
include_once '../config/database.php';

class DiagnosisController {
    private $db;
    private $diagnosis;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->diagnosis = new Diagnosis($this->db);
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch($method) {
            case 'GET':
                $this->getDiagnoses();
                break;
            case 'POST':
                $this->createDiagnosis();
                break;
            case 'PUT':
                $this->updateDiagnosis();
                break;
            case 'DELETE':
                $this->deleteDiagnosis();
                break;
            default:
                http_response_code(405);
                echo json_encode(array("message" => "Method not allowed"));
        }
    }

    private function getDiagnoses() {
        if(isset($_GET['patient_id'])) {
            $this->diagnosis->patient_id = $_GET['patient_id'];
            $stmt = $this->diagnosis->getByPatient();
        } else {
            $stmt = $this->diagnosis->read();
        }
        
        $num = $stmt->rowCount();

        if($num > 0) {
            $diagnoses_arr = array();
            $diagnoses_arr["records"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $diagnosis_item = array(
                    "id" => $id,
                    "patient_id" => $patient_id,
                    "date_diagnostic" => $date_diagnostic,
                    "diagnostic" => $diagnostic,
                    "observations" => $observations,
                    "traitement_propose" => $traitement_propose,
                    "medecin" => $medecin,
                    "created_at" => $created_at
                );
                array_push($diagnoses_arr["records"], $diagnosis_item);
            }
            echo json_encode($diagnoses_arr);
        } else {
            echo json_encode(array("message" => "No diagnoses found."));
        }
    }

    private function createDiagnosis() {
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->patient_id) && !empty($data->diagnostic)) {
            $this->diagnosis->patient_id = $data->patient_id;
            $this->diagnosis->date_diagnostic = $data->date_diagnostic;
            $this->diagnosis->diagnostic = $data->diagnostic;
            $this->diagnosis->observations = $data->observations;
            $this->diagnosis->traitement_propose = $data->traitement_propose;
            $this->diagnosis->medecin = $data->medecin;

            if($this->diagnosis->create()) {
                http_response_code(201);
                echo json_encode(array("message" => "Diagnostic créé avec succès."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Impossible de créer le diagnostic."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Données incomplètes."));
        }
    }

    private function updateDiagnosis() {
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id) && !empty($data->patient_id)) {
            $this->diagnosis->id = $data->id;
            $this->diagnosis->patient_id = $data->patient_id;
            $this->diagnosis->date_diagnostic = $data->date_diagnostic;
            $this->diagnosis->diagnostic = $data->diagnostic;
            $this->diagnosis->observations = $data->observations;
            $this->diagnosis->traitement_propose = $data->traitement_propose;
            $this->diagnosis->medecin = $data->medecin;

            if($this->diagnosis->update()) {
                echo json_encode(array("message" => "Diagnostic mis à jour avec succès."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Impossible de mettre à jour le diagnostic."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "ID manquant."));
        }
    }

    private function deleteDiagnosis() {
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id) && !empty($data->patient_id)) {
            $this->diagnosis->id = $data->id;
            $this->diagnosis->patient_id = $data->patient_id;

            if($this->diagnosis->delete()) {
                echo json_encode(array("message" => "Diagnostic supprimé avec succès."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Impossible de supprimer le diagnostic."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "ID manquant."));
        }
    }
}

$controller = new DiagnosisController();
$controller->handleRequest();
?>
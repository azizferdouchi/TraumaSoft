<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../models/Patient.php';
include_once '../models/Diagnosis.php';
include_once '../models/Prescription.php';
include_once '../config/database.php';

class PatientController {
    private $db;
    private $patient;
    private $diagnosis;
    private $prescription;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->patient = new Patient($this->db);
        $this->diagnosis = new Diagnosis($this->db);
        $this->prescription = new Prescription($this->db);
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch($method) {
            case 'GET':
                if(isset($_GET['id'])) {
                    $this->getPatient($_GET['id']);
                } else if(isset($_GET['search'])) {
                    $this->searchPatients($_GET['search']);
                } else {
                    $this->getAllPatients();
                }
                break;
            case 'POST':
                $this->createPatient();
                break;
            case 'PUT':
                $this->updatePatient();
                break;
            case 'DELETE':
                $this->deletePatient();
                break;
            default:
                http_response_code(405);
                echo json_encode(array("message" => "Method not allowed"));
        }
    }

    private function getAllPatients() {
        $stmt = $this->patient->read();
        $num = $stmt->rowCount();

        if($num > 0) {
            $patients_arr = array();
            $patients_arr["records"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $patient_item = array(
                    "id" => $id,
                    "nom" => $nom,
                    "prenom" => $prenom,
                    "date_naissance" => $date_naissance,
                    "sexe" => $sexe,
                    "telephone" => $telephone,
                    "email" => $email,
                    "assurance_compagnie" => $assurance_compagnie,
                    "assurance_numero" => $assurance_numero
                );
                array_push($patients_arr["records"], $patient_item);
            }
            echo json_encode($patients_arr);
        } else {
            echo json_encode(array("message" => "No patients found."));
        }
    }

    private function getPatient($id) {
        $this->patient->id = $id;
        $stmt = $this->patient->readOne();
        
        if($stmt && $stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get diagnoses
            $this->diagnosis->patient_id = $id;
            $diagnoses_stmt = $this->diagnosis->getByPatient();
            $diagnoses = array();
            while($diag_row = $diagnoses_stmt->fetch(PDO::FETCH_ASSOC)) {
                $diagnoses[] = $diag_row;
            }
            
            // Get prescriptions
            $this->prescription->patient_id = $id;
            $prescriptions_stmt = $this->prescription->getByPatient();
            $prescriptions = array();
            while($presc_row = $prescriptions_stmt->fetch(PDO::FETCH_ASSOC)) {
                $prescriptions[] = $presc_row;
            }
            
            $patient_data = array(
                "patient" => $row,
                "diagnoses" => $diagnoses,
                "prescriptions" => $prescriptions
            );
            
            echo json_encode($patient_data);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Patient not found."));
        }
    }

    private function searchPatients($searchTerm) {
        $stmt = $this->patient->search($searchTerm);
        $num = $stmt->rowCount();

        if($num > 0) {
            $patients_arr = array();
            $patients_arr["records"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $patient_item = array(
                    "id" => $id,
                    "nom" => $nom,
                    "prenom" => $prenom,
                    "date_naissance" => $date_naissance,
                    "sexe" => $sexe,
                    "telephone" => $telephone,
                    "email" => $email,
                    "assurance_compagnie" => $assurance_compagnie
                );
                array_push($patients_arr["records"], $patient_item);
            }
            echo json_encode($patients_arr);
        } else {
            echo json_encode(array("message" => "No patients found."));
        }
    }

    private function createPatient() {
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->nom) && !empty($data->prenom) && !empty($data->telephone)) {
            $this->patient->nom = $data->nom;
            $this->patient->prenom = $data->prenom;
            $this->patient->date_naissance = $data->date_naissance;
            $this->patient->sexe = $data->sexe;
            $this->patient->adresse = $data->adresse;
            $this->patient->telephone = $data->telephone;
            $this->patient->email = $data->email;
            $this->patient->assurance_compagnie = $data->assurance_compagnie;
            $this->patient->assurance_numero = $data->assurance_numero;
            $this->patient->assurance_type = $data->assurance_type;

            if($this->patient->create()) {
                http_response_code(201);
                echo json_encode(array("message" => "Patient créé avec succès."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Impossible de créer le patient."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Données incomplètes."));
        }
    }

    private function updatePatient() {
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $this->patient->id = $data->id;
            $this->patient->nom = $data->nom;
            $this->patient->prenom = $data->prenom;
            $this->patient->date_naissance = $data->date_naissance;
            $this->patient->sexe = $data->sexe;
            $this->patient->adresse = $data->adresse;
            $this->patient->telephone = $data->telephone;
            $this->patient->email = $data->email;
            $this->patient->assurance_compagnie = $data->assurance_compagnie;
            $this->patient->assurance_numero = $data->assurance_numero;
            $this->patient->assurance_type = $data->assurance_type;

            if($this->patient->update()) {
                echo json_encode(array("message" => "Patient mis à jour avec succès."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Impossible de mettre à jour le patient."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "ID patient manquant."));
        }
    }

    private function deletePatient() {
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $this->patient->id = $data->id;

            if($this->patient->delete()) {
                echo json_encode(array("message" => "Patient supprimé avec succès."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Impossible de supprimer le patient."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "ID patient manquant."));
        }
    }
}

$controller = new PatientController();
$controller->handleRequest();
?>
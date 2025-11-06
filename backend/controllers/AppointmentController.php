<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../models/Appointment.php';
include_once '../models/Patient.php';
include_once '../config/database.php';

class AppointmentController {
    private $db;
    private $appointment;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->appointment = new Appointment($this->db);
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch($method) {
            case 'GET':
                if(isset($_GET['action'])) {
                    switch($_GET['action']) {
                        case 'today':
                            $this->getTodayAppointments();
                            break;
                        case 'waiting':
                            $this->getWaitingRoom();
                            break;
                        default:
                            $this->getAppointments();
                    }
                } else {
                    $this->getAppointments();
                }
                break;
            case 'POST':
                $this->createAppointment();
                break;
            case 'PUT':
                $this->updateAppointment();
                break;
            case 'DELETE':
                $this->deleteAppointment();
                break;
            default:
                http_response_code(405);
                echo json_encode(array("message" => "Method not allowed"));
        }
    }

    private function getAppointments() {
        $stmt = $this->appointment->read();
        $num = $stmt->rowCount();

        if($num > 0) {
            $appointments_arr = array();
            $appointments_arr["records"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $appointment_item = array(
                    "id" => $id,
                    "patient_id" => $patient_id,
                    "patient_nom" => $patient_nom,
                    "patient_prenom" => $patient_prenom,
                    "date_rdv" => $date_rdv,
                    "heure" => $heure,
                    "type_consultation" => $type_consultation,
                    "statut" => $statut,
                    "notes" => $notes
                );
                array_push($appointments_arr["records"], $appointment_item);
            }
            echo json_encode($appointments_arr);
        } else {
            echo json_encode(array("message" => "No appointments found."));
        }
    }

    private function getTodayAppointments() {
        $stmt = $this->appointment->readToday();
        $num = $stmt->rowCount();

        if($num > 0) {
            $appointments_arr = array();
            $appointments_arr["records"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $appointment_item = array(
                    "id" => $id,
                    "patient_nom" => $patient_nom,
                    "patient_prenom" => $patient_prenom,
                    "heure" => $heure,
                    "type_consultation" => $type_consultation
                );
                array_push($appointments_arr["records"], $appointment_item);
            }
            echo json_encode($appointments_arr);
        } else {
            echo json_encode(array("message" => "No appointments today."));
        }
    }

    private function getWaitingRoom() {
        $stmt = $this->appointment->getWaitingRoom();
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
                    "heure_arrivee" => $heure_arrivee,
                    "ordre_passage" => $ordre_passage
                );
                array_push($patients_arr["records"], $patient_item);
            }
            echo json_encode($patients_arr);
        } else {
            echo json_encode(array("message" => "No patients in waiting room."));
        }
    }

    private function createAppointment() {
        $data = json_decode(file_get_contents("php://input"));
        
        $this->appointment->patient_id = $data->patient_id;
        $this->appointment->date_rdv = $data->date_rdv;
        $this->appointment->heure = $data->heure;
        $this->appointment->type_consultation = $data->type_consultation;
        $this->appointment->statut = $data->statut;
        $this->appointment->notes = $data->notes;

        if($this->appointment->create()) {
            http_response_code(201);
            echo json_encode(array("message" => "Rendez-vous créé avec succès."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Impossible de créer le rendez-vous."));
        }
    }

    private function updateAppointment() {
        $data = json_decode(file_get_contents("php://input"));
        
        $this->appointment->id = $data->id;
        $this->appointment->patient_id = $data->patient_id;
        $this->appointment->date_rdv = $data->date_rdv;
        $this->appointment->heure = $data->heure;
        $this->appointment->type_consultation = $data->type_consultation;
        $this->appointment->statut = $data->statut;
        $this->appointment->notes = $data->notes;

        if($this->appointment->update()) {
            echo json_encode(array("message" => "Rendez-vous mis à jour avec succès."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Impossible de mettre à jour le rendez-vous."));
        }
    }

    private function deleteAppointment() {
        $data = json_decode(file_get_contents("php://input"));
        $this->appointment->id = $data->id;

        if($this->appointment->delete()) {
            echo json_encode(array("message" => "Rendez-vous supprimé avec succès."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Impossible de supprimer le rendez-vous."));
        }
    }
}

$controller = new AppointmentController();
$controller->handleRequest();
?>
<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../models/Certificate.php';
include_once '../config/database.php';

class CertificateController {
    private $db;
    private $certificate;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->certificate = new Certificate($this->db);
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch($method) {
            case 'GET':
                $this->getCertificates();
                break;
            case 'POST':
                $this->createCertificate();
                break;
            case 'PUT':
                $this->updateCertificate();
                break;
            case 'DELETE':
                $this->deleteCertificate();
                break;
            default:
                http_response_code(405);
                echo json_encode(array("message" => "Method not allowed"));
        }
    }

    private function getCertificates() {
        $stmt = $this->certificate->read();
        $num = $stmt->rowCount();

        if($num > 0) {
            $certificates_arr = array();
            $certificates_arr["records"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $certificate_item = array(
                    "id" => $id,
                    "patient_id" => $patient_id,
                    "patient_nom" => $patient_nom,
                    "patient_prenom" => $patient_prenom,
                    "date_certificat" => $date_certificat,
                    "type_certificat" => $type_certificat,
                    "duree" => $duree,
                    "diagnostic" => $diagnostic,
                    "medecin" => $medecin
                );
                array_push($certificates_arr["records"], $certificate_item);
            }
            echo json_encode($certificates_arr);
        } else {
            echo json_encode(array("message" => "No certificates found."));
        }
    }

    private function createCertificate() {
        $data = json_decode(file_get_contents("php://input"));
        
        $this->certificate->patient_id = $data->patient_id;
        $this->certificate->date_certificat = $data->date_certificat;
        $this->certificate->type_certificat = $data->type_certificat;
        $this->certificate->duree = $data->duree;
        $this->certificate->diagnostic = $data->diagnostic;
        $this->certificate->recommendations = $data->recommendations;
        $this->certificate->medecin = $data->medecin;

        if($this->certificate->create()) {
            http_response_code(201);
            echo json_encode(array("message" => "Certificat créé avec succès."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Impossible de créer le certificat."));
        }
    }

    public function generatePDF() {
        $data = json_decode(file_get_contents("php://input"));
        $certificateId = $data->id;
        
        // Générer le PDF du certificat (implémentation basique)
        $this->certificate->id = $certificateId;
        $certificate = $this->certificate->readOne();
        
        if($certificate) {
            // Ici vous intégrerez une librairie PDF comme TCPDF ou Dompdf
            $pdfContent = $this->generateCertificatePDF($certificate);
            
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="certificat_medical.pdf"');
            echo $pdfContent;
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Certificat non trouvé."));
        }
    }

    private function generateCertificatePDF($certificate) {
        // Implémentation basique - à remplacer par une vraie génération PDF
        $html = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .content { margin: 20px 0; }
                .signature { margin-top: 50px; text-align: right; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h2>CERTIFICAT MÉDICAL</h2>
            </div>
            <div class='content'>
                <p>Je soussigné, Dr. " . $certificate['medecin'] . ", certifie que :</p>
                <p><strong>" . $certificate['patient_nom'] . " " . $certificate['patient_prenom'] . "</strong></p>
                <p>Diagnostic: " . $certificate['diagnostic'] . "</p>
                <p>Durée: " . $certificate['duree'] . "</p>
                <p>Recommandations: " . $certificate['recommendations'] . "</p>
            </div>
            <div class='signature'>
                <p>Fait à ________, le " . date('d/m/Y') . "</p>
                <p>Signature et cachet</p>
            </div>
        </body>
        </html>";
        
        return $html;
    }
}

$controller = new CertificateController();
$controller->handleRequest();
?>
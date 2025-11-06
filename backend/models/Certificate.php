<?php
class Certificate {
    private $conn;
    private $table = "certificates";

    public $id;
    public $patient_id;
    public $date_certificat;
    public $type_certificat;
    public $duree;
    public $diagnostic;
    public $recommendations;
    public $medecin;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                SET patient_id=:patient_id, date_certificat=:date_certificat, 
                    type_certificat=:type_certificat, duree=:duree, diagnostic=:diagnostic,
                    recommendations=:recommendations, medecin=:medecin";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":patient_id", $this->patient_id);
        $stmt->bindParam(":date_certificat", $this->date_certificat);
        $stmt->bindParam(":type_certificat", $this->type_certificat);
        $stmt->bindParam(":duree", $this->duree);
        $stmt->bindParam(":diagnostic", $this->diagnostic);
        $stmt->bindParam(":recommendations", $this->recommendations);
        $stmt->bindParam(":medecin", $this->medecin);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read() {
        $query = "SELECT c.*, p.nom as patient_nom, p.prenom as patient_prenom 
                 FROM " . $this->table . " c 
                 LEFT JOIN patients p ON c.patient_id = p.id 
                 ORDER BY c.date_certificat DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT c.*, p.nom as patient_nom, p.prenom as patient_prenom 
                 FROM " . $this->table . " c 
                 LEFT JOIN patients p ON c.patient_id = p.id 
                 WHERE c.id = ? 
                 LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->patient_id = $row['patient_id'];
            $this->date_certificat = $row['date_certificat'];
            $this->type_certificat = $row['type_certificat'];
            $this->duree = $row['duree'];
            $this->diagnostic = $row['diagnostic'];
            $this->recommendations = $row['recommendations'];
            $this->medecin = $row['medecin'];
            return $row;
        }
        return false;
    }
}
?>
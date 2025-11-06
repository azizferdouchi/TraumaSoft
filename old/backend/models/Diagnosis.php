<?php
class Diagnosis {
    private $conn;
    private $table = "diagnoses";

    public $id;
    public $patient_id;
    public $date_diagnostic;
    public $diagnostic;
    public $observations;
    public $traitement_propose;
    public $medecin;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                SET patient_id=:patient_id, date_diagnostic=:date_diagnostic, 
                    diagnostic=:diagnostic, observations=:observations,
                    traitement_propose=:traitement_propose, medecin=:medecin";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":patient_id", $this->patient_id);
        $stmt->bindParam(":date_diagnostic", $this->date_diagnostic);
        $stmt->bindParam(":diagnostic", $this->diagnostic);
        $stmt->bindParam(":observations", $this->observations);
        $stmt->bindParam(":traitement_propose", $this->traitement_propose);
        $stmt->bindParam(":medecin", $this->medecin);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getByPatient() {
        $query = "SELECT * FROM " . $this->table . " 
                 WHERE patient_id = ? 
                 ORDER BY date_diagnostic DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->patient_id);
        $stmt->execute();

        return $stmt;
    }

    public function read() {
        $query = "SELECT d.*, p.nom as patient_nom, p.prenom as patient_prenom 
                 FROM " . $this->table . " d 
                 LEFT JOIN patients p ON d.patient_id = p.id 
                 ORDER BY d.date_diagnostic DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function update() {
        $query = "UPDATE " . $this->table . " 
                SET date_diagnostic=:date_diagnostic, diagnostic=:diagnostic, 
                    observations=:observations, traitement_propose=:traitement_propose,
                    medecin=:medecin
                WHERE id=:id AND patient_id=:patient_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":patient_id", $this->patient_id);
        $stmt->bindParam(":date_diagnostic", $this->date_diagnostic);
        $stmt->bindParam(":diagnostic", $this->diagnostic);
        $stmt->bindParam(":observations", $this->observations);
        $stmt->bindParam(":traitement_propose", $this->traitement_propose);
        $stmt->bindParam(":medecin", $this->medecin);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = ? AND patient_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->bindParam(2, $this->patient_id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
<?php
class Prescription {
    private $conn;
    private $table = "prescriptions";

    public $id;
    public $patient_id;
    public $date_prescription;
    public $medicaments;
    public $instructions;
    public $duree_traitement;
    public $medecin_prescripteur;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                SET patient_id=:patient_id, date_prescription=:date_prescription, 
                    medicaments=:medicaments, instructions=:instructions,
                    duree_traitement=:duree_traitement, medecin_prescripteur=:medecin_prescripteur";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":patient_id", $this->patient_id);
        $stmt->bindParam(":date_prescription", $this->date_prescription);
        $stmt->bindParam(":medicaments", $this->medicaments);
        $stmt->bindParam(":instructions", $this->instructions);
        $stmt->bindParam(":duree_traitement", $this->duree_traitement);
        $stmt->bindParam(":medecin_prescripteur", $this->medecin_prescripteur);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getByPatient() {
        $query = "SELECT * FROM " . $this->table . " 
                 WHERE patient_id = ? 
                 ORDER BY date_prescription DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->patient_id);
        $stmt->execute();

        return $stmt;
    }

    public function read() {
        $query = "SELECT p.*, pt.nom as patient_nom, pt.prenom as patient_prenom 
                 FROM " . $this->table . " p 
                 LEFT JOIN patients pt ON p.patient_id = pt.id 
                 ORDER BY p.date_prescription DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function update() {
        $query = "UPDATE " . $this->table . " 
                SET date_prescription=:date_prescription, medicaments=:medicaments, 
                    instructions=:instructions, duree_traitement=:duree_traitement,
                    medecin_prescripteur=:medecin_prescripteur
                WHERE id=:id AND patient_id=:patient_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":patient_id", $this->patient_id);
        $stmt->bindParam(":date_prescription", $this->date_prescription);
        $stmt->bindParam(":medicaments", $this->medicaments);
        $stmt->bindParam(":instructions", $this->instructions);
        $stmt->bindParam(":duree_traitement", $this->duree_traitement);
        $stmt->bindParam(":medecin_prescripteur", $this->medecin_prescripteur);

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
<?php
class Operation {
    private $conn;
    private $table = "operations";

    public $id;
    public $patient_id;
    public $date_operation;
    public $type_operation;
    public $lieu;
    public $duree;
    public $anesthesie;
    public $compte_rendu;
    public $medecin_operateur;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                SET patient_id=:patient_id, date_operation=:date_operation, 
                    type_operation=:type_operation, lieu=:lieu, duree=:duree,
                    anesthesie=:anesthesie, compte_rendu=:compte_rendu, 
                    medecin_operateur=:medecin_operateur";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":patient_id", $this->patient_id);
        $stmt->bindParam(":date_operation", $this->date_operation);
        $stmt->bindParam(":type_operation", $this->type_operation);
        $stmt->bindParam(":lieu", $this->lieu);
        $stmt->bindParam(":duree", $this->duree);
        $stmt->bindParam(":anesthesie", $this->anesthesie);
        $stmt->bindParam(":compte_rendu", $this->compte_rendu);
        $stmt->bindParam(":medecin_operateur", $this->medecin_operateur);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read() {
        $query = "SELECT o.*, p.nom as patient_nom, p.prenom as patient_prenom 
                 FROM " . $this->table . " o 
                 LEFT JOIN patients p ON o.patient_id = p.id 
                 ORDER BY o.date_operation DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function update() {
        $query = "UPDATE " . $this->table . " 
                SET patient_id=:patient_id, date_operation=:date_operation, 
                    type_operation=:type_operation, lieu=:lieu, duree=:duree,
                    anesthesie=:anesthesie, compte_rendu=:compte_rendu, 
                    medecin_operateur=:medecin_operateur
                WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":patient_id", $this->patient_id);
        $stmt->bindParam(":date_operation", $this->date_operation);
        $stmt->bindParam(":type_operation", $this->type_operation);
        $stmt->bindParam(":lieu", $this->lieu);
        $stmt->bindParam(":duree", $this->duree);
        $stmt->bindParam(":anesthesie", $this->anesthesie);
        $stmt->bindParam(":compte_rendu", $this->compte_rendu);
        $stmt->bindParam(":medecin_operateur", $this->medecin_operateur);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
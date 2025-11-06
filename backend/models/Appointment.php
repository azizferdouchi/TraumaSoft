<?php
class Appointment {
    private $conn;
    private $table = "appointments";

    public $id;
    public $patient_id;
    public $date_rdv;
    public $heure;
    public $type_consultation;
    public $statut;
    public $notes;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                SET patient_id=:patient_id, date_rdv=:date_rdv, heure=:heure, 
                    type_consultation=:type_consultation, statut=:statut, notes=:notes";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":patient_id", $this->patient_id);
        $stmt->bindParam(":date_rdv", $this->date_rdv);
        $stmt->bindParam(":heure", $this->heure);
        $stmt->bindParam(":type_consultation", $this->type_consultation);
        $stmt->bindParam(":statut", $this->statut);
        $stmt->bindParam(":notes", $this->notes);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read() {
        $query = "SELECT a.*, p.nom as patient_nom, p.prenom as patient_prenom 
                 FROM " . $this->table . " a 
                 LEFT JOIN patients p ON a.patient_id = p.id 
                 ORDER BY a.date_rdv DESC, a.heure DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readToday() {
        $query = "SELECT a.*, p.nom as patient_nom, p.prenom as patient_prenom 
                 FROM " . $this->table . " a 
                 LEFT JOIN patients p ON a.patient_id = p.id 
                 WHERE a.date_rdv = CURDATE() 
                 ORDER BY a.heure ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getWaitingRoom() {
        $query = "SELECT wr.*, p.nom, p.prenom 
                 FROM waiting_room wr 
                 LEFT JOIN patients p ON wr.patient_id = p.id 
                 WHERE wr.statut = 'en attente' 
                 ORDER BY wr.ordre_passage ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function update() {
        $query = "UPDATE " . $this->table . " 
                SET patient_id=:patient_id, date_rdv=:date_rdv, heure=:heure, 
                    type_consultation=:type_consultation, statut=:statut, notes=:notes
                WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":patient_id", $this->patient_id);
        $stmt->bindParam(":date_rdv", $this->date_rdv);
        $stmt->bindParam(":heure", $this->heure);
        $stmt->bindParam(":type_consultation", $this->type_consultation);
        $stmt->bindParam(":statut", $this->statut);
        $stmt->bindParam(":notes", $this->notes);

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
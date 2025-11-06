<?php
class Payment {
    private $conn;
    private $table = "payments";

    public $id;
    public $patient_id;
    public $date_paiement;
    public $montant;
    public $mode_paiement;
    public $statut;
    public $facture_numero;
    public $notes;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                SET patient_id=:patient_id, date_paiement=:date_paiement, 
                    montant=:montant, mode_paiement=:mode_paiement, 
                    statut=:statut, facture_numero=:facture_numero, notes=:notes";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":patient_id", $this->patient_id);
        $stmt->bindParam(":date_paiement", $this->date_paiement);
        $stmt->bindParam(":montant", $this->montant);
        $stmt->bindParam(":mode_paiement", $this->mode_paiement);
        $stmt->bindParam(":statut", $this->statut);
        $stmt->bindParam(":facture_numero", $this->facture_numero);
        $stmt->bindParam(":notes", $this->notes);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read() {
        $query = "SELECT p.*, pt.nom as patient_nom, pt.prenom as patient_prenom 
                 FROM " . $this->table . " p 
                 LEFT JOIN patients pt ON p.patient_id = pt.id 
                 ORDER BY p.date_paiement DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function update() {
        $query = "UPDATE " . $this->table . " 
                SET patient_id=:patient_id, date_paiement=:date_paiement, 
                    montant=:montant, mode_paiement=:mode_paiement, 
                    statut=:statut, facture_numero=:facture_numero, notes=:notes
                WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":patient_id", $this->patient_id);
        $stmt->bindParam(":date_paiement", $this->date_paiement);
        $stmt->bindParam(":montant", $this->montant);
        $stmt->bindParam(":mode_paiement", $this->mode_paiement);
        $stmt->bindParam(":statut", $this->statut);
        $stmt->bindParam(":facture_numero", $this->facture_numero);
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
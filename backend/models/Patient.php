<?php
class Patient {
    private $conn;
    private $table = "patients";

    public $id;
    public $nom;
    public $prenom;
    public $date_naissance;
    public $sexe;
    public $adresse;
    public $telephone;
    public $email;
    public $assurance_compagnie;
    public $assurance_numero;
    public $assurance_type;
    public $date_creation;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                SET nom=:nom, prenom=:prenom, date_naissance=:date_naissance, 
                    sexe=:sexe, adresse=:adresse, telephone=:telephone, email=:email,
                    assurance_compagnie=:assurance_compagnie, 
                    assurance_numero=:assurance_numero, assurance_type=:assurance_type";

        $stmt = $this->conn->prepare($query);

        $this->nom = htmlspecialchars(strip_tags($this->nom));
        $this->prenom = htmlspecialchars(strip_tags($this->prenom));

        $stmt->bindParam(":nom", $this->nom);
        $stmt->bindParam(":prenom", $this->prenom);
        $stmt->bindParam(":date_naissance", $this->date_naissance);
        $stmt->bindParam(":sexe", $this->sexe);
        $stmt->bindParam(":adresse", $this->adresse);
        $stmt->bindParam(":telephone", $this->telephone);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":assurance_compagnie", $this->assurance_compagnie);
        $stmt->bindParam(":assurance_numero", $this->assurance_numero);
        $stmt->bindParam(":assurance_type", $this->assurance_type);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY date_creation DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->nom = $row['nom'];
            $this->prenom = $row['prenom'];
            $this->date_naissance = $row['date_naissance'];
            $this->sexe = $row['sexe'];
            $this->adresse = $row['adresse'];
            $this->telephone = $row['telephone'];
            $this->email = $row['email'];
            $this->assurance_compagnie = $row['assurance_compagnie'];
            $this->assurance_numero = $row['assurance_numero'];
            $this->assurance_type = $row['assurance_type'];
            $this->date_creation = $row['date_creation'];
            return $stmt;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table . " 
                SET nom=:nom, prenom=:prenom, date_naissance=:date_naissance, 
                    sexe=:sexe, adresse=:adresse, telephone=:telephone, email=:email,
                    assurance_compagnie=:assurance_compagnie, 
                    assurance_numero=:assurance_numero, assurance_type=:assurance_type
                WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":nom", $this->nom);
        $stmt->bindParam(":prenom", $this->prenom);
        $stmt->bindParam(":date_naissance", $this->date_naissance);
        $stmt->bindParam(":sexe", $this->sexe);
        $stmt->bindParam(":adresse", $this->adresse);
        $stmt->bindParam(":telephone", $this->telephone);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":assurance_compagnie", $this->assurance_compagnie);
        $stmt->bindParam(":assurance_numero", $this->assurance_numero);
        $stmt->bindParam(":assurance_type", $this->assurance_type);

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

    public function search($keywords) {
        $query = "SELECT * FROM " . $this->table . " 
                 WHERE nom LIKE ? OR prenom LIKE ? OR telephone LIKE ? 
                 ORDER BY date_creation DESC";

        $stmt = $this->conn->prepare($query);
        
        $keywords = "%{$keywords}%";
        $stmt->bindParam(1, $keywords);
        $stmt->bindParam(2, $keywords);
        $stmt->bindParam(3, $keywords);

        $stmt->execute();
        return $stmt;
    }
}
?>
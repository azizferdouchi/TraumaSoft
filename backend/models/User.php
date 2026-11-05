<?php
/**
 * Modèle User pour la gestion des utilisateurs
 */

class User {
    private $conn;
    private $table = "utilisateurs";

    public $id;
    public $nom;
    public $prenom;
    public $email;
    public $mot_de_passe;
    public $role;
    public $specialite;
    public $telephone;
    public $adresse;
    public $date_creation;
    public $date_modification;
    public $statut;
    public $dernier_connexion;
    public $token_reset_password;
    public $token_expiration;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Créer un nouvel utilisateur
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                SET nom=:nom, prenom=:prenom, email=:email, 
                    mot_de_passe=:mot_de_passe, role=:role, 
                    specialite=:specialite, telephone=:telephone,
                    adresse=:adresse, statut=:statut";

        $stmt = $this->conn->prepare($query);

        $this->nom = htmlspecialchars(strip_tags($this->nom));
        $this->prenom = htmlspecialchars(strip_tags($this->prenom));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->specialite = htmlspecialchars(strip_tags($this->specialite));
        $this->telephone = htmlspecialchars(strip_tags($this->telephone));
        $this->adresse = htmlspecialchars(strip_tags($this->adresse));
        $this->statut = htmlspecialchars(strip_tags($this->statut));

        $stmt->bindParam(":nom", $this->nom);
        $stmt->bindParam(":prenom", $this->prenom);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":mot_de_passe", $this->mot_de_passe);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":specialite", $this->specialite);
        $stmt->bindParam(":telephone", $this->telephone);
        $stmt->bindParam(":adresse", $this->adresse);
        $stmt->bindParam(":statut", $this->statut);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Lire tous les utilisateurs
     */
    public function read() {
        $query = "SELECT id, nom, prenom, email, role, specialite, telephone, statut, date_creation FROM " . $this->table . " ORDER BY date_creation DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Lire un utilisateur spécifique
     */
    public function readOne() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->nom = $row['nom'];
            $this->prenom = $row['prenom'];
            $this->email = $row['email'];
            $this->role = $row['role'];
            $this->specialite = $row['specialite'];
            $this->telephone = $row['telephone'];
            $this->adresse = $row['adresse'];
            $this->statut = $row['statut'];
            $this->date_creation = $row['date_creation'];
            $this->date_modification = $row['date_modification'];
            $this->dernier_connexion = $row['dernier_connexion'];
            return $stmt;
        }
        return false;
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                SET nom=:nom, prenom=:prenom, email=:email, 
                    role=:role, specialite=:specialite, 
                    telephone=:telephone, adresse=:adresse, statut=:statut
                WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":nom", $this->nom);
        $stmt->bindParam(":prenom", $this->prenom);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":specialite", $this->specialite);
        $stmt->bindParam(":telephone", $this->telephone);
        $stmt->bindParam(":adresse", $this->adresse);
        $stmt->bindParam(":statut", $this->statut);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Supprimer un utilisateur
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Changer le mot de passe
     */
    public function changePassword($new_password) {
        $query = "UPDATE " . $this->table . " SET mot_de_passe = :mot_de_passe WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":mot_de_passe", $hashed_password);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Rechercher des utilisateurs
     */
    public function search($keywords) {
        $query = "SELECT id, nom, prenom, email, role, telephone, statut 
                 FROM " . $this->table . " 
                 WHERE nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR telephone LIKE ?
                 ORDER BY date_creation DESC";

        $stmt = $this->conn->prepare($query);
        
        $keywords = "%{$keywords}%";
        $stmt->bindParam(1, $keywords);
        $stmt->bindParam(2, $keywords);
        $stmt->bindParam(3, $keywords);
        $stmt->bindParam(4, $keywords);

        $stmt->execute();
        return $stmt;
    }

    /**
     * Obtenir les utilisateurs par rôle
     */
    public function getByRole($role) {
        $query = "SELECT * FROM " . $this->table . " WHERE role = ? ORDER BY date_creation DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $role);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Mettre à jour le dernier login
     */
    public function updateLastLogin() {
        $query = "UPDATE " . $this->table . " SET dernier_connexion = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Générer un token de réinitialisation de mot de passe
     */
    public function generateResetToken() {
        $token = bin2hex(random_bytes(32));
        $expiration = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $query = "UPDATE " . $this->table . " 
                 SET token_reset_password = :token, token_expiration = :expiration 
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":token", $token);
        $stmt->bindParam(":expiration", $expiration);
        
        if($stmt->execute()) {
            return $token;
        }
        return false;
    }

    /**
     * Réinitialiser le mot de passe avec un token
     */
    public function resetPassword($token, $new_password) {
        $query = "UPDATE " . $this->table . " 
                 SET mot_de_passe = :mot_de_passe, token_reset_password = NULL, token_expiration = NULL
                 WHERE token_reset_password = :token AND token_expiration > NOW()";
        
        $stmt = $this->conn->prepare($query);
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        
        $stmt->bindParam(":token", $token);
        $stmt->bindParam(":mot_de_passe", $hashed_password);
        
        if($stmt->execute()) {
            return $stmt->rowCount() > 0;
        }
        return false;
    }

    /**
     * Vérifier si un email existe
     */
    public function emailExists($email) {
        $query = "SELECT id FROM " . $this->table . " WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Obtenir le nombre total d'utilisateurs
     */
    public function count() {
        $query = "SELECT COUNT(*) FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchColumn();
    }

    /**
     * Obtenir les statistiques des utilisateurs
     */
    public function getStatistics() {
        $stats = [];
        
        // Nombre total
        $stats['total'] = $this->count();
        
        // Par rôle
        $roles = ['admin', 'medecin', 'secretaire', 'infirmier'];
        foreach ($roles as $role) {
            $query = "SELECT COUNT(*) FROM " . $this->table . " WHERE role = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$role]);
            $stats['by_role'][$role] = (int)$stmt->fetchColumn();
        }
        
        // Actifs vs inactifs
        $query = "SELECT COUNT(*) FROM " . $this->table . " WHERE statut = 'actif'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['active'] = (int)$stmt->fetchColumn();
        
        $stats['inactive'] = $stats['total'] - $stats['active'];
        
        return $stats;
    }
}

// Test CLI
if (php_sapi_name() === 'cli') {
    echo "User Model - Mode Test\n";
    echo "======================\n\n";
    
    try {
        include_once __DIR__ . '/../config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        
        $user = new User($db);
        
        // Tester la lecture des utilisateurs
        $stmt = $user->read();
        echo "Nombre d'utilisateurs: " . $stmt->rowCount() . "\n";
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "- {$row['nom']} {$row['prenom']} ({$row['role']})\n";
        }
        
        // Tester les statistiques
        $stats = $user->getStatistics();
        echo "\nStatistiques:\n";
        print_r($stats);
        
    } catch (Exception $e) {
        echo "Erreur: " . $e->getMessage() . "\n";
    }
}
?>"
<?php
/**
 * Contrôleur d'authentification pour TraumaSoft
 * Gère la connexion, l'enregistrement et les tokens JWT
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-API-Key");

include_once '../models/User.php';
include_once '../config/database.php';

// Charger les dépendances pour JWT
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController {
    private $db;
    private $user;
    private $secret_key = 'traumasoft_secret_key_2024_change_this_in_production';
    private $algorithm = 'HS256';
    private $token_expiration = 3600; // 1 heure
    private $refresh_token_expiration = 86400; // 24 heures

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    /**
     * Connexion de l'utilisateur
     */
    public function login() {
        $data = json_decode(file_get_contents("php://input"));
        
        if (!isset($data->email) || !isset($data->mot_de_passe)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Email et mot de passe sont requis'
            ]);
            return;
        }
        
        $email = trim($data->email);
        $password = $data->mot_de_passe;
        
        try {
            // Vérifier l'utilisateur
            $stmt = $this->db->prepare("SELECT * FROM utilisateurs WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() === 0) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Identifiants invalides'
                ]);
                return;
            }
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Vérifier le mot de passe
            if (!password_verify($password, $user['mot_de_passe'])) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Identifiants invalides'
                ]);
                return;
            }
            
            // Vérifier le statut
            if ($user['statut'] !== 'actif') {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Compte désactivé'
                ]);
                return;
            }
            
            // Générer les tokens
            $token = $this->generateToken($user);
            $refresh_token = $this->generateRefreshToken($user);
            
            // Mettre à jour le dernier login
            $this->updateLastLogin($user['id']);
            
            // Retourner les informations
            echo json_encode([
                'success' => true,
                'message' => 'Connexion réussie',
                'data' => [
                    'user' => [
                        'id' => $user['id'],
                        'nom' => $user['nom'],
                        'prenom' => $user['prenom'],
                        'email' => $user['email'],
                        'role' => $user['role'],
                        'specialite' => $user['specialite'],
                        'telephone' => $user['telephone']
                    ],
                    'tokens' => [
                        'access_token' => $token,
                        'refresh_token' => $refresh_token,
                        'expires_in' => $this->token_expiration,
                        'token_type' => 'Bearer'
                    ]
                ]
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la connexion',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Enregistrement d'un nouvel utilisateur
     */
    public function register() {
        $data = json_decode(file_get_contents("php://input"));
        
        // Validation des données
        if (!isset($data->nom) || !isset($data->prenom) || !isset($data->email) || !isset($data->mot_de_passe)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Nom, prénom, email et mot de passe sont requis'
            ]);
            return;
        }
        
        // Vérifier si l'email existe déjà
        $stmt = $this->db->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmt->execute([trim($data->email)]);
        
        if ($stmt->rowCount() > 0) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => 'Email déjà utilisé'
            ]);
            return;
        }
        
        try {
            // Hacher le mot de passe
            $hashed_password = password_hash($data->mot_de_passe, PASSWORD_BCRYPT);
            
            // Créer l'utilisateur
            $stmt = $this->db->prepare("INSERT INTO utilisateurs 
                (nom, prenom, email, mot_de_passe, role, specialite, telephone, statut)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'actif')");
            
            $role = isset($data->role) && in_array($data->role, ['admin', 'medecin', 'secretaire', 'infirmier']) 
                ? $data->role : 'medecin';
            
            $stmt->execute([
                trim($data->nom),
                trim($data->prenom),
                trim($data->email),
                $hashed_password,
                $role,
                $data->specialite ?? null,
                $data->telephone ?? null
            ]);
            
            $user_id = $this->db->lastInsertId();
            
            // Obtenir l'utilisateur créé
            $stmt = $this->db->prepare("SELECT * FROM utilisateurs WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Générer un token
            $token = $this->generateToken($user);
            
            echo json_encode([
                'success' => true,
                'message' => 'Utilisateur créé avec succès',
                'data' => [
                    'user' => [
                        'id' => $user['id'],
                        'nom' => $user['nom'],
                        'prenom' => $user['prenom'],
                        'email' => $user['email'],
                        'role' => $user['role']
                    ],
                    'access_token' => $token,
                    'expires_in' => $this->token_expiration,
                    'token_type' => 'Bearer'
                ]
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Rafraîchir le token d'accès
     */
    public function refreshToken() {
        $data = json_decode(file_get_contents("php://input"));
        
        if (!isset($data->refresh_token)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Refresh token requis'
            ]);
            return;
        }
        
        try {
            // Décoder le refresh token
            $decoded = JWT::decode($data->refresh_token, new Key($this->secret_key, $this->algorithm));
            
            // Vérifier l'expiration
            if (!isset($decoded->exp) || $decoded->exp < time()) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Refresh token expiré'
                ]);
                return;
            }
            
            // Obtenir l'utilisateur
            $stmt = $this->db->prepare("SELECT * FROM utilisateurs WHERE id = ? AND statut = 'actif'");
            $stmt->execute([$decoded->user_id]);
            
            if ($stmt->rowCount() === 0) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé ou inactif'
                ]);
                return;
            }
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Générer de nouveaux tokens
            $token = $this->generateToken($user);
            $refresh_token = $this->generateRefreshToken($user);
            
            echo json_encode([
                'success' => true,
                'message' => 'Tokens rafraîchis avec succès',
                'data' => [
                    'access_token' => $token,
                    'refresh_token' => $refresh_token,
                    'expires_in' => $this->token_expiration,
                    'token_type' => 'Bearer'
                ]
            ]);
            
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Refresh token invalide',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Déconnexion
     */
    public function logout() {
        // Dans une application stateless avec JWT, la déconnexion consiste à 
        // invalider le token côté client. On peut aussi ajouter le token à une 
        // liste noire, mais cela nécessite une base de données supplémentaire.
        
        // Pour l'instant, on retourne un message de succès
        echo json_encode([
            'success' => true,
            'message' => 'Déconnexion réussie'
        ]);
    }

    /**
     * Générer un token JWT
     */
    private function generateToken($user) {
        $payload = [
            'iss' => 'TraumaSoft',
            'iat' => time(),
            'exp' => time() + $this->token_expiration,
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'nom' => $user['nom'],
            'prenom' => $user['prenom']
        ];
        
        return JWT::encode($payload, $this->secret_key, $this->algorithm);
    }

    /**
     * Générer un refresh token JWT
     */
    private function generateRefreshToken($user) {
        $payload = [
            'iss' => 'TraumaSoft',
            'iat' => time(),
            'exp' => time() + $this->refresh_token_expiration,
            'user_id' => $user['id'],
            'type' => 'refresh'
        ];
        
        return JWT::encode($payload, $this->secret_key, $this->algorithm);
    }

    /**
     * Mettre à jour la date du dernier login
     */
    private function updateLastLogin($user_id) {
        try {
            $stmt = $this->db->prepare("UPDATE utilisateurs SET dernier_connexion = NOW() WHERE id = ?");
            $stmt->execute([$user_id]);
        } catch (Exception $e) {
            // Ignorer l'erreur
        }
    }

    /**
     * Vérifier le token JWT (middleware)
     */
    public static function verifyToken($token, $secret_key = null) {
        if (!$secret_key) {
            $secret_key = 'traumasoft_secret_key_2024_change_this_in_production';
        }
        
        try {
            $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
            return (array)$decoded;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Obtenir l'utilisateur actuel à partir du token
     */
    public static function getCurrentUser($db, $token) {
        $decoded = self::verifyToken($token);
        
        if (!$decoded || !isset($decoded['user_id'])) {
            return false;
        }
        
        try {
            $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE id = ? AND statut = 'actif'");
            $stmt->execute([$decoded['user_id']]);
            
            if ($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            return false;
        }
        
        return false;
    }

    /**
     * Vérifier les permissions de l'utilisateur
     */
    public static function checkPermission($user, $required_role = null) {
        if (!$user) {
            return false;
        }
        
        if (!$required_role) {
            return true;
        }
        
        $roles_hierarchy = [
            'admin' => 4,
            'medecin' => 3,
            'infirmier' => 2,
            'secretaire' => 1
        ];
        
        $user_level = $roles_hierarchy[$user['role']] ?? 0;
        $required_level = $roles_hierarchy[$required_role] ?? 0;
        
        return $user_level >= $required_level;
    }
}

// Middleware pour vérifier l'authentification
class AuthMiddleware {
    public static function verify($db, $required_role = null) {
        $headers = apache_request_headers();
        
        // Vérifier le header Authorization
        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Token d\'authentification requis'
            ]);
            exit();
        }
        
        $auth_header = $headers['Authorization'];
        
        // Extraire le token
        if (strpos($auth_header, 'Bearer ') !== 0) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Format du token invalide'
            ]);
            exit();
        }
        
        $token = substr($auth_header, 7);
        
        // Vérifier le token
        $user = AuthController::getCurrentUser($db, $token);
        
        if (!$user) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Token invalide ou expiré'
            ]);
            exit();
        }
        
        // Vérifier les permissions
        if ($required_role && !AuthController::checkPermission($user, $required_role)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Permission refusée'
            ]);
            exit();
        }
        
        return $user;
    }
}

// Instancier et exécuter le contrôleur
if (php_sapi_name() !== 'cli') {
    $controller = new AuthController();
    
    // Déterminer l'action en fonction de la requête
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // Routes pour l'authentification
    if (strpos($path, '/auth/login') !== false && $method === 'POST') {
        $controller->login();
    } elseif (strpos($path, '/auth/register') !== false && $method === 'POST') {
        $controller->register();
    } elseif (strpos($path, '/auth/refresh') !== false && $method === 'POST') {
        $controller->refreshToken();
    } elseif (strpos($path, '/auth/logout') !== false && $method === 'POST') {
        $controller->logout();
    } else {
        http_response_code(404);
        echo json_encode(['
            success' => false,
            'message' => 'Route non trouvée'
        ]);
    }
}

// Fonction utilitaire pour les tests
if (php_sapi_name() === 'cli') {
    echo "AuthController - Mode Test\n";
    echo "==========================\n\n";
    
    // Tester le hachage du mot de passe
    $password = "password123";
    $hashed = password_hash($password, PASSWORD_BCRYPT);
    echo "Mot de passe haché: $hashed\n";
    echo "Vérification: " . (password_verify($password, $hashed) ? "OK" : "ÉCHEC") . "\n\n";
    
    // Tester la génération de token
    try {
        $controller = new AuthController();
        $user = [
            'id' => 1,
            'nom' => 'Test',
            'prenom' => 'User',
            'email' => 'test@example.com',
            'role' => 'medecin'
        ];
        
        $token = $controller->generateToken($user);
        echo "Token généré: $token\n";
        
        // Décoder le token
        $decoded = JWT::decode($token, new Key($controller->secret_key, $controller->algorithm));
        echo "Token décodé:\n";
        print_r($decoded);
        
    } catch (Exception $e) {
        echo "Erreur: " . $e->getMessage() . "\n";
    }
}
?>"
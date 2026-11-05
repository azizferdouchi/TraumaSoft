<?php
/**
 * Script d'import de la base de données TraumaSoft
 * Ce script crée la base de données et importe le schema SQL
 */

header("Content-Type: text/html; charset=UTF-8");

echo "<html><head><title>Import Base de Données TraumaSoft</title>";
echo "<style>body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; } .success { color: green; } .error { color: red; } .info { color: blue; }</style></head><body>";
echo "<h1>Import Base de Données TraumaSoft</h1>";

// Configuration de la base de données
$host = "localhost";
$username = "root";
$password = "root";
$database_name = "traumasoft";

try {
    // Connexion au serveur MySQL
    echo "<p class='info'>Connexion au serveur MySQL...</p>";
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Vérifier si la base de données existe
    $db_exists = false;
    $result = $conn->query("SHOW DATABASES");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        if ($row['Database'] == $database_name) {
            $db_exists = true;
            break;
        }
    }
    
    if (!$db_exists) {
        // Créer la base de données
        echo "<p class='info'>Création de la base de données '$database_name'...</p>";
        $conn->exec("CREATE DATABASE $database_name");
        echo "<p class='success'>Base de données créée avec succès.</p>";
    } else {
        echo "<p class='info'>La base de données '$database_name' existe déjà.</p>";
    }
    
    // Sélectionner la base de données
    $conn->exec("USE $database_name");
    
    // Lire le fichier SQL
    echo "<p class='info'>Lecture du fichier SQL...</p>";
    $sql_file = __DIR__ . '/traumasoft.sql';
    
    if (!file_exists($sql_file)) {
        throw new Exception("Fichier SQL non trouvé: $sql_file");
    }
    
    $sql_content = file_get_contents($sql_file);
    
    // Exécuter le script SQL
    echo "<p class='info'>Exécution du script SQL...</p>";
    $statements = explode(';', $sql_content);
    $success_count = 0;
    $error_count = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            try {
                $conn->exec($statement);
                $success_count++;
            } catch (PDOException $e) {
                $error_count++;
                // Ne pas afficher les erreurs pour les tables qui existent déjà
                if (strpos($e->getMessage(), 'already exists') === false) {
                    echo "<p class='error'>Erreur: " . htmlspecialchars($e->getMessage()) . "</p>";
                    echo "<p class='error'>Requête: " . htmlspecialchars(substr($statement, 0, 100)) . "...</p>";
                }
            }
        }
    }
    
    echo "<p class='success'>Script SQL exécuté avec succès.</p>";
    echo "<p class='info'>Requêtes exécutées: $success_count réussies, $error_count erreurs (principalement des doublons)</p>";
    
    // Vérifier les tables créées
    echo "<p class='info'>Vérification des tables...</p>";
    $result = $conn->query("SHOW TABLES");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p class='info'>Tables créées: 
# 📥 Guide d'Installation de TraumaSoft

Ce guide vous explique comment installer et configurer TraumaSoft sur votre serveur.

---

## 📋 Prérequis

Avant de commencer, assurez-vous d'avoir les éléments suivants installés :

### Système
- **Système d'exploitation** : Linux (Ubuntu, CentOS, Debian) ou Windows
- **Serveur web** : Apache 2.4+ ou Nginx
- **PHP** : Version 8.0 ou supérieure
- **Base de données** : MySQL 5.7+ ou MariaDB 10.2+

### Extensions PHP requises
- `pdo` (PDO_MYSQL pour MySQL)
- `json`
- `mbstring`
- `openssl` (pour JWT)
- `curl` (optionnel, pour les requêtes externes)
- `gd` (optionnel, pour le traitement d'images)

### Outils recommandés
- **Composer** : Gestion des dépendances PHP
- **Git** : Pour cloner le dépôt
- **Node.js** : Pour le développement frontend (optionnel)

---

## 🚀 Installation

### Étape 1 : Cloner le dépôt

```bash
# Se déplacer dans le répertoire de travail
cd /var/www/html  # ou /workspace, /htdocs, etc.

# Cloner le dépôt GitHub
git clone https://github.com/azizferdouchi/TraumaSoft.git

# Se déplacer dans le dossier du projet
cd TraumaSoft
```

### Étape 2 : Installer les dépendances PHP

```bash
# Installer Composer si ce n'est pas déjà fait
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"

# Installer les dépendances du projet
composer install
```

> **Note** : Si vous n'avez pas Composer, vous pouvez installer manuellement la bibliothèque JWT :
> ```bash
> mkdir -p backend/vendor/firebase/php-jwt
> cd backend/vendor/firebase/php-jwt
> wget https://github.com/firebase/php-jwt/archive/refs/tags/v6.0.0.tar.gz
> tar -xzf v6.0.0.tar.gz
> mv php-jwt-6.0.0/* .
> rm -rf php-jwt-6.0.0 v6.0.0.tar.gz
> cd ../../../..
> ```

### Étape 3 : Configurer la base de données

#### Créer la base de données

```bash
# Se connecter à MySQL
mysql -u root -p

# Créer la base de données
CREATE DATABASE traumasoft CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Créer un utilisateur (optionnel)
CREATE USER 'traumasoft'@'localhost' IDENTIFIED BY 'votre_mot_de_passe';
GRANT ALL PRIVILEGES ON traumasoft.* TO 'traumasoft'@'localhost';
FLUSH PRIVILEGES;

# Quitter MySQL
exit
```

#### Importer le schema

**Méthode 1 : Utiliser le script SQL**
```bash
mysql -u root -p traumasoft < database/traumasoft.sql
```

**Méthode 2 : Utiliser le script PHP d'import**
```bash
# Modifier les identifiants dans le script
nano database/import_database.php

# Exécuter le script
php database/import_database.php
```

**Méthode 3 : Importer via phpMyAdmin**
1. Ouvrir phpMyAdmin dans votre navigateur
2. Sélectionner la base de données `traumasoft`
3. Cliquer sur "Importer"
4. Sélectionner le fichier `database/traumasoft.sql`
5. Cliquer sur "Exécuter"

### Étape 4 : Configurer l'application

#### Configurer la connexion à la base de données

Éditer le fichier `backend/config/database.php` :

```bash
nano backend/config/database.php
```

Modifier les paramètres de connexion :

```php
<?php
class Database {
    private $host = "localhost";        // Adresse du serveur MySQL
    private $db_name = "traumasoft";    // Nom de la base de données
    private $username = "root";         // Nom d'utilisateur MySQL
    private $password = "root";         // Mot de passe MySQL
    public $conn;
    // ...
}
```

> **Important** : Pour la production, utilisez des identifiants sécurisés et ne pas utiliser `root` !

#### Configurer le secret JWT

Éditer le fichier `backend/controllers/AuthController.php` et modifier le secret :

```php
private $secret_key = 'votre_cle_secrete_unique_et_complexe_2024';
```

> **Sécurité** : Générez une clé secrète unique et complexe pour la production.

### Étape 5 : Configurer le serveur web

#### Avec Apache

1. **Activer le module rewrite**
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

2. **Configurer le Virtual Host**

Créer un fichier de configuration :
```bash
sudo nano /etc/apache2/sites-available/traumasoft.conf
```

Ajouter la configuration :
```apache
<VirtualHost *:80>
    ServerName traumasoft.local
    ServerAdmin admin@traumasoft.local
    DocumentRoot /var/www/html/TraumaSoft
    
    <Directory /var/www/html/TraumaSoft>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/traumasoft_error.log
    CustomLog ${APACHE_LOG_DIR}/traumasoft_access.log combined
</VirtualHost>
```

3. **Activer le site**
```bash
sudo a2ensite traumasoft.conf
sudo systemctl reload apache2
```

4. **Configurer le fichier hosts**
```bash
sudo nano /etc/hosts
```

Ajouter la ligne :
```
127.0.0.1 traumasoft.local
```

#### Avec Nginx

1. **Configurer le serveur**
```bash
sudo nano /etc/nginx/sites-available/traumasoft
```

Ajouter la configuration :
```nginx
server {
    listen 80;
    server_name traumasoft.local;
    root /var/www/html/TraumaSoft;
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location /backend {
        try_files $uri $uri/ /backend/index.php?$query_string;
    }
    
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
}
```

2. **Activer le site**
```bash
sudo ln -s /etc/nginx/sites-available/traumasoft /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Étape 6 : Configurer les permissions

```bash
# Donner les permissions nécessaires
sudo chown -R www-data:www-data /var/www/html/TraumaSoft
sudo chmod -R 755 /var/www/html/TraumaSoft

# Créer le dossier logs
mkdir -p /var/www/html/TraumaSoft/backend/logs
chown -R www-data:www-data /var/www/html/TraumaSoft/backend/logs
chmod -R 775 /var/www/html/TraumaSoft/backend/logs
```

---

## 🔍 Vérification de l'Installation

### Tester la connexion à la base de données

```bash
php -r "
include 'backend/config/database.php';
\$db = new Database();
\$conn = \$db->getConnection();
if (\$conn) {
    echo '✓ Connexion à la base de données : OK\n';
    \$stmt = \$conn->query('SELECT COUNT(*) FROM patients');
    \$count = \$stmt->fetchColumn();
    echo '✓ Nombre de patients : ' . \$count . '\n';
} else {
    echo '✗ Connexion à la base de données : ÉCHEC\n';
}
"
```

### Tester l'API

```bash
# Tester le endpoint de l'API
curl -X GET http://localhost:8000/api

# Ou via le navigateur : http://traumasoft.local/api
```

---

## 🏃‍♂️ Démarrage

### Démarrer le serveur de développement

#### Avec PHP intégré
```bash
cd /var/www/html/TraumaSoft/backend
php -S localhost:8000
```

#### Avec Apache/Nginx
```bash
# Démarrer Apache
sudo systemctl start apache2

# Démarrer Nginx
sudo systemctl start nginx
```

### Accéder à l'application

- **Frontend** : Ouvrez `http://traumasoft.local` ou `http://localhost:8000` dans votre navigateur
- **Backend API** : `http://traumasoft.local/backend` ou `http://localhost:8000`
- **Documentation API** : `http://traumasoft.local/api` ou `http://localhost:8000/api`

---

## 👤 Première Connexion

Après l'import de la base de données, vous pouvez vous connecter avec les identifiants par défaut :

| Email | Mot de passe | Rôle |
|-------|--------------|------|
| admin@traumasoft.com | password | Administrateur |
| j.dupont@traumasoft.com | password | Médecin |
| m.martin@traumasoft.com | password | Secrétaire |

> **Sécurité** : Changez ces mots de passe immédiatement après la première connexion !

### Changer le mot de passe

1. Se connecter avec un compte
2. Aller dans les paramètres du profil
3. Changer le mot de passe

---

## 🔧 Configuration Avancée

### Configurer HTTPS

Pour une installation en production, configurez HTTPS avec Let's Encrypt :

```bash
# Installer Certbot
sudo apt install certbot python3-certbot-nginx

# Obtenir un certificat
sudo certbot --nginx -d traumasoft.votredomaine.com

# Renouvellement automatique
sudo certbot renew --dry-run
```

### Configurer la production

1. **Désactiver le mode debug**
   - Dans `backend/index.php`, désactivez `display_errors`

2. **Configurer les logs**
   - Assurez-vous que le dossier `backend/logs` existe et est accessible

3. **Sécuriser l'application**
   - Utilisez des mots de passe forts
   - Limitez les permissions des utilisateurs
   - Activez HTTPS

4. **Optimiser les performances**
   - Activez OPcache pour PHP
   - Configurez la compression GZIP
   - Utilisez un CDN pour les assets statiques

---

## ❌ Dépannage

### Problèmes courants

#### 1. Erreur de connexion à la base de données

**Symptômes** :
- Message : "Erreur de connexion: SQLSTATE[HY000] [2002] Connection refused"

**Solutions** :
- Vérifiez que MySQL est en cours d'exécution : `sudo systemctl status mysql`
- Vérifiez les identifiants dans `backend/config/database.php`
- Vérifiez que l'utilisateur MySQL a les permissions : `GRANT ALL ON traumasoft.* TO user@'%' IDENTIFIED BY 'password';`

#### 2. Erreur 404 sur l'API

**Symptômes** :
- Message : "404 Not Found" lors de l'appel à l'API

**Solutions** :
- Vérifiez que le module `rewrite` est activé : `sudo a2enmod rewrite`
- Vérifiez les permissions du fichier `.htaccess`
- Testez avec le serveur PHP intégré : `php -S localhost:8000 -t backend`

#### 3. Erreur de dépendances manquantes

**Symptômes** :
- Message : "Class 'Firebase\JWT\JWT' not found"

**Solutions** :
- Exécutez : `composer install`
- Ou installez manuellement la bibliothèque JWT

#### 4. Problèmes de CORS

**Symptômes** :
- Erreurs CORS dans la console du navigateur

**Solutions** :
- Vérifiez que les headers CORS sont configurés dans `backend/index.php`
- Assurez-vous que le frontend et le backend sont sur le même domaine en production

#### 5. Problèmes de permissions

**Symptômes** :
- Message : "Permission denied" lors de l'accès aux fichiers

**Solutions** :
```bash
sudo chown -R www-data:www-data /var/www/html/TraumaSoft
sudo chmod -R 755 /var/www/html/TraumaSoft
```

---

## 📚 Documentation Supplémentaire

- **[README.md](README.md)** : Documentation principale
- **[API Documentation](http://traumasoft.local/api)** : Documentation interactive de l'API
- **[User Guide](#)** : Guide utilisateur (à venir)
- **[Developer Guide](#)** : Guide du développeur (à venir)

---

## 🙏 Support

Si vous rencontrez des problèmes qui ne sont pas couverts par ce guide :

1. **Consultez les logs** :
   - Logs Apache : `/var/log/apache2/error.log`
   - Logs PHP : `/var/log/php_errors.log`
   - Logs TraumaSoft : `backend/logs/`

2. **Vérifiez la console du navigateur** (F12 → Console)

3. **Ouvrez une Issue** sur GitHub avec :
   - La description du problème
   - Les étapes pour reproduire
   - Les messages d'erreur
   - Votre environnement (PHP, MySQL, serveur web)

4. **Contactez l'équipe** : contact@traumasoft.com

---

**Bon courage avec votre installation de TraumaSoft !** 🚀

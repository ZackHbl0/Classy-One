# Guide de Durcissement de la Base de Données et des Secrets — Classy-One

Ce document présente la stratégie formelle de durcissement (Hardening) de la plateforme **Classy-One** pour le déploiement en environnement de production, conformément au principe du moindre privilège (*Principle of Least Privilege*) et aux normes de sécurité OWASP / ISO 27001.

---

## 1. Séparation Stricte des Utilisateurs MySQL (Moindre Privilège)

En environnement de production, **l'application Laravel ne doit JAMAIS se connecter avec le compte `root`**.
Nous appliquons une séparation étanche entre trois rôles de base de données :

```
                  ┌─────────────────────────────────────────┐
                  │          Instance MySQL 8.x             │
                  └───────────────────┬─────────────────────┘
                                      │
        ┌─────────────────────────────┼─────────────────────────────┐
        ▼                             ▼                             ▼
┌──────────────┐              ┌──────────────┐              ┌──────────────┐
│  classy_app  │              │ classy_deploy│              │classy_reports│
│ (Application)│              │  (Migrations)│              │ (Read-Only)  │
└──────────────┘              └──────────────┘              └──────────────┘
 DML Seulement:                 DDL Complet:                  SELECT Uniquement:
 SELECT, INSERT,               CREATE, ALTER,               Audits financiers,
 UPDATE, DELETE,               INDEX, DROP (sur             BI & Tableaux de bord
 EXECUTE                       staging/deploy)              analytiques
```

### 1.1. Compte d'Exécution Quotidienne : `classy_app`
Ce compte est utilisé par PHP-FPM / Laravel pour exécuter les requêtes quotidiennes (CRUD). Il ne peut ni altérer le schéma, ni supprimer des tables, ni créer de nouveaux utilisateurs.

```sql
-- 1. Création de l'utilisateur avec authentification native sécurisée
CREATE USER 'classy_app'@'localhost' IDENTIFIED WITH caching_sha2_password BY 'VOTRE_MOT_DE_PASSE_ULTRA_SECURISE_APP_32CHARS';

-- 2. Attribution exclusive des privilèges DML (Data Manipulation Language)
GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE ON classy_one.* TO 'classy_app'@'localhost';

-- 3. Révocation explicite des privilèges destructeurs ou administratifs
REVOKE DROP, ALTER, CREATE, GRANT OPTION, SUPER, FILE, RELOAD, SHUTDOWN ON *.* FROM 'classy_app'@'localhost';

FLUSH PRIVILEGES;
```

### 1.2. Compte de Déploiement & Migrations : `classy_deployer`
Ce compte est utilisé **uniquement** lors des pipelines CI/CD ou des maintenances système par la commande `php artisan migrate`.

```sql
CREATE USER 'classy_deployer'@'localhost' IDENTIFIED WITH caching_sha2_password BY 'VOTRE_MOT_DE_PASSE_DEPLOY_48CHARS';

-- Privilèges DDL nécessaires pour créer/modifier les tables, colonnes et index
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP, REFERENCES, CREATE TEMPORARY TABLES, LOCK TABLES 
ON classy_one.* TO 'classy_deployer'@'localhost';

FLUSH PRIVILEGES;
```

### 1.3. Compte Lecture Seule & Reporting : `classy_analytics`
Pour les audits comptables ou réplications vers un Data Warehouse :

```sql
CREATE USER 'classy_analytics'@'localhost' IDENTIFIED WITH caching_sha2_password BY 'VOTRE_MOT_DE_PASSE_READONLY';

GRANT SELECT ON classy_one.* TO 'classy_analytics'@'localhost';
FLUSH PRIVILEGES;
```

---

## 2. Configuration Sécurisée dans `.env`

La configuration de l'application dans `.env` utilise le compte `classy_app` :

```env
# Connexion Principale Laravel (Production)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=classy_one
DB_USERNAME=classy_app
DB_PASSWORD=VOTRE_MOT_DE_PASSE_ULTRA_SECURISE_APP_32CHARS

# Strict SSL / TLS pour la connexion MySQL
MYSQL_ATTR_SSL_CA=/etc/ssl/certs/mysql-ca.pem
```

---

## 3. Durcissement des Secrets & Clés API

### 3.1. Règle Zéro Hardcoding
Tous les secrets externes et clés d'accès doivent impérativement être résolus depuis `.env` sans valeur de secours (*fallback*) sensible en dur dans le code source :

| Service | Variable d'environnement requise | Fichier de configuration |
|---|---|---|
| Chiffrement Laravel | `APP_KEY` (base64 32 bytes) | `config/app.php` |
| Firebase Service Account | `FIREBASE_CREDENTIALS` | `config/firebase.php` |
| Firebase FCM Push | `FCM_SERVER_KEY` | `app/Services/FcmService.php` |
| Hash Chaining Audit | `APP_KEY` / `AUDIT_LOG_SECRET` | `app/Models/AuditLog.php` |

### 3.2. Fichier `.env.production` et Permissions Système
Sur le serveur Linux de production :
```bash
# Verrouiller l'accès au fichier .env
chmod 600 /var/www/classy-one/.env
chown www-data:www-data /var/www/classy-one/.env

# Empêcher l'accès Apache / Nginx à tout fichier sensible
# Vérifié par SecurityHeadersMiddleware et .htaccess
```

### 3.3. Mise en Cache Sécurisée des Configurations
En production, les variables d'environnement sont compilées via :
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
`env()` n'est jamais appelé directement dans les contrôleurs ou les services applicatifs, mais exclusivement à travers `config('...')`.

---

## 4. Audit des Failles Résolues

1. **Aucun mot de passe en clair** : Tous les mots de passe élèves, parents, enseignants et administrateurs sont hachés via `bcrypt` (`cost 12`).
2. **Journaux d'Audit Infalsifiables** : Les entrées `audit_logs` sont chaînées cryptographiquement par HMAC-SHA256 (`signature` et `previous_signature`). Toute tentative de modification directe ou suppression déclenche une exception `RuntimeException`.
3. **Double Authentification (2FA)** : Chiffrement symétrique AES-256-CBC de la clé secrète TOTP dans `users.two_factor_secret` via `Crypt::encryptString`.
4. **Validation Binaire des Téléversements** : Inspection du type MIME réel par inspection des octets magiques (`finfo`) et stockage dans le répertoire privé hors de la racine web (`storage/app/private_documents`).

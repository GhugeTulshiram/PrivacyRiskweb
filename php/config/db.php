<?php

// Configure writable session path for serverless (e.g. Vercel)
if (session_status() === PHP_SESSION_NONE) {
    if (getenv('VERCEL') || isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL'])) {
        @ini_set('session.save_path', '/tmp');
    }
}

// -----------------------------------------------------------------------------
// Database Configuration: Supabase (PostgreSQL) vs MySQL (Local/Cloud)
// -----------------------------------------------------------------------------

$database_url = getenv('DATABASE_URL') ?: (getenv('SUPABASE_DB_URL') ?: '');
$supabase_host = getenv('SUPABASE_HOST') ?: getenv('PGHOST');

// Determine if we should connect to Supabase (PostgreSQL)
$is_supabase = !empty($supabase_host) || (!empty($database_url) && (strpos($database_url, 'postgres') === 0 || strpos($database_url, 'postgresql') === 0));

if ($is_supabase) {
    // -------------------------------------------------------------------------
    // Supabase (PostgreSQL) Connection & MySQL-Compatibility Wrapper
    // -------------------------------------------------------------------------

    if (!empty($database_url)) {
        $parts = parse_url($database_url);
        $pg_host = $parts['host'] ?? 'localhost';
        $pg_port = $parts['port'] ?? 5432;
        $pg_user = $parts['user'] ?? 'postgres';
        $pg_pass = $parts['pass'] ?? '';
        $pg_db   = ltrim($parts['path'] ?? 'postgres', '/');
    } else {
        $pg_host = $supabase_host;
        $pg_port = getenv('SUPABASE_PORT') ?: (getenv('PGPORT') ?: 5432);
        $pg_user = getenv('SUPABASE_USER') ?: (getenv('PGUSER') ?: 'postgres');
        $pg_pass = getenv('SUPABASE_PASSWORD') ?: (getenv('PGPASSWORD') ?: '');
        $pg_db   = getenv('SUPABASE_DB') ?: (getenv('PGDATABASE') ?: 'postgres');
    }

    class SupabaseResult {
        private $rows;
        private $cursor = 0;
        public $num_rows = 0;

        public function __construct($stmt) {
            $this->rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $this->num_rows = count($this->rows);
            $this->cursor = 0;
        }

        public function fetch_assoc() {
            if ($this->cursor < $this->num_rows) {
                return $this->rows[$this->cursor++];
            }
            return null;
        }
    }

    class SupabaseStatement {
        private $stmt;
        private $params = [];
        public $error = "";

        public function __construct($stmt) {
            $this->stmt = $stmt;
        }

        public function bind_param($types, &...$args) {
            $this->params = &$args;
            return true;
        }

        public function execute($params = null) {
            try {
                $exec_params = ($params !== null) ? $params : $this->params;
                return $this->stmt->execute(array_values($exec_params));
            } catch (Exception $e) {
                $this->error = $e->getMessage();
                return false;
            }
        }

        public function get_result() {
            return new SupabaseResult($this->stmt);
        }

        public function close() {
            if ($this->stmt) {
                $this->stmt->closeCursor();
            }
            return true;
        }
    }

    class SupabaseDB {
        public $pdo;
        public $connect_error = null;
        public $error = "";

        public function __construct($host, $port, $dbname, $user, $password) {
            $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode=require";
            try {
                $this->pdo = new PDO($dsn, $user, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                $this->connect_error = $e->getMessage();
            }
        }

        public function prepare($query) {
            if (!$this->pdo) {
                return false;
            }

            // Compatibility: Convert MySQL ON DUPLICATE KEY UPDATE to PostgreSQL ON CONFLICT
            if (stripos($query, 'ON DUPLICATE KEY UPDATE') !== false) {
                $query = preg_replace(
                    '/ON\s+DUPLICATE\s+KEY\s+UPDATE\s+face_encoding\s*=\s*VALUES\(face_encoding\)/i',
                    'ON CONFLICT (user_id) DO UPDATE SET face_encoding = EXCLUDED.face_encoding',
                    $query
                );
            }

            try {
                $stmt = $this->pdo->prepare($query);
                return new SupabaseStatement($stmt);
            } catch (Exception $e) {
                $this->error = $e->getMessage();
                return false;
            }
        }

        public function set_charset($charset) {
            // PostgreSQL charset handled by connection
            return true;
        }
    }

    $conn = new SupabaseDB($pg_host, $pg_port, $pg_db, $pg_user, $pg_pass);

    if ($conn->connect_error) {
        die("Database Connection Failed: " . $conn->connect_error);
    }

} else {
    // -------------------------------------------------------------------------
    // MySQL Fallback (Local XAMPP or Remote MySQL)
    // -------------------------------------------------------------------------

    $host     = getenv("DB_HOST") ?: "localhost";
    $user     = getenv("DB_USER") ?: "root";
    $password = getenv("DB_PASS") ?: "";
    $database = getenv("DB_NAME") ?: "privacy_risk_assistant";
    $port     = (int)(getenv("DB_PORT") ?: 3306);

    $conn = @new mysqli($host, $user, $password, $database, $port);

    if ($conn->connect_error) {
        die("Database Connection Failed: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");
}
<?php

// -----------------------------------------------------------------------------
// Auto-load .env file if present in the project root
// -----------------------------------------------------------------------------
$env_file = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
if (file_exists($env_file) && is_readable($env_file)) {
    $env_lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($env_lines as $env_line) {
        $env_line = trim($env_line);
        if ($env_line === '' || strpos($env_line, '#') === 0) {
            continue;
        }
        if (strpos($env_line, '=') !== false) {
            list($env_k, $env_v) = explode('=', $env_line, 2);
            $env_k = trim($env_k);
            $env_v = trim($env_v, " \t\n\r\0\x0B\"'");
               $curr = getenv($env_k);
               if ($curr === false || $curr === '') {
                   putenv("{$env_k}={$env_v}");
                   $_ENV[$env_k] = $env_v;
                   $_SERVER[$env_k] = $env_v;
               }
        }
    }
}

// -----------------------------------------------------------------------------
// Environment Variable Helper (reads getenv, $_ENV, and $_SERVER)
// -----------------------------------------------------------------------------
if (!function_exists('get_config_env')) {
    function get_config_env($key, $default = '') {
        $val = getenv($key);
        if ($val !== false && $val !== '') {
            return $val;
        }
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return $_SERVER[$key];
        }
        return $default;
    }
}

// Check for Vercel or AWS Lambda serverless execution
$is_vercel = !empty(get_config_env('VERCEL')) 
    || !empty(get_config_env('AWS_LAMBDA_FUNCTION_NAME'))
    || isset($_ENV['VERCEL']) 
    || isset($_SERVER['VERCEL']);

// Configure writable session path for serverless (e.g. Vercel)
if (session_status() === PHP_SESSION_NONE) {
    if ($is_vercel) {
        @ini_set('session.save_path', '/tmp');
    }
}

// -----------------------------------------------------------------------------
// Database Configuration: Supabase (PostgreSQL) vs MySQL (Local/Cloud)
// -----------------------------------------------------------------------------

$database_url = get_config_env('DATABASE_URL')
    ?: get_config_env('POSTGRES_URL')
    ?: get_config_env('POSTGRES_URL_NON_POOLING')
    ?: get_config_env('POSTGRES_PRISMA_URL')
    ?: get_config_env('SUPABASE_DB_URL')
    ?: '';

$supabase_host = get_config_env('SUPABASE_HOST')
    ?: get_config_env('PGHOST')
    ?: get_config_env('POSTGRES_HOST')
    ?: '';

$supabase_url = get_config_env('SUPABASE_URL')
    ?: get_config_env('NEXT_PUBLIC_SUPABASE_URL')
    ?: 'https://abnkzobsndllckanbiiu.supabase.co';

if (empty($supabase_host) && !empty($supabase_url)) {
    $parsed_supa = parse_url($supabase_url);
    if (!empty($parsed_supa['host'])) {
        $supabase_host = preg_replace('/^([a-z0-9_-]+)\.supabase\.co$/i', 'db.$1.supabase.co', $parsed_supa['host']);
    }
}

// Determine if we should connect to Supabase (PostgreSQL)
$is_supabase = !empty($supabase_host) || (!empty($database_url) && (strpos($database_url, 'postgres') === 0 || strpos($database_url, 'postgresql') === 0));

if ($is_supabase) {
    // -------------------------------------------------------------------------
    // Supabase (PostgreSQL) Connection & MySQL-Compatibility Wrapper
    // -------------------------------------------------------------------------

    if (!empty($database_url)) {
        $parts   = parse_url($database_url);
        $pg_host = $parts['host'] ?? 'localhost';
        $pg_port = $parts['port'] ?? 5432;
        $pg_user = isset($parts['user']) ? urldecode($parts['user']) : 'postgres';
        $pg_pass = isset($parts['pass']) ? urldecode($parts['pass']) : '';
        $pg_db   = isset($parts['path']) ? urldecode(ltrim($parts['path'], '/')) : 'postgres';
    } else {
        $pg_host = $supabase_host ?: 'db.abnkzobsndllckanbiiu.supabase.co';
        $pg_port = get_config_env('SUPABASE_PORT') ?: (get_config_env('PGPORT') ?: (get_config_env('POSTGRES_PORT') ?: 5432));
        $pg_user = get_config_env('SUPABASE_USER') ?: (get_config_env('PGUSER') ?: (get_config_env('POSTGRES_USER') ?: 'postgres'));
        $pg_pass = get_config_env('SUPABASE_PASSWORD') ?: (get_config_env('PGPASSWORD') ?: (get_config_env('POSTGRES_PASSWORD') ?: '9307615742@Ram'));
        $pg_db   = get_config_env('SUPABASE_DB') ?: (get_config_env('PGDATABASE') ?: (get_config_env('POSTGRES_DATABASE') ?: 'postgres'));
    }

    // Guard: Supabase requires Database Password for PostgreSQL connections
    if (empty($pg_pass)) {
        http_response_code(500);
        die(
            '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Supabase Database Password Required</title>' .
            '<meta name="viewport" content="width=device-width, initial-scale=1.0">' .
            '<style>body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;background:#0f172a;color:#f8fafc;padding:30px 16px;margin:0;display:flex;align-items:center;justify-content:center;min-height:100vh;box-sizing:border-box;}' .
            '.card{background:#1e293b;border:1px solid #334155;border-radius:16px;max-width:620px;width:100%;padding:32px;box-shadow:0 10px 25px rgba(0,0,0,0.5);}' .
            'h2{color:#38bdf8;margin-top:0;font-size:22px;margin-bottom:12px;}' .
            'p{color:#cbd5e1;line-height:1.6;font-size:14px;}' .
            'ol{color:#cbd5e1;font-size:14px;line-height:1.8;padding-left:22px;margin:16px 0;}' .
            'code{background:#0f172a;border:1px solid #334155;color:#f472b6;padding:2px 6px;border-radius:6px;font-family:monospace;font-size:13px;}' .
            'a{color:#38bdf8;text-decoration:none;font-weight:600;}a:hover{text-decoration:underline;}' .
            '.tag{display:inline-block;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:600;background:#0369a1;color:#e0f2fe;margin-bottom:14px;}' .
            '</style></head><body>' .
            '<div class="card">' .
            '<span class="tag">Supabase Setup</span>' .
            '<h2>Database Password Required</h2>' .
            '<p>Your Supabase project URL (<code>' . htmlspecialchars($pg_host) . '</code>) is configured, but connecting to PostgreSQL requires your <strong>Database Password</strong>.</p>' .
            '<p style="color:#94a3b8;font-size:13px;"><em>Note: The publishable key (<code>sb_publishable_...</code>) is an API key for frontend clients, not the PostgreSQL database password.</em></p>' .
            '<ol>' .
            '<li>In your project <code>.env</code> file (or in Vercel Environment Variables), set:<br><code>SUPABASE_PASSWORD=your_database_password</code></li>' .
            '<li>Alternatively, copy your full connection URI from Supabase: <strong>Project Settings &rarr; Database &rarr; Connection string &rarr; URI</strong> and set it as <code>DATABASE_URL</code>.</li>' .
            '<li>If you don\'t remember your password, you can reset it in Supabase under: <strong>Project Settings &rarr; Database &rarr; Database password &rarr; Reset password</strong>.</li>' .
            '</ol>' .
            '</div></body></html>'
        );
    }

    class SupabaseResult {
        private $rows;
        private $cursor = 0;
        public $num_rows = 0;

        public function __construct($stmt) {
            try {
                $this->rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } catch (Exception $e) {
                $this->rows = [];
            }
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
                    PDO::ATTR_TIMEOUT => 10,
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
        http_response_code(500);
        die("Database Connection Failed (Supabase PostgreSQL): " . htmlspecialchars($conn->connect_error));
    }

} else {
    // -------------------------------------------------------------------------
    // MySQL Fallback (Local XAMPP or Remote MySQL)
    // -------------------------------------------------------------------------

    $db_host   = get_config_env("DB_HOST");
    $mysql_url = get_config_env("MYSQL_URL");

    if (empty($mysql_url) && !empty($database_url) && strpos($database_url, 'mysql://') === 0) {
        $mysql_url = $database_url;
    }

    // Guard: Running on Vercel without database configuration
    if ($is_vercel && empty($db_host) && empty($mysql_url)) {
        http_response_code(500);
        die(
            '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Database Configuration Required</title>' .
            '<meta name="viewport" content="width=device-width, initial-scale=1.0">' .
            '<style>body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;background:#0f172a;color:#f8fafc;padding:30px 16px;margin:0;display:flex;align-items:center;justify-content:center;min-height:100vh;box-sizing:border-box;}' .
            '.card{background:#1e293b;border:1px solid #334155;border-radius:16px;max-width:620px;width:100%;padding:32px;box-shadow:0 10px 25px rgba(0,0,0,0.5);}' .
            'h2{color:#38bdf8;margin-top:0;font-size:22px;margin-bottom:12px;}' .
            'p{color:#cbd5e1;line-height:1.6;font-size:14px;}' .
            'ol{color:#cbd5e1;font-size:14px;line-height:1.8;padding-left:22px;margin:16px 0;}' .
            'code{background:#0f172a;border:1px solid #334155;color:#f472b6;padding:2px 6px;border-radius:6px;font-family:monospace;font-size:13px;}' .
            'a{color:#38bdf8;text-decoration:none;font-weight:600;}a:hover{text-decoration:underline;}' .
            '.tag{display:inline-block;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:600;background:#0369a1;color:#e0f2fe;margin-bottom:14px;}' .
            '</style></head><body>' .
            '<div class="card">' .
            '<span class="tag">Vercel Deployment Notice</span>' .
            '<h2>Database Configuration Required</h2>' .
            '<p>Your application is running on <strong>Vercel</strong>, but no cloud database connection is configured yet.</p>' .
            '<p>Because Vercel serverless containers do not include a local MySQL server (<code>localhost:3306</code>), a cloud database like <strong>Supabase</strong> (PostgreSQL) is required.</p>' .
            '<ol>' .
            '<li>Create a free PostgreSQL project at <a href="https://supabase.com" target="_blank">supabase.com</a>.</li>' .
            '<li>Run the schema script from <code>database/supabase_schema.sql</code> in the Supabase <strong>SQL Editor</strong>.</li>' .
            '<li>In Supabase, go to <strong>Project Settings &rarr; Database &rarr; Connection string &rarr; URI</strong> and copy it.</li>' .
            '<li>In your <strong>Vercel Project &rarr; Settings &rarr; Environment Variables</strong>, add:' .
            '<br>Name: <code>DATABASE_URL</code>' .
            '<br>Value: <em>[Your Supabase URI with password]</em></li>' .
            '<li>Redeploy the application in Vercel.</li>' .
            '</ol>' .
            '</div></body></html>'
        );
    }

    if (!empty($mysql_url)) {
        $parts    = parse_url($mysql_url);
        $host     = $parts['host'] ?? 'localhost';
        $user     = isset($parts['user']) ? urldecode($parts['user']) : 'root';
        $password = isset($parts['pass']) ? urldecode($parts['pass']) : '';
        $database = isset($parts['path']) ? urldecode(ltrim($parts['path'], '/')) : 'privacy_risk_assistant';
        $port     = (int)($parts['port'] ?? 3306);
    } else {
        $host     = $db_host ?: "localhost";
        $user     = get_config_env("DB_USER", "root");
        $password = get_config_env("DB_PASS", "");
        $database = get_config_env("DB_NAME", "privacy_risk_assistant");
        $port     = (int)(get_config_env("DB_PORT", 3306));
    }

    // Disable default mysqli exceptions so we can catch and handle connection issues gracefully
    mysqli_report(MYSQLI_REPORT_OFF);

    try {
        $conn = @new mysqli($host, $user, $password, $database, $port);
        if ($conn->connect_error) {
            throw new Exception($conn->connect_error);
        }
        $conn->set_charset("utf8mb4");
    } catch (Throwable $e) {
        $error_msg = $e->getMessage();
        http_response_code(500);
        if ($is_vercel) {
            die("Database Connection Failed: " . htmlspecialchars($error_msg) . " (Host: {$host}:{$port})");
        } else {
            die("Database Connection Failed: " . htmlspecialchars($error_msg) . ". If testing locally, make sure MySQL is started in XAMPP.");
        }
    }
}
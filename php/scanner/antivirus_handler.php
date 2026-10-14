<?php

session_start();

require_once __DIR__ . "/../config/db.php";

header('Content-Type: application/json');

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized. Please log in."]);
    exit;
}

$user_id = (int)$_SESSION["user_id"];
$action = $_POST["action"] ?? ($_GET["action"] ?? "");

// -----------------------------------------------------------------------------
// Action: Scan Uploaded File for Malware & Signatures
// -----------------------------------------------------------------------------
if ($action === "scan_file") {
    if (!isset($_FILES["file"]) || $_FILES["file"]["error"] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "No file uploaded or upload error occurred."]);
        exit;
    }

    $uploaded_file = $_FILES["file"];
    $filename = basename($uploaded_file["name"]);
    $filesize = (int)$uploaded_file["size"];
    $tmp_path = $uploaded_file["tmp_name"];
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    // Limit maximum scan file size to 15MB
    if ($filesize > 15 * 1024 * 1024) {
        echo json_encode(["status" => "error", "message" => "File exceeds 15MB maximum scan size."]);
        exit;
    }

    $content = file_get_contents($tmp_path);
    $sha256 = hash('sha256', $content);
    $md5 = hash('md5', $content);

    $threats = [];
    $risk_score = 0; // 0 - 100

    // High risk file extensions
    $high_risk_exts = ['exe', 'bat', 'cmd', 'ps1', 'vbs', 'scr', 'pif', 'com', 'hta', 'wsf', 'jar', 'apk'];
    $script_exts = ['php', 'phtml', 'php5', 'sh', 'py', 'pl', 'cgi', 'jsp', 'asp', 'aspx', 'js'];

    if (in_array($extension, $high_risk_exts)) {
        $threats[] = [
            "type" => "Dangerous Executable Extension",
            "severity" => "HIGH",
            "detail" => "File extension '.{$extension}' is directly executable and frequently leveraged by trojans, droppers, and ransomware."
        ];
        $risk_score += 40;
    } elseif (in_array($extension, $script_exts)) {
        $threats[] = [
            "type" => "Executable Script File",
            "severity" => "MEDIUM",
            "detail" => "File is an executable script ('.{$extension}'). Server-side execution rules checked."
        ];
        $risk_score += 20;
    }

    // Signature Rules (Webshells, Backdoors, Reverse Shells, Ransomware, EICAR)
    $signatures = [
        // EICAR Standard Antivirus Test String
        '/X5O!P%@AP\[4\\\\PZX54\(P\^\)7CC\)7\}\$EICAR-STANDARD-ANTIVIRUS-TEST-FILE!\$H\+H\*/i' => [
            "type" => "EICAR Standard Antivirus Test Signature",
            "severity" => "HIGH",
            "score" => 100,
            "detail" => "Standard European Anti-Virus Test string confirmed."
        ],
        // PHP Webshells & Backdoors
        '/(c99shell|r57shell|b374k|WSOshell|alfa\s*shell|c100shell)/i' => [
            "type" => "Known Webshell / Backdoor Signature",
            "severity" => "CRITICAL",
            "score" => 95,
            "detail" => "Identified signature of an automated web administration backdoor tool."
        ],
        // Dangerous Execution functions with obfuscation
        '/(passthru|shell_exec|system|popen|proc_open)\s*\(\s*(base64_decode|gzinflate|gzuncompress|str_rot13)/i' => [
            "type" => "Obfuscated Command Execution",
            "severity" => "CRITICAL",
            "score" => 90,
            "detail" => "Obfuscated dynamic payload combined with system command execution primitives."
        ],
        // Eval of decoded content
        '/eval\s*\(\s*(base64_decode|gzinflate|gzuncompress|str_rot13|hex2bin)\s*\(/i' => [
            "type" => "Hidden Encoded Eval Payload",
            "severity" => "CRITICAL",
            "score" => 85,
            "detail" => "Code dynamically executes encoded/compressed strings, standard signature of trojan droppers."
        ],
        // Ransomware shadow copy deletion & inhibition
        '/(vssadmin\s+delete\s+shadows|wmic\s+shadowcopy\s+delete|bcdedit\s+\/set\s+\{default\}\s+recoveryenabled\s+No)/i' => [
            "type" => "Ransomware Recovery Inhibition Command",
            "severity" => "CRITICAL",
            "score" => 95,
            "detail" => "Commands specifically engineered to wipe Volume Shadow Copies and system recovery points."
        ],
        // Windows Registry Run persistence
        '/(CurrentVersion\\\\Run|CurrentVersion\\\\RunOnce|Services\\\\Tcpip\\\\Parameters)/i' => [
            "type" => "Persistence Registry Manipulation",
            "severity" => "HIGH",
            "score" => 60,
            "detail" => "Script modifies Windows automatic startup and persistence registry keys."
        ],
        // Powershell hidden download execution
        '/powershell(\.exe)?\s+(-(w|windowstyle)\s+hidden\s+)?(-enc|-encodedcommand|-e\s+|-(nop|noprofile)\s+)?(iex|invoke-expression|downloadstring|downloadfile)/i' => [
            "type" => "Stealth PowerShell Dropper",
            "severity" => "CRITICAL",
            "score" => 90,
            "detail" => "Hidden/Encoded PowerShell invocation downloading or evaluating remote code."
        ],
        // Credential harvesting / mimikatz
        '/(sekurlsa::logonpasswords|lsadump::sam|privilege::debug)/i' => [
            "type" => "Mimikatz Credential Dumping String",
            "severity" => "CRITICAL",
            "score" => 95,
            "detail" => "Signatures of memory injection tool for dumping plain-text Windows passwords."
        ],
        // Keylogger hooks
        '/(SetWindowsHookEx|GetAsyncKeyState|GetKeyboardState)/i' => [
            "type" => "Keylogger API Hook",
            "severity" => "HIGH",
            "score" => 55,
            "detail" => "Monitors raw keystrokes and keyboard input buffers."
        ],
        // Cryptominer patterns
        '/(stratum\+tcp:\/\/|stratum\+ssl:\/\/|xmrig|minergate|cryptonight|coinhive)/i' => [
            "type" => "Cryptocurrency Miner Threat",
            "severity" => "HIGH",
            "score" => 75,
            "detail" => "Monero / Stratum protocol connection strings detected for unauthorized background mining."
        ]
    ];

    foreach ($signatures as $regex => $meta) {
        if (preg_match($regex, $content)) {
            $threats[] = [
                "type" => $meta["type"],
                "severity" => $meta["severity"],
                "detail" => $meta["detail"]
            ];
            $risk_score += $meta["score"];
        }
    }

    // High Entropy (Packed/Encrypted binary payload check in text scripts)
    if (in_array($extension, ['php', 'js', 'py', 'sh', 'bat', 'vbs', 'html'])) {
        $len = strlen($content);
        if ($len > 256) {
            // Count byte frequencies
            $freqs = count_chars($content, 1);
            $entropy = 0.0;
            foreach ($freqs as $cnt) {
                $p = $cnt / $len;
                $entropy -= $p * log($p, 2);
            }
            if ($entropy > 6.8) {
                $threats[] = [
                    "type" => "Suspicious High-Entropy Data (Packed/Encrypted)",
                    "severity" => "MEDIUM",
                    "detail" => "File content exhibits abnormally high entropy (" . round($entropy, 2) . "/8.0), indicating packed, encrypted, or heavily obfuscated payloads."
                ];
                $risk_score += 35;
            }
        }
    }

    $risk_score = min(100, $risk_score);
    if ($risk_score >= 70) {
        $verdict = "MALWARE DETECTED";
        $verdict_color = "#ef4444";
        $status_label = "CRITICAL THREAT";
    } elseif ($risk_score >= 35) {
        $verdict = "SUSPICIOUS";
        $verdict_color = "#f59e0b";
        $status_label = "WARNING";
    } else {
        $verdict = "CLEAN";
        $verdict_color = "#10b981";
        $status_label = "SAFE";
    }

    // Log scan to database if user is authenticated
    try {
        $log_summary = "Antivirus File Scan: {$filename} (SHA-256: " . substr($sha256, 0, 12) . "...)";
        $masked_summary = "File Antivirus: {$filename} [Verdict: {$verdict}]";
        $stmt = $conn->prepare(
            "INSERT INTO scans (user_id, original_text, masked_text, risk_score, risk_level) VALUES (?, ?, ?, ?, ?)"
        );
        if ($stmt) {
            $stmt->bind_param("issis", $user_id, $log_summary, $masked_summary, $risk_score, $status_label);
            $stmt->execute();
            $stmt->close();
        }
    } catch (Throwable $t) {
        // Continue even if logging fails
    }

    echo json_encode([
        "status" => "success",
        "file_info" => [
            "name" => $filename,
            "size" => $filesize,
            "extension" => $extension,
            "sha256" => $sha256,
            "md5" => $md5,
        ],
        "verdict" => $verdict,
        "verdict_color" => $verdict_color,
        "status_label" => $status_label,
        "risk_score" => $risk_score,
        "threats" => $threats,
        "threat_count" => count($threats)
    ]);
    exit;
}

// -----------------------------------------------------------------------------
// Action: Scan URL for Phishing, Malware, and Suspicious TLDs
// -----------------------------------------------------------------------------
if ($action === "scan_url") {
    $url = trim($_POST["url"] ?? "");

    if (empty($url)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Please enter a valid URL."]);
        exit;
    }

    if (!preg_match('#^https?://#i', $url)) {
        $url = 'https://' . $url;
    }

    $parsed = parse_url($url);
    $host = $parsed['host'] ?? '';
    $scheme = $parsed['scheme'] ?? '';
    $path = $parsed['path'] ?? '/';

    $threats = [];
    $risk_score = 0;

    // Check scheme
    if (strtolower($scheme) !== 'https') {
        $threats[] = [
            "type" => "Unencrypted HTTP Protocol",
            "severity" => "HIGH",
            "detail" => "Website lacks SSL/TLS encryption. Sensitive inputs (passwords, credentials) are transmitted in plaintext."
        ];
        $risk_score += 35;
    }

    // Check IP as host
    if (filter_var($host, FILTER_VALIDATE_IP)) {
        $threats[] = [
            "type" => "Direct IP Address Host",
            "severity" => "HIGH",
            "detail" => "URL points directly to an IP address ({$host}) without a verified domain name, a common vector for phishing command-and-control servers."
        ];
        $risk_score += 45;
    }

    // Check suspicious TLDs often used for spam & malware
    $suspicious_tlds = ['zip', 'mov', 'top', 'xyz', 'gq', 'cf', 'ml', 'tk', 'work', 'click', 'link', 'buzz', 'cc', 'ru'];
    $host_parts = explode('.', $host);
    $tld = strtolower(end($host_parts));

    if (in_array($tld, $suspicious_tlds)) {
        $threats[] = [
            "type" => "High-Risk Top Level Domain (TLD)",
            "severity" => "MEDIUM",
            "detail" => "The .{$tld} domain extension has historically high rates of malware hosting and malicious disposable infrastructure."
        ];
        $risk_score += 25;
    }

    // Check for Punycode / Homograph attack (e.g. xn--...)
    if (strpos($host, 'xn--') !== false) {
        $threats[] = [
            "type" => "Punycode Homograph Domain Impersonation",
            "severity" => "CRITICAL",
            "score" => 50,
            "detail" => "Domain uses internationalized punycode characters (IDN) to masquerade visually as reputable platforms (e.g. Cyrillic 'a' for Latin 'a')."
        ];
        $risk_score += 50;
    }

    // Check Brand Impersonation in subdomains
    $brands = ['paypal', 'bank', 'google', 'apple', 'microsoft', 'netflix', 'amazon', 'sbi', 'hdfc', 'chase', 'wellsfargo', 'login', 'verify', 'update-account'];
    foreach ($brands as $brand) {
        if (strpos($host, $brand) !== false && !preg_match("/(^|\.){$brand}\.(com|org|net|in|co\.uk)$/i", $host)) {
            $threats[] = [
                "type" => "Target Brand Spoofing / Impersonation",
                "severity" => "CRITICAL",
                "detail" => "Host contains keyword '{$brand}' inside a secondary subdomain, typical of credential harvesting phishing portals."
            ];
            $risk_score += 50;
            break;
        }
    }

    // Check suspicious path payloads (.exe, .scr, .apk in download link)
    if (preg_match('/\.(exe|scr|bat|ps1|apk|msi|vbs|jar)(\?.*)?$/i', $path)) {
        $threats[] = [
            "type" => "Direct Malicious Payload Download",
            "severity" => "CRITICAL",
            "detail" => "URL directly references an executable binary download."
        ];
        $risk_score += 60;
    }

    $risk_score = min(100, $risk_score);
    if ($risk_score >= 60) {
        $verdict = "DANGEROUS / PHISHING";
        $verdict_color = "#ef4444";
        $status_label = "HIGH";
    } elseif ($risk_score >= 25) {
        $verdict = "SUSPICIOUS";
        $verdict_color = "#f59e0b";
        $status_label = "MEDIUM";
    } else {
        $verdict = "SAFE / REPUTABLE";
        $verdict_color = "#10b981";
        $status_label = "SAFE";
    }

    // Log scan to database
    try {
        $log_summary = "Antivirus URL Scan: {$url}";
        $masked_summary = "URL Threat Scan: {$host} [Verdict: {$verdict}]";
        $stmt = $conn->prepare(
            "INSERT INTO scans (user_id, original_text, masked_text, risk_score, risk_level) VALUES (?, ?, ?, ?, ?)"
        );
        if ($stmt) {
            $stmt->bind_param("issis", $user_id, $log_summary, $masked_summary, $risk_score, $status_label);
            $stmt->execute();
            $stmt->close();
        }
    } catch (Throwable $t) {
        // continue
    }

    echo json_encode([
        "status" => "success",
        "url" => $url,
        "host" => $host,
        "scheme" => $scheme,
        "verdict" => $verdict,
        "verdict_color" => $verdict_color,
        "status_label" => $status_label,
        "risk_score" => $risk_score,
        "threats" => $threats,
        "threat_count" => count($threats)
    ]);
    exit;
}

// -----------------------------------------------------------------------------
// Action: Log Threat Event from Camera or Environmental Detection
// -----------------------------------------------------------------------------
if ($action === "log_threat") {
    $threat_type = trim($_POST["threat_type"] ?? "Camera Privacy Threat");
    $detail = trim($_POST["detail"] ?? "");
    $risk_score = (int)($_POST["risk_score"] ?? 65);
    $risk_level = ($risk_score >= 70) ? "HIGH" : (($risk_score >= 40) ? "MEDIUM" : "SAFE");

    try {
        $log_summary = "Camera/Environment Threat: [{$threat_type}] {$detail}";
        $masked_summary = "Real-Time Camera Threat: {$threat_type}";
        $stmt = $conn->prepare(
            "INSERT INTO scans (user_id, original_text, masked_text, risk_score, risk_level) VALUES (?, ?, ?, ?, ?)"
        );
        if ($stmt) {
            $stmt->bind_param("issis", $user_id, $log_summary, $masked_summary, $risk_score, $risk_level);
            $stmt->execute();
            $stmt->close();
        }
    } catch (Throwable $t) {
        // continue
    }

    echo json_encode(["status" => "success", "message" => "Threat logged successfully."]);
    exit;
}

http_response_code(400);
echo json_encode(["status" => "error", "message" => "Unknown action."]);

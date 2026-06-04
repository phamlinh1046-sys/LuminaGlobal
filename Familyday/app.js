// Chibi Animal Derby - Main Game Logic and Physics Engine

// Game States
const STATES = {
  LOBBY: 'LOBBY',
  COUNTDOWN: 'COUNTDOWN',
  RACING: 'RACING',
  FINISHED: 'FINISHED'
};

class Game {
  constructor() {
    this.state = STATES.LOBBY;
    this.cheeredId = 1; // Default cheered animal
    this.raceDuration = 30; // Seconds
    this.theme = 'meadow';
    this.cameraMode = 'auto'; // auto, focus, group
    
    // Race Physics constants
    this.startX = 300;
    this.finishX = 5300;
    this.trackLength = this.finishX - this.startX; // 5000px
    
    // Running variables
    this.runners = []; // Array of runner objects
    this.startTime = null;
    this.elapsedTime = 0;
    this.animationFrameId = null;
    this.frameNumber = 0;
    this.isSlowMo = false;
    this.slowMoScale = 1.0;
    this.cameraScrollX = 0;
    
    // Cache DOM Elements
    this.dom = {
      // Screens
      lobbyScreen: document.getElementById('screen-lobby'),
      raceScreen: document.getElementById('screen-race'),
      victoryScreen: document.getElementById('screen-victory'),
      
      // Configuration Inputs
      durationSlider: document.getElementById('duration-slider'),
      durationVal: document.getElementById('duration-val'),
      muteBtn: document.getElementById('mute-sound-btn'),
      soundIcon: document.getElementById('sound-icon'),
      soundLabel: document.getElementById('sound-label'),
      startBtn: document.getElementById('start-race-btn'),
      replayBtn: document.getElementById('replay-game-btn'),
      
      // Selection Grid
      selectionGrid: document.getElementById('animals-selection-grid'),
      
      // Detail Card
      detailCard: document.getElementById('champion-detail-card'),
      detailSvg: document.getElementById('detail-svg-container'),
      detailName: document.getElementById('detail-name'),
      detailFood: document.getElementById('detail-food'),
      detailBio: document.getElementById('detail-bio'),
      statSpeed: document.getElementById('stat-val-speed'),
      statAccel: document.getElementById('stat-val-accel'),
      statLuck: document.getElementById('stat-val-luck'),
      
      // Racetrack viewport
      racetrackViewport: document.getElementById('racetrack-viewport'),
      racetrackScroll: document.getElementById('racetrack-scroll'),
      runnersContainer: document.getElementById('runners-container'),
      decorationsLayer: document.getElementById('decorations-layer'),
      countdownOverlay: document.getElementById('countdown-overlay'),
      countdownNumber: document.getElementById('countdown-number'),
      flashOverlay: document.getElementById('photofinish-flash'),
      
      // Minimap & HUD
      minimapTrack: document.getElementById('minimap-track-line'),
      timerDisplay: document.getElementById('race-timer-display'),
      liveLeaderboard: document.getElementById('live-leaderboard-rows'),
      
      // Victory/Podium elements
      confettiContainer: document.getElementById('confetti-container'),
      winnerAvatar: document.getElementById('winner-avatar-container'),
      winnerBanner: document.getElementById('winner-banner-text'),
      outcomeBadge: document.getElementById('cheered-outcome-badge'),
      winnerBio: document.getElementById('winner-bio-text'),
      podium1: document.getElementById('podium-1-avatar'),
      podium2: document.getElementById('podium-2-avatar'),
      podium3: document.getElementById('podium-3-avatar'),
      podium1Name: document.getElementById('podium-1-name'),
      podium2Name: document.getElementById('podium-2-name'),
      podium3Name: document.getElementById('podium-3-name'),
      resultsTable: document.getElementById('results-table-body'),
    };
  }

  init() {
    this.setupLobby();
    this.bindEvents();
    this.animateDetailCard();
  }

  // Generate 40 animal cards in lobby and setup details
  setupLobby() {
    this.dom.selectionGrid.innerHTML = '';
    
    ANIMALS.forEach(animal => {
      const card = document.createElement('div');
      card.className = `animal-card ${animal.id === this.cheeredId ? 'selected' : ''}`;
      card.id = `animal-card-${animal.id}`;
      card.dataset.id = animal.id;
      
      card.innerHTML = `
        <span class="card-id">#${animal.id}</span>
        <div class="card-svg">${renderAnimalSVG(animal.id, 'idle', 0)}</div>
        <span class="selected-badge">✓</span>
      `;
      
      card.addEventListener('click', () => {
        sounds.playClick();
        this.selectChampion(animal.id);
      });
      
      this.dom.selectionGrid.appendChild(card);
    });

    this.selectChampion(this.cheeredId);
  }

  // Update selected champion display
  selectChampion(id) {
    this.cheeredId = parseInt(id);
    
    // Toggle active classes in grid
    document.querySelectorAll('.animal-card').forEach(card => {
      card.classList.remove('selected');
    });
    const selectedCard = document.getElementById(`animal-card-${id}`);
    if (selectedCard) selectedCard.classList.add('selected');
    
    // Update preview card details
    const animal = getAnimal(id);
    this.dom.detailName.textContent = `#${animal.id} - ${animal.name}`;
    this.dom.detailFood.textContent = `🍜 Thèm: ${animal.favFood}`;
    this.dom.detailBio.textContent = `"${animal.bio}"`;
    
    // Visual stars for stats
    this.dom.statSpeed.textContent = '⭐'.repeat(animal.stats.speed);
    this.dom.statAccel.textContent = '⭐'.repeat(animal.stats.accel);
    this.dom.statLuck.textContent = '⭐'.repeat(animal.stats.luck);
    
    // Render preview SVG
    this.updateDetailSVG();
  }

  updateDetailSVG() {
    const frame = Math.floor(Date.now() / 150);
    this.dom.detailSvg.innerHTML = renderAnimalSVG(this.cheeredId, 'idle', frame);
  }

  // Live animation loop for the lobby details preview card
  animateDetailCard() {
    const loop = () => {
      if (this.state === STATES.LOBBY) {
        this.updateDetailSVG();
        requestAnimationFrame(loop);
      }
    };
    requestAnimationFrame(loop);
  }

  bindEvents() {
    // Duration slider
    this.dom.durationSlider.addEventListener('input', (e) => {
      this.raceDuration = parseInt(e.target.value);
      this.dom.durationVal.textContent = `${this.raceDuration}s`;
    });
    
    // Theme selectors
    document.querySelectorAll('.theme-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        sounds.playClick();
        document.querySelectorAll('.theme-btn').forEach(b => b.classList.remove('active'));
        e.target.classList.add('active');
        this.theme = e.target.dataset.theme;
      });
    });

    // Mute button
    this.dom.muteBtn.addEventListener('click', () => {
      const isMuted = sounds.toggleMute();
      sounds.playClick();
      if (isMuted) {
        this.dom.soundIcon.textContent = '🔇';
        this.dom.soundLabel.textContent = 'Tắt âm thanh';
        this.dom.muteBtn.style.background = '#e9ecef';
      } else {
        this.dom.soundIcon.textContent = '🔊';
        this.dom.soundLabel.textContent = 'Bật âm thanh';
        this.dom.muteBtn.style.background = '#fff';
      }
    });

    // Start Race
    this.dom.startBtn.addEventListener('click', () => {
      sounds.init();
      sounds.playClick();
      this.transitionToScreen(STATES.COUNTDOWN);
    });

    // Replay Game
    this.dom.replayBtn.addEventListener('click', () => {
      sounds.playClick();
      this.transitionToScreen(STATES.LOBBY);
    });

    // Camera modes
    document.querySelectorAll('[data-cam]').forEach(btn => {
      btn.addEventListener('click', (e) => {
        sounds.playClick();
        document.querySelectorAll('[data-cam]').forEach(b => b.classList.remove('active'));
        e.target.classList.add('active');
        this.cameraMode = e.target.dataset.cam;
      });
    });
  }

  transitionToScreen(state) {
    this.state = state;
    
    // Hide all screens
    this.dom.lobbyScreen.classList.remove('active');
    this.dom.raceScreen.classList.remove('active');
    this.dom.victoryScreen.classList.remove('active');
    
    // Toggle in-race class for body to hide header and maximize track height
    if (state === STATES.COUNTDOWN || state === STATES.RACING) {
      document.body.classList.add('in-race');
    } else {
      document.body.classList.remove('in-race');
    }
    
    if (state === STATES.LOBBY) {
      this.dom.lobbyScreen.classList.add('active');
      this.setupLobby();
      this.animateDetailCard();
    } else if (state === STATES.COUNTDOWN) {
      this.dom.raceScreen.classList.add('active');
      this.prepareRaceTrack();
      this.runCountdown();
    } else if (state === STATES.FINISHED) {
      this.dom.victoryScreen.classList.add('active');
      this.showVictoryCelebration();
    }
  }

  // SETUP RACE TRACK GRAPHICS AND RUNNERS
  prepareRaceTrack() {
    // 1. Select random theme for variety since manual map selector is removed!
    const themes = ['meadow', 'candyland', 'cloud', 'beach'];
    this.theme = themes[Math.floor(Math.random() * themes.length)];
    
    // Set track theme class
    this.dom.racetrackScroll.className = `racetrack-scroll track-theme-${this.theme}`;
    
    // 2. Generate theme-specific decorations
    this.generateDecorations();
    
    // 3. Clear runners containers
    this.dom.runnersContainer.innerHTML = '';
    this.dom.minimapTrack.querySelectorAll('.minimap-runner').forEach(e => e.remove());
    
    this.runners = [];
    this.isSlowMo = false;
    this.slowMoScale = 1.0;
    this.cameraScrollX = 0;
    this.dom.racetrackViewport.scrollLeft = 0;
    
    // 4. Create runners
    const trackHeight = this.dom.racetrackScroll.clientHeight || 500;
    const runnerHeight = ANIMALS.length > 50 ? 70 : 110;
    const paddingY = 15;
    const availableRange = Math.max(100, trackHeight - runnerHeight - (paddingY * 2));

    ANIMALS.forEach((animal, index) => {
      // Dynamically stagger Y position so they fit perfectly in the computed track height
      const yPos = paddingY + (index / (ANIMALS.length - 1)) * availableRange;

      const isSelected = animal.id === this.cheeredId;
      
      // Create DOM runner element
      const runnerDiv = document.createElement('div');
      runnerDiv.className = `runner-element ${isSelected ? 'selected-champion' : ''}`;
      runnerDiv.id = `runner-${animal.id}`;
      runnerDiv.style.left = `${this.startX}px`;
      runnerDiv.style.top = `${yPos}px`;
      runnerDiv.style.width = `${runnerHeight}px`;
      runnerDiv.style.height = `${runnerHeight}px`;
      runnerDiv.style.zIndex = Math.floor(yPos); // Y-sorting depth
      
      runnerDiv.innerHTML = `
        ${isSelected ? '<span class="champion-pointer">▼</span>' : ''}
        <div class="bubble-status" id="bubble-${animal.id}">🔥 SIÊU TỐC!</div>
        <div class="runner-svg-box" id="svg-box-${animal.id}">
          ${renderAnimalSVG(animal.id, 'idle', 0)}
        </div>
      `;
      
      this.dom.runnersContainer.appendChild(runnerDiv);
      
      // Create minimap dot (we remove direct inline font settings, handled by CSS)
      const miniDot = document.createElement('div');
      miniDot.className = `minimap-runner ${isSelected ? 'selected-runner' : ''}`;
      miniDot.id = `mini-dot-${animal.id}`;
      miniDot.style.left = '0%';
      miniDot.style.backgroundColor = animal.color;
      miniDot.style.color = animal.text;
      miniDot.innerHTML = animal.id;
      
      this.dom.minimapTrack.appendChild(miniDot);
      
      // Push object into tracking list
      this.runners.push({
        id: animal.id,
        name: animal.name,
        color: animal.color,
        text: animal.text,
        stats: animal.stats,
        x: this.startX,
        y: yPos,
        velocity: 0,
        baseSpeed: (this.trackLength / this.raceDuration) * (0.85 + Math.random() * 0.1), // slightly varied base
        state: 'idle', // idle, running, sleepy, boosting
        statusTimeLeft: 0,
        bubbleTimeout: null,
        crossed: false,
        finishTime: null,
        domElement: runnerDiv,
        miniElement: miniDot,
        svgBox: document.getElementById(`svg-box-${animal.id}`),
        bubbleElement: document.getElementById(`bubble-${animal.id}`),
        lastRenderedState: 'idle'
      });
    });

    this.updateHUD(0);
  }

  generateDecorations() {
    this.dom.decorationsLayer.innerHTML = '';
    const trackHeight = this.dom.racetrackScroll.clientHeight || 500;
    
    // Choose emoji set based on theme
    let decors = ['🌸', '🍀', '🌼', '🍄', '🌳', '🏡'];
    if (this.theme === 'candyland') {
      decors = ['🍬', '🍭', '🧁', '🍦', '🍩', '🍪', '🍫'];
    } else if (this.theme === 'cloud') {
      decors = ['☁️', '⭐', '🌈', '🌙', '✨'];
    } else if (this.theme === 'beach') {
      decors = ['🐚', '🏖️', '🌴', '🦀', '🍍', '🍉', '🌊'];
    }

    // Place 80 random decorative items along the 6000px track length
    for (let i = 0; i < 80; i++) {
      const icon = decors[Math.floor(Math.random() * decors.length)];
      const x = 100 + Math.random() * 5800;
      
      // Keep them at the very top or very bottom to avoid blocking track lanes visually
      const y = Math.random() > 0.5 ? (10 + Math.random() * 30) : ((trackHeight - 50) + Math.random() * 30);
      
      const div = document.createElement('div');
      div.className = 'decor-item';
      div.style.left = `${x}px`;
      div.style.top = `${y}px`;
      div.style.fontSize = `${16 + Math.random() * 16}px`;
      div.textContent = icon;
      
      this.dom.decorationsLayer.appendChild(div);
    }
  }

  // COUNTDOWN OVERLAY PHASE
  runCountdown() {
    this.dom.countdownOverlay.style.display = 'flex';
    let count = 3;
    this.dom.countdownNumber.textContent = count;
    sounds.playTick();

    const timer = setInterval(() => {
      count--;
      if (count > 0) {
        this.dom.countdownNumber.textContent = count;
        sounds.playTick();
      } else if (count === 0) {
        this.dom.countdownNumber.textContent = "CHẠY!";
        sounds.playStartWhistle();
        sounds.startCheer();
      } else {
        clearInterval(timer);
        this.dom.countdownOverlay.style.display = 'none';
        this.startRacePhysics();
      }
    }, 1000);
  }

  // START GAME LOOP
  startRacePhysics() {
    this.state = STATES.RACING;
    this.startTime = performance.now();
    this.frameNumber = 0;
    
    // Set all runners to running state
    this.runners.forEach(r => {
      r.state = 'running';
      r.velocity = r.baseSpeed;
    });

    const loop = (timestamp) => {
      if (this.state !== STATES.RACING) return;
      
      // Calculate delta time (dt)
      let dt = (timestamp - (this.lastTime || timestamp)) / 1000;
      if (dt > 0.1) dt = 0.1; // Cap lag spikes
      this.lastTime = timestamp;
      
      this.elapsedTime = (timestamp - this.startTime) / 1000;
      this.frameNumber++;
      
      this.updatePhysics(dt);
      this.updateCamera();
      this.updateHUD(this.elapsedTime);
      
      // Check if all finished
      const allFinished = this.runners.every(r => r.crossed);
      if (allFinished) {
        setTimeout(() => {
          sounds.stopCheer();
          this.transitionToScreen(STATES.FINISHED);
        }, 1500); // 1.5s delay before standings to let camera rest
      } else {
        this.animationFrameId = requestAnimationFrame(loop);
      }
    };
    
    this.lastTime = performance.now();
    this.animationFrameId = requestAnimationFrame(loop);
  }

  // INDIVIDUAL PHYSICS STEP
  updatePhysics(dt) {
    // Apply time scaling for slow motion photo finish
    const timeScale = this.isSlowMo ? 0.25 : 1.0;
    const actualDt = dt * timeScale;
    
    // Collect step sound stats
    let totalSpeed = 0;
    
    this.runners.forEach(runner => {
      if (runner.crossed) {
        // Continue sliding slightly after crossing finish
        if (runner.x < this.finishX + 150) {
          runner.x += runner.velocity * actualDt * 0.3;
          runner.domElement.style.left = `${runner.x}px`;
        }
        return;
      }
      
      // 1. Manage status effects durations
      if (runner.statusTimeLeft > 0) {
        runner.statusTimeLeft -= actualDt;
        if (runner.statusTimeLeft <= 0) {
          runner.state = 'running';
          runner.statusTimeLeft = 0;
        }
      }
      
      // 2. Trigger random status effects
      // Trigger chances are low per frame (60fps scale)
      if (runner.statusTimeLeft === 0) {
        const rand = Math.random();
        
        if (rand < 0.0018) { // 0.18% chance per tick: BOOST
          runner.state = 'boosting';
          runner.statusTimeLeft = 1.2 + Math.random() * 1.0; // 1.2s to 2.2s
          sounds.playBoost();
          this.showBubble(runner, 'boost', ['🔥 TĂNG TỐC!', '⚡ VÙUUUU!', '🚀 SIÊU TỐC!', '🔥 ĐI THÔI!']);
        } else if (rand < 0.0026) { // 0.08% chance: STUMBLE
          runner.state = 'stumble';
          runner.statusTimeLeft = 0.8 + Math.random() * 0.5; // 0.8s to 1.3s
          sounds.playStumble();
          this.showBubble(runner, 'stumble', ['💫 Trơn trượt!', '😭 Vấp rồi!', '🤕 Á đau!', '💫 Hu hu...']);
        } else if (rand < 0.0030) { // 0.04% chance: SLEEPY
          runner.state = 'sleepy';
          runner.statusTimeLeft = 1.5 + Math.random() * 1.0; // 1.5s to 2.5s
          this.showBubble(runner, 'sleep', ['💤 Oáp...', '😴 Khò khò...', '💤 Chờ tí...', '💤 Mệt ngủ...']);
        }
      }

      // 3. Compute Velocity based on state
      let speedMultiplier = 1.0;
      if (runner.state === 'boosting') {
        speedMultiplier = 2.0 + (runner.stats.accel * 0.1); // accel stats affect boost
      } else if (runner.state === 'stumble') {
        speedMultiplier = 0.25;
      } else if (runner.state === 'sleepy') {
        speedMultiplier = 0.0;
      }
      
      // Add random walking noise (luck stat affects noise amplitude positively!)
      const noise = (Math.random() - 0.47) * (150 + (runner.stats.luck * 15));
      
      runner.velocity = (runner.baseSpeed * speedMultiplier) + noise;
      if (runner.velocity < 0) runner.velocity = 0;
      
      // 4. Update Position
      runner.x += runner.velocity * actualDt;
      
      // Keep inside bounds
      if (runner.x < this.startX) runner.x = this.startX;
      
      // 5. Check if crossed finish line
      if (runner.x >= this.finishX) {
        runner.x = this.finishX;
        runner.crossed = true;
        runner.finishTime = this.elapsedTime;
        runner.state = 'winner';
        
        // Triggers camera slow-mo photo finish if this is the absolute 1st place!
        const firstWinner = this.runners.filter(r => r.crossed).length === 1;
        if (firstWinner) {
          this.triggerPhotoFinish();
        }
      }
      
      // 6. Apply position to DOM
      runner.domElement.style.left = `${runner.x}px`;
      
      // 7. Update animated SVG vector content (optimized: viewport culling + state change guard)
      const viewportWidth = this.dom.racetrackViewport.clientWidth || 1000;
      const isVisible = runner.x >= this.cameraScrollX - 200 && runner.x <= this.cameraScrollX + viewportWidth + 200;
      if (isVisible || runner.state !== runner.lastRenderedState) {
        runner.svgBox.innerHTML = renderAnimalSVG(runner.id, runner.state, this.frameNumber);
        runner.lastRenderedState = runner.state;
      }
      
      totalSpeed += runner.velocity;
    });

    // Play running galloping sounds periodically
    if (this.frameNumber % 14 === 0 && totalSpeed > 100) {
      sounds.playStep();
    }
  }

  showBubble(runner, type, textOptions) {
    if (runner.bubbleTimeout) clearTimeout(runner.bubbleTimeout);
    
    const text = textOptions[Math.floor(Math.random() * textOptions.length)];
    runner.bubbleElement.textContent = text;
    runner.bubbleElement.className = `bubble-status show ${type}`;
    
    runner.bubbleTimeout = setTimeout(() => {
      runner.bubbleElement.className = 'bubble-status';
    }, 1200);
  }

  triggerPhotoFinish() {
    this.isSlowMo = true;
    
    // Visual flash overlay
    this.dom.flashOverlay.classList.add('flash-active');
    setTimeout(() => {
      this.dom.flashOverlay.classList.remove('flash-active');
    }, 800);
    
    // Revert slow motion after 1.5 seconds (in game duration context: 1.5 / 0.25 = 6 seconds real time, 
    // actually, let's keep slow mo for 1.8 seconds real time)
    setTimeout(() => {
      this.isSlowMo = false;
    }, 1800);
  }

  // CAMERA LERP POSITION SCROLLING
  updateCamera() {
    const viewportWidth = this.dom.racetrackViewport.clientWidth;
    const scrollMax = 6000 - viewportWidth;
    
    let targetScrollX = 0;
    
    if (this.cameraMode === 'group') {
      // Find average position of all runners
      const avgX = this.runners.reduce((acc, r) => acc + r.x, 0) / this.runners.length;
      targetScrollX = avgX - viewportWidth / 2;
    } else if (this.cameraMode === 'focus') {
      // Focus strictly on cheered champion
      const champion = this.runners.find(r => r.id === this.cheeredId);
      targetScrollX = champion.x - viewportWidth / 3; // Keep them in the first third
    } else {
      // Auto mode: follow the leading pack (average of top 3)
      const sorted = [...this.runners].sort((a, b) => b.x - a.x);
      const leadPackAvgX = (sorted[0].x + sorted[1].x + sorted[2].x) / 3;
      targetScrollX = leadPackAvgX - viewportWidth / 2.5; // Lead pack centered-ish
    }
    
    // Clamp target
    if (targetScrollX < 0) targetScrollX = 0;
    if (targetScrollX > scrollMax) targetScrollX = scrollMax;
    
    // Smooth LERP animation
    this.cameraScrollX += (targetScrollX - this.cameraScrollX) * 0.08;
    this.dom.racetrackViewport.scrollLeft = this.cameraScrollX;
  }

  // UPDATE HUD STATS AND MINIMAP DOTS
  updateHUD(seconds) {
    // 1. Format time label
    const ms = Math.floor((seconds % 1) * 100).toString().padStart(2, '0');
    const sec = Math.floor(seconds % 60).toString().padStart(2, '0');
    const min = Math.floor(seconds / 60).toString().padStart(2, '0');
    this.dom.timerDisplay.textContent = `⏱️ ${min}:${sec}.${ms}`;
    
    // 2. Sort runners to find rankings
    // Runners that have crossed are ranked by their completion time, 
    // others are ranked by distance (X coordinate)
    const sorted = [...this.runners].sort((a, b) => {
      if (a.crossed && b.crossed) {
        return a.finishTime - b.finishTime;
      }
      if (a.crossed) return -1;
      if (b.crossed) return 1;
      return b.x - a.x;
    });
    
    // 3. Update top 3 leaderboard overlay
    const top3Ids = sorted.slice(0, 3).map(r => r.id);
    for (let i = 0; i < 3; i++) {
      const runner = sorted[i];
      const nameElem = document.getElementById(`leader-${i+1}-name`);
      const avatarElem = document.getElementById(`leader-${i+1}-avatar`);
      
      if (runner) {
        nameElem.textContent = `#${runner.id} ${runner.name}`;
        avatarElem.innerHTML = renderAnimalSVG(runner.id, 'running', this.frameNumber);
      }
    }
    
    // 4. Update dots on minimap line and adjust highlight classes (avoids 200 dots overlapping clump)
    this.runners.forEach(runner => {
      // Percent position: start is 300px, end is 5300px. Length 5000px.
      const pct = Math.max(0, Math.min(100, ((runner.x - this.startX) / this.trackLength) * 100));
      runner.miniElement.style.left = `${pct}%`;
      
      const isSelected = runner.id === this.cheeredId;
      const isTop3 = top3Ids.includes(runner.id);
      
      let className = 'minimap-runner';
      if (isSelected) {
        className += ' selected-runner';
      } else if (isTop3) {
        className += ' top-runner';
      }
      
      if (runner.miniElement.className !== className) {
        runner.miniElement.className = className;
      }
    });
  }

  // END OF RACE CELEBRATION AND RESULTS TABLE
  showVictoryCelebration() {
    // Sort final results
    const results = [...this.runners].sort((a, b) => {
      return a.finishTime - b.finishTime;
    });
    
    const winner = results[0];
    const cheeredFinisher = results.find(r => r.id === this.cheeredId);
    const cheeredRank = results.indexOf(cheeredFinisher) + 1;
    
    // Play fanfare melody
    sounds.playFanfare();
    
    // 1. Generate Confetti
    this.generateConfetti();
    
    // 2. Render Gold Winner showcase
    this.dom.winnerAvatar.innerHTML = renderAnimalSVG(winner.id, 'winner', 0);
    this.dom.winnerBanner.textContent = `SỐ ${winner.id} - ${winner.name} THẮNG CUỘC!`;
    this.dom.winnerBio.textContent = `"${winner.name} gửi lời chúc ngọt ngào nhất tới khán giả!"`;
    
    // Toggle correct cheering outcome banner
    if (winner.id === this.cheeredId) {
      this.dom.outcomeBadge.textContent = `🏆 DỰ ĐOÁN CHÍNH XÁC! Bạn đã cổ vũ đúng nhà vô địch!`;
      this.dom.outcomeBadge.className = 'cheered-outcome-badge outcome-won';
    } else {
      this.dom.outcomeBadge.textContent = `Nhà vô địch bạn chọn (#${this.cheeredId} ${cheeredFinisher.name}) đã về đích ở hạng #${cheeredRank}`;
      this.dom.outcomeBadge.className = 'cheered-outcome-badge outcome-lost';
    }
    
    // 3. Assemble Podium Avatars (Top 3)
    this.dom.podium1.innerHTML = renderAnimalSVG(winner.id, 'winner', 0);
    this.dom.podium1Name.textContent = `#${winner.id} ${winner.name}`;
    
    if (results[1]) {
      this.dom.podium2.innerHTML = renderAnimalSVG(results[1].id, 'idle', 0);
      this.dom.podium2Name.textContent = `#${results[1].id} ${results[1].name}`;
    }
    if (results[2]) {
      this.dom.podium3.innerHTML = renderAnimalSVG(results[2].id, 'idle', 0);
      this.dom.podium3Name.textContent = `#${results[2].id} ${results[2].name}`;
    }
    
    // 4. Fill final standings table
    this.dom.resultsTable.innerHTML = '';
    
    results.forEach((runner, idx) => {
      const rank = idx + 1;
      const isCheered = runner.id === this.cheeredId;
      const ms = Math.floor((runner.finishTime % 1) * 100).toString().padStart(2, '0');
      const sec = Math.floor(runner.finishTime % 60).toString().padStart(2, '0');
      const min = Math.floor(runner.finishTime / 60).toString().padStart(2, '0');
      const timeStr = `${min}:${sec}.${ms}`;
      
      const tr = document.createElement('tr');
      if (isCheered) tr.className = 'row-cheered';
      
      tr.innerHTML = `
        <td class="table-rank">#${rank}</td>
        <td class="table-avatar-cell">
          <div class="table-avatar">${renderAnimalSVG(runner.id, 'idle', 0)}</div>
          <span>${runner.name}</span>
        </td>
        <td><span class="table-number-badge">${runner.id}</span></td>
        <td>
          ${isCheered ? '<span class="table-cheered-flag">⭐️ Bạn chọn</span>' : ''}
          ${rank === 1 ? '🥇 Vô địch' : rank === 2 ? '🥈 Á quân' : rank === 3 ? '🥉 Hạng ba' : timeStr}
        </td>
      `;
      this.dom.resultsTable.appendChild(tr);
    });

    // Make winner SVG jump in loop
    let winnerFrame = 0;
    this.winnerLoopInterval = setInterval(() => {
      winnerFrame++;
      this.dom.winnerAvatar.innerHTML = renderAnimalSVG(winner.id, 'winner', winnerFrame);
      this.dom.podium1.innerHTML = renderAnimalSVG(winner.id, 'winner', winnerFrame);
      if (results[1]) this.dom.podium2.innerHTML = renderAnimalSVG(results[1].id, 'running', winnerFrame);
      if (results[2]) this.dom.podium3.innerHTML = renderAnimalSVG(results[2].id, 'idle', winnerFrame);
    }, 100);
  }

  generateConfetti() {
    this.dom.confettiContainer.innerHTML = '';
    
    // Spawn 100 colorful pieces of falling confetti
    const colors = [
      'var(--pastel-pink-dark)', 
      'var(--pastel-blue-dark)', 
      'var(--pastel-purple-dark)', 
      'var(--pastel-yellow)', 
      '#ffd6a5', 
      '#fdffb6', 
      '#caffbf', 
      '#ffc6ff'
    ];
    
    for (let i = 0; i < 100; i++) {
      const piece = document.createElement('div');
      piece.className = 'confetti-piece';
      
      const color = colors[Math.floor(Math.random() * colors.length)];
      const left = Math.random() * 100;
      const size = 6 + Math.random() * 8;
      const delay = Math.random() * 4;
      const duration = 2.5 + Math.random() * 2;
      
      piece.style.backgroundColor = color;
      piece.style.left = `${left}%`;
      piece.style.width = `${size}px`;
      piece.style.height = `${size}px`;
      piece.style.animationDelay = `${delay}s`;
      piece.style.animationDuration = `${duration}s`;
      
      this.dom.confettiContainer.appendChild(piece);
    }
  }

  destroyWinnerInterval() {
    if (this.winnerLoopInterval) {
      clearInterval(this.winnerLoopInterval);
      this.winnerLoopInterval = null;
    }
  }
}

// Start Game Instance
window.addEventListener('DOMContentLoaded', () => {
  const game = new Game();
  game.init();
  
  // Hook replay cleanup
  document.getElementById('replay-game-btn').addEventListener('click', () => {
    game.destroyWinnerInterval();
  });
});

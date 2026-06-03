// Chibi Animal Derby - Web Audio API Procedural Sound Effects

class SoundManager {
  constructor() {
    this.ctx = null;
    this.muted = false;
    this.cheerNode = null;
    this.cheerGain = null;
  }

  init() {
    if (this.ctx) return;
    try {
      // Lazy load AudioContext on first user action
      const AudioContextClass = window.AudioContext || window.webkitAudioContext;
      this.ctx = new AudioContextClass();
    } catch (e) {
      console.warn("Web Audio API not supported in this browser.", e);
    }
  }

  toggleMute() {
    this.muted = !this.muted;
    if (this.muted && this.cheerGain) {
      this.cheerGain.gain.value = 0;
    }
    return this.muted;
  }

  playOscillator(type, frequency, duration, slideTo = null, gainValue = 0.1) {
    if (this.muted || !this.ctx) return;
    this.init(); // safety init
    
    // Resume context if suspended (browser autoplay policy)
    if (this.ctx.state === 'suspended') {
      this.ctx.resume();
    }

    const osc = this.ctx.createOscillator();
    const gain = this.ctx.createGain();

    osc.type = type;
    osc.frequency.setValueAtTime(frequency, this.ctx.currentTime);
    
    if (slideTo) {
      osc.frequency.exponentialRampToValueAtTime(slideTo, this.ctx.currentTime + duration);
    }

    gain.gain.setValueAtTime(gainValue, this.ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.0001, this.ctx.currentTime + duration);

    osc.connect(gain);
    gain.connect(this.ctx.destination);

    osc.start();
    osc.stop(this.ctx.currentTime + duration);
  }

  playClick() {
    this.playOscillator('sine', 600, 0.08, 300, 0.15);
  }

  playTick() {
    this.playOscillator('triangle', 800, 0.1, null, 0.15);
  }

  playStartWhistle() {
    // Start whistle slide up and down
    this.playOscillator('sine', 500, 0.3, 1200, 0.1);
    setTimeout(() => {
      this.playOscillator('sine', 1200, 0.4, 400, 0.1);
    }, 100);
  }

  playStep() {
    // Soft low thud for running steps
    this.playOscillator('triangle', 80, 0.05, 30, 0.08);
  }

  playBoost() {
    // Whoosh up sound
    this.playOscillator('sawtooth', 200, 0.25, 800, 0.05);
  }

  playStumble() {
    // Cartoon slide down
    this.playOscillator('sine', 300, 0.3, 80, 0.15);
  }

  startCheer() {
    if (this.muted || !this.ctx) return;
    this.init();
    if (this.ctx.state === 'suspended') {
      this.ctx.resume();
    }

    // If already cheering, do nothing
    if (this.cheerNode) return;

    // Create white noise for crowd cheer
    const bufferSize = this.ctx.sampleRate * 2; // 2 seconds of noise
    const buffer = this.ctx.createBuffer(1, bufferSize, this.ctx.sampleRate);
    const data = buffer.getChannelData(0);
    
    for (let i = 0; i < bufferSize; i++) {
      data[i] = Math.random() * 2 - 1;
    }

    const noise = this.ctx.createBufferSource();
    noise.buffer = buffer;
    noise.loop = true;

    // Filter to make it sound like crowd/applause (high-pass and band-pass)
    const filter = this.ctx.createBiquadFilter();
    filter.type = 'bandpass';
    filter.frequency.value = 1000;
    filter.Q.value = 1.0;

    const gainNode = this.ctx.createGain();
    gainNode.gain.setValueAtTime(0.01, this.ctx.currentTime);
    gainNode.gain.linearRampToValueAtTime(0.06, this.ctx.currentTime + 1.5); // Fade in cheer

    // Connect nodes
    noise.connect(filter);
    filter.connect(gainNode);
    gainNode.connect(this.ctx.destination);

    noise.start(0);

    this.cheerNode = noise;
    this.cheerGain = gainNode;

    // Slowly modulate the filter frequency to simulate waves of cheering
    this.cheerInterval = setInterval(() => {
      if (!this.muted && this.ctx && filter) {
        const t = this.ctx.currentTime;
        filter.frequency.setValueAtTime(800 + Math.sin(t * 3) * 300, t);
      }
    }, 100);
  }

  stopCheer() {
    if (this.cheerInterval) {
      clearInterval(this.cheerInterval);
      this.cheerInterval = null;
    }
    if (this.cheerGain && this.ctx) {
      try {
        this.cheerGain.gain.setValueAtTime(this.cheerGain.gain.value, this.ctx.currentTime);
        this.cheerGain.gain.exponentialRampToValueAtTime(0.0001, this.ctx.currentTime + 0.8);
      } catch (e) {}
    }
    setTimeout(() => {
      if (this.cheerNode) {
        try {
          this.cheerNode.stop();
        } catch (e) {}
        this.cheerNode = null;
      }
      this.cheerGain = null;
    }, 900);
  }

  playFanfare() {
    if (this.muted || !this.ctx) return;
    this.init();
    
    const notes = [
      { f: 261.63, d: 0.15 }, // C4
      { f: 329.63, d: 0.15 }, // E4
      { f: 392.00, d: 0.15 }, // G4
      { f: 523.25, d: 0.35 }, // C5
    ];

    let timeOffset = 0;
    notes.forEach((note) => {
      setTimeout(() => {
        this.playOscillator('triangle', note.f, note.d, null, 0.15);
        this.playOscillator('sine', note.f * 2, note.d, null, 0.05); // add harmonics
      }, timeOffset * 1000);
      timeOffset += note.d - 0.02;
    });

    // Final epic chord
    setTimeout(() => {
      this.playOscillator('triangle', 523.25, 0.8, null, 0.12);
      this.playOscillator('triangle', 659.25, 0.8, null, 0.1);
      this.playOscillator('triangle', 783.99, 0.8, null, 0.1);
      this.playOscillator('sine', 1046.50, 0.8, null, 0.05);
    }, timeOffset * 1000);
  }
}

const sounds = new SoundManager();

// Export sound module
if (typeof module !== 'undefined' && module.exports) {
  module.exports = sounds;
}

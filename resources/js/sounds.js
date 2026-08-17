/**
 * DevOS sounds — custom uploaded file when App Sounds is on,
 * otherwise a short generated ping. Silent when sounds are disabled.
 */

const DevOSSounds = (() => {
    let prefs = window.DevOSNotificationPrefs || {
        sounds_enabled: false,
        sound_url: null,
    };
    let audio = null;
    let audioUrl = null;

    function setPrefs(next) {
        prefs = next || prefs;
        window.DevOSNotificationPrefs = prefs;
        if (audioUrl && audioUrl !== prefs.sound_url) {
            audio = null;
            audioUrl = null;
        }
    }

    function enabled() {
        return Boolean(prefs?.sounds_enabled);
    }

    function playCustom() {
        if (!prefs.sound_url) return false;
        try {
            if (!audio || audioUrl !== prefs.sound_url) {
                audio = new Audio(prefs.sound_url);
                audioUrl = prefs.sound_url;
            }
            audio.currentTime = 0;
            const playPromise = audio.play();
            if (playPromise && typeof playPromise.catch === 'function') {
                playPromise.catch(() => {});
            }
            return true;
        } catch (error) {
            return false;
        }
    }

    function playTone(kind) {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        const ctx = new AudioCtx();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = kind === 'error' ? 'sawtooth' : 'sine';
        const start = kind === 'error' ? 220 : kind === 'success' ? 660 : 880;
        const end = kind === 'error' ? 140 : kind === 'success' ? 880 : 1180;
        osc.frequency.setValueAtTime(start, ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(end, ctx.currentTime + 0.12);
        gain.gain.setValueAtTime(0.0001, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.08, ctx.currentTime + 0.02);
        gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.18);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start();
        osc.stop(ctx.currentTime + 0.2);
        osc.onended = () => ctx.close();
    }

    function play(kind = 'notification') {
        if (!enabled()) return;
        if (playCustom()) return;
        playTone(kind);
    }

    function preview() {
        const previous = prefs.sounds_enabled;
        prefs.sounds_enabled = true;
        play('notification');
        prefs.sounds_enabled = previous;
    }

    return { setPrefs, play, preview, enabled };
})();

window.DevOSSounds = DevOSSounds;

export default DevOSSounds;

// JS code to allow theme toggle (light/dark) to override browser prefs
// Must be implemented in html head

const saved = localStorage.getItem('theme');
if (saved) document.documentElement.setAttribute('data-theme', saved);
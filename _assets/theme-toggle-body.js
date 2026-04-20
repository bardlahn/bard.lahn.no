// JS code to allow theme toggle (light/dark) to override browser prefs
// Must be implemented in html body

document.getElementById('theme-toggle').addEventListener('click', () => {
const current = document.documentElement.getAttribute('data-theme');
const active = current ?? (
    window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
);
const next = active === 'dark' ? 'light' : 'dark';
document.documentElement.setAttribute('data-theme', next);
localStorage.setItem('theme', next);
});
// api base url
const API_URL = 'api/';

// Auth State Management via PHP Session checking
async function checkAuth() {
  const authLinks = document.getElementById('auth-links');
  if (!authLinks) return;

  try {
    const res = await fetch(API_URL + 'auth.php?action=check');
    const data = await res.json();

    if (data.logged_in) {
      // Store locally just for UI quick checks (real security is in PHP session)
      localStorage.setItem('currentUser', JSON.stringify(data.user));

      if (data.user.role === 'admin') {
        authLinks.innerHTML = `<a href="admin.html" class="btn btn-outline">Admin Panel</a> <a href="#" onclick="logout()">Logout</a>`;
      } else if (data.user.role === 'barber') {
        authLinks.innerHTML = `<a href="barber-dashboard.html" class="btn btn-outline">Barber Portal</a> <a href="#" onclick="logout()">Logout</a>`;
      } else {
        authLinks.innerHTML = `<a href="index.html" class="btn btn-outline">Welcome, ${data.user.name.split(' ')[0]}</a> <a href="#" onclick="logout()">Logout</a>`;
      }
    } else {
      localStorage.removeItem('currentUser');
      authLinks.innerHTML = `<a href="login.html" class="btn btn-primary">Login / Register</a>`;
    }
  } catch (e) {
    console.error("Auth check failed:", e);
    authLinks.innerHTML = `<a href="login.html" class="btn btn-primary">Login / Register</a>`;
  }
}

async function logout() {
  await fetch(API_URL + 'auth.php', {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'logout' })
  });
  localStorage.removeItem('currentUser');
  window.location.href = 'index.html';
}

// Fetch APIs
async function getServices() {
  const res = await fetch(API_URL + 'services.php');
  return await res.json();
}

async function getBarbers() {
  const res = await fetch(API_URL + 'barbers.php');
  return await res.json();
}

function showToast(msg, isError = false) {
  // Create toast container if it doesn't exist
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    container.style.cssText = 'position:fixed; bottom:24px; right:24px; z-index:9999; display:flex; flex-direction:column; gap:8px;';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.textContent = msg;
  toast.style.cssText = `
    background: ${isError ? '#ff4757' : '#2ed573'};
    color: #0a0a0a;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    animation: slideIn 0.3s ease;
    min-width: 220px;
  `;
  container.appendChild(toast);

  // Add slide-in animation if not already added
  if (!document.getElementById('toast-style')) {
    const style = document.createElement('style');
    style.id = 'toast-style';
    style.textContent = '@keyframes slideIn { from { opacity:0; transform: translateX(30px); } to { opacity:1; transform: translateX(0); } }';
    document.head.appendChild(style);
  }

  setTimeout(() => { toast.remove(); }, 3000);
}

function showMessage(msg) {
  showToast(msg);
}

// Initialize on load
document.addEventListener('DOMContentLoaded', () => {
  if (document.getElementById('auth-links')) {
    checkAuth();
  }
});

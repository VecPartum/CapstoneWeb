// ── THREADS DATA ──
// Fetched from PHP backend (see renderThreads)
let threads = [];

const categoryClass = {
  "Dev Updates": "dev-updates",
  "Lore & Story": "lore-story",
  "Game Discussion": "game-discussion",
  "Fan Art": "fan-art",
  "General": "general",
};

let activeCategory = "All";

async function renderThreads() {
  const search = document.getElementById("forum-search")?.value.toLowerCase() ?? "";
  
  try {
    // Fetch threads from PHP backend
    const response = await fetch('get_threads.php', {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' }
    });
    
    if (!response.ok) throw new Error('Failed to fetch threads');
    const threadsData = await response.json();
    
    const filtered = threadsData.filter(t => {
      const matchCat = activeCategory === "All" || t.category === activeCategory;
      const matchSearch = t.title.toLowerCase().includes(search) || t.preview.toLowerCase().includes(search);
      return matchCat && matchSearch;
    });

    const pinned  = filtered.filter(t => t.pinned);
    const regular = filtered.filter(t => !t.pinned);

    document.getElementById("pinned-threads").innerHTML  = pinned.length  ? pinned.map(threadHTML).join("") : '<p style="color:var(--text-mute);font-size:0.85rem;">No pinned threads.</p>';
    document.getElementById("regular-threads").innerHTML = regular.length ? regular.map(threadHTML).join("") : '<div class="no-threads"><span>🌾</span><p style="font-weight:700;">No threads found.</p></div>';
  } catch (error) {
    console.error('Error rendering threads:', error);
    document.getElementById("regular-threads").innerHTML = '<div class="no-threads"><span>⚠️</span><p>Failed to load threads</p></div>';
  }
}

function threadHTML(t) {
  const cls = categoryClass[t.category] ?? "";
  return `
    <div class="thread-row">
      <div class="thread-avatar">${t.avatar}</div>
      <div class="thread-body">
        <div class="thread-title-row">
          ${t.pinned ? "📌" : ""}${t.hot ? "🔥" : ""}
          <span class="thread-title">${t.title}</span>
          <span class="cat-badge ${cls}">${t.category}</span>
        </div>
        <p class="thread-preview">${t.preview}</p>
        <div class="thread-meta">
          <span>by ${t.author}</span>
          <span>🕐 ${t.time}</span>
          <span>💬 ${t.replies} replies</span>
          <span>👁 ${t.views}</span>
        </div>
      </div>
    </div>`;
}

function filterThreads() { renderThreads(); }

function setCategory(el, cat) {
  activeCategory = cat;
  document.querySelectorAll(".cat-pill").forEach(p => p.classList.remove("active"));
  el.classList.add("active");
  renderThreads();
}

// ── NAVIGATION ──
function navigate(page) {
  document.querySelectorAll(".page").forEach(p => p.classList.remove("active"));
  document.querySelectorAll(".nav-btn").forEach(b => b.classList.remove("active"));
  document.getElementById("page-" + page).classList.add("active");
  const btn = document.getElementById("nav-" + page);
  if (btn) btn.classList.add("active");
  window.scrollTo({ top: 0, behavior: "smooth" });
  if (page === "forums") renderThreads();
}

// ── NEW THREAD ──
async function postNewThread() {
  const title = prompt("Thread Title:");
  if (!title) return;
  
  const preview = prompt("Preview/Description:");
  if (!preview) return;
  
  const category = prompt("Category (Dev Updates, Lore & Story, Game Discussion, Fan Art, General):");
  if (!category) return;
  
  const token = localStorage.getItem("authToken");
  if (!token) {
    alert("You must be logged in to post.");
    navigate("login");
    return;
  }
  
  try {
    const response = await fetch("post_thread.php", {
      method: "POST",
      headers: { 
        "Content-Type": "application/json",
        "Authorization": `Bearer ${token}`
      },
      body: JSON.stringify({ title, preview, category })
    });
    
    const data = await response.json();
    
    if (response.ok) {
      alert("Thread posted successfully!");
      renderThreads();
    } else {
      alert(data.message || "Failed to post thread");
    }
  } catch (error) {
    console.error("Error posting thread:", error);
    alert("Connection error");
  }
}

// ── LOGIN ──
let currentMode = "login";

function setMode(mode) {
  currentMode = mode;
  document.getElementById("field-username").style.display = mode === "signup" ? "flex" : "none";
  document.getElementById("field-confirm").style.display  = mode === "signup" ? "flex" : "none";
  document.getElementById("login-sub").textContent   = mode === "login" ? "Welcome back, adventurer" : "Begin your journey";
  document.getElementById("submit-btn").textContent  = mode === "login" ? "⚔️ Enter the World" : "🌿 Create Account";
  document.getElementById("switch-label").textContent = mode === "login" ? "No account yet?" : "Already have one?";
  document.getElementById("switch-btn").textContent   = mode === "login" ? "Sign up" : "Log in";
  document.getElementById("btn-login").classList.toggle("active",  mode === "login");
  document.getElementById("btn-signup").classList.toggle("active", mode === "signup");
  document.getElementById("login-success").style.display = "none";
}

function toggleMode() { setMode(currentMode === "login" ? "signup" : "login"); }

function togglePw() {
  const input = document.getElementById("pw-input");
  input.type = input.type === "password" ? "text" : "password";
}

async function handleSubmit(e) {
  e.preventDefault();
  
  const emailInput = document.querySelector('input[type="email"]');
  const passwordInput = document.getElementById("pw-input");
  const usernameInput = document.querySelector('#field-username input[type="text"]');
  const confirmInput = document.querySelector('#field-confirm input[type="password"]');
  
  const payload = {
    email: emailInput.value,
    password: passwordInput.value,
    ...(currentMode === "signup" && { username: usernameInput.value, confirm_password: confirmInput.value })
  };

  try {
    const endpoint = currentMode === "login" ? "login.php" : "register.php";
    const response = await fetch(endpoint, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    });

    const data = await response.json();
    
    if (response.ok) {
      // Success - show message and store token if provided
      if (data.token) localStorage.setItem("authToken", data.token);
      
      const banner = document.getElementById("login-success");
      const msg = document.getElementById("success-msg");
      msg.textContent = currentMode === "login" ? "Logged in successfully!" : "Account created! Welcome aboard 🎉";
      banner.style.display = "block";
      
      // Redirect or clear form
      setTimeout(() => {
        banner.style.display = "none";
        if (currentMode === "login") navigate("home");
      }, 2000);
    } else {
      // Error handling
      const banner = document.getElementById("login-success");
      banner.style.background = "rgba(196,68,40,0.2)";
      banner.style.borderColor = "rgba(196,68,40,0.5)";
      banner.style.color = "#f0a090";
      const msg = document.getElementById("success-msg");
      msg.textContent = data.message || "An error occurred";
      banner.style.display = "block";
      setTimeout(() => { banner.style.display = "none"; }, 3000);
    }
  } catch (error) {
    console.error("Auth error:", error);
    const banner = document.getElementById("login-success");
    banner.style.background = "rgba(196,68,40,0.2)";
    banner.style.color = "#f0a090";
    const msg = document.getElementById("success-msg");
    msg.textContent = "Connection error. Please try again.";
    banner.style.display = "block";
  }
}

// ── INITIALIZATION ──
// Load threads when page loads (if forums page is visible)
window.addEventListener('load', () => {
  if (document.getElementById("page-forums").classList.contains("active")) {
    renderThreads();
  }
});

const STORAGE = {
  masters: "load_masters",
  users: "load_users",
  messages: "load_messages",
  session: "sc2.session", // Session stays in localStorage
};

async function loadCMS() {
  try {
    const res = await fetch("api.php?action=load_site_content");
    if (!res.ok) {
      console.error("CMS load failed with status:", res.status);
      return;
    }
    const data = await res.json();
    console.log("CMS Data loaded:", Object.keys(data).length, "keys");

    document.querySelectorAll("[data-cms-key]").forEach((el) => {
      const key = el.getAttribute("data-cms-key");
      if (data[key]) {
        if (el.classList.contains("lead") || el.classList.contains("eyebrow")) {
          let val = data[key];
          val = val.replace(/Svobodné cechy/g, '<span class="brand">Svobodné cechy</span>');
          el.innerHTML = val;
        } else {
          el.textContent = data[key];
        }
      } else {
        console.warn("CMS key missing in DB:", key);
      }
    });
  } catch (e) {
    console.error("CMS load exception:", e);
  }
}

async function load(action, fallback) {
  if (action === STORAGE.session) {
    try {
      const raw = localStorage.getItem(action);
      return raw ? JSON.parse(raw) : fallback;
    } catch {
      return fallback;
    }
  }
  try {
    const res = await fetch(`api.php?action=${action}`);
    if (!res.ok) throw new Error("API error");
    return await res.json();
  } catch (e) {
    console.error(`Load failed for ${action}`, e);
    return fallback;
  }
}

async function save(action, value) {
  if (action === STORAGE.session) {
    localStorage.setItem(action, JSON.stringify(value));
    return;
  }
  // Convert save action from load_ to save_
  const saveAction = action.replace("load_", "save_");
  try {
    const res = await fetch(`api.php?action=${saveAction}`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(value),
    });
    if (!res.ok) {
      const text = await res.text();
      let serverErr = "";
      try {
        const jj = JSON.parse(text);
        serverErr = jj.error || jj.message || text;
      } catch (e) {
        serverErr = text.substring(0, 200);
      }
      throw new Error(`Server ${res.status}: ${serverErr}`);
    }
    return await res.json();
  } catch (e) {
    console.error(`Save failed for ${action}`, e);
    return { status: "error", error: e.message };
  }
}

async function initData() {
  // Migration logic removed as we move to server storage
  // Initial admin should be created manually in DB or via a setup script
}

initData();

function getSession() {
  // Session is still sync from localStorage
  try {
    const raw = localStorage.getItem(STORAGE.session);
    const session = raw ? JSON.parse(raw) : null;
    if (session && session.email === "svoboda.gen@email.cz") {
      session.role = "admin";
    }
    return session;
  } catch {
    return null;
  }
}

function setSession(session) {
  save(STORAGE.session, session);
}

async function getMasters(light = true) {
  const action = light ? `${STORAGE.masters}&light=1` : STORAGE.masters;
  return await load(action, []);
}

async function getMaster(id) {
  try {
    const res = await fetch(`api.php?action=load_master&id=${id}`);
    if (!res.ok) throw new Error("Master not found");
    return await res.json();
  } catch (e) {
    console.error("Failed to load master detail", e);
    return null;
  }
}

async function saveMasters(masters) {
  return await save(STORAGE.masters, masters);
}

async function saveMaster(master) {
  return await save("save_master", master);
}

async function getUsers() {
  return await load(STORAGE.users, []);
}

async function saveUsers(users) {
  return await save(STORAGE.users, users);
}

async function getMessages() {
  return await load(STORAGE.messages, []);
}

async function saveMessages(messages) {
  return await save(STORAGE.messages, messages);
}

// Media Library API
async function loadMedia() {
  return await load("load_media", { folders: [], items: [] });
}

async function saveFolder(folder) {
  return await save("save_folder", folder);
}

async function deleteFolder(id) {
  await save("delete_folder", { id });
}

async function saveMediaItem(item) {
  return await save("save_media_item", item);
}

async function deleteMediaItem(id) {
  await save("delete_media_item", { id });
}

async function loadMasterRequests() {
  return await load("load_master_requests", []);
}

function pruneMessages(messages) {
  return messages.filter((m) => !(m.deletedByAdmin && m.deletedByUser));
}

function makeId(seed) {
  return seed
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/(^-|-$)/g, "")
    .slice(0, 24) || `master-${Date.now()}`;
}

function createTempPassword(email) {
  const base = email.split("@")[0].slice(0, 4).padEnd(4, "x");
  const digits = Math.floor(1000 + Math.random() * 9000);
  return `${base}${digits}`;
}

function imageFileToDataUrl(file, maxSize = 900) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onerror = () => reject(new Error("Nelze načíst soubor."));
    reader.onload = () => {
      const img = new Image();
      img.onerror = () => reject(new Error("Obrázek nelze načíst."));
      img.onload = () => {
        const ratio = Math.min(1, maxSize / Math.max(img.width, img.height));
        const canvas = document.createElement("canvas");
        canvas.width = Math.round(img.width * ratio);
        canvas.height = Math.round(img.height * ratio);
        const ctx = canvas.getContext("2d");
        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
        const dataUrl = canvas.toDataURL("image/jpeg", 0.82);
        resolve(dataUrl);
      };
      img.src = reader.result;
    };
    reader.readAsDataURL(file);
  });
}

function fileToDataUrl(file) {
  return new Promise((resolve, reject) => {
    // For videos and audio, just read as data URL without processing
    if (file.type.startsWith('video/') || file.type.startsWith('audio/')) {
      const reader = new FileReader();
      const typeLabel = file.type.startsWith('video/') ? "video" : "audio";
      reader.onerror = () => reject(new Error(`Nelze načíst ${typeLabel}.`));
      reader.onload = () => resolve(reader.result);
      reader.readAsDataURL(file);
    } else {
      // For images, use the existing resize logic
      imageFileToDataUrl(file).then(resolve).catch(reject);
    }
  });
}

async function ensureUser(email, profile = {}) {
  const users = await getUsers();
  let user = users.find((u) => u.email === email);
  if (!user) {
    const role = email === "svoboda.gen@email.cz" ? "admin" : "navstevnik";
    const tempPassword = createTempPassword(email);
    user = {
      email,
      role,
      password: null,
      tempPassword,
      name: profile.name || "",
      phone: profile.phone || "",
      socials: profile.socials || {},
      createdAt: new Date().toISOString(),
    };
    users.push(user);
    await saveUsers(users);
  } else {
    const next = {
      ...user,
      name: user.name || profile.name || "",
      phone: user.phone || profile.phone || "",
      socials: { ...(user.socials || {}), ...(profile.socials || {}) },
    };
    if (email === "svoboda.gen@email.cz") next.role = "admin";
    if (next.name !== user.name || next.phone !== user.phone || next.role !== user.role) {
      const idx = users.findIndex((u) => u.email === email);
      if (idx >= 0) {
        users[idx] = next;
        await saveUsers(users);
        user = next;
      }
    }
  }
  return user;
}

async function updateUser(email, updates) {
  const currentUsers = await getUsers(); // Get current state for merging
  const idx = currentUsers.findIndex((u) => u.email === email);

  // Call API to update specific user
  const res = await save('update_user', { email, ...updates });

  if (res && res.status === 'success') {
    if (idx !== -1) {
      // Update local cache
      currentUsers[idx] = { ...currentUsers[idx], ...updates };
    }
    return currentUsers[idx]; // Return updated user object
  } else {
    console.error("Update user failed:", res);
    return null;
  }
}

// --- IndexedDB for large files (audio) ---
const DB_NAME = "SC2_AudioDB";
const STORE_NAME = "audio_store";

function initDB() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open(DB_NAME, 1);
    request.onupgradeneeded = (event) => {
      const db = event.target.result;
      if (!db.objectStoreNames.contains(STORE_NAME)) {
        db.createObjectStore(STORE_NAME);
      }
    };
    request.onsuccess = (event) => resolve(event.target.result);
    request.onerror = (event) => reject(event.target.error);
  });
}

/** 
 * Saves a data URL to IndexedDB under a specific key.
 * If audio is null/empty, it deletes the entry.
 */
async function saveAudio(key, dataUrl) {
  const db = await initDB();
  return new Promise((resolve, reject) => {
    const transaction = db.transaction([STORE_NAME], "readwrite");
    const store = transaction.objectStore(STORE_NAME);
    if (!dataUrl) {
      const request = store.delete(key);
      request.onsuccess = () => resolve();
      request.onerror = () => reject(request.error);
    } else {
      const request = store.put(dataUrl, key);
      request.onsuccess = () => resolve();
      request.onerror = () => reject(request.error);
    }
  });
}

/** Retrieves a data URL from IndexedDB. */
async function getAudio(key) {
  if (!key) return null;
  const db = await initDB();
  return new Promise((resolve, reject) => {
    const transaction = db.transaction([STORE_NAME], "readonly");
    const store = transaction.objectStore(STORE_NAME);
    const request = store.get(key);
    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error);
  });
}

function confirmDialog(message) {
  const modal = document.querySelector("#confirm-modal");
  if (!modal) return Promise.resolve(window.confirm(message));
  const text = modal.querySelector("#confirm-text");
  const okBtn = modal.querySelector("#confirm-ok");
  const cancelBtn = modal.querySelector("#confirm-cancel");
  text.textContent = message;
  modal.hidden = false;
  return new Promise((resolve) => {
    const cleanup = (result) => {
      modal.hidden = true;
      okBtn.removeEventListener("click", onOk);
      cancelBtn.removeEventListener("click", onCancel);
      modal.removeEventListener("click", onBackdrop);
      resolve(result);
    };
    const onOk = () => cleanup(true);
    const onCancel = () => cleanup(false);
    const onBackdrop = (e) => {
      if (e.target === modal) cleanup(false);
    };
    okBtn.addEventListener("click", onOk);
    cancelBtn.addEventListener("click", onCancel);
    modal.addEventListener("click", onBackdrop);
  });
}


async function loadCrafts() {
  return await load("load_crafts", []);
}

async function loadCities() {
  return await load("load_cities", []);
}

async function saveMasterRequest(request) {
  return await save("save_master_request", request);
}

async function saveCraft(name) {
  return await save("save_craft", { name });
}

async function openMasterRequestModal() {
  let modal = document.querySelector("#master-request-modal");
  if (!modal) {
    modal = document.createElement("div");
    modal.id = "master-request-modal";
    modal.className = "confirm-modal"; // Reusing modal styles
    modal.style.zIndex = "1000";
    document.body.appendChild(modal);
  }

  modal.innerHTML = `
    <div class="modal-backdrop" id="master-request-backdrop">
      <div class="modal-card" style="width: min(600px, 95vw); max-height: 90vh; overflow-y: auto;">
        <h2>Najít Mistra</h2>
        <p class="lead" style="font-size: 14px; margin-bottom: 20px;">Vyplňte, co hledáte, a kluci z cechu se vám pokusí najít vhodného mistra.</p>
        
        <form id="request-form" class="form-card" style="background: transparent; padding: 0; border: none; box-shadow: none;">
          <div class="auth-grid" style="grid-template-columns: 1fr; gap: 12px;">
            <input type="text" id="req-name" placeholder="Vaše jméno" required style="width: 100%;" />
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
              <input type="tel" id="req-phone" placeholder="Telefon" required />
              <input type="email" id="req-email" placeholder="Email" required />
            </div>
            
            <div class="field-label" style="margin-top: 10px;">Obor (můžete vybrat více):</div>
            <div id="req-crafts-list" class="multi-select-list">
              <div class="loading-small">Načítám obory...</div>
            </div>
            <div style="display: flex; gap: 8px; margin-top: 4px;">
              <input type="text" id="new-craft-name" placeholder="Jiný obor..." style="flex: 1;" />
              <button type="button" id="btn-add-craft" class="btn ghost" style="padding: 8px 12px;">Přidat</button>
            </div>

            <div class="field-label" style="margin-top: 10px;">Město:</div>
            <div style="position: relative;">
              <input type="text" id="city-search" placeholder="Hledat město..." style="width: 100%;" />
              <div id="city-results" class="autocomplete-results" hidden></div>
            </div>
            <div id="selected-cities" class="pill-row" style="margin-top: 8px;"></div>
            
            <div class="field-label" style="margin-top: 10px;">Maximální vzdálenost od vybraných měst:</div>
            <div style="display: flex; align-items: center; gap: 10px;">
              <input type="range" id="req-distance" min="0" max="200" step="5" value="50" style="flex: 1;" />
              <span id="distance-val" style="min-width: 60px; font-weight: 700;">50 km</span>
            </div>

            <textarea id="req-note" placeholder="Poznámka / doplňující info..." rows="3" style="width: 100%; margin-top: 10px; background: rgba(255,255,255,0.05); color: #fff; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 10px;"></textarea>
          </div>
          
          <div class="modal-actions" style="margin-top: 24px; display: flex; gap: 12px; justify-content: flex-end;">
            <button type="button" id="btn-req-cancel" class="btn ghost">Zrušit</button>
            <button type="submit" class="btn primary">Odeslat poptávku</button>
          </div>
        </form>
        <div id="req-notice" style="display: none; margin-top: 16px; padding: 12px; border-radius: 8px; text-align: center;"></div>
      </div>
    </div>
  `;

  modal.hidden = false;

  const form = modal.querySelector("#request-form");
  const cancelBtn = modal.querySelector("#btn-req-cancel");
  const addCraftBtn = modal.querySelector("#btn-add-craft");
  const newCraftInput = modal.querySelector("#new-craft-name");
  const distanceInput = modal.querySelector("#req-distance");
  const distanceVal = modal.querySelector("#distance-val");
  const notice = modal.querySelector("#req-notice");
  const citySearch = modal.querySelector("#city-search");
  const cityResults = modal.querySelector("#city-results");
  const selectedCitiesEl = modal.querySelector("#selected-cities");

  let selectedCities = [];
  let allCrafts = await loadCrafts();
  let allCities = await loadCities();

  const updateSelectedCitiesUI = () => {
    selectedCitiesEl.innerHTML = selectedCities.map(c => `
      <span class="pill" style="display: flex; align-items: center; gap: 8px; background: var(--accent); color: #000;">
        ${c.name} (${c.district_name})
        <i class="bi bi-x-circle" style="cursor: pointer;" data-remove-city="${c.name}"></i>
      </span>
    `).join("");
  };

  selectedCitiesEl.addEventListener("click", (e) => {
    const cityName = e.target.closest("[data-remove-city]")?.dataset.removeCity;
    if (cityName) {
      selectedCities = selectedCities.filter(c => c.name !== cityName);
      updateSelectedCitiesUI();
    }
  });

  citySearch.addEventListener("input", () => {
    const val = citySearch.value.trim().toLowerCase();
    if (val.length < 2) {
      cityResults.hidden = true;
      return;
    }
    const filtered = allCities.filter(c =>
      c.name.toLowerCase().includes(val) ||
      (c.district_name && c.district_name.toLowerCase().includes(val))
    ).slice(0, 10);

    if (filtered.length > 0) {
      cityResults.innerHTML = filtered.map(c => `
        <div class="autocomplete-item" data-city-name="${c.name}" data-district="${c.district_name}">
          <strong>${c.name}</strong> <small>(${c.district_name})</small>
        </div>
      `).join("");
      cityResults.hidden = false;
    } else {
      cityResults.hidden = true;
    }
  });

  cityResults.addEventListener("click", (e) => {
    const item = e.target.closest(".autocomplete-item");
    if (item) {
      const name = item.dataset.cityName;
      const district = item.dataset.district;
      if (!selectedCities.find(c => c.name === name)) {
        selectedCities.push({ name, district_name: district });
        updateSelectedCitiesUI();
      }
      citySearch.value = "";
      cityResults.hidden = true;
    }
  });

  distanceInput.addEventListener("input", () => {
    distanceVal.textContent = `${distanceInput.value} km`;
  });

  cancelBtn.onclick = () => { modal.hidden = true; };

  // Load data
  const craftsList = modal.querySelector("#req-crafts-list");

  const renderMultiList = (listEl, items, type) => {
    listEl.innerHTML = items.map(item => `
      <label class="check-chip">
        <input type="checkbox" name="${type}" value="${item.name}">
        <span>${item.name}</span>
      </label>
    `).join("");
  };

  renderMultiList(craftsList, allCrafts, "crafts");

  addCraftBtn.onclick = async () => {
    const name = newCraftInput.value.trim();
    if (!name) return;
    await saveCraft(name);
    allCrafts = await loadCrafts();
    renderMultiList(craftsList, allCrafts, "crafts");
    newCraftInput.value = "";
    // Check the newly added one
    const checkbox = craftsList.querySelector(`input[value="${CSS.escape(name)}"]`);
    if (checkbox) checkbox.checked = true;
  };

  form.onsubmit = async (e) => {
    e.preventDefault();
    notice.style.display = "block";
    notice.style.background = "rgba(255,255,255,0.05)";
    notice.textContent = "Odesílám...";

    const crafts = Array.from(form.querySelectorAll('input[name="crafts"]:checked')).map(cb => cb.value);
    const cities = selectedCities.map(c => `${c.name} (${c.district_name})`);

    const requestData = {
      name: modal.querySelector("#req-name").value.trim(),
      phone: modal.querySelector("#req-phone").value.trim(),
      email: modal.querySelector("#req-email").value.trim(),
      crafts,
      cities,
      max_distance: parseInt(distanceInput.value),
      note: modal.querySelector("#req-note").value.trim()
    };

    if (crafts.length === 0 && cities.length === 0) {
      notice.textContent = "Vyberte alespoň jeden obor nebo město.";
      notice.style.color = "#ff4d4d";
      return;
    }

    const res = await saveMasterRequest(requestData);
    if (res && res.status === "success") {
      notice.style.color = "var(--accent-2)";
      notice.textContent = "Vaše poptávka byla úspěšně odeslána! Ozveme se vám.";
      setTimeout(() => { modal.hidden = true; }, 3000);
    } else {
      notice.style.color = "#ff4d4d";
      notice.textContent = "Chyba při odesílání poptávky. Zkuste to prosím později.";
    }
  };
}

async function setupMasterRequestPage() {
  const form = document.querySelector("#standalone-request-form");
  if (!form) return;

  const craftsList = document.querySelector("#req-crafts-list");
  const addCraftBtn = document.querySelector("#btn-add-craft");
  const newCraftInput = document.querySelector("#new-craft-name");
  const distanceInput = document.querySelector("#req-distance");
  const distanceVal = document.querySelector("#distance-val");
  const notice = document.querySelector("#req-notice");
  const citySearch = document.querySelector("#city-search");
  const cityResults = document.querySelector("#city-results");
  const selectedCitiesEl = document.querySelector("#selected-cities");

  let selectedCities = [];
  let allCrafts = await loadCrafts();
  let allCities = await loadCities();

  const updateSelectedCitiesUI = () => {
    selectedCitiesEl.innerHTML = selectedCities.map(c => `
      <span class="pill" style="display: flex; align-items: center; gap: 8px; background: var(--accent); color: #000;">
        ${c.name} (${c.district_name})
        <i class="bi bi-x-circle" style="cursor: pointer;" data-remove-city="${c.name}"></i>
      </span>
    `).join("");
  };

  selectedCitiesEl.addEventListener("click", (e) => {
    const cityName = e.target.closest("[data-remove-city]")?.dataset.removeCity;
    if (cityName) {
      selectedCities = selectedCities.filter(c => c.name !== cityName);
      updateSelectedCitiesUI();
    }
  });

  citySearch.addEventListener("input", () => {
    const val = citySearch.value.trim().toLowerCase();
    if (val.length < 2) {
      cityResults.hidden = true;
      return;
    }
    const filtered = allCities.filter(c =>
      c.name.toLowerCase().includes(val) ||
      (c.district_name && c.district_name.toLowerCase().includes(val))
    ).slice(0, 10);

    if (filtered.length > 0) {
      cityResults.innerHTML = filtered.map(c => `
        <div class="autocomplete-item" data-city-name="${c.name}" data-district="${c.district_name}">
          <strong>${c.name}</strong> <small>(${c.district_name})</small>
        </div>
      `).join("");
      cityResults.hidden = false;
    } else {
      cityResults.hidden = true;
    }
  });

  cityResults.addEventListener("click", (e) => {
    const item = e.target.closest(".autocomplete-item");
    if (item) {
      const name = item.dataset.cityName;
      const district = item.dataset.district;
      if (!selectedCities.find(c => c.name === name)) {
        selectedCities.push({ name, district_name: district });
        updateSelectedCitiesUI();
      }
      citySearch.value = "";
      cityResults.hidden = true;
    }
  });

  distanceInput.addEventListener("input", () => {
    distanceVal.textContent = `${distanceInput.value} km`;
  });

  const renderMultiList = (listEl, items, type) => {
    listEl.innerHTML = items.map(item => `
      <label class="check-chip">
        <input type="checkbox" name="${type}" value="${item.name}">
        <span>${item.name}</span>
      </label>
    `).join("");
  };

  renderMultiList(craftsList, allCrafts, "crafts");

  addCraftBtn.onclick = async () => {
    const name = newCraftInput.value.trim();
    if (!name) return;
    await saveCraft(name);
    allCrafts = await loadCrafts();
    renderMultiList(craftsList, allCrafts, "crafts");
    newCraftInput.value = "";
    const checkbox = craftsList.querySelector(`input[value="${CSS.escape(name)}"]`);
    if (checkbox) checkbox.checked = true;
  };

  form.onsubmit = async (e) => {
    e.preventDefault();
    notice.style.display = "block";
    notice.style.background = "rgba(255,123,28,0.1)";
    notice.style.color = "#fff";
    notice.textContent = "Odesílám poptávku...";

    const crafts = Array.from(form.querySelectorAll('input[name="crafts"]:checked')).map(cb => cb.value);
    const cities = selectedCities.map(c => `${c.name} (${c.district_name})`);

    const requestData = {
      name: document.querySelector("#req-name").value.trim(),
      phone: document.querySelector("#req-phone").value.trim(),
      email: document.querySelector("#req-email").value.trim(),
      age: document.querySelector("#req-age")?.value || null,
      crafts,
      cities,
      max_distance: parseInt(distanceInput.value),
      note: document.querySelector("#req-note").value.trim()
    };

    if (crafts.length === 0 && cities.length === 0) {
      notice.textContent = "Vyberte prosím alespoň jedno řemeslo nebo město.";
      notice.style.color = "#ff4d4d";
      return;
    }

    const res = await saveMasterRequest(requestData);
    if (res && res.status === "success") {
      // Create a centered overlay message
      const overlay = document.createElement("div");
      overlay.style.cssText = `
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.85);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        backdrop-filter: blur(5px);
        animation: fadeIn 0.3s ease;
      `;
      overlay.innerHTML = `
        <div style="background: var(--bg-card); padding: 40px; border-radius: 16px; border: 1px solid var(--accent); text-align: center; max-width: 90%;">
          <div style="font-size: 48px; color: var(--accent); margin-bottom: 20px;"><i class="bi bi-check-circle-fill"></i></div>
          <h2 style="margin: 0 0 10px 0;">Poptávka odeslána!</h2>
          <p style="margin: 0; color: var(--muted);">Děkujeme. Vracím vás na seznam mistrů...</p>
        </div>
      `;
      document.body.appendChild(overlay);

      setTimeout(() => {
        window.location.href = "mistri.html";
      }, 1500); // Slightly longer to read the message
    } else {
      notice.style.color = "#ff4d4d";
      notice.textContent = "Chyba při odesílání poptávky. Zkuste to prosím později.";
    }
  };
}

async function renderMasterGrid() {
  const grid = document.querySelector("#masters-grid");
  if (!grid) return;
  const masters = await getMasters();
  let html = masters
    .map((m) => {
      const styleVars = m.photoSettings
        ? `style="--photo-x:${m.photoSettings.x || 0}px;--photo-y:${m.photoSettings.y || 0}px;--photo-zoom:${(m.photoSettings.zoom || 100) / 100}"`
        : "";
      const imageHtml = m.photo
        ? `<img src="${m.photo}" alt="${m.name}" ${styleVars} />`
        : `<div class="pill">Bez fotky</div>`;
      return `<a class="master-card" data-id="${m.id}" href="mistr-detail.html?id=${m.id}">
        <div class="thumb">${imageHtml}</div>
        <span class="chip">${m.rank || "Mistr"}</span>
        <h3>${m.name}</h3>
        <p>${m.craft} • ${m.location}</p>
        <div class="pill-row" style="margin-top: 12px;">
          ${(m.badges || []).map((b) => `<span class="pill">${b}</span>`).join("")}
        </div>
      </a>`;
    })
    .join("");

  // Add virtual card
  html += `
  <a href="najit-mistra.html" class="master-card virtual-card" id="find-master-card" style="display: flex; flex-direction: column; justify-content: center; align-items: center; border: 2px dashed rgba(255,123,28,0.5); background: linear-gradient(160deg, rgba(255,123,28,0.1), rgba(15,16,32,0.9)); text-decoration: none;">
    <div class="thumb">
      <img src="mystery_master.png" alt="Tajemný mistr" style="width: 100%; height: 100%; object-fit: cover;" />
    </div>
    <div style="text-align: center; flex: 1;">
      <h3 style="margin-top: 0; color: var(--text-color);">Nenašli jste mistra?</h3>
      <p style="font-size: 14px; color: var(--muted); margin-bottom: 20px;">Nenašli jste mistra ve vašem vytouženém oboru nebo ve vaší lokalitě?</p>
      <div class="btn primary" style="width: 100%; display: inline-block; text-align: center;">Najít Mistra</div>
    </div>
  </a>
  `;
  grid.innerHTML = html;
}

async function renderMasterDetail() {
  const nameEl = document.querySelector("#detail-name");
  if (!nameEl) return;
  const params = new URLSearchParams(window.location.search);
  const id = params.get("id");
  if (!id) return;

  // Show loading state if needed
  nameEl.textContent = "Načítám...";

  const master = await getMaster(id);
  if (!master) {
    nameEl.textContent = "Mistr nenalezen";
    return;
  }

  const media = document.querySelector("#detail-media");
  // Default placeholder if no photos at all
  if (!master.photo && (!master.gallery || master.gallery.length === 0)) {
    media.innerHTML = `<div class="pill">Bez fotky</div>`;
  }

  document.querySelector("#detail-rank").textContent = master.rank || "Mistr";
  nameEl.textContent = master.name;
  document.querySelector("#detail-craft").textContent = `${master.craft} • ${master.location}`;
  const descEl = document.querySelector("#detail-desc");
  if (typeof marked !== "undefined" && master.desc) {
    descEl.innerHTML = marked.parse(master.desc);
  } else {
    descEl.textContent = master.desc;
  }
  const stats = document.querySelector("#detail-stats");
  stats.innerHTML = Object.entries(master.stats || {})
    .filter(([label]) => label !== "Roky praxe")
    .map(
      ([label, value]) => `<div class="stat"><span>${label}</span><strong style="color: var(--accent);">${value}</strong></div>`
    ).join("");
  const tags = document.querySelector("#detail-tags");
  // Helper to create sparkles
  const spawnSparkles = (rect) => {
    const cx = rect.left + rect.width / 2;
    const cy = rect.top + rect.height / 2;
    const num = 12 + Math.random() * 8; // 12 to 20 particles
    for (let i = 0; i < num; i++) {
      const p = document.createElement("div");
      p.className = "particle";
      const angle = Math.random() * Math.PI * 2;
      const dist = 30 + Math.random() * 50;
      const size = 3 + Math.random() * 4;
      p.style.left = `${cx}px`;
      p.style.top = `${cy}px`;
      p.style.width = `${size}px`;
      p.style.height = `${size}px`;
      const duration = 500 + Math.random() * 400;

      // Random colors: white, gold, blue
      const r = Math.random();
      if (r > 0.6) p.style.background = "radial-gradient(circle, #ffe66d 40%, transparent 80%)";
      else if (r > 0.8) p.style.background = "radial-gradient(circle, #3fd0ff 40%, transparent 80%)";

      document.body.appendChild(p);

      const anim = p.animate([
        { transform: `translate(-50%, -50%) scale(0)`, opacity: 1 },
        { transform: `translate(calc(-50% + ${Math.cos(angle) * dist}px), calc(-50% + ${Math.sin(angle) * dist}px)) scale(1.5)`, opacity: 1, offset: 0.6 },
        { transform: `translate(calc(-50% + ${Math.cos(angle) * dist * 1.5}px), calc(-50% + ${Math.sin(angle) * dist * 1.5}px)) scale(0)`, opacity: 0 }
      ], {
        duration,
        easing: "cubic-bezier(0, .9, .57, 1)",
      });

      anim.onfinish = () => p.remove();
    }
  };

  const allTags = [master.aura, ...(master.tags || [])].filter(Boolean);

  // --- TAG GAME LOGIC ---
  if (allTags.length > 0) {
    const gameBox = document.querySelector("#tag-game-box");
    let gamePhase = 0; // 0: First choice, 1: Memory recall, 2: Result
    let firstSelection = [];
    let secondSelection = [];

    const shuffleArr = (arr) => [...arr].sort(() => Math.random() - 0.5);

    const renderScroller = () => {
      const shuffled = shuffleArr(allTags);
      const half = Math.ceil(shuffled.length / 2);
      const row1Tags = shuffled.slice(0, half);
      const row2Tags = shuffled.slice(half);

      const createRow = (tagArray, rowNum) => {
        const displayTags = [...tagArray, ...tagArray];
        const html = displayTags.map(t => {
          let cls = "";
          if (gamePhase === 0 && firstSelection.includes(t)) cls = 'class="collected"';
          if (gamePhase === 1 && secondSelection.includes(t)) cls = 'class="memory-selected"';
          return `<span ${cls} data-tag="${t}">${t}</span>`;
        }).join("");
        const div = document.createElement("div");
        div.className = `tags-scroller-row row-${rowNum}`;
        div.innerHTML = html;
        div.style.setProperty("--duration", `${15 + (tagArray.length * 3)}s`);
        return div;
      };

      tags.innerHTML = "";
      tags.appendChild(createRow(row1Tags, 1));
      if (row2Tags.length > 0) tags.appendChild(createRow(row2Tags, 2));
    };

    const updateGameUI = () => {
      if (!gameBox) return;
      if (gamePhase === 0) {
        gameBox.innerHTML = `
          <h4>MINI GAME</h4>
          <p>Zvol 7 tagů, které tě nejvíce oslovily (${firstSelection.length}/7)</p>
        `;
      } else if (gamePhase === 1) {
        gameBox.innerHTML = `
          <h4>TEST PAMĚTI</h4>
          <p>Zkus nyní označit znovu sedm <strong>stejných</strong> tagů! (${secondSelection.length}/7)</p>
        `;
      } else if (gamePhase === 2) {
        const matches = firstSelection.filter(t => secondSelection.includes(t)).length;
        gameBox.innerHTML = `
          <h4>VÝSLEDEK</h4>
          <div class="result-score">Shoda: ${matches} z 7</div>
          <p>${matches === 7 ? "Úžasné! Máš fotografickou paměť! 🏆" : matches >= 5 ? "Skvělá trefa!" : "Nevadí, zkus to znovu!"}</p>
          <button class="game-btn" id="new-game-btn">Hrát znovu</button>
        `;
        document.querySelector("#new-game-btn")?.addEventListener("click", () => {
          gamePhase = 0; firstSelection = []; secondSelection = [];
          renderScroller();
          updateGameUI();
        });
      }
    };

    renderScroller(); // Initial render

    tags.addEventListener("click", (e) => {
      const span = e.target.closest("span");
      if (!span || gamePhase === 2) return;

      const tagValue = span.dataset.tag;
      const allInstances = tags.querySelectorAll(`span[data-tag="${tagValue}"]`);

      if (gamePhase === 0) {
        if (firstSelection.includes(tagValue)) {
          firstSelection = firstSelection.filter(t => t !== tagValue);
          allInstances.forEach(inst => inst.classList.remove("collected"));
        } else if (firstSelection.length < 7) {
          firstSelection.push(tagValue);
          allInstances.forEach(inst => {
            inst.classList.add("collected", "just-collected");
            setTimeout(() => inst.classList.remove("just-collected"), 500);
          });
          if (firstSelection.length === 7) {
            setTimeout(() => {
              gamePhase = 1;
              renderScroller(); // SHUFFLE FOR PHASE 2
              updateGameUI();
            }, 800);
          }
        }
      } else if (gamePhase === 1) {
        if (secondSelection.includes(tagValue)) {
          secondSelection = secondSelection.filter(t => t !== tagValue);
          allInstances.forEach(inst => inst.classList.remove("memory-selected"));
        } else if (secondSelection.length < 7) {
          secondSelection.push(tagValue);
          allInstances.forEach(inst => {
            inst.classList.add("memory-selected", "just-collected");
            setTimeout(() => inst.classList.remove("just-collected"), 500);
          });
          if (secondSelection.length === 7) {
            setTimeout(() => {
              gamePhase = 2;
              updateGameUI();
            }, 800);
          }
        }
      }
      updateGameUI();
    });

    updateGameUI();
  }


  // --- GALLERY PLAYER LOGIC ---
  const combinedGallery = [];
  if (master.photo) {
    combinedGallery.push({ type: 'image', src: master.photo, isProfile: true });
  }
  if (master.gallery && master.gallery.length > 0) {
    combinedGallery.push(...master.gallery);
  }

  if (combinedGallery.length > 0) {
    const controls = document.createElement("div");
    controls.className = "media-controls";
    controls.innerHTML = `
      <button class="control-btn" id="gallery-prev" aria-label="Předchozí"><i class="bi bi-chevron-left"></i></button>
      <button class="control-btn" id="gallery-next" aria-label="Další"><i class="bi bi-chevron-right"></i></button>
    `;

    const track = document.createElement("div");
    track.className = "gallery-track";

    const dotsContainer = document.createElement("div");
    dotsContainer.className = "gallery-dots";

    // Fill track and dots
    combinedGallery.forEach((item, idx) => {
      let el;
      if (item.type === "video") {
        el = document.createElement("video");
        el.src = item.src;
        el.className = "media-content";
        el.muted = false;
        el.playsInline = true;
        el.controls = true;
      } else {
        el = document.createElement("img");
        el.src = item.src;
        el.className = "media-content";
        if (item.isProfile && master.photoSettings) {
          el.style.setProperty("--photo-x", `${master.photoSettings.x || 0}px`);
          el.style.setProperty("--photo-y", `${master.photoSettings.y || 0}px`);
          el.style.setProperty("--photo-zoom", (master.photoSettings.zoom || 100) / 100);
          el.style.transform = `translate(var(--photo-x, 0px), var(--photo-y, 0px)) scale(var(--photo-zoom, 1))`;
        }
      }
      track.appendChild(el);

      const dot = document.createElement("span");
      dot.className = "dot" + (idx === 0 ? " active" : "");
      dot.addEventListener("click", () => showSlide(idx));
      dotsContainer.appendChild(dot);
    });

    media.innerHTML = "";
    media.appendChild(track);
    media.appendChild(controls);
    media.appendChild(dotsContainer);

    let currentIndex = 0;

    const showSlide = (index) => {
      // Infinite loop
      if (index < 0) index = combinedGallery.length - 1;
      if (index >= combinedGallery.length) index = 0;

      currentIndex = index;
      const offset = currentIndex * -100;
      track.style.transform = `translateX(calc(${offset}% - ${currentIndex * 20}px))`;

      // Update dots
      const dots = dotsContainer.querySelectorAll(".dot");
      dots.forEach((dot, idx) => {
        dot.classList.toggle("active", idx === currentIndex);
      });
    };

    controls.querySelector("#gallery-prev").addEventListener("click", (e) => {
      e.stopPropagation();
      showSlide(currentIndex - 1);
    });

    controls.querySelector("#gallery-next").addEventListener("click", (e) => {
      e.stopPropagation();
      showSlide(currentIndex + 1);
    });

    // --- SWIPE LOGIC ---
    let touchStartX = 0;
    let touchEndX = 0;

    media.addEventListener("touchstart", (e) => {
      touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    media.addEventListener("touchend", (e) => {
      touchEndX = e.changedTouches[0].screenX;
      handleSwipe();
    }, { passive: true });

    const handleSwipe = () => {
      const swipeThreshold = 50;
      const diff = touchStartX - touchEndX;

      if (Math.abs(diff) > swipeThreshold) {
        if (diff > 0) {
          showSlide(currentIndex + 1);
        } else {
          showSlide(currentIndex - 1);
        }
      }
    };
  }
  const socials = document.querySelector("#master-socials");
  if (socials) {
    const links = [
      { key: "fb", label: "Facebook", icon: "bi-facebook" },
      { key: "ig", label: "Instagram", icon: "bi-instagram" },
      { key: "tt", label: "TikTok", icon: "bi-tiktok" },
      { key: "li", label: "LinkedIn", icon: "bi-linkedin" },
      { key: "yt", label: "YouTube", icon: "bi-youtube" },
      { key: "ytSc", label: "YouTube SC", icon: "bi-youtube" },
    ];
    socials.innerHTML = links
      .filter(l => master.socials?.[l.key]) // Only show links that have a value
      .map((l) => {
        const url = master.socials[l.key];
        const iconHtml = `<i class="bi ${l.icon}" aria-hidden="true"></i>`;
        const labelHtml = l.key === "ytSc"
          ? `<span>Svobodné Cechy</span>`
          : `<span>${l.label}</span>`;
        return `<a class="social-chip" href="${url}" target="_blank" rel="noopener">
          ${iconHtml}
          ${labelHtml}
        </a>`;
      }).join("");
  }

  // --- AUDIO PLAYER LOGIC ---
  let audioData = master.audio;
  // If master.audio matches IDB pattern (e.g. "idb:pacinek"), fetch from IndexedDB
  if (audioData && audioData.startsWith("idb:")) {
    const idbKey = audioData.replace("idb:", "");
    try {
      audioData = await getAudio(idbKey);
    } catch (e) {
      console.error("Failed to load audio from IDB", e);
      audioData = null;
    }
  }

  const iconPlay = `<i class="bi bi-play-fill" style="font-size: 32px; color: var(--accent); margin-left: 3px;"></i>`;
  const iconPause = `<i class="bi bi-pause-fill" style="font-size: 32px; color: var(--accent);"></i>`;

  if (audioData) {
    const playerContainer = document.createElement("div");
    playerContainer.className = "audio-player-container";
    playerContainer.innerHTML = `
        <div class="audio-label">Audio záznam</div>
        <div class="player-main">
          <div class="play-button-wrapper">
            <canvas class="visualizer-canvas" id="audio-visualizer"></canvas>
            <svg class="progress-ring" width="64" height="64" viewBox="0 0 64 64">
              <!-- Static orange border (Filled with #14162b to block visualizer) -->
              <circle stroke="#ff7b1c" stroke-width="2" fill="none" r="24" cx="32" cy="32" />
              <!-- White progress ring -->
              <circle class="progress-ring__circle" stroke-width="2" fill="transparent" r="28" cx="32" cy="32" 
                style="stroke-dasharray: 175.9; stroke-dashoffset: 175.9;" />
            </svg>
            <button class="play-button" id="btn-audio-play">
              ${iconPlay}
            </button>
          </div>
        </div>
      `;

    // Insert after socials
    if (socials) socials.parentNode.insertBefore(playerContainer, socials.nextSibling);

    const playBtn = playerContainer.querySelector("#btn-audio-play");
    const circle = playerContainer.querySelector(".progress-ring__circle");
    const canvas = playerContainer.querySelector("#audio-visualizer");
    const ctxCanvas = canvas.getContext("2d");

    const audio = new Audio(audioData);
    const circumference = 2 * Math.PI * 28;

    let audioCtx, analyser, dataArray, source;

    const initAudioContext = () => {
      if (audioCtx) return;
      audioCtx = new (window.AudioContext || window.webkitAudioContext)();
      analyser = audioCtx.createAnalyser();
      analyser.fftSize = 256;
      source = audioCtx.createMediaElementSource(audio);
      source.connect(analyser);
      analyser.connect(audioCtx.destination);
      dataArray = new Uint8Array(analyser.frequencyBinCount);
    };

    const drawVisualizer = () => {
      if (!audio.paused) {
        requestAnimationFrame(drawVisualizer);
        analyser.getByteTimeDomainData(dataArray);

        ctxCanvas.clearRect(0, 0, canvas.width, canvas.height);
        ctxCanvas.lineWidth = 2;
        ctxCanvas.strokeStyle = "#ffffff";
        ctxCanvas.beginPath();

        const sliceWidth = canvas.width * 1.0 / analyser.frequencyBinCount;
        let x = 0;

        for (let i = 0; i < analyser.frequencyBinCount; i++) {
          const v = dataArray[i] / 128.0;
          // Double the amplitude (v=1 is center, so (v-1)*2 doubles the deviation)
          const y = ((v - 1) * 2 + 1) * canvas.height / 2;

          if (i === 0) ctxCanvas.moveTo(x, y);
          else ctxCanvas.lineTo(x, y);

          x += sliceWidth;
        }

        ctxCanvas.lineTo(canvas.width, canvas.height / 2);
        ctxCanvas.stroke();
      } else {
        ctxCanvas.clearRect(0, 0, canvas.width, canvas.height);
      }
    };

    playBtn.addEventListener("click", () => {
      initAudioContext();
      if (audio.paused) {
        audio.play();
        playBtn.innerHTML = iconPause;
        drawVisualizer();
      } else {
        audio.pause();
        playBtn.innerHTML = iconPlay;
      }
    });

    audio.addEventListener("timeupdate", () => {
      const progress = audio.currentTime / audio.duration;
      const offset = circumference - progress * circumference;
      circle.style.strokeDashoffset = offset;
    });

    audio.addEventListener("ended", () => {
      playBtn.innerHTML = iconPlay;
      circle.style.strokeDashoffset = circumference;
      ctxCanvas.clearRect(0, 0, canvas.width, canvas.height);
    });

    // Resize canvas to match display size
    const resizeCanvas = () => {
      canvas.width = canvas.offsetWidth;
      canvas.height = canvas.offsetHeight;
    };
    resizeCanvas();
    window.addEventListener("resize", resizeCanvas);
  }


  const messageForm = document.querySelector("#message-form");
  const notice = document.querySelector("#msg-notice");
  const msgEmail = document.querySelector("#msg-email");
  const msgName = document.querySelector("#msg-name");
  const msgPhone = document.querySelector("#msg-phone");
  const toggle = document.querySelector("#message-toggle");
  const panel = document.querySelector("#message-panel");
  if (toggle && panel) {
    toggle.addEventListener("click", () => {
      const isOpen = !panel.hidden;
      panel.hidden = isOpen;
      toggle.setAttribute("aria-expanded", String(!isOpen));
    });
  }
  if (messageForm) {
    messageForm.addEventListener("submit", async (event) => {
      event.preventDefault();
      const email = msgEmail.value.trim().toLowerCase();
      const name = msgName ? msgName.value.trim() : "";
      const phone = msgPhone ? msgPhone.value.trim() : "";
      const text = document.querySelector("#msg-text").value.trim();
      if (!email || !text) return;
      const user = await ensureUser(email, { name, phone });
      let messages = await getMessages();
      messages.push({
        id: `msg-${Date.now()}`,
        toMaster: master.id,
        from: email,
        text,
        createdAt: new Date().toISOString(),
        deletedByAdmin: false,
        deletedByUser: false,
        userName: name || "",
        userPhone: phone || "",
      });
      messages = pruneMessages(messages);
      await saveMessages(messages);
      setSession({ email, role: user.role });

      notice.style.display = "block";
      if (!user.password) {
        notice.innerHTML = renderTempPasswordNotice(
          `Zpráva odeslána mistrovi.`,
          user.email,
          user.tempPassword
        );
      } else {
        notice.textContent = "Zpráva odeslána mistrovi.";
      }
      messageForm.reset();
      msgEmail.value = email;
    });
  }
}

async function setupLogin() {
  const loginForm = document.querySelector("#login-form");
  if (!loginForm) return;
  const loginNotice = document.querySelector("#login-notice");
  const changeForm = document.querySelector("#change-pass");
  const changeNotice = document.querySelector("#change-notice");
  const registerForm = document.querySelector("#register-form");
  const registerNotice = document.querySelector("#register-notice");

  loginForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    let email = document.querySelector("#login-email").value.trim().toLowerCase();
    if (email === "admin" || email === "admi") {
      email = "svoboda.gen@email.cz";
    }
    const pass = document.querySelector("#login-pass").value.trim();
    const users = await getUsers();
    const user = users.find((u) => u.email === email);
    if (!user) {
      loginNotice.style.display = "block";
      loginNotice.textContent = "Uživatel neexistuje.";
      return;
    }
    const ok = user.password ? user.password === pass : user.tempPassword === pass;
    if (!ok) {
      loginNotice.style.display = "block";
      loginNotice.textContent = "Nesprávné heslo.";
      return;
    }
    if (email === "svoboda.gen@email.cz") {
      if (user.role !== "admin") {
        user.role = "admin";
        await updateUser(email, { role: "admin" });
      }
    }
    setSession({ email, role: user.role });
    await refreshNav();
    loginNotice.style.display = "block";
    loginNotice.textContent = `Přihlášen jako ${user.role}.`;
    if (!user.password || forceChange) {
      changeForm.style.display = "grid";
      changeNotice.style.display = "block";
      changeNotice.textContent = "Doporučujeme změnit dočasné heslo.";
      if (tempHint && user.tempPassword) {
        tempHint.style.display = "block";
        tempHint.textContent = `Dočasné heslo: ${user.tempPassword}`;
      }
      const newPassLine = document.querySelector("#new-pass");
      if (newPassLine) newPassLine.focus();
    }
  });

  const params = new URLSearchParams(window.location.search);
  const prefEmail = params.get("email");
  const forceChange = params.get("change") === "1";
  const tempParam = params.get("temp");
  if (prefEmail) {
    const field = document.querySelector("#login-email");
    if (field && !field.value) field.value = prefEmail;
  }
  const tempHint = document.querySelector("#temp-hint");

  changeForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    const email = document.querySelector("#login-email").value.trim().toLowerCase();
    const newPassValue = document.querySelector("#new-pass").value.trim();
    const updated = await updateUser(email, { password: newPassValue, tempPassword: null });
    if (updated) {
      changeNotice.style.display = "block";
      changeNotice.textContent = "Heslo změněno.";
      if (tempHint) tempHint.style.display = "none";
    }
  });

  if (forceChange && prefEmail && tempParam) {
    const users = await getUsers();
    const user = users.find((u) => u.email === prefEmail);
    if (user && user.tempPassword === tempParam) {
      setSession({ email: prefEmail, role: user.role });
      refreshNav();
      loginNotice.style.display = "block";
      loginNotice.textContent = "Přihlášeno dočasným heslem.";
      changeForm.style.display = "grid";
      changeNotice.style.display = "block";
      changeNotice.textContent = "Nastav si nové heslo.";
      if (tempHint) {
        tempHint.style.display = "block";
        tempHint.textContent = `Dočasné heslo: ${tempParam}`;
      }
      const newPass = document.querySelector("#new-pass");
      if (newPass) newPass.focus();
    }
  }

  registerForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    const email = document.querySelector("#reg-email").value.trim().toLowerCase();
    if (!email) return;
    const allUsers = await getUsers();
    let existing = allUsers.find((u) => u.email === email);

    if (existing && existing.password && email !== "svoboda.gen@email.cz") {
      registerNotice.style.display = "block";
      registerNotice.textContent = "Účet už existuje.";
      return;
    }

    const role = email === "svoboda.gen@email.cz" ? "admin" : "navstevnik";
    const tempPassword = createTempPassword(email);

    if (existing) {
      existing.role = role;
      existing.tempPassword = tempPassword;
      existing.password = null; // Reset pass to temp if re-registering
    } else {
      allUsers.push({
        email,
        role,
        password: null,
        tempPassword,
        createdAt: new Date().toISOString(),
      });
    }
    await saveUsers(allUsers);
    registerNotice.style.display = "block";
    registerNotice.innerHTML = renderTempPasswordNotice(
      "Účet vytvořen.",
      email,
      tempPassword
    );
    registerForm.reset();
  });
}

async function setupAdmin() {
  const adminGuard = document.querySelector("#admin-guard");
  const mastersAdmin = document.querySelector("#masters-admin");
  if (!adminGuard) return;
  const session = getSession();
  const users = await getUsers();
  const user = users.find(u => u?.email === session?.email);
  const role = (session?.email === "svoboda.gen@email.cz") ? "admin" : (user?.role || session?.role || "navstevnik");
  const isAdmin = role === "admin";
  if (!isAdmin) {
    window.location.href = "login.html";
    return;
  }
  adminGuard.style.display = "none";
  // Show all possible admin panels across different pages
  const panels = ["#admin-hub", "#admin-users", "#masters-admin", "#messages-panel", "#media-admin", ".admin-content", "#requests-admin"];
  panels.forEach(selector => {
    const el = document.querySelector(selector);
    if (el) el.style.display = "block";
    // Also check for collections
    if (selector.startsWith(".")) {
      document.querySelectorAll(selector).forEach(item => item.style.display = "block");
    }
  });

  const masterList = document.querySelector("#admin-master-list") || document.querySelector("#master-list");

  const refreshMasterList = async () => {
    if (!masterList) return;
    const masters = await getMasters();
    masterList.innerHTML = masters.map((m) => `
      <div class="row">
        <div>
          <strong>${m.name}</strong><br />
          <small>${m.craft} • ${m.location}</small>
        </div>
        <div>
          <button class="btn ghost" data-edit="${m.id}">Upravit</button>
          <button class="btn primary" data-del="${m.id}">Smazat</button>
        </div>
      </div>
    `).join("");
    if (typeof syncAdminButtons === "function") syncAdminButtons();
  };

  if (masterList) {
    refreshMasterList(); // Initial load
  }

  // --- SPRÁVA UŽIVATELŮ ---
  const userForm = document.querySelector("#user-form");
  const userEditModal = document.querySelector("#user-edit-modal");
  const userEditTitle = document.querySelector("#user-edit-title");
  const userFormCancel = document.querySelector("#user-form-cancel");
  const userNotice = document.querySelector("#user-notice");
  const userList = document.querySelector("#user-list");
  const userSearch = document.querySelector("#user-search");

  let allUsersCache = [];
  let activeRoleFilter = "";

  const roleBadge = (role = "") => {
    const rKey = String(role || "navstevnik").toLowerCase().trim();
    const map = {
      admin: { label: "Admin", cls: "role-badge role-admin" },
      ucednik: { label: "Učedník", cls: "role-badge role-ucednik" },
      navstevnik: { label: "Návštěvník", cls: "role-badge role-navstevnik" },
    };
    const config = map[rKey] || { label: role, cls: "role-badge role-navstevnik" };
    return `<span class="${config.cls}">${config.label}</span>`;
  };

  const openUserModal = (title = "Upravit uživatele") => {
    if (!userEditModal) return;
    if (userEditTitle) userEditTitle.textContent = title;
    userEditModal.hidden = false;
    userEditModal.style.display = "grid";
  };

  const closeUserModal = () => {
    if (!userEditModal) return;
    userEditModal.hidden = true;
    userEditModal.style.display = "none";
    if (userForm) userForm.reset();
    if (userNotice) userNotice.style.display = "none";
  };

  if (userFormCancel) userFormCancel.addEventListener("click", closeUserModal);
  if (userEditModal) {
    userEditModal.addEventListener("click", (e) => {
      if (e.target === userEditModal) closeUserModal();
    });
  }

  const renderFilteredList = () => {
    if (!userList) return;
    const q = (userSearch ? userSearch.value.trim().toLowerCase() : "");
    const filtered = allUsersCache.filter(u => {
      const uRole = String(u.role || "").toLowerCase().trim();
      const matchRole = !activeRoleFilter || uRole === activeRoleFilter;
      const matchSearch = !q ||
        (u.name || "").toLowerCase().includes(q) ||
        (u.email || "").toLowerCase().includes(q);
      return matchRole && matchSearch;
    });

    if (filtered.length === 0) {
      userList.innerHTML = '<p style="color:var(--muted); padding: 12px 0;">Žádný uživatel nenalezen.</p>';
      return;
    }

    userList.innerHTML = filtered.map((u) => {
      const isPrimary = u.email === "svoboda.gen@email.cz";
      const delBtn = isPrimary ? "" : `<button class="btn ghost" style="color:#ff6b6b; border-color:#ff6b6b55; padding:6px 12px; font-size:13px;" data-user-del="${u.email}">Smazat</button>`;
      return `
      <div class="admin-nav-item" style="cursor:default; transform:none; margin-bottom: 8px;">
        <div style="flex:1; min-width:0;">
          <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:3px;">
            <strong style="font-size:15px;">${u.name || u.email}</strong>
            ${roleBadge(u.role)}
          </div>
          <small style="color:var(--muted); opacity: 0.8;">${u.email}${u.phone ? " · " + u.phone : ""}</small>
        </div>
        <div style="display:flex; gap:8px; flex-shrink:0;">
          <button class="btn ghost" style="padding:6px 12px; font-size:13px;" data-user-edit="${u.email}">Upravit</button>
          ${delBtn}
        </div>
      </div>
    `;
    }).join("");
  };

  async function refreshUserList() {
    try {
      allUsersCache = await getUsers();
      renderFilteredList();
    } catch (err) {
      console.error("refreshUserList failed:", err);
    }
  }

  // Filtr + vyhledávání
  const filterBtns = document.querySelectorAll(".role-filter-btn");
  filterBtns.forEach(btn => {
    btn.addEventListener("click", () => {
      filterBtns.forEach(b => b.classList.remove("active"));
      btn.classList.add("active");
      activeRoleFilter = btn.dataset.role;
      renderFilteredList();
    });
  });
  if (userSearch) {
    userSearch.addEventListener("input", renderFilteredList);
  }

  if (userForm) {
    userForm.addEventListener("submit", async (event) => {
      event.preventDefault();
      try {
        const name = document.querySelector("#user-name").value.trim();
        const email = document.querySelector("#user-email").value.trim().toLowerCase();
        const phone = document.querySelector("#user-phone").value.trim();
        const role = document.querySelector("#user-role").value;
        const pass = document.querySelector("#user-pass").value.trim();
        if (!email) return;

        const users = await getUsers();
        const idx = users.findIndex((u) => u.email === email);
        const base = idx >= 0 ? users[idx] : { email, role: "navstevnik" };

        // Zákaz změny role pro hlavního admina
        const finalRole = (email === "svoboda.gen@email.cz") ? "admin" : role;

        const nextUser = {
          ...base,
          name: name || base.name || "",
          phone: phone || base.phone || "",
          role: finalRole,
          socials: base.socials || {},
        };
        if (pass) {
          nextUser.password = pass;
          nextUser.tempPassword = null;
        }

        if (idx >= 0) { users[idx] = nextUser; }
        else { users.push({ ...nextUser, createdAt: new Date().toISOString() }); }

        await saveUsers(users);
        if (userNotice) {
          userNotice.style.display = "block";
          userNotice.textContent = `Uživatel ${email} uložen.`;
        }
        setTimeout(closeUserModal, 900);
        await refreshUserList();
      } catch (err) {
        if (userNotice) {
          userNotice.style.display = "block";
          userNotice.style.color = "#ff6b6b";
          userNotice.textContent = "Chyba při ukládání.";
        }
      }
    });
  }

  if (userList) {
    userList.addEventListener("click", async (event) => {
      const editBtn = event.target.closest("[data-user-edit]");
      const delBtn = event.target.closest("[data-user-del]");

      if (delBtn) {
        const email = delBtn.dataset.userDel;
        if (email === "svoboda.gen@email.cz") return;
        const ok = await confirmDialog(`Opravdu chcete smazat uživatele ${email}?`);
        if (!ok) return;
        try {
          const next = (await getUsers()).filter((u) => u.email !== email);
          await saveUsers(next);
          await refreshUserList();
        } catch (err) {
          console.error("Delete failed:", err);
        }
        return;
      }

      if (editBtn) {
        const email = editBtn.dataset.userEdit;
        const user = allUsersCache.find((u) => u.email === email);
        if (!user) return;

        document.querySelector("#user-name").value = user.name || "";
        document.querySelector("#user-email").value = user.email || "";
        document.querySelector("#user-phone").value = user.phone || "";
        document.querySelector("#user-pass").value = user.password || user.tempPassword || "";

        const roleSelect = document.querySelector("#user-role");
        roleSelect.value = user.role || "navstevnik";

        // Zakázat změnu role pro hlavního admina
        if (email === "svoboda.gen@email.cz") {
          roleSelect.disabled = true;
        } else {
          roleSelect.disabled = false;
        }

        openUserModal(`Upravit: ${user.name || user.email}`);
      }
    });
  }

  refreshUserList();

  async function renderMasterRequests() {
    const listEl = document.querySelector("#requests-list");
    if (!listEl) return;
    const requests = await loadMasterRequests();
    if (requests.length === 0) {
      listEl.innerHTML = '<div class="form-card" style="text-align: center;">Žádné poptávky zatím nepřišly.</div>';
      return;
    }

    listEl.innerHTML = requests.map(r => `
      <div class="request-card" data-request-id="${r.id}">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
          <div>
            <h4>${r.name}</h4>
            <div class="request-meta">Poptáno: ${new Date(r.createdAt).toLocaleString('cs-CZ')}</div>
          </div>
          <div class="pill-row">
            <span class="pill" style="background: var(--accent); color: #000;">Max. ${r.max_distance} km</span>
            <button class="btn ghost" data-delete-request="${r.id}" style="padding: 8px 12px; margin-left: 8px;">
              <i class="bi bi-trash"></i> Smazat
            </button>
          </div>
        </div>
        
        <div class="request-details">
          <div>
            <div class="field-label">Kontakt:</div>
            <p style="margin: 0;">
              Tel: <a href="tel:${r.phone}" style="color: var(--accent-2);">${r.phone}</a><br>
              Email: <a href="mailto:${r.email}" style="color: var(--accent-2);">${r.email}</a><br>
              ${r.age ? `Věk: ${r.age} let` : ''}
            </p>
            
            <div class="field-label" style="margin-top: 16px;">Obory:</div>
            <div class="pill-row">
              ${(r.crafts || []).map(c => `<span class="pill">${c}</span>`).join("") || '<span class="muted">Nevybráno</span>'}
            </div>
          </div>
          
          <div>
            <div class="field-label">Lokalita (města):</div>
            <div class="pill-row" style="margin-bottom: 16px;">
              ${(r.cities || []).map(c => `<span class="pill">${c}</span>`).join("") || '<span class="muted">Nevybráno</span>'}
            </div>
            
            <div class="field-label">Poznámka žadatele:</div>
            <p style="margin: 0 0 16px 0; font-size: 14px; color: var(--muted); line-height: 1.5;">${r.note || 'Bez poznámky.'}</p>
            
            <div class="field-label">Poznámka admina:</div>
            <textarea 
              class="admin-note-input" 
              data-request-id="${r.id}" 
              placeholder="Přidat poznámku pro interní použití..."
              style="width: 100%; min-height: 80px; background: rgba(255,255,255,0.05); color: #fff; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 10px; margin-bottom: 8px;"
            >${r.admin_note || ''}</textarea>
            <button class="btn ghost" data-save-note="${r.id}" style="padding: 8px 16px;">
              <i class="bi bi-save"></i> Uložit poznámku
            </button>
          </div>
        </div>
      </div>
    `).join("");

    // Add event listeners for delete buttons
    listEl.querySelectorAll('[data-delete-request]').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        const id = e.currentTarget.dataset.deleteRequest;
        const confirmed = await confirmDialog('Opravdu chcete smazat tuto žádost?');
        if (!confirmed) return;

        try {
          await save('delete_master_request', { id });
          renderMasterRequests();
        } catch (err) {
          alert('Chyba při mazání žádosti: ' + err.message);
        }
      });
    });

    // Add event listeners for save note buttons
    listEl.querySelectorAll('[data-save-note]').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        const id = e.currentTarget.dataset.saveNote;
        const textarea = listEl.querySelector(`.admin-note-input[data-request-id="${id}"]`);
        const admin_note = textarea.value.trim();

        try {
          await save('update_request_admin_note', { id, admin_note });
          alert('Poznámka byla uložena.');
        } catch (err) {
          alert('Chyba při ukládání poznámky: ' + err.message);
        }
      });
    });
  }

  const requestsAdmin = document.querySelector("#requests-admin");
  if (requestsAdmin) {
    renderMasterRequests();
  }

  const masterForm = document.querySelector("#master-form");
  const masterNotice = document.querySelector("#master-notice");
  const masterListTable = document.querySelector("#master-list"); // Same as masterList in outer scope

  const listView = document.querySelector("#masters-list-view");
  const editView = document.querySelector("#master-edit-view");
  const newMasterBtn = document.querySelector("#new-master-btn");
  const backBtn = document.querySelector("#back-to-list-btn");
  const editTitle = document.querySelector("#edit-view-title");

  const showEditView = (isNew = false) => {
    if (listView) listView.hidden = true;
    if (editView) editView.hidden = false;
    if (editTitle) editTitle.textContent = isNew ? "Nový mistr" : "Upravit mistra";
    if (masterNotice) masterNotice.style.display = "none";
  };

  const showListView = () => {
    if (listView) listView.hidden = false;
    if (editView) editView.hidden = true;
    if (masterForm) masterForm.reset();
    resetMasterFormExtras();
  };

  const resetMasterFormExtras = () => {
    const inputs = getMasterInputs();
    if (!inputs) return;
    inputs.id.value = "";
    inputs.photoX.value = 0;
    inputs.photoY.value = 0;
    inputs.photoZoom.value = 100;
    const galleryPreview = document.querySelector("#gallery-preview");
    const audioPreview = document.querySelector("#audio-preview");
    const photoPreview = document.querySelector("#photo-preview");
    if (galleryPreview) galleryPreview.innerHTML = "";
    if (audioPreview) {
      audioPreview.textContent = "";
      delete audioPreview.dataset.selectedPath;
    }
    if (photoPreview) {
      photoPreview.innerHTML = '<div class="pill">Náhled fotky</div>';
      delete photoPreview.dataset.selectedPath;
    }
    if (typeof applyPreviewTransform === "function") applyPreviewTransform();
    if (typeof syncSocialToggles === "function") syncSocialToggles();
  };

  if (newMasterBtn) {
    newMasterBtn.addEventListener("click", () => {
      showEditView(true);
    });
  }

  if (backBtn) {
    backBtn.addEventListener("click", () => {
      showListView();
    });
  }

  function getMasterInputs() {
    return {
      id: document.querySelector("#master-id"),
      name: document.querySelector("#master-name"),
      craft: document.querySelector("#master-craft"),
      location: document.querySelector("#master-location"),
      rank: document.querySelector("#master-rank"),
      aura: document.querySelector("#master-aura"),
      desc: document.querySelector("#master-desc"),
      tags: document.querySelector("#master-tags"),
      badges: document.querySelector("#master-badges"),
      motivation: document.querySelector("#master-stat-motivation"),
      capacity: document.querySelector("#master-stat-capacity"),
      speed: document.querySelector("#master-stat-speed"),
      specialty: document.querySelector("#master-stat-specialty"),
      photo: document.querySelector("#master-photo"),
      photoX: document.querySelector("#photo-x"),
      photoY: document.querySelector("#photo-y"),
      photoZoom: document.querySelector("#photo-zoom"),
      fb: document.querySelector("#master-fb"),
      ig: document.querySelector("#master-ig"),
      tt: document.querySelector("#master-tt"),
      li: document.querySelector("#master-li"),
      yt: document.querySelector("#master-yt"),
      ytSc: document.querySelector("#master-yt-sc"),
      gallery: document.querySelector("#master-gallery"),
      audio: document.querySelector("#master-audio"),
      education: document.querySelector("#master-education"),
      accommodation: document.querySelector("#master-accommodation"),
      compensation: document.querySelector("#master-compensation"),
      recommendations: document.querySelector("#master-recommendations"),
      requirements: document.querySelector("#master-requirements"),
    };
  }

  if (masterForm && masterNotice && mastersAdmin) {
    const inputs = getMasterInputs();
    const photoPreview = document.querySelector("#photo-preview");
    const photoClear = document.querySelector("#photo-clear");
    const galleryPreview = document.querySelector("#gallery-preview");
    const galleryClear = document.querySelector("#gallery-clear");
    const audioPreview = document.querySelector("#audio-preview");
    const audioClear = document.querySelector("#audio-clear");
    const saveLog = document.querySelector("#save-log");
    let currentGalleryItems = []; // State for the gallery being edited

    const renderGalleryItem = (item, idx) => {
      const thumb = document.createElement("div");
      thumb.className = "gallery-item-editable";
      thumb.style.position = "relative";
      thumb.dataset.idx = idx;

      if (item.type === 'video') {
        thumb.innerHTML = `<div class="thumb video-thumb"><i class="bi bi-play-circle"></i></div>`;
      } else {
        thumb.innerHTML = `<div class="thumb"><img src="${item.src}" alt="Gallery ${idx + 1}" /></div>`;
      }

      const controls = document.createElement("div");
      controls.className = "gallery-item-controls";
      controls.innerHTML = `
        <button type="button" class="gc-btn move-left" title="Posunout vlevo" ${idx === 0 ? 'disabled' : ''}><i class="bi bi-chevron-left"></i></button>
        <button type="button" class="gc-btn move-right" title="Posunout vpravo" ${idx === currentGalleryItems.length - 1 ? 'disabled' : ''}><i class="bi bi-chevron-right"></i></button>
        <button type="button" class="gc-btn delete" title="Smazat"><i class="bi bi-trash"></i></button>
      `;

      controls.querySelector(".move-left").onclick = () => {
        if (idx > 0) {
          const temp = currentGalleryItems[idx];
          currentGalleryItems[idx] = currentGalleryItems[idx - 1];
          currentGalleryItems[idx - 1] = temp;
          renderGallery();
        }
      };

      controls.querySelector(".move-right").onclick = () => {
        if (idx < currentGalleryItems.length - 1) {
          const temp = currentGalleryItems[idx];
          currentGalleryItems[idx] = currentGalleryItems[idx + 1];
          currentGalleryItems[idx + 1] = temp;
          renderGallery();
        }
      };

      controls.querySelector(".delete").onclick = () => {
        currentGalleryItems.splice(idx, 1);
        renderGallery();
      };

      thumb.appendChild(controls);
      return thumb;
    };

    const renderGallery = () => {
      if (!galleryPreview) return;
      galleryPreview.innerHTML = "";
      currentGalleryItems.forEach((item, idx) => {
        galleryPreview.appendChild(renderGalleryItem(item, idx));
      });
    };

    const logSave = (msg) => {
      if (!saveLog) return;
      saveLog.style.display = "block";
      const line = document.createElement("div");
      line.textContent = `[${new Date().toLocaleTimeString()}] ${msg}`;
      saveLog.appendChild(line);
      saveLog.scrollTop = saveLog.scrollHeight;
    };

    // Social toggles logic
    const socialToggles = masterForm.querySelectorAll(".social-toggles input[type='checkbox']");
    const socialFields = masterForm.querySelectorAll(".social-field");

    const updateSocialVisibility = () => {
      socialToggles.forEach(toggle => {
        const type = toggle.id.replace("toggle-", "");
        // Map yt-sc to ytSc
        const dataSocial = type === "yt-sc" ? "yt-sc" : type;
        const field = masterForm.querySelector(`.social-field[data-social="${dataSocial}"]`);
        if (field) {
          field.classList.toggle("hidden", !toggle.checked);
        }
      });
    };

    socialToggles.forEach(toggle => {
      toggle.addEventListener("change", updateSocialVisibility);
    });

    const syncSocialToggles = (socials = {}) => {
      socialToggles.forEach(toggle => {
        const type = toggle.id.replace("toggle-", "");
        const key = type === "yt-sc" ? "ytSc" : type;
        toggle.checked = !!(socials && socials[key]);
      });
      updateSocialVisibility();
    };

    // Initial state
    updateSocialVisibility();


    masterListTable.addEventListener("click", async (event) => {
      const editBtn = event.target.closest("[data-edit]");
      const delBtn = event.target.closest("[data-del]");
      const editId = editBtn ? editBtn.dataset.edit : null;
      const delId = delBtn ? delBtn.dataset.del : null;
      const masters = await getMasters();
      if (editId) {
        showEditView(false);
        const master = await getMaster(editId);
        if (!master) return;
        inputs.id.value = master.id;
        inputs.name.value = master.name;
        inputs.craft.value = master.craft;
        inputs.location.value = master.location;
        inputs.rank.value = master.rank || "";
        inputs.aura.value = master.aura || "";
        inputs.desc.value = master.desc || "";
        inputs.tags.value = (master.tags || []).join(", ");
        inputs.badges.value = (master.badges || []).join(", ");
        inputs.motivation.value = master.stats?.["Co nás motivuje učit"] || "";
        inputs.capacity.value = master.stats?.["Kapacita učedníků"] || "";
        inputs.speed.value = master.stats?.["Očekávání"] || "";
        inputs.specialty.value = master.stats?.["Specialita"] || "";
        inputs.education.value = master.education || "";
        inputs.accommodation.value = master.accommodation || "";
        inputs.compensation.value = master.compensation || "";
        inputs.recommendations.value = master.recommendations || "";
        inputs.requirements.value = master.requirements || "";
        inputs.fb.value = master.socials?.fb || "";
        inputs.ig.value = master.socials?.ig || "";
        inputs.tt.value = master.socials?.tt || "";
        inputs.li.value = master.socials?.li || "";
        inputs.yt.value = master.socials?.yt || "";
        inputs.ytSc.value = master.socials?.ytSc || "";
        inputs.photo.value = "";
        inputs.gallery.value = "";
        currentGalleryItems = master.gallery || [];
        renderGallery();

        // Rendered via renderGallery() above

        inputs.photoX.value = master.photoSettings?.x ?? 0;
        inputs.photoY.value = master.photoSettings?.y ?? 0;
        inputs.photoZoom.value = master.photoSettings?.zoom ?? 100;
        if (photoPreview) {
          photoPreview.innerHTML = master.photo
            ? `<img src="${master.photo}" alt="${master.name}" />`
            : `<div class="pill">Náhled fotky</div>`;
          const img = photoPreview.querySelector("img");
          if (img) {
            img.onload = () => {
              applyPreviewTransform();
              updatePhotoBounds();
            };
          } else {
            resetPhotoBounds();
          }
        }

        const audioPreview = document.querySelector("#audio-preview");
        if (audioPreview) {
          audioPreview.textContent = master.audio ? "Zadán zvukový vzkaz" : "";
        }

        syncSocialToggles(master.socials);
      }
      if (delId) {
        const ok = await confirmDialog("Opravdu chceš mistra smazat?");
        if (!ok) return;
        const next = masters.filter((m) => String(m.id) !== String(delId));
        await saveMasters(next);
        await refreshMasterList();
        masterNotice.style.display = "block";
        masterNotice.textContent = "Mistr smazán.";
      }
    });

    masterForm.addEventListener("submit", async (event) => {
      event.preventDefault();
      masterNotice.style.display = "block";
      masterNotice.textContent = "Ukládám...";
      if (saveLog) saveLog.innerHTML = "";
      logSave("Zahajuji ukládání...");

      const id = inputs.id.value.trim() || makeId(inputs.name.value || "mistr");
      const tags = inputs.tags.value.split(",").map((t) => t.trim()).filter(Boolean);
      const badges = inputs.badges.value.split(",").map((t) => t.trim()).filter(Boolean);
      const stats = {
        "Co nás motivuje učit": inputs.motivation.value || "-",
        "Kapacita učedníků": inputs.capacity.value || "-",
        "Očekávání": inputs.speed.value || "-",
        "Specialita": inputs.specialty.value || "-",
      };

      logSave("Načítám aktuální data ze serveru...");
      const existing = await getMaster(id);

      const baseMaster = {
        id,
        name: inputs.name.value.trim(),
        craft: inputs.craft.value.trim(),
        location: inputs.location.value.trim(),
        rank: inputs.rank.value.trim(),
        aura: inputs.aura.value.trim(),
        desc: inputs.desc.value.trim(),
        tags,
        badges,
        stats,
        education: inputs.education.value.trim(),
        accommodation: inputs.accommodation.value.trim(),
        compensation: inputs.compensation.value.trim(),
        recommendations: inputs.recommendations.value.trim(),
        requirements: inputs.requirements.value.trim(),
        photo: existing ? existing.photo : null,
        gallery: existing ? existing.gallery || [] : [],
        socials: {
          fb: inputs.fb.value.trim(),
          ig: inputs.ig.value.trim(),
          tt: inputs.tt.value.trim(),
          li: inputs.li.value.trim(),
          yt: inputs.yt.value.trim(),
          ytSc: inputs.ytSc.value.trim(),
        },
        audio: existing ? existing.audio : null,
        photoSettings: {
          x: parseInt(inputs.photoX.value) || 0,
          y: parseInt(inputs.photoY.value) || 0,
          zoom: parseInt(inputs.photoZoom.value) || 100
        }
      };

      // Set gallery from current state
      baseMaster.gallery = [...currentGalleryItems];

      try {
        // Process Main Photo
        const selectedPhotoPath = photoPreview?.dataset.selectedPath;
        if (selectedPhotoPath) {
          baseMaster.photo = selectedPhotoPath;
          logSave("Používám fotku z knihovny.");
        } else if (inputs.photo.files && inputs.photo.files[0]) {
          logSave("Zpracovávám novou fotku...");
          baseMaster.photo = await imageFileToDataUrl(inputs.photo.files[0]);
          logSave(`Nová fotka zpracována (${(baseMaster.photo.length / 1024).toFixed(1)} KB)`);
        }

        // Process Gallery
        const selectedGalleryPath = galleryPreview?.dataset.selectedPath;
        if (selectedGalleryPath) {
          const type = selectedGalleryPath.includes("video") || selectedGalleryPath.toLowerCase().endsWith(".mp4") ? "video" : "image";
          currentGalleryItems.push({ type, src: selectedGalleryPath });
          baseMaster.gallery = [...currentGalleryItems];
          logSave("Přidána položka z knihovny do galerie.");
        }

        if (inputs.gallery.files && inputs.gallery.files.length > 0) {
          logSave(`Zpracovávám nové soubory do galerie (${inputs.gallery.files.length})...`);
          const galleryPromises = Array.from(inputs.gallery.files).map(f => fileToDataUrl(f).then(src => ({
            type: f.type.startsWith('video/') ? 'video' : 'image',
            src
          })));
          const newItems = await Promise.all(galleryPromises);
          currentGalleryItems = [...currentGalleryItems, ...newItems];
          baseMaster.gallery = [...currentGalleryItems];
          logSave("Nové soubory v galerii zpracovány.");
        }

        // Process Audio
        const selectedAudioPath = audioPreview?.dataset.selectedPath;
        if (selectedAudioPath) {
          baseMaster.audio = selectedAudioPath;
          logSave("Používám audio z knihovny.");
        } else if (inputs.audio.files && inputs.audio.files[0]) {
          logSave("Zpracovávám nové audio...");
          baseMaster.audio = await fileToDataUrl(inputs.audio.files[0]);
          logSave(`Nové audio zpracováno (${(baseMaster.audio.length / 1024).toFixed(1)} KB)`);
        }

        // Save to Server
        logSave("Odesílám data na server...");
        const response = await saveMaster(baseMaster);

        if (response && response.status === "success") {
          logSave("✅ Server potvrdil úspěšné uložení.");
          await refreshMasterList();
          masterNotice.textContent = "Mistr uložen.";
          setTimeout(() => {
            showListView();
          }, 1000);
        } else {
          const errorMsg = response?.error || "Neznámá chyba serveru";
          logSave(`❌ Chyba serveru: ${errorMsg}`);
          masterNotice.textContent = `Chyba: ${errorMsg}`;
        }
      } catch (err) {
        logSave(`❌ Systémová chyba: ${err.message}`);
        console.error("Save error:", err);
        masterNotice.textContent = "Uložení selhalo. Zkontrolujte log pod tlačítkem.";
      }
    });

    if (inputs.gallery && galleryPreview) {
      inputs.gallery.addEventListener("change", async () => {
        const files = inputs.gallery.files;
        if (!files || files.length === 0) return;

        logSave(`Zpracovávám ${files.length} nových souborů...`);
        const galleryPromises = Array.from(files).map(f => fileToDataUrl(f).then(src => ({
          type: f.type.startsWith('video/') ? 'video' : 'image',
          src
        })));
        const newItems = await Promise.all(galleryPromises);
        currentGalleryItems = [...currentGalleryItems, ...newItems];
        renderGallery();
        inputs.gallery.value = ""; // Clear input so it can be used again
      });
    }

    if (galleryClear) {
      galleryClear.addEventListener("click", async () => {
        const ok = await confirmDialog("Opravdu chceš odstranit galerii?");
        if (!ok) return;
        inputs.gallery.value = "";
        if (galleryPreview) galleryPreview.innerHTML = "";
        const id = inputs.id.value.trim();
        if (!id) return;
        const masters = await getMasters();
        const idx = masters.findIndex((m) => m.id === id);
        if (idx >= 0) {
          masters[idx].gallery = [];
          await saveMasters(masters);
          await refreshMasterList();
          masterNotice.style.display = "block";
          masterNotice.textContent = "Galerie odstraněna.";
        }
      });
    }

    // Expose function so the gallery picker (outside setupAdmin) can add items
    window.addGalleryItemFromPicker = (path) => {
      const isVideo = path.includes("video") || /\.(mp4|webm|mov|avi)$/i.test(path);
      const newItem = { type: isVideo ? 'video' : 'image', src: path };
      currentGalleryItems = [...currentGalleryItems, newItem];
      renderGallery();
    };

    if (inputs.audio && audioPreview) {
      inputs.audio.addEventListener("change", () => {
        const file = inputs.audio.files && inputs.audio.files[0];
        audioPreview.textContent = file ? `Vybrán soubor: ${file.name}` : "";
      });
    }

    if (audioClear) {
      audioClear.addEventListener("click", async () => {
        const ok = await confirmDialog("Opravdu chceš odstranit audio?");
        if (!ok) return;
        inputs.audio.value = "";
        if (audioPreview) audioPreview.textContent = "";
        const id = inputs.id.value.trim();
        if (!id) return;
        const masters = await getMasters();
        const idx = masters.findIndex((m) => m.id === id);
        if (idx >= 0) {
          masters[idx].audio = null;
          await saveMasters(masters);
          await refreshMasterList();
          masterNotice.style.display = "block";
          masterNotice.textContent = "Audio odstraněno.";
        }
      });
    }

    if (inputs.photo && photoPreview) {
      inputs.photo.addEventListener("change", () => {
        const file = inputs.photo.files && inputs.photo.files[0];
        if (!file) {
          photoPreview.innerHTML = `<div class="pill">Náhled fotky</div>`;
          resetPhotoBounds();
          return;
        }
        imageFileToDataUrl(file)
          .then((dataUrl) => {
            photoPreview.innerHTML = `<img src="${dataUrl}" alt="Náhled" />`;
            const img = photoPreview.querySelector("img");
            if (img) {
              img.onload = () => {
                applyPreviewTransform();
                updatePhotoBounds();
              };
            }
          })
          .catch(() => {
            photoPreview.innerHTML = `<div class="pill">Náhled selhal</div>`;
            resetPhotoBounds();
          });
      });
    }

    if (photoClear) {
      photoClear.addEventListener("click", async () => {
        const ok = await confirmDialog("Opravdu chceš odstranit fotku mistra?");
        if (!ok) return;
        inputs.photo.value = "";
        if (photoPreview) {
          photoPreview.innerHTML = `<div class="pill">Náhled fotky</div>`;
        }
        inputs.photoX.value = 0;
        inputs.photoY.value = 0;
        inputs.photoZoom.value = 100;
        applyPreviewTransform();
        resetPhotoBounds();
        const id = inputs.id.value.trim();
        if (!id) return;
        const masters = await getMasters();
        const idx = masters.findIndex((m) => m.id === id);
        if (idx >= 0) {
          masters[idx].photo = null;
          masters[idx].photoSettings = { x: 0, y: 0, zoom: 100 };
          await saveMasters(masters);
          await refreshMasterList();
          masterNotice.style.display = "block";
          masterNotice.textContent = "Fotka odstraněna.";
        }
      });
    }

    function applyPreviewTransform() {
      const img = photoPreview ? photoPreview.querySelector("img") : null;
      if (!img) return;
      const clamped = clampOffsets();
      img.style.setProperty("--photo-x", `${clamped.x}px`);
      img.style.setProperty("--photo-y", `${clamped.y}px`);
      img.style.setProperty("--photo-zoom", `${clamped.zoom}`);
    }

    [inputs.photoX, inputs.photoY, inputs.photoZoom].forEach((control) => {
      if (!control) return;
      control.addEventListener("input", () => {
        applyPreviewTransform();
        updatePhotoBounds();
      });
    });

    function resetPhotoBounds() {
      inputs.photoX.min = "-50";
      inputs.photoX.max = "50";
      inputs.photoY.min = "-50";
      inputs.photoY.max = "50";
    }

    function clampOffsets() {
      const zoom = Number(inputs.photoZoom.value || 100) / 100;
      const x = Number(inputs.photoX.value || 0);
      const y = Number(inputs.photoY.value || 0);
      const minX = Number(inputs.photoX.min || -50);
      const maxX = Number(inputs.photoX.max || 50);
      const minY = Number(inputs.photoY.min || -50);
      const maxY = Number(inputs.photoY.max || 50);
      const clampedX = Math.max(minX, Math.min(maxX, x));
      const clampedY = Math.max(minY, Math.min(maxY, y));
      if (clampedX !== x) inputs.photoX.value = clampedX;
      if (clampedY !== y) inputs.photoY.value = clampedY;
      return { x: clampedX, y: clampedY, zoom };
    }

    function updatePhotoBounds() {
      const img = photoPreview ? photoPreview.querySelector("img") : null;
      if (!img || !img.naturalWidth || !img.naturalHeight) return;
      const box = photoPreview.getBoundingClientRect();
      if (!box.width || !box.height) return;
      const zoom = Number(inputs.photoZoom.value || 100) / 100;
      const scale = Math.max(box.width / img.naturalWidth, box.height / img.naturalHeight) * zoom;
      const dispW = img.naturalWidth * scale;
      const dispH = img.naturalHeight * scale;
      const maxX = Math.max(0, (dispW - box.width) / 2);
      const maxY = Math.max(0, (dispH - box.height) / 2);
      inputs.photoX.min = (-maxX).toFixed(0);
      inputs.photoX.max = maxX.toFixed(0);
      inputs.photoY.min = (-maxY).toFixed(0);
      inputs.photoY.max = maxY.toFixed(0);
      clampOffsets();
    }

    if (window.ResizeObserver && photoPreview) {
      const ro = new ResizeObserver(() => updatePhotoBounds());
      ro.observe(photoPreview);
    }
  }

  refreshMasterList();

  function syncAdminButtons() {
    const rows = masterList.querySelectorAll(".row");
    rows.forEach((row) => {
      const textBox = row.children[0];
      if (!textBox) return;
      const strong = textBox.querySelector("strong");
      const small = textBox.querySelector("small");
      const strongLH = strong ? parseFloat(getComputedStyle(strong).lineHeight) : 0;
      const smallLH = small ? parseFloat(getComputedStyle(small).lineHeight) : 0;
      const expected = (strongLH || 0) + (smallLH || 0) + 6;
      const actual = textBox.getBoundingClientRect().height;
      const isWrapped = actual > expected;
      row.classList.toggle("stack", isWrapped);
    });
  }

  if (window.ResizeObserver && masterList && masterList instanceof Element) {
    const ro = new ResizeObserver(() => syncAdminButtons());
    ro.observe(masterList);
  } else if (!window.ResizeObserver) {
    window.addEventListener("resize", syncAdminButtons);
  }

  const messagesList = document.querySelector("#messages-list");
  if (messagesList) {
    const masters = await getMasters();
    const messages = (await getMessages()).filter((m) => !m.deletedByAdmin);
    messagesList.innerHTML = messages
      .map((msg) => {
        const master = masters.find((m) => m.id === msg.toMaster);
        return `<div class="message-card">
            <div>
              <strong>${msg.from}</strong> → ${master ? master.name : msg.toMaster}<br />
              <small>${msg.userName || ""} ${msg.userPhone || ""}</small><br />
              <small>${new Date(msg.createdAt).toLocaleString("cs-CZ")}</small>
              <p>${msg.text}</p>
            </div>
            <div class="message-actions">
              <button class="btn ghost" data-msg-admin-del="${msg.id}">Smazat</button>
            </div>
          </div>`;
      })
      .join("");

    messagesList.addEventListener("click", async (event) => {
      const id = event.target.dataset.msgAdminDel;
      if (!id) return;
      const ok = await confirmDialog("Opravdu chceš zprávu smazat?");
      if (!ok) return;
      let all = await getMessages();
      const idx = all.findIndex((m) => m.id === id);
      if (idx >= 0) {
        all[idx].deletedByAdmin = true;
        all = pruneMessages(all);
        await saveMessages(all);
        await setupAdmin();
      }
    });
  }
}

const mediaAdmin = document.querySelector("#media-admin");
if (mediaAdmin) {
  let currentFolderId = null;
  const mediaGrid = document.querySelector("#media-grid");
  const breadcrumbs = document.querySelector("#media-breadcrumbs");
  const uploadInput = document.querySelector("#media-upload-input");
  const newFolderBtn = document.querySelector("#new-folder-btn");
  const folderModal = document.querySelector("#folder-modal");
  const folderNameInput = document.querySelector("#folder-name-input");
  const folderSaveBtn = document.querySelector("#folder-modal-save");
  const folderCancelBtn = document.querySelector("#folder-modal-cancel");

  async function refreshMedia() {
    const { folders, items } = await loadMedia();
    const currentFolders = folders.filter(f => f.parentId == currentFolderId);
    const currentItems = items.filter(i => i.folderId == currentFolderId);

    const path = [];
    let tempId = currentFolderId;
    while (tempId) {
      const f = folders.find(x => x.id == tempId);
      if (f) { path.unshift(f); tempId = f.parentId; } else { tempId = null; }
    }
    breadcrumbs.innerHTML = `<span data-id="null" style="cursor:pointer;">Root</span>` +
      path.map(f => ` / <span data-id="${f.id}" style="cursor:pointer;">${f.name}</span>`).join("");

    mediaGrid.innerHTML = "";
    currentFolders.forEach(f => {
      const el = document.createElement("div");
      el.className = "media-item folder text-center";
      el.innerHTML = `
          <div style="font-size: 40px; cursor:pointer;" data-open="${f.id}"><i class="bi bi-folder-fill" style="color: #ffd700;"></i></div>
          <div style="font-size: 11px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${f.name}</div>
          <div style="margin-top:4px;">
            <i class="bi bi-pencil" style="cursor:pointer; margin-right:8px;" data-edit-folder="${f.id}"></i>
            <i class="bi bi-trash" style="cursor:pointer; color:red;" data-del-folder="${f.id}"></i>
          </div>
        `;
      mediaGrid.appendChild(el);
    });

    currentItems.forEach(i => {
      const el = document.createElement("div");
      el.className = "media-item file text-center";
      let thumbUrl = i.thumbnail || i.path;
      let icon = i.type === "audio" ? "music-note" : (i.type === "video" ? "play-circle" : "");
      el.innerHTML = `
          <div style="width:100%; height:80px; background:#f0f0f0; border-radius:4px; display:flex; align-items:center; justify-content:center; overflow:hidden; position:relative; cursor:pointer;" data-view="${i.id}">
            ${i.type === 'image' ? `<img src="${thumbUrl}" style="width:100%; height:100%; object-fit:cover;" />` : `<i class="bi bi-${icon}" style="font-size:30px;"></i>`}
          </div>
          <div style="font-size: 11px; margin-top:4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${i.name}">${i.name}</div>
          <div style="margin-top:4px;">
            <i class="bi bi-trash" style="cursor:pointer; color:red;" data-del-item="${i.id}"></i>
          </div>
        `;
      mediaGrid.appendChild(el);
    });
  }

  mediaGrid.addEventListener("click", async (e) => {
    const openId = e.target.closest("[data-open]")?.dataset.open;
    if (openId) { currentFolderId = openId; refreshMedia(); return; }
    const delFolder = e.target.closest("[data-del-folder]")?.dataset.delFolder;
    if (delFolder) { if (await confirmDialog("Opravdu smazat složku?")) { await deleteFolder(delFolder); refreshMedia(); } return; }
    const delItem = e.target.closest("[data-del-item]")?.dataset.delItem;
    if (delItem) { if (await confirmDialog("Opravdu smazat soubor?")) { await deleteMediaItem(delItem); refreshMedia(); } return; }
    const editFolderId = e.target.closest("[data-edit-folder]")?.dataset.editFolder;
    if (editFolderId) {
      const { folders } = await loadMedia();
      const f = folders.find(x => x.id == editFolderId);
      if (f) { folderNameInput.value = f.name; folderNameInput.dataset.editId = f.id; folderModal.hidden = false; }
    }
  });

  breadcrumbs.addEventListener("click", e => {
    const id = e.target.closest("[data-id]")?.dataset.id;
    if (id !== undefined) { currentFolderId = id === "null" ? null : id; refreshMedia(); }
  });

  newFolderBtn.addEventListener("click", () => { folderNameInput.value = ""; delete folderNameInput.dataset.editId; folderModal.hidden = false; });
  folderCancelBtn.addEventListener("click", () => { folderModal.hidden = true; });
  folderSaveBtn.addEventListener("click", async () => {
    const name = folderNameInput.value.trim();
    if (!name) return;
    await saveFolder({ id: folderNameInput.dataset.editId, name, parentId: currentFolderId });
    folderModal.hidden = true;
    refreshMedia();
  });

  uploadInput.addEventListener("change", async () => {
    const files = uploadInput.files;
    if (!files.length) return;
    const notice = document.querySelector("#media-notice");
    if (notice) { notice.style.display = "block"; notice.textContent = "Nahrávám..."; }
    for (const file of files) {
      let type = file.type.startsWith("video/") ? "video" : (file.type.startsWith("audio/") ? "audio" : "image");
      let path = await fileToDataUrl(file);
      let thumb = type === "image" ? await imageFileToDataUrl(file, 200) : null;
      await saveMediaItem({ folderId: currentFolderId, name: file.name, type, path, thumbnail: thumb, size: file.size });
    }
    if (notice) notice.textContent = "Nahráno.";
    uploadInput.value = "";
    refreshMedia();
  });
  refreshMedia();
}

function setupMediaPicker() {
  const pickerModal = document.querySelector("#media-picker-modal");
  if (!pickerModal) return;
  let pickerFolderId = null;
  const pickerGrid = document.querySelector("#picker-grid");
  const pickerBreadcrumbs = document.querySelector("#picker-breadcrumbs");
  const pickerCancel = document.querySelector("#picker-cancel");
  let onSelectCallback = null;
  let allowedType = null;

  window.openMediaPicker = (type, onSelect) => {
    allowedType = type; onSelectCallback = onSelect; pickerModal.hidden = false; refreshPicker();
  };

  async function refreshPicker() {
    const { folders, items } = await loadMedia();
    const currentFolders = folders.filter(f => f.parentId == pickerFolderId);
    const currentItems = items.filter(i => {
      if (i.folderId != pickerFolderId) return false;
      if (!allowedType) return true;
      if (allowedType === "image") return i.type === "image";
      if (allowedType === "audio") return i.type === "audio";
      if (allowedType === "gallery") return i.type === "image" || i.type === "video";
      return true;
    });
    const path = [];
    let tempId = pickerFolderId;
    while (tempId) { const f = folders.find(x => x.id == tempId); if (f) { path.unshift(f); tempId = f.parentId; } else { tempId = null; } }
    pickerBreadcrumbs.innerHTML = `<span data-id="null" style="cursor:pointer;">Root</span>` + path.map(f => ` / <span data-id="${f.id}" style="cursor:pointer;">${f.name}</span>`).join("");
    pickerGrid.innerHTML = "";
    currentFolders.forEach(f => {
      const el = document.createElement("div");
      el.className = "media-item folder text-center";
      el.innerHTML = `<div style="font-size: 30px; cursor:pointer;" data-open="${f.id}"><i class="bi bi-folder-fill" style="color: #ffd700;"></i><br><small>${f.name}</small></div>`;
      pickerGrid.appendChild(el);
    });
    currentItems.forEach(i => {
      const el = document.createElement("div");
      el.className = "media-item file text-center";
      let thumbUrl = i.thumbnail || i.path;
      el.innerHTML = `
          <div style="width:100%; height:80px; background:#f0f0f0; border-radius:4px; display:flex; align-items:center; justify-content:center; overflow:hidden; position:relative; cursor:pointer;" data-select-path="${i.path}">
             ${i.type === 'image' ? `<img src="${thumbUrl}" style="width:100%; height:100%; object-fit:cover;" />` : `<i class="bi bi-${i.type === 'audio' ? 'music-note' : 'play-circle'}" style="font-size:24px;"></i>`}
          </div>
          <div style="font-size: 10px; margin-top:4px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${i.name}</div>
        `;
      pickerGrid.appendChild(el);
    });
  }

  pickerGrid.addEventListener("click", e => {
    const openId = e.target.closest("[data-open]")?.dataset.open;
    if (openId) { pickerFolderId = openId; refreshPicker(); return; }
    const selectPath = e.target.closest("[data-select-path]")?.dataset.selectPath;
    if (selectPath) { if (onSelectCallback) onSelectCallback(selectPath); pickerModal.hidden = true; }
  });
  pickerBreadcrumbs.addEventListener("click", e => {
    const id = e.target.closest("[data-id]")?.dataset.id;
    if (id !== undefined) { pickerFolderId = id === "null" ? null : id; refreshPicker(); }
  });
  pickerCancel.addEventListener("click", () => { pickerModal.hidden = true; });
}

const photoPickerBtn = document.querySelector("#master-photo-picker");
if (photoPickerBtn) {
  photoPickerBtn.addEventListener("click", () => {
    window.openMediaPicker("image", (path) => {
      const preview = document.querySelector("#photo-preview");
      if (preview) {
        preview.innerHTML = `<img src="${path}" alt="Náhled" />`;
        const img = preview.querySelector("img");
        if (img) img.onload = () => { if (typeof applyPreviewTransform === "function") applyPreviewTransform(); };
        preview.dataset.selectedPath = path;
      }
    });
  });
}
const audioPickerBtn = document.querySelector("#master-audio-picker");
if (audioPickerBtn) {
  audioPickerBtn.addEventListener("click", () => {
    window.openMediaPicker("audio", (path) => {
      const preview = document.querySelector("#audio-preview");
      if (preview) { preview.textContent = "Zadán zvukový vzkaz z knihovny"; preview.dataset.selectedPath = path; }
    });
  });
}
const galleryPickerBtn = document.querySelector("#master-gallery-picker");
if (galleryPickerBtn) {
  galleryPickerBtn.addEventListener("click", () => {
    window.openMediaPicker("gallery", (path) => {
      // Use the same internal state as file upload so items are saved correctly
      if (typeof window.addGalleryItemFromPicker === "function") {
        window.addGalleryItemFromPicker(path);
      }
    });
  });
}


async function setupProfile() {
  const guard = document.querySelector("#profile-guard");
  const card = document.querySelector("#profile-card");
  if (!guard) return;
  const msgCard = document.querySelector("#profile-messages");
  const session = getSession();
  if (!session) return;

  guard.style.display = "none";
  card.style.display = "block";
  msgCard.style.display = "block";

  const user = (await getUsers()).find((u) => u.email === session.email);
  if (session.email === "svoboda.gen@email.cz" && user && user.role !== "admin") {
    user.role = "admin";
    await updateUser(session.email, { role: "admin" });
  }
  const summary = document.querySelector("#profile-summary");
  const displayRole = (session.email === "svoboda.gen@email.cz") ? "admin" : (user?.role || "navstevnik");
  summary.textContent = `Přihlášen jako ${session.email} • role: ${displayRole}`;

  const profileForm = document.querySelector("#profile-form");
  const profileName = document.querySelector("#profile-name");
  const profileEmail = document.querySelector("#profile-email"); // This is usually readonly/disabled or hidden as ID
  const profilePhone = document.querySelector("#profile-phone");
  const profilePass = document.querySelector("#profile-pass-input"); // Fixed ID match

  const profileToggle = document.querySelector("#profile-pass-toggle");
  const profileEdit = document.querySelector("#profile-edit");
  const profileSave = document.querySelector("#profile-save");
  const profileDelete = document.querySelector("#profile-delete");

  if (profileName) profileName.value = user?.name || "";
  if (profileEmail) profileEmail.value = user?.email || "";
  if (profilePhone) profilePhone.value = user?.phone || "";
  if (profilePass) profilePass.value = user?.password || user?.tempPassword || "";

  if (profileToggle && profilePass) {
    profileToggle.addEventListener("click", () => {
      const isHidden = profilePass.type === "password";
      profilePass.type = isHidden ? "text" : "password";
      profileToggle.innerHTML = isHidden ? '<i class="bi bi-eye-slash" aria-hidden="true"></i>' : '<i class="bi bi-eye" aria-hidden="true"></i>';
    });
  }

  if (profileEdit && profileSave) {
    profileEdit.addEventListener("click", () => {
      [profileName, profilePhone, profilePass].forEach((el) => {
        if (el) {
          el.removeAttribute("readonly");
          el.classList.add("active-outline");
        }
      });
      profileSave.disabled = false;
      profileEdit.classList.add("active-outline");
    });
  }

  const logoutBtn = document.querySelector("#logout-btn");
  if (logoutBtn) {
    logoutBtn.addEventListener("click", () => {
      localStorage.removeItem(STORAGE.session);
      refreshNav();
      window.location.reload();
    });
  }

  const passNotice = document.querySelector("#profile-pass-notice");
  if (profileForm) {
    profileForm.addEventListener("submit", async (event) => {
      event.preventDefault();

      const newPass = profilePass.value.trim();
      if (!newPass) {
        if (passNotice) {
          passNotice.style.display = "block";
          passNotice.textContent = "Heslo nesmí být prázdné.";
          passNotice.style.color = "#ff4d4d";
        }
        return;
      }

      // Feedback
      const originalText = profileSave.textContent;
      profileSave.textContent = "Ukládám...";
      profileSave.disabled = true;

      try {
        const updated = await updateUser(session.email, {
          name: profileName.value.trim(),
          phone: profilePhone.value.trim(),
          password: newPass,
          tempPassword: null,
        });

        if (updated) {
          if (passNotice) {
            passNotice.style.display = "block";
            passNotice.textContent = "Profil úspěšně uložen.";
            passNotice.style.color = "#2ed573"; // Green
            setTimeout(() => { passNotice.style.display = "none"; }, 3000);
          }
          // Lock inputs
          [profileName, profilePhone, profilePass].forEach((el) => {
            if (el) {
              el.setAttribute("readonly", "readonly");
              el.classList.remove("active-outline");
            }
          });
          if (profileEdit) profileEdit.classList.remove("active-outline");
          // Keep save disabled
        } else {
          throw new Error("Chyba při komunikaci se serverem.");
        }
      } catch (err) {
        console.error(err);
        if (passNotice) {
          passNotice.style.display = "block";
          passNotice.textContent = "Chyba při ukládání: " + err.message;
          passNotice.style.color = "#ff4d4d";
        }
        // Re-enable save button on error so user can try again
        profileSave.textContent = originalText;
        profileSave.disabled = false;
      } finally {
        if (profileSave.textContent === "Ukládám...") { // If success logic didn't reset it (it doesn't, it leaves it disabled)
          profileSave.textContent = originalText;
        }
      }
    });
  }

  if (profileDelete) {
    profileDelete.addEventListener("click", async () => {
      if (session.email === "svoboda.gen@email.cz") {
        if (passNotice) {
          passNotice.style.display = "block";
          passNotice.textContent = "Hlavní admin nemůže smazat svůj profil.";
        }
        return;
      }
      const ok = await confirmDialog("Opravdu chceš smazat svůj profil?");
      if (!ok) return;
      const nextUsers = (await getUsers()).filter((u) => u.email !== session.email);
      await saveUsers(nextUsers);
      localStorage.removeItem(STORAGE.session);
      refreshNav();
      window.location.href = "index.html";
    });
  }

  const profileMessagesList = document.querySelector("#profile-messages-list");
  if (profileMessagesList) {
    const messages = (await getMessages()).filter((m) => m.from === session.email && !m.deletedByUser);
    const masters = await getMasters();
    profileMessagesList.innerHTML = messages.map((msg) => {
      const master = masters.find((m) => m.id === msg.toMaster);
      return `<div class="message-card">
        <div>
          <strong>${master ? master.name : msg.toMaster}</strong><br />
          <small>${msg.userName || ""} ${msg.userPhone || ""}</small><br />
          <small>${new Date(msg.createdAt).toLocaleString("cs-CZ")}</small>
          <p>${msg.text}</p>
        </div>
        <div class="message-actions">
          <button class="btn ghost" data-msg-del="${msg.id}">Smazat</button>
        </div>
      </div>`;
    }).join("");

    profileMessagesList.addEventListener("click", async (event) => {
      const id = event.target.dataset.msgDel;
      if (!id) return;
      const ok = await confirmDialog("Opravdu chceš zprávu smazat?");
      if (!ok) return;
      let all = await getMessages();
      const idx = all.findIndex((m) => m.id === id);
      if (idx >= 0) {
        all[idx].deletedByUser = true;
        all = pruneMessages(all);
        await saveMessages(all);
        setupProfile();
      }
    });
  }
}

// Global Nav & Init
// VERZE: stav aktual - Round 15 (Final Opaque)
document.addEventListener("DOMContentLoaded", async () => {
  // Common UI event listeners
  document.querySelector(".nav-toggle")?.addEventListener("click", toggleNav);
  document.getElementById("user-toggle")?.addEventListener("click", toggleUserMenu);
  document.getElementById("user-logout")?.addEventListener("click", logout);

  // CMS Load
  loadCMS();

  // Specific page init
  if (document.getElementById("masters-grid")) {
    renderMasterGrid();
  }
  if (document.getElementById("detail-name")) {
    renderMasterDetail();
  }
  setupLogin();
  setupAdmin();
  setupProfile();
  setupMediaPicker();
  setupMasterRequestPage();
  refreshNav();

  document.addEventListener("click", (event) => {
    const dropdown = document.querySelector("#user-dropdown");
    const userToggle = document.querySelector("#user-toggle");

    // Close dropdown on outside click
    if (dropdown && !dropdown.hidden && !event.target.closest(".user-menu")) {
      dropdown.hidden = true;
      if (userToggle) userToggle.setAttribute("aria-expanded", "false");
      const menu = document.querySelector(".user-menu");
      if (menu) menu.classList.remove("open");
    }
  });
});


async function refreshNav() {
  const session = getSession();
  const users = await getUsers();
  const user = users.find(u => u?.email === session?.email);
  const role = (session?.email === "svoboda.gen@email.cz") ? "admin" : (user?.role || session?.role || "navstevnik");
  const isAdmin = role === "admin";

  const profileToggle = document.querySelector("#user-toggle");
  const adminOnly = document.querySelectorAll(".admin-only");
  adminOnly.forEach((link) => link.classList.toggle("hidden", !isAdmin));

  if (profileToggle) {
    if (session && session.email) {
      console.log("refreshNav: user logged in", session.email);
      const users = await getUsers();
      const user = users.find((u) => u.email === session.email);
      const role = (session.email === "svoboda.gen@email.cz") ? "admin" : (user?.role || session.role || "navstevnik");
      const name = user?.name ? user.name.split(" ")[0] : "";
      const fallback = session.email.split("@")[0];
      profileToggle.textContent = name || fallback;
    } else {
      console.log("refreshNav: no session");
      profileToggle.textContent = "Přihlásit";
    }
  }
}

function positionUserDropdown() {
  const dropdown = document.querySelector("#user-dropdown");
  if (!dropdown || dropdown.hidden) return;
  dropdown.style.left = "auto";
  dropdown.style.right = "0";
  const rect = dropdown.getBoundingClientRect();
  if (rect.left < 8) {
    dropdown.style.right = "auto";
    dropdown.style.left = "0";
  }
}

window.addEventListener("resize", () => {
  const dropdown = document.querySelector("#user-dropdown");
  if (dropdown && !dropdown.hidden) positionUserDropdown();
});

async function logout() {
  localStorage.removeItem(STORAGE.session);
  await refreshNav();

  const protectedPages = [
    "profil.html",
    "admin.html",
    "admin-content.html",
    "admin-requests.html",
    "admin-masters.html",
    "admin-messages.html",
    "admin-media.html",
    "admin-roles.html"
  ];

  const currentPath = window.location.pathname.split("/").pop();

  if (protectedPages.includes(currentPath)) {
    window.location.href = "index.html";
  } else {
    // Close user dropdown if open
    const dropdown = document.querySelector("#user-dropdown");
    if (dropdown) dropdown.hidden = true;
    const userToggle = document.querySelector("#user-toggle");
    if (userToggle) userToggle.setAttribute("aria-expanded", "false");
  }
}

async function toggleUserMenu() {
  const session = getSession();
  if (!session) {
    if (typeof openLoginModal === "function") {
      openLoginModal();
    } else {
      window.location.href = "login.html";
    }
    return;
  }
  const dropdown = document.querySelector("#user-dropdown");
  const userToggle = document.querySelector("#user-toggle");
  if (!dropdown || !userToggle) return;
  const isOpen = !dropdown.hidden;
  dropdown.hidden = isOpen;
  userToggle.setAttribute("aria-expanded", String(!isOpen));
  const menu = document.querySelector(".user-menu");
  if (menu) menu.classList.toggle("open", !isOpen);
  if (!isOpen) positionUserDropdown();
}

async function toggleNav() {
  const isOpen = document.body.classList.toggle("nav-open");
  const toggle = document.querySelector(".nav-toggle");
  if (toggle) toggle.setAttribute("aria-expanded", String(isOpen));
}

function renderTempPasswordNotice(prefix, email, tempPassword) {
  const safeEmail = encodeURIComponent(email);
  const safeTemp = encodeURIComponent(tempPassword);
  return `${prefix} Dočasné heslo: <strong>${tempPassword}</strong>
    <button class="copy-btn" type="button" data-copy="${tempPassword}">Kopírovat</button>
    <a class="inline-link" href="login.html?email=${safeEmail}&change=1&temp=${safeTemp}">Nastavit vlastní heslo</a>`;
}

document.addEventListener("click", (event) => {
  const btn = event.target.closest(".copy-btn");
  if (!btn) return;
  const value = btn.getAttribute("data-copy");
  if (!value) return;
  navigator.clipboard.writeText(value).then(() => { btn.textContent = "Zkopírováno"; });
});

async function openLoginModal() {
  if (window.location.pathname.endsWith("login.html")) return;

  let modal = document.querySelector("#login-modal-overlay");

  // If modal doesn't exist, create it
  if (!modal) {
    console.log("Creating login modal...");
    modal = document.createElement("div");
    modal.id = "login-modal-overlay";
    // Important: display: none initially
    modal.style.cssText = "position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(5px); z-index: 9999; display: none; align-items: center; justify-content: center; padding: 20px; overflow-y: auto;";

    modal.innerHTML = `
      <div style="background: var(--bg-card); width: min(800px, 100%); max-height: 90vh; border-radius: 16px; border: 1px solid rgba(255,255,255,0.1); position: relative; overflow-y: auto; box-shadow: 0 20px 50px rgba(0,0,0,0.5);">
        <button type="button" id="close-login-modal" style="position: absolute; top: 16px; right: 16px; background: none; border: none; color: var(--muted); font-size: 24px; cursor: pointer; z-index: 10; padding: 10px;">&times;</button>
        <div class="auth-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 0;">
          <div style="padding: 40px; border-right: 1px solid rgba(255,255,255,0.05);">
            <h2 style="margin-top: 0;">Přihlášení</h2>
            <form id="modal-login-form">
              <div style="margin-bottom: 16px;"><label class="field-label">Email</label><input type="text" name="username" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: white;" autocomplete="username"></div>
              <div style="margin-bottom: 24px;"><label class="field-label">Heslo</label><input type="password" name="password" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: white;" autocomplete="current-password"></div>
              <button class="btn primary" type="submit" style="width: 100%; padding: 12px; cursor: pointer;">Přihlásit se</button>
              <div id="modal-login-notice" class="notice" style="display:none; margin-top: 16px;"></div>
            </form>
          </div>
          <div style="padding: 40px; background: rgba(255,255,255,0.02);">
            <h2 style="margin-top: 0;">Registrace</h2>
            <p style="font-size: 14px; color: var(--muted); margin-bottom: 24px;">Zaregistrujte se pro plný přístup.</p>
            <form id="modal-register-form">
              <div style="margin-bottom: 16px;"><label class="field-label">Váš Email</label><input type="email" name="email" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: white;" autocomplete="email"></div>
              
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 24px;">
                <div>
                   <label class="field-label">Heslo</label>
                   <input type="password" name="reg_password" required minlength="6" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: white;" autocomplete="new-password">
                </div>
                <div>
                   <label class="field-label">Heslo znovu</label>
                   <input type="password" name="reg_password_check" required minlength="6" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: white;" autocomplete="new-password">
                </div>
              </div>

              <button class="btn ghost" type="submit" style="width: 100%; padding: 12px; cursor: pointer;">Vytvořit účet</button>
              <div id="modal-reg-notice" class="notice" style="display:none; margin-top: 16px;"></div>
            </form>
          </div>
        </div>
      </div>
    `;
    document.body.appendChild(modal);

    // Attach Listeners
    const closeBtn = document.getElementById("close-login-modal");
    if (closeBtn) {
      closeBtn.onclick = function () { // Use onclick property for reliability
        console.log("Close clicked");
        document.getElementById("login-modal-overlay").style.display = "none";
      };
    }

    modal.onclick = function (e) {
      if (e.target === modal) {
        console.log("Overlay clicked");
        modal.style.display = "none";
      }
    };

    // LOGIN Logic
    const loginForm = document.getElementById("modal-login-form");
    if (loginForm) {
      loginForm.onsubmit = async function (e) { // Use onsubmit property
        e.preventDefault();
        console.log("Login submit");

        const submitBtn = loginForm.querySelector("button[type='submit']");
        const originalText = submitBtn.textContent;
        submitBtn.textContent = "Přihlašuji...";
        submitBtn.disabled = true;

        const notice = document.getElementById("modal-login-notice");
        const data = Object.fromEntries(new FormData(loginForm).entries());
        const emailInput = data.username.trim().toLowerCase();
        const passwordInput = data.password;

        notice.style.display = "none";

        try {
          const users = await getUsers(); // Ensure this is available
          const user = users.find(u => u.email.toLowerCase() === emailInput);

          let authenticated = false;
          if (user) {
            if (user.password && user.password === passwordInput) authenticated = true;
            if (user.tempPassword && user.tempPassword === passwordInput) authenticated = true;
          }

          if (authenticated) {
            const session = { email: user.email, role: user.role || "navstevnik" };
            localStorage.setItem(STORAGE.session, JSON.stringify(session));
            await refreshNav();
            document.getElementById("login-modal-overlay").style.display = "none";
            if (user.tempPassword === passwordInput) {
              alert("Použili jste dočasné heslo. Doporučujeme si ho změnit v profilu.");
            }
          } else {
            notice.textContent = "Chybné jméno nebo heslo.";
            notice.style.color = "#ff4d4d";
            notice.style.display = "block";
          }
        } catch (err) {
          console.error("Login error:", err);
          notice.textContent = "Chyba přihlášení.";
          notice.style.display = "block";
        } finally {
          submitBtn.textContent = originalText;
          submitBtn.disabled = false;
        }
      };
    }

    // REGISTER Logic
    const regForm = document.getElementById("modal-register-form");
    if (regForm) {
      regForm.onsubmit = async function (e) {
        e.preventDefault();
        console.log("Register submit");

        const submitBtn = regForm.querySelector("button[type='submit']");
        const originalText = submitBtn.textContent;
        submitBtn.textContent = "Vytvářím...";
        submitBtn.disabled = true;

        const notice = document.getElementById("modal-reg-notice");
        const formData = new FormData(regForm);
        const email = formData.get("email").trim().toLowerCase();
        const pass1 = formData.get("reg_password");
        const pass2 = formData.get("reg_password_check");

        notice.style.display = "none";

        try {
          if (!email) throw new Error("Zadejte email.");
          if (pass1 !== pass2) throw new Error("Hesla se neshodují.");
          if (pass1.length < 6) throw new Error("Heslo musí mít alespoň 6 znaků.");

          const users = await getUsers();
          if (users.find(u => u.email === email)) {
            throw new Error("Tento email je již registrován."); // Better error handling
          }

          // Register with manual password
          const res = await save('register_user', { email, password: pass1, manual: true });

          if (res && res.status === 'success') {
            // Auto-login
            const session = { email: email, role: "navstevnik" };
            localStorage.setItem(STORAGE.session, JSON.stringify(session));
            await refreshNav();

            notice.innerHTML = "Účet úspěšně vytvořen! Přesměrovávám na profil...";
            notice.style.color = "#2ed573";
            notice.style.display = "block";
            regForm.reset();

            setTimeout(() => {
              window.location.href = "profil.html";
            }, 1500);

          } else {
            throw new Error((res && res.error) || "Chyba API");
          }
        } catch (err) {
          console.error(err);
          notice.textContent = err.message;
          notice.style.color = "#ff4d4d";
          notice.style.display = "block";
        } finally {
          submitBtn.textContent = originalText;
          submitBtn.disabled = false;
        }
      };
    }
  }

  // Show the modal
  modal.style.display = "flex";
}


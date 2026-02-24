// navigation removed (no hamburger menu)
         
 // folders

const dropZone   = document.getElementById('dropZone');
if (dropZone) {
const fileInput  = document.getElementById('file-input');
const fileList   = document.getElementById('fileList');
const browseLink = document.getElementById('browseLink');
const hint       = document.getElementById('hint');

// Open file picker
browseLink.addEventListener('click', e => { e.stopPropagation(); fileInput.click(); });
dropZone.addEventListener('click', () => fileInput.click());
fileInput.addEventListener('change', () => handleFiles(fileInput.files));

// Drag events
['dragenter','dragover'].forEach(evt => {
  dropZone.addEventListener(evt, e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
});
['dragleave','dragend'].forEach(evt => {
  dropZone.addEventListener(evt, () => dropZone.classList.remove('drag-over'));
});
dropZone.addEventListener('drop', e => {
  e.preventDefault();
  dropZone.classList.remove('drag-over');
  handleFiles(e.dataTransfer.files);
});

function formatSize(bytes) {
  if (bytes >= 1073741824) return (bytes / 1073741824).toFixed(1) + ' GB';
  if (bytes >= 1048576)    return (bytes / 1048576).toFixed(1) + ' MB';
  if (bytes >= 1024)       return (bytes / 1024).toFixed(1) + ' KB';
  return bytes + ' B';
}

function handleFiles(files) {
  if (!files.length) return;
  [...files].forEach(uploadFile);
}

function uploadFile(file) {
  const ext  = (file.name.split('.').pop() || 'FILE').toUpperCase();
  const id   = 'f-' + Date.now() + '-' + Math.random().toString(36).slice(2);

  // Build item
  const item = document.createElement('div');
  item.className = 'file-item';
  item.id = id;
  item.innerHTML = `
    <div class="file-badge" id="${id}-badge">${ext}</div>
    <div class="file-info">
      <div class="file-name">${escHtml(file.name)}</div>
      <div class="file-meta">${formatSize(file.size)}</div>
      <div class="progress-wrap"><div class="progress-bar" id="${id}-bar"></div></div>
    </div>
    <span class="file-status uploading" id="${id}-status">Uploading…</span>
    <button class="remove-btn" title="Remove" onclick="removeItem('${id}')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
      </svg>
    </button>
  `;
  fileList.appendChild(item);
  hint.style.display = 'block';

  // XHR with progress
  const formData = new FormData();
  formData.append('files[]', file);

  const xhr = new XMLHttpRequest();
  xhr.open('POST', '');

  xhr.upload.addEventListener('progress', e => {
    if (e.lengthComputable) {
      const pct = Math.round(e.loaded / e.total * 100);
      document.getElementById(`${id}-bar`).style.width = pct + '%';
    }
  });

  xhr.addEventListener('load', () => {
    const bar    = document.getElementById(`${id}-bar`);
    const status = document.getElementById(`${id}-status`);
    const badge  = document.getElementById(`${id}-badge`);
    bar.style.width = '100%';

    try {
      const results = JSON.parse(xhr.responseText);
      const res = results[0];
      if (res && res.success) {
        status.textContent = 'Done';
        status.className   = 'file-status done';
        badge.className    = 'file-badge success';
      } else {
        status.textContent = 'Failed';
        status.className   = 'file-status failed';
        badge.className    = 'file-badge error';
      }
    } catch {
      status.textContent = 'Error';
      status.className   = 'file-status failed';
      badge.className    = 'file-badge error';
    }
  });

  xhr.addEventListener('error', () => {
    document.getElementById(`${id}-status`).textContent = 'Error';
    document.getElementById(`${id}-status`).className   = 'file-status failed';
    document.getElementById(`${id}-badge`).className    = 'file-badge error';
  });

  xhr.send(formData);

  // Reset input so same file can be re-added
  fileInput.value = '';
}

function removeItem(id) {
  const el = document.getElementById(id);
  if (el) {
    el.style.transition = 'opacity 0.2s, transform 0.2s';
    el.style.opacity = '0';
    el.style.transform = 'translateX(12px)';
    setTimeout(() => {
      el.remove();
      if (!fileList.children.length) hint.style.display = 'none';
    }, 200);
  }
}

function escHtml(str) {
  return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

} // end folders guard

 //
         
  new WOW().init();
         
 // All animations will take twice the time to accomplish
 document.documentElement.style.setProperty('--animate-duration', '5s');
<?php @ini_set('display_errors','1'); @error_reporting(E_ALL); ?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>OCR Voucher – CDN Only</title>
<!-- Library OCR dari CDN -->
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
<style>
:root{--bg:#f7fafc;--card:#fff;--txt:#0f172a;--muted:#64748b;--border:#e5e7eb;--border2:#d1d5db;--acc:#16a34a;--acc2:#0ea5e9;--warn:#f59e0b;--danger:#ef4444;--shadow:0 10px 30px rgba(2,6,23,.08);--br:16px;--pad:14px}
*{box-sizing:border-box}body{margin:0;background:linear-gradient(180deg,#f8fafc,#f1f5f9);color:var(--txt);font:14px/1.5 system-ui,Segoe UI,Roboto,Helvetica,Arial}
.wrap{max-width:1200px;margin:0 auto;padding:16px}
h1{margin:0 0 6px;font-size:18px}.muted{color:var(--muted)}
.top{display:flex;gap:12px;align-items:center}
.prog{display:flex;gap:12px;align-items:center;margin-left:auto;min-width:320px}
.bar{background:#e2e8f0;border-radius:999px;height:12px;overflow:hidden;flex:1}
.bar>span{display:block;height:100%;background:linear-gradient(90deg,var(--acc2),var(--acc));width:0%}
.row{display:grid;grid-template-columns:1.1fr .9fr;gap:16px;margin-top:12px}
@media(max-width:1100px){.row{grid-template-columns:1fr}}
.card{background:var(--card);border:1px solid var(--border);border-radius:var(--br);box-shadow:var(--shadow);padding:var(--pad)}
/* Upload frame */
.uploader{display:grid;grid-template-columns:auto 1fr;gap:14px;align-items:center;border:2px dashed var(--border2);border-radius:20px;padding:20px;background:#fcfdff;cursor:pointer;transition:.2s}
.uploader:hover{border-color:#93c5fd;background:#f5faff}
.uploader.drag{background:#eef6ff;border-color:#93c5fd}
.icon{width:56px;height:56px;border-radius:14px;background:#eef2ff;border:1px solid #dde3ff;display:grid;place-items:center;font-size:26px}
.uploader h2{margin:0 0 2px 0;font-size:16px}
.small{font-size:12px;color:var(--muted)}
/* Buttons */
.btn{border:1px solid var(--border2);background:#fff;padding:10px 14px;border-radius:12px;cursor:pointer;transition:.15s}
.btn:hover{box-shadow:0 1px 0 rgba(0,0,0,.02);transform:translateY(-1px)}
.btn.primary{background:linear-gradient(90deg,#bae6fd,#bbf7d0);border:1px solid #93c5fd;font-weight:700}
.btn.warn{background:linear-gradient(90deg,#fde68a,#fdba74);border:1px solid #f59e0b}
.btn.danger{background:linear-gradient(90deg,#fecaca,#fda4af);border:1px solid #ef4444}
/* List items */
.item{border:1px solid var(--border);border-radius:14px;padding:12px;background:#fff;margin-bottom:12px}
.thumb{width:100%;aspect-ratio:16/10;object-fit:contain;background:#f8fafc;border:1px solid var(--border);border-radius:10px;margin-bottom:8px}
.kv{display:grid;grid-template-columns:110px 1fr;gap:6px 10px;margin-top:8px}.kv .k{color:#475569}
.mono{font-family:ui-monospace,Menlo,Consolas,monospace;white-space:pre-wrap;word-break:break-word}
.copybox{border:1px dashed #cbd5e1;border-radius:10px;padding:8px;background:#f8fafc;cursor:pointer;position:relative}
.copybox:hover{background:#eef2f7}.copybtn{margin-top:6px}
.tooltip{position:absolute;right:8px;top:-10px;background:#fff;border:1px solid var(--border);color:#065f46;padding:2px 6px;border-radius:8px;font-size:12px;opacity:0;pointer-events:none;transform:translateY(-6px);transition:.2s}
.copybox.copied .tooltip{opacity:1;transform:none}
.log{margin-top:10px;padding:8px;border:1px dashed #cbd5e1;background:#f8fafc;border-radius:10px;height:160px;overflow:auto;font-size:12px;white-space:pre-wrap}
.combo{display:grid;grid-template-columns:1fr auto;gap:8px;align-items:start}.combo .copybox{min-height:120px}
select{border:1px solid var(--border2);border-radius:10px;padding:8px 10px;background:#fff}
</style>
</head>
<body>
<div class="wrap">
  <div class="top">
    <div>
      <h1>OCR Voucher – CDN Only</h1>
      <div class="muted">jsDelivr + tessdata (eng / eng+ind) · Upload rapi · 1 tombol OCR · Hasil gabungan</div>
    </div>
    <div class="prog">
      <div class="bar"><span id="bar"></span></div>
      <div class="small" id="status">Siap.</div>
    </div>
  </div>

  <div class="row">
    <!-- LEFT: Upload & Controls -->
    <section class="card">
      <label class="uploader" id="drop">
        <div class="icon">📄</div>
        <div>
          <h2>Tarik & lepas gambar di sini, atau klik untuk pilih</h2>
          <div class="small">Multi gambar (JPG/PNG). Data lama tetap ada saat menambah.</div>
          <input id="file" type="file" accept="image/*" multiple hidden>
        </div>
      </label>

      <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:12px;align-items:center">
        <label>Bahasa:
          <select id="lang">
            <option value="eng" selected>eng (stabil)</option>
            <option value="eng+ind">eng+ind</option>
          </select>
        </label>
        <button class="btn primary" id="run" type="button">Mulai OCR</button>
        <button class="btn" id="save" type="button">Simpan Ingatan</button>
        <button class="btn warn" id="load" type="button">Muat Ingatan</button>
        <button class="btn danger" id="clear" type="button">Clear</button>
      </div>

      <div class="log" id="log"></div>
    </section>

    <!-- RIGHT: Results + Combined -->
    <section class="card">
      <h3 style="margin:4px 0 10px">Hasil Per Gambar</h3>
      <div id="list"></div>

      <hr style="margin:14px 0;border:none;border-top:1px solid var(--border)">

      <h3 style="margin:0 0 8px">Hasil Gabungan</h3>
      <div style="display:flex;gap:8px;align-items:center;margin-bottom:8px">
        <label class="small">Format:
          <select id="fmt">
            <option value="pipe" selected>Pipe | (Kode | RpNominal | NamaFile)</option>
            <option value="csv">CSV</option>
            <option value="jsonl">JSON Lines</option>
          </select>
        </label>
        <span class="small muted">Klik kotak untuk copy</span>
      </div>
      <div class="combo">
        <div class="copybox mono" id="combined">(kosong)<div class="tooltip">Tersalin!</div></div>
        <button class="btn" id="copyCombined" type="button">Copy</button>
      </div>
    </section>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
/* ===== Helpers ===== */
const el={bar:document.getElementById('bar'),status:document.getElementById('status'),log:document.getElementById('log'),
  drop:document.getElementById('drop'),file:document.getElementById('file'),lang:document.getElementById('lang'),
  run:document.getElementById('run'),save:document.getElementById('save'),load:document.getElementById('load'),clear:document.getElementById('clear'),
  list:document.getElementById('list'),combined:document.getElementById('combined'),copyCombined:document.getElementById('copyCombined'),fmt:document.getElementById('fmt')};
const state={items:[]};
function logln(s){el.log.textContent+=s+'\n'; el.log.scrollTop=el.log.scrollHeight;}
function setStatus(s){el.status.textContent=s; logln(s);}
function setProgress(p){el.bar.style.width=Math.max(0,Math.min(100,p))+'%';}
function esc(s){return (s||'').replace(/[<>&]/g,c=>({'<':'&lt;','>':'&gt;','&':'&amp;'}[c]||c));}

/* ===== Upload UI ===== */
el.drop.addEventListener('click',()=>el.file.click());
['dragenter','dragover'].forEach(ev=>el.drop.addEventListener(ev,e=>{e.preventDefault();e.stopPropagation();el.drop.classList.add('drag');}));
['dragleave','drop'].forEach(ev=>el.drop.addEventListener(ev,e=>{e.preventDefault();e.stopPropagation();el.drop.classList.remove('drag');}));
el.drop.addEventListener('drop',e=>addFiles(e.dataTransfer.files));
el.file.addEventListener('change',e=>addFiles(e.target.files));
function addFiles(fs){Array.from(fs||[]).forEach(f=>{if(!f.type.startsWith('image/'))return;
  const id='id_'+Date.now()+'_'+Math.random().toString(36).slice(2,7);
  const url=URL.createObjectURL(f);
  state.items.push({id,fileName:f.name,url,kode:'',nominal:'',raw:''});
}); render(); updateCombined();}

/* ===== Tesseract Worker (CDN) ===== */
const CDN={
  worker:'https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/worker.min.js',
  core:'https://cdn.jsdelivr.net/npm/tesseract.js-core@5/dist/tesseract-core.wasm.js',
  lang:'https://tessdata.projectnaptha.com/5'
};
const Engine={
  worker:null,
  async init(lang){
    setStatus('Menyiapkan engine OCR…');
    this.worker=await Tesseract.createWorker({
      workerPath:CDN.worker,
      corePath:CDN.core,
      langPath:CDN.lang,
      logger:m=>{ if(m&&m.status){ logln(`[${m.status}] ${m.progress!=null?(m.progress*100|0)+'%':''}`); } }
    });
    await this.worker.loadLanguage(lang);
    await this.worker.initialize(lang);
    await this.worker.setParameters({user_defined_dpi:'300',preserve_interword_spaces:'1',tessedit_pageseg_mode:'6'});
    setStatus('Engine siap (CDN).');
  },
  async ocr(img, params){ if(params) await this.worker.setParameters(params); return await this.worker.recognize(img); },
  async end(){ if(this.worker){ try{await this.worker.terminate();}catch{} } this.worker=null; }
};

/* ===== Preprocess (upscale + grayscale + contrast) ===== */
function preprocessCanvas(img){
  const s=2,w=Math.round(img.naturalWidth*s),h=Math.round(img.naturalHeight*s);
  const cv=document.createElement('canvas'); cv.width=w; cv.height=h;
  const ctx=cv.getContext('2d'); ctx.imageSmoothingEnabled=false; ctx.drawImage(img,0,0,w,h);
  const id=ctx.getImageData(0,0,w,h), d=id.data; let min=255,max=0;
  for(let i=0;i<d.length;i+=4){const g=(d[i]*0.299+d[i+1]*0.587+d[i+2]*0.114)|0; d[i]=d[i+1]=d[i+2]=g; if(g<min)min=g;if(g>max)max=g;}
  const range=Math.max(1,max-min); for(let i=0;i<d.length;i+=4){let g=d[i]; g=((g-min)*255/range)|0; d[i]=d[i+1]=d[i+2]=g;}
  ctx.putImageData(id,0,0); return cv;
}

/* ===== Ekstraksi aturanmu ===== */
function extractFields(fullText){
  const txt=fullText.replace(/\r/g,'\n').split('\n').map(s=>s.replace(/\s+/g,' ').trim()).filter(Boolean).join(' ');
  // 1 kata setelah "Kode Voucher" sampai sebelum "valid until/berlaku sampai"
  let kode=''; const mK=txt.match(/kode\s*voucher\s+([A-Z0-9\-]+)(?=(?:.*?(valid\s*until|berlaku\s*sampai))|[\s]|$)/i); if(mK) kode=mK[1].toUpperCase();
  // 1 kata setelah "Rp/IDR"
  let nominal=''; const mN=txt.match(/\b(?:Rp|IDR)\s*([0-9][0-9\.\,]*)/i); if(mN) nominal='Rp '+mN[1];
  return {kode,nominal};
}

/* ===== Run OCR ===== */
async function run(){
  if(!state.items.length){ alert('Belum ada gambar'); return; }
  setProgress(0); el.log.textContent='';
  try{ await Engine.init(el.lang.value||'eng'); }catch(e){ setStatus('Gagal init: '+(e.message||e)); return; }

  for(let i=0;i<state.items.length;i++){
    const it=state.items[i]; setStatus('Memproses: '+(it.fileName||it.id));
    const img=await new Promise((res,rej)=>{const im=new Image();im.onload=()=>res(im);im.onerror=rej;im.src=it.url;});
    const cv=preprocessCanvas(img);
    try{
      const r=await Engine.ocr(cv,{tessedit_pageseg_mode:'6'});
      it.raw=r.data.text||'';
      const f=extractFields(it.raw); it.kode=f.kode; it.nominal=f.nominal;
    }catch(e){ it.raw='(gagal OCR) '+e; }
    render(); updateCombined(); setProgress(((i+1)/state.items.length)*100|0);
  }
  setStatus('Selesai.');
}

/* ===== Render per item (klik-untuk-copy) ===== */
function copyToClipboard(text){ return navigator.clipboard.writeText(text).then(()=>true).catch(()=>false); }
function clickableSpan(text){ const t=esc(text||''); return `<span class="copybox mono" data-copy="${esc(text||'')}">${t}<div class="tooltip">Tersalin!</div></span>`; }
function card(it){return `
  <div class="item">
    <img class="thumb" src="${it.url||''}" alt="">
    <div class="kv"><div class="k">Nama</div><div class="v mono">${esc(it.fileName||'(tersimpan)')}</div></div>
    <div class="kv"><div class="k">Kode</div><div class="v">${clickableSpan(it.kode||'')}</div></div>
    <div class="kv"><div class="k">Nominal</div><div class="v">${clickableSpan(it.nominal||'')}</div></div>
    <details style="margin-top:6px"><summary class="small">Teks OCR</summary><div class="small mono">${esc(it.raw||'')}</div></details>
    <div style="display:flex;gap:8px;margin-top:6px">
      <button class="btn" data-btncopy="${esc((it.kode||'')+' '+(it.nominal||''))}">Copy Kode+Nominal</button>
    </div>
  </div>`;}
function render(){
  el.list.innerHTML = state.items.length? state.items.map(card).join('') : '<div class="muted">Belum ada data.</div>';
  // Delegasi: klik-untuk-copy
  el.list.querySelectorAll('.copybox').forEach(node=>{
    node.addEventListener('click', async ()=>{
      const ok=await copyToClipboard(node.getAttribute('data-copy')||'');
      if(ok){ node.classList.add('copied'); setTimeout(()=>node.classList.remove('copied'),650); }
    });
  });
  el.list.querySelectorAll('[data-btncopy]').forEach(btn=>{
    btn.addEventListener('click', async ()=>{
      const ok=await copyToClipboard(btn.getAttribute('data-btncopy')||'');
      if(ok){ btn.textContent='Tersalin!'; setTimeout(()=>btn.textContent='Copy Kode+Nominal',700); }
    });
  });
}

/* ===== Hasil gabungan ===== */
function buildCombined(){
  const rows=state.items.map(it=>({kode:it.kode||'', nominal:it.nominal||'', filename:it.fileName||''}));
  const fmt=el.fmt.value;
  if(fmt==='csv'){
    const H='kode,nominal,filename';
    const B=rows.map(r=>[r.kode,r.nominal,r.filename].map(v=>{const s=(v||'').replace(/"/g,'""');return /[",\n]/.test(s)?`"${s}"`:s;}).join(',')).join('\n');
    return H+'\n'+B;
  }else if(fmt==='jsonl'){
    return rows.map(r=>JSON.stringify(r)).join('\n');
  }
  return rows.map(r=>`${r.kode} | ${r.nominal} | ${r.filename}`).join('\n');
}
function updateCombined(){
  const text=buildCombined()||'(kosong)';
  el.combined.textContent=text;
  const tip=document.createElement('div'); tip.className='tooltip'; tip.textContent='Tersalin!'; el.combined.appendChild(tip);
}
el.fmt.addEventListener('change', updateCombined);
el.combined.addEventListener('click', async ()=>{ const t=buildCombined(); if(await copyToClipboard(t)){ el.combined.classList.add('copied'); setTimeout(()=>el.combined.classList.remove('copied'),650); }});
el.copyCombined.addEventListener('click', async ()=>{ const t=buildCombined(); if(await copyToClipboard(t)){ el.combined.classList.add('copied'); setTimeout(()=>el.combined.classList.remove('copied'),650); }});

/* ===== IndexedDB (Simpan/Muat/Clear) ===== */
const DB='ocr_cdn_mem', STORE='items'; let idb;
function idbOpen(){return new Promise((res,rej)=>{const r=indexedDB.open(DB,1);
  r.onupgradeneeded=e=>{const db=e.target.result;if(!db.objectStoreNames.contains(STORE))db.createObjectStore(STORE,{keyPath:'id'});};
  r.onsuccess=e=>{idb=e.target.result;res();}; r.onerror=e=>rej(e.target.error);});}
async function idbPut(o){await idbOpen();return new Promise((res,rej)=>{const tx=idb.transaction([STORE],'readwrite');tx.objectStore(STORE).put(o);tx.oncomplete=()=>res();tx.onerror=e=>rej(e.target.error);});}
async function idbAll(){await idbOpen();return new Promise((res,rej)=>{const tx=idb.transaction([STORE],'readonly');const q=tx.objectStore(STORE).getAll();q.onsuccess=()=>res(q.result||[]);q.onerror=e=>rej(e.target.error);});}
async function idbClr(){await idbOpen();return new Promise((res,rej)=>{const tx=idb.transaction([STORE],'readwrite');tx.objectStore(STORE).clear();tx.oncomplete=()=>res();tx.onerror=e=>rej(e.target.error);});}
async function saveMem(){ for(const it of state.items){ await idbPut({id:it.id,fileName:it.fileName,url:it.url,raw:it.raw||'',kode:it.kode||'',nominal:it.nominal||''}); } setStatus('Tersimpan.'); }
async function loadMem(){ const rows=await idbAll(); state.items.push(...rows.map(r=>({id:r.id,fileName:r.fileName,url:r.url,raw:r.raw,kode:r.kode,nominal:r.nominal}))); render(); updateCombined(); setStatus('Dimuat '+rows.length+' item.'); }
async function clearMem(){ if(!confirm('Hapus semua ingatan?'))return; await idbClr(); state.items=[]; render(); updateCombined(); setProgress(0); setStatus('Bersih.'); }
el.save.addEventListener('click', saveMem);
el.load.addEventListener('click', loadMem);
el.clear.addEventListener('click', clearMem);

/* ===== Bind OCR ===== */
el.run.addEventListener('click', run);

/* ===== Init ===== */
setStatus('Siap. Tambah gambar → pilih Bahasa → “Mulai OCR”.');
}); // DOMContentLoaded
</script>
</body>
</html>

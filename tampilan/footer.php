  <footer class="sf">
    <span>&copy; <?=date('Y')?> <strong>CURA-LOG</strong> &mdash; Sistem Manajemen Inventaris Alat Medis</span>
    <span style="color:var(--p);font-weight:600;">RSUD v1.0</span>
  </footer>
</div><!-- /.main -->
</div><!-- /.wrap -->

<script>
/* ── Sidebar ──────────────────────────────────────────────── */
function toggleSb(){
  const s=document.getElementById('sidebar'),o=document.getElementById('sbOvl');
  s.classList.toggle('open');o.style.display=s.classList.contains('open')?'block':'none';
}
function closeSb(){
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sbOvl').style.display='none';
}

/* ── Toast ────────────────────────────────────────────────── */
const TICO={success:'bi-check-circle-fill',error:'bi-x-circle-fill',
            warning:'bi-exclamation-triangle-fill',info:'bi-info-circle-fill'};
function showToast(type,msg,dur){
  dur=dur||3800;
  const box=document.getElementById('toastBox');
  const t=document.createElement('div');
  t.className='toast '+type;
  t.innerHTML=`<i class="bi ${TICO[type]||TICO.info} t-ico"></i>
    <span class="t-msg">${msg}</span>
    <button class="t-x" onclick="this.closest('.toast').remove()">
      <i class="bi bi-x"></i></button>`;
  box.appendChild(t);
  setTimeout(()=>{t.style.transition='opacity .3s';t.style.opacity='0';
    setTimeout(()=>t.remove(),320);},dur);
}
/* PHP-injected toast */
<?php $toast=popToast(); if($toast): ?>
document.addEventListener('DOMContentLoaded',()=>{
  showToast('<?=$toast['type']?>','<?=addslashes(htmlspecialchars($toast['msg']))?>');
});
<?php endif; ?>

/* ── Modal helpers ────────────────────────────────────────── */
function openModal(id){const o=document.getElementById(id);if(o)o.classList.add('on');}
function closeModal(id){const o=document.getElementById(id);if(o)o.classList.remove('on');}
document.addEventListener('click',e=>{if(e.target.classList.contains('overlay'))e.target.classList.remove('on');});
document.addEventListener('keydown',e=>{if(e.key==='Escape')document.querySelectorAll('.overlay.on').forEach(o=>o.classList.remove('on'));});

/* ── Confirm delete ───────────────────────────────────────── */
function confirmDel(msg,url){
  if(!confirm(msg||'Yakin ingin menghapus data ini? Tindakan tidak dapat dibatalkan.'))return false;
  if(url)window.location=url;
  return true;
}

/* ── Live table search ────────────────────────────────────── */
function liveSearch(inputId,tableId){
  const inp=document.getElementById(inputId);
  const tbl=document.getElementById(tableId);
  if(!inp||!tbl)return;
  inp.addEventListener('input',function(){
    const q=this.value.toLowerCase().trim();
    const rows=tbl.querySelectorAll('tbody tr');
    let vis=0;
    rows.forEach(r=>{
      const txt=r.textContent.toLowerCase();
      const show=q===''||txt.includes(q);
      r.style.display=show?'':'none';
      if(show)vis++;
    });
    const emp=tbl.querySelector('.empty-row');
    if(emp) emp.style.display=vis===0?'':'none';
  });
}
</script>
<?php if(isset($extraJS)) echo $extraJS; ?>
</body>
</html>

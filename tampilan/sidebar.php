<?php
$cur = basename($_SERVER['PHP_SELF']);
$nav = [
  ['h'=>BASE_URL.'index.php',              'i'=>'bi-speedometer2', 'l'=>'Dashboard',      'r'=>['admin','dokter','organizer']],
  ['h'=>BASE_URL.'fungsi/inventory.php',   'i'=>'bi-box-seam',     'l'=>'Inventaris Alat','r'=>['admin','dokter','organizer']],
  ['h'=>BASE_URL.'fungsi/tambah_alat.php', 'i'=>'bi-plus-circle',  'l'=>'Tambah Alat',    'r'=>['admin','organizer']],
  ['h'=>BASE_URL.'fungsi/history.php',     'i'=>'bi-clock-history','l'=>'Histori Pinjam', 'r'=>['admin','dokter','organizer']],
];
$mgmt = [
  ['h'=>BASE_URL.'fungsi/users.php',  'i'=>'bi-people',  'l'=>'Manajemen User'],
  ['h'=>BASE_URL.'fungsi/ruangan.php','i'=>'bi-building','l'=>'Data Ruangan'],
];
?>
<aside class="sidebar" id="sidebar">
  <div class="sb-brand">
    <div class="b-ico"><i class="bi bi-heart-pulse-fill"></i></div>
    <div>
      <div class="b-name">CURA-LOG</div>
      <div class="b-sub">Inventaris Alat Medis</div>
    </div>
  </div>

  <div class="sb-sec">
    <div class="sb-sec-label">Menu Utama</div>
    <ul class="nav-list">
      <?php foreach($nav as $n):
        if(!in_array($ME['role'],$n['r'],true)) continue;
        $a=($cur===basename($n['h']))?'active':''; ?>
      <li><a class="nav-link <?=$a?>" href="<?=$n['h']?>">
        <i class="bi <?=$n['i']?>"></i><span><?=$n['l']?></span>
      </a></li>
      <?php endforeach; ?>
    </ul>
  </div>

  <?php if($ME['role']==='admin'): ?>
  <div class="sb-sec">
    <div class="sb-sec-label">Manajemen</div>
    <ul class="nav-list">
      <?php foreach($mgmt as $m):
        $a=($cur===basename($m['h']))?'active':''; ?>
      <li><a class="nav-link <?=$a?>" href="<?=$m['h']?>">
        <i class="bi <?=$m['i']?>"></i><span><?=$m['l']?></span>
      </a></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <div class="sb-user">
    <div class="u-card">
      <div class="u-ava"><?=strtoupper(mb_substr($ME['nama'],0,1))?></div>
      <div style="flex:1;min-width:0">
        <div class="u-name"><?=htmlspecialchars(mb_strimwidth($ME['nama'],0,20,'…'))?></div>
        <div class="u-role"><?=ucfirst($ME['role'])?></div>
      </div>
      <a href="<?=BASE_URL?>logout.php" class="btn-lo" title="Keluar">
        <i class="bi bi-box-arrow-right"></i>
      </a>
    </div>
  </div>
</aside>

<div class="main">
  <header class="topbar">
    <div class="tb-left">
      <button class="menu-btn" onclick="toggleSb()"><i class="bi bi-list"></i></button>
      <div>
        <div class="tb-title"><?=htmlspecialchars($pageTitle??'Dashboard')?></div>
        <div class="tb-sub"><?=date('l, d F Y')?> &middot; <?=ucfirst($ME['role'])?></div>
      </div>
    </div>
    <div class="tb-right">
      <span class="tb-chip">
        <i class="bi bi-person-circle"></i>
        <?=htmlspecialchars($ME['nama'])?>
      </span>
      <a href="<?=BASE_URL?>logout.php" class="btn btn-o btn-sm"
         style="color:var(--red);border-color:var(--red);">
        <i class="bi bi-box-arrow-right"></i> Keluar
      </a>
    </div>
  </header>

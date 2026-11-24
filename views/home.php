<section class="hero"><div class="container hero-grid">
 <div class="hero-copy">
   <span class="eyebrow"><span class="eyebrow-dot"></span> <?=h($eventMonth)?> · FTI Universitas Sebelas April</span>
   <h1>Karya mahasiswa.<br><em>Dampak nyata.</em></h1>
   <p><?=h(setting('event_description'))?> Jelajahi solusi digital yang dikembangkan melalui proyek kolaboratif Semester V dan dipersiapkan untuk <?=h($eventName)?>.</p>
   <div class="hero-actions"><a href="#projects" class="btn primary large">Jelajahi Proyek <span aria-hidden="true">↗</span></a><?php if(!current_user()):?><a href="<?=h(route_url('login'))?>" class="btn ghost-light large">Akses Pengumpulan</a><?php else:?><a href="<?=h(route_url('dashboard'))?>" class="btn ghost-light large">Buka Dashboard</a><?php endif;?></div>
   <div class="hero-meta"><span><strong><?=number_format($publicCount)?></strong> proyek terpublikasi</span><span><strong><?=h($eventMonth)?></strong> showcase FTI</span></div>
 </div>
 <div class="hero-art" aria-hidden="true"><div class="art-ring ring-one"></div><div class="art-ring ring-two"></div><div class="art-card art-main"><div class="art-icon">⌘</div><div class="art-small">INNOVATION LAB</div><div class="art-headline">From idea<br>to impact.</div><div class="art-lines"><span></span><span></span><span></span></div></div><div class="art-card art-float"><div class="art-mini">✦</div><div><small>STUDENT BUILDERS</small><strong>Future-ready</strong></div></div><div class="art-floater">✳</div></div>
</div></section>
<section class="container info-strip" aria-label="Tahapan proyek"><div class="info-item"><span class="info-num">01</span><div><strong>Kumpulkan</strong><small>Unggah laporan, poster, dan tautan demo</small></div></div><div class="info-divider"></div><div class="info-item"><span class="info-num">02</span><div><strong>Kurasi</strong><small>Verifikasi oleh dosen dan pengelola</small></div></div><div class="info-divider"></div><div class="info-item"><span class="info-num">03</span><div><strong>Tampilkan</strong><small>Seminar atau booth saat FTI Fest</small></div></div></section>
<section id="projects" class="container section-pad">
 <div class="section-head"><div><span class="section-kicker">SHOWCASE KARYA</span><h2>Galeri Proyek</h2><p>Eksplorasi inovasi digital yang siap diperkenalkan kepada sivitas akademika dan pengunjung.</p></div><span class="count-label"><?=number_format($count)?> proyek</span></div>
 <form class="filter-bar" method="get" action="<?=h(app_url('/index.php'))?>" role="search">
 <input type="hidden" name="page" value="home"><label class="sr-only" for="searchq">Cari proyek</label><input id="searchq" class="input search" type="search" name="q" placeholder="Cari nama produk, kelompok, atau solusi..." value="<?=h($q)?>">
 <label class="sr-only" for="cat">Kategori</label><select id="cat" name="category"><option value="">Semua kategori</option><?php foreach(['campus','umkm','agritech','tourism','environment','community','other'] as $cat):?><option value="<?=$cat?>" <?=$category===$cat?'selected':''?>><?=h(category_name($cat))?></option><?php endforeach;?></select>
 <button type="submit" class="btn primary">Cari proyek</button>
 </form>
 <?php if(!$projects):?><div class="empty-state"><div class="empty-icon">⌁</div><h3>Belum ada proyek yang cocok</h3><p>Coba ubah kata kunci atau kategori. Proyek baru akan muncul setelah disetujui dan dipublikasikan.</p><a class="btn secondary" href="<?=h(route_url('home'))?>">Tampilkan Semua</a></div><?php else:?>
 <div class="project-grid">
 <?php foreach($projects as $p):?>
 <article class="project-card"><a class="project-visual" href="<?=h(public_project_url($p))?>">
 <?php if($p['cover_file']||$p['poster_file']):?><img src="<?=h(app_url('/download.php?id='.(int)$p['id'].'&type='.($p['cover_file']?'cover':'poster')))?>" alt="Ilustrasi proyek <?=h($p['title'])?>" loading="lazy"><?php else:?><div class="project-placeholder"><span><?=h(mb_safe(strtoupper(category_name($p['category'])),24))?></span><i>✦</i></div><?php endif;?>
 <?php if((int)$p['featured']):?><span class="featured-tag">✦ Pilihan Kurator</span><?php endif;?></a>
 <div class="project-card-body"><span class="category-label"><?=h(category_name($p['category']))?></span><h3><a href="<?=h(public_project_url($p))?>"><?=h($p['title'])?></a></h3><p><?=h($p['short_description'])?></p><div class="card-footer"><span><?=h($p['group_name'])?> · <?=h($p['class_name'])?></span><a href="<?=h(public_project_url($p))?>" aria-label="Buka proyek <?=h($p['title'])?>" class="round-arrow">↗</a></div></div>
 </article>
 <?php endforeach; ?></div>
 <?php endif;?>
 <?php if($count>$size):?><nav aria-label="Halaman proyek" class="pagination"><?php for($pg=1;$pg<=min(20,(int)ceil($count/$size));$pg++):?><a href="<?=h(route_url('home',['q'=>$q,'category'=>$category,'n'=>$pg]))?>" <?=$pg===$n?'aria-current="page"':''?>><?=$pg?></a><?php endfor;?></nav><?php endif;?>
</section>
<section class="callout-band"><div class="container callout-content"><div><span class="section-kicker">PUNYA KARYA UNTUK DITAMPILKAN?</span><h2>Berikan proyekmu panggung.</h2><p>Mahasiswa semester 5 dapat mengumpulkan dan memperbarui proyek melalui akun tim.</p></div><a class="btn light large" href="<?=h(route_url(current_user()?'dashboard':'register'))?>">Mulai Pengumpulan ↗</a></div></section>

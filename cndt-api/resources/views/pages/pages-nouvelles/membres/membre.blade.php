@extends('layouts.site')

@section('title', "Fiche | CNDT Cameroun")

@section('description', "Fiche individuelle d'un membre du personnel du Comité National de Développement des Technologies.")

@section('content')
  <main id="contenu">

    <!-- =========================================================
         HERO du profil (alimenté par l'API : ?slug=…)
         ========================================================= -->
    <section class="profile-hero" data-membre-hero>
      <img class="profile-hero__bg" src="../assets/S-3.webp" alt="" aria-hidden="true">

      <div class="container profile-hero__inner">

        <div class="profile-hero__body">

          <figure class="profile-hero__photo">
            <img data-membre-photo src="../assets/logo-cndt.webp" alt="" width="480" height="640">
          </figure>

          <div class="profile-hero__info">
            <h1 data-membre-nom>Chargement…</h1>
            <p class="profile-hero__role" data-membre-fonction></p>

            <ul class="profile-hero__meta" aria-label="Informations clés" data-membre-meta>
              <li data-meta="grade" hidden><span>Grade</span><strong></strong></li>
              <li data-meta="specialite" hidden><span>Spécialité</span><strong></strong></li>
              <li data-meta="fonction" hidden><span>Fonction</span><strong></strong></li>
              <li data-meta="depuis" hidden><span>Depuis</span><strong></strong></li>
            </ul>
          </div>

        </div>
      </div>
    </section>

    <section class="inner-section">
      <div class="container">

        <!-- =========================================================
             LAYOUT : contenu + sidebar
             ========================================================= -->
        <div class="profile-layout">

          <!-- ---------- Contenu principal ---------- -->
          <article class="project-content">

            <!-- ============ Biographie ============ -->
            <section id="biographie" class="project-section" data-membre-biographie>
              <h2>Biographie</h2>
              <p data-membre-bio>La biographie de cette personne n'a pas encore été renseignée.</p>
            </section>

            <!-- ============ Centres d'intérêts ============ -->
            <section id="interets" class="project-section" data-membre-interets hidden>
              <h2>Centres d'intérêts</h2>
              <ul class="chip-list" aria-label="Domaines d'expertise et centres d'intérêts" data-membre-chips></ul>
            </section>

          </article>

          <!-- ---------- SIDEBAR ---------- -->
          <aside class="sidebar" aria-label="Actualités et publications du CNDT">
            <section class="news-agenda" aria-labelledby="membre-news-title">
              <div class="sidebar-heading">
                <h2 id="membre-news-title">Actualités</h2>
              </div>
              <div class="agenda-list" data-news-list></div>
              <a class="sidebar-more" href="/actualites">Toutes les actualités <span aria-hidden="true">→</span></a>
            </section>

            <section id="ressources" class="resource-module" aria-labelledby="membre-resources-title">
              <h2 id="membre-resources-title">Publications &amp; médias</h2>
              <nav aria-label="Publications et médias">
                <a href="/publications"><span aria-hidden="true">›</span>Techno Mag</a>
                <a href="https://www.youtube.com/channel/UC3OfHkDXBNVGwiI8henjNww" target="_blank" rel="noopener"><span aria-hidden="true">›</span>Émissions TV</a>
                <a href="/galerie"><span aria-hidden="true">›</span>Photothèque</a>
                <a href="#"><span aria-hidden="true">›</span>Innovation</a>
                <a href="#"><span aria-hidden="true">›</span>Rapports</a>
                <a href="/contact"><span aria-hidden="true">›</span>Contact</a>
              </nav>
            </section>
          </aside>
        </div>

        <!-- Retour -->
        <div class="project-back">
          <a class="text-link" href="/personnel">← Retour au personnel du CNDT</a>
        </div>

      </div>
    </section>

    <section class="instituts">
      <div class="block-heading">
        <h2 class="sp-title">Instituts sous tutelle du MINRESI</h2>
      </div>
      <div class="swiper instituts-carousel">
        <div class="swiper-wrapper">
          <div class="swiper-slide"><a href="https://www.cndtcameroun.cm/" target="_blank" rel="noopener noreferrer" class="partner-card"><img src="../assets/logo-cndt.webp" alt="CNDT" loading="lazy"></a></div>
          <div class="swiper-slide"><a href="http://anrp.cm/" target="_blank" rel="noopener noreferrer" class="partner-card"><img src="../assets/instituts/anrp-1.jpg" alt="ANRP" loading="lazy"></a></div>
          <div class="swiper-slide"><a href="http://irad.cm/index.php/fr/" target="_blank" rel="noopener noreferrer" class="partner-card"><img src="../assets/instituts/irad-1.jpg" alt="IRAD" loading="lazy"></a></div>
          <div class="swiper-slide"><a href="https://impm-cm.org/" target="_blank" rel="noopener noreferrer" class="partner-card"><img src="../assets/instituts/impm.png" alt="IMPM" loading="lazy"></a></div>
          <div class="swiper-slide"><a href="http://www.inc.ayoos.com/" target="_blank" rel="noopener noreferrer" class="partner-card"><img src="../assets/instituts/inc-1.jpg" alt="INC" loading="lazy"></a></div>
          <div class="swiper-slide"><a href="http://www.mipromalo.cm/index.php/fr/" target="_blank" rel="noopener noreferrer" class="partner-card"><img src="../assets/instituts/mipromalo-1.jpg" alt="MIPROMALO" loading="lazy"></a></div>
          <div class="swiper-slide"><a href="http://195.24.207.248/~minresi/index.php/2015/07/21/accueil_principal/" target="_blank" rel="noopener noreferrer" class="partner-card"><img src="../assets/instituts/cne-1.jpeg" alt="CNE" loading="lazy"></a></div>
          <div class="swiper-slide"><a href="https://irgm-cameroun.org/" target="_blank" rel="noopener noreferrer" class="partner-card"><img src="../assets/instituts/irgm-1.jpg" alt="IRGM" loading="lazy"></a></div>
        </div>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
      </div>
    </section>
  </main>

  
@endsection

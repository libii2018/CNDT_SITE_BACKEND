@extends('layouts.site')

@section('title', "Projets | CNDT Cameroun")

@section('description', "Projets et activités de Recherche & Développement du Comité National de Développement des Technologies.")

@section('content')
  <main id="contenu">

    <section class="inner-hero">
      <img src="assets/header_personnel.jpg" alt="Projets de recherche et développement du CNDT" width="1100" height="825">
      <div class="container">
        <p class="page-kicker">Recherche &amp; Développement</p>
        <h1>Nos projets</h1>
        <p>Les projets et activités de R&amp;D portés ou accompagnés par le CNDT et ses partenaires.</p>
      </div>
    </section>

    <section class="inner-section" aria-labelledby="projets-title">
      <div class="container">

        <nav class="breadcrumb" aria-label="Fil d'Ariane">
          <a href="index.html">Accueil</a>
          <span aria-hidden="true">›</span>
          <span aria-current="page">Projets</span>
        </nav>

        <div class="archive-toolbar">
          <p id="projets-title"><strong>Tous les projets</strong></p>
          <div class="archive-filter" data-projets-filter aria-label="Filtrer les projets">
            <button class="is-active" type="button" data-filter="all">Tous</button>
            <button type="button" data-filter="a_venir">À venir</button>
            <button type="button" data-filter="en_cours">En cours</button>
            <button type="button" data-filter="termine">Terminés</button>
          </div>
        </div>

        <div class="archive-grid" data-projets-grid>
          <!-- Projets alimentés dynamiquement (dashboard → API) -->
        </div>

        <p style="text-align:center; margin-top:2rem;">
          <button type="button" class="button" id="projets-more" hidden>Charger plus de projets</button>
        </p>

      </div>
    </section>

    <section class="instituts">
      <div class="block-heading">
        <h2 class="sp-title">Instituts sous tutelle du MINRESI</h2>
      </div>
      <div class="swiper instituts-carousel">
        <div class="swiper-wrapper">
          <div class="swiper-slide"><a href="https://www.cndtcameroun.cm/" target="_blank" rel="noopener noreferrer" class="partner-card"><img src="assets/logo-cndt.webp" alt="CNDT" loading="lazy"></a></div>
          <div class="swiper-slide"><a href="http://anrp.cm/" target="_blank" rel="noopener noreferrer" class="partner-card"><img src="assets/instituts/anrp-1.jpg" alt="ANRP" loading="lazy"></a></div>
          <div class="swiper-slide"><a href="http://irad.cm/index.php/fr/" target="_blank" rel="noopener noreferrer" class="partner-card"><img src="assets/instituts/irad-1.jpg" alt="IRAD" loading="lazy"></a></div>
          <div class="swiper-slide"><a href="https://impm-cm.org/" target="_blank" rel="noopener noreferrer" class="partner-card"><img src="assets/instituts/impm.png" alt="IMPM" loading="lazy"></a></div>
          <div class="swiper-slide"><a href="http://www.inc.ayoos.com/" target="_blank" rel="noopener noreferrer" class="partner-card"><img src="assets/instituts/inc-1.jpg" alt="INC" loading="lazy"></a></div>
          <div class="swiper-slide"><a href="http://www.mipromalo.cm/index.php/fr/" target="_blank" rel="noopener noreferrer" class="partner-card"><img src="assets/instituts/mipromalo-1.jpg" alt="MIPROMALO" loading="lazy"></a></div>
          <div class="swiper-slide"><a href="http://195.24.207.248/~minresi/index.php/2015/07/21/accueil_principal/" target="_blank" rel="noopener noreferrer" class="partner-card"><img src="assets/instituts/cne-1.jpeg" alt="CNE" loading="lazy"></a></div>
          <div class="swiper-slide"><a href="https://irgm-cameroun.org/" target="_blank" rel="noopener noreferrer" class="partner-card"><img src="assets/instituts/irgm-1.jpg" alt="IRGM" loading="lazy"></a></div>
        </div>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
      </div>
    </section>
  </main>

  
@endsection

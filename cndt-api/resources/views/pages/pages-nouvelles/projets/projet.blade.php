@extends('layouts.site')

@section('title', "Projet | CNDT Cameroun")

@section('description', "Fiche projet du Comité National de Développement des Technologies — objectifs, partenaire et avancement.")

@section('content')
  <main id="contenu">
    <div data-projet-detail>

      <!-- =========================================================
           HERO : titre + image (alimentés dynamiquement)
           ========================================================= -->
      <section class="inner-hero inner-hero--project" data-projet-hero>
        <img src="../assets/logo-cndt.webp" alt="" width="1100" height="825">
        <div class="container">
          <p class="page-kicker" data-projet-statut>Projet</p>
          <h1>Chargement…</h1>
          <ul class="project-hero-meta" aria-label="Informations clés" data-projet-meta>
            <li data-meta="statut" hidden><span>Statut</span><strong></strong></li>
            <li data-meta="partenaire" hidden><span>Partenaire</span><strong></strong></li>
            <li data-meta="periode" hidden><span>Période</span><strong></strong></li>
          </ul>
        </div>
      </section>

      <section class="inner-section">
        <div class="container">

          <nav class="breadcrumb" aria-label="Fil d'Ariane">
            <a href="/">Accueil</a>
            <span aria-hidden="true">›</span>
            <a href="/projets">Projets</a>
            <span aria-hidden="true">›</span>
            <span aria-current="page">Projet</span>
          </nav>

          <div class="project-layout">

            <!-- ---------- Contenu principal ---------- -->
            <article class="project-content">

              <section id="projet" class="project-section">
                <h2>Le projet</h2>
                <div class="article-content" data-projet-body>
                  <p>Chargement…</p>
                </div>
                <p data-projet-lien hidden style="margin-top:1.5rem;">
                  <a class="button button-light" data-projet-lien-cible href="#" target="_blank" rel="noopener noreferrer">Consulter le projet <span aria-hidden="true">→</span></a>
                </p>
              </section>

            </article>

            <!-- ---------- Colonne latérale ---------- -->
            <aside class="sidebar" aria-label="Actualités et publications du CNDT">
              <section class="news-agenda" aria-labelledby="projet-news-title">
                <div class="sidebar-heading">
                  <h2 id="projet-news-title">Actualités</h2>
                </div>
                <div class="agenda-list" data-news-list></div>
                <a class="sidebar-more" href="/actualites">Toutes les actualités <span aria-hidden="true">→</span></a>
              </section>

              <section id="ressources" class="resource-module" aria-labelledby="projet-resources-title">
                <h2 id="projet-resources-title">Publications &amp; médias</h2>
                <nav aria-label="Publications et médias">
                  <a href="/publications"><span aria-hidden="true">›</span>Techno Mag</a>
                  <a href="https://www.youtube.com/channel/UC3OfHkDXBNVGwiI8henjNww" target="_blank" rel="noopener"><span aria-hidden="true">›</span>Émissions TV</a>
                  <a href="/galerie"><span aria-hidden="true">›</span>Photothèque</a>
                  <a href="/projets"><span aria-hidden="true">›</span>Projets</a>
                  <a href="/publications"><span aria-hidden="true">›</span>Rapports</a>
                  <a href="/contact"><span aria-hidden="true">›</span>Contact</a>
                </nav>
              </section>
            </aside>
          </div>

          <!-- Retour -->
          <div class="project-back">
            <a class="text-link" href="/projets">← Retour aux projets</a>
          </div>

        </div>
      </section>
    </div>

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

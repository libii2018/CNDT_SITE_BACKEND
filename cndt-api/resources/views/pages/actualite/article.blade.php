@extends('layouts.site')

@section('title', "Actualité | CNDT Cameroun")

@section('description', "Actualité du Comité National de Développement des Technologies.")

@section('content')
  <main id="contenu">
    <div data-article-detail>

      <!-- =========================================================
           HERO : titre + image (alimentés dynamiquement)
           ========================================================= -->
      <section class="inner-hero inner-hero--project" data-article-hero>
        <img src="../assets/logo-cndt.webp" alt="" width="1100" height="825">
        <div class="container">
          <h1>Chargement…</h1>
          <ul class="project-hero-meta" aria-label="Informations clés">
            <li><span>Publié le</span><strong><time datetime="">…</time></strong></li>
            <li><span>Category</span><strong data-article-category>…</strong></li>
          </ul>
        </div>
      </section>

      <section class="inner-section">
        <div class="container">

          <nav class="breadcrumb" aria-label="Fil d'Ariane">
            <a href="/">Accueil</a>
            <span aria-hidden="true">›</span>
            <a href="/actualites">Actualités</a>
            <span aria-hidden="true">›</span>
            <span aria-current="page">Actualité</span>
          </nav>

          <!-- =========================================================
               LAYOUT : navigation latérale + contenu (comme les pages d'origine)
               ========================================================= -->
          <div class="project-layout">

            <!-- ---------- Contenu principal ---------- -->
            <article class="project-content">

              <section id="actualite" class="project-section">
                <h2>Actualité</h2>
                <!-- Contenu de l'article (alimenté dynamiquement) -->
                <div class="article-content" data-article-body>
                  <p>Chargement…</p>
                </div>
                <!-- Galerie injectée ici dynamiquement (style project-gallery) -->
              </section>

            </article>

            <!-- ---------- Colonne latérale : Actualités + Publications & médias ---------- -->
            <aside class="sidebar" aria-label="Actualités et publications du CNDT">
              <section class="news-agenda" aria-labelledby="news-title">
                <div class="sidebar-heading">
                  <h2 id="news-title">Actualités</h2>
                </div>
                <div class="agenda-list" data-news-list></div>
                <a class="sidebar-more" href="/actualites">Toutes les actualités <span aria-hidden="true">→</span></a>
              </section>

              <section id="ressources" class="resource-module" aria-labelledby="resources-title">
                <h2 id="resources-title">Publications &amp; médias</h2>
                <nav aria-label="Publications et médias">
                  <a href="/publications#technomag"><span aria-hidden="true">›</span> Technomag</a>
                  <a href="/publications#rapports"><span aria-hidden="true">›</span> Rapports et actes scientifiques</a>
                  <a href="/publications#documents"><span aria-hidden="true">›</span> Documents techniques</a>
                  <a href="https://www.youtube.com/channel/UC3OfHkDXBNVGwiI8henjNww" target="_blank" rel="noopener"><span aria-hidden="true">›</span> Émissions TV</a>
                  <a href="/publications#phototheque"><span aria-hidden="true">›</span> Photothèque</a>
                </nav>
              </section>
            </aside>

          </div>

          <p style="margin-top:34px;">
            <a class="text-link" href="/actualites"><span aria-hidden="true">←</span> Retour aux actualités</a>
          </p>

        </div>
      </section>
    </div>
  </main>

  <!-- =========================================================
       INSTITUTS SOUS TUTELLE DU MINRESI (comme les pages d'origine)
       ========================================================= -->
  <section class="instituts">
    <div class="block-heading">
        <h2 class="sp-title">Instituts sous tutelle du MINRESI</h2>
    </div>

    <!-- Conteneur du Carrousel Swiper -->
    <div class="swiper instituts-carousel">
        <div class="swiper-wrapper">

            <div class="swiper-slide">
                <a href="https://www.cndtcameroun.cm/" target="_blank" rel="noopener noreferrer" class="partner-card">
                    <img src="../assets/logo-cndt.webp" alt="CNDT" loading="lazy">
                </a>
            </div>

            <div class="swiper-slide">
                <a href="http://anrp.cm/" target="_blank" rel="noopener noreferrer" class="partner-card">
                    <img src="../assets/instituts/anrp-1.jpg" alt="ANRP" loading="lazy">
                </a>
            </div>

            <div class="swiper-slide">
                <a href="http://irad.cm/index.php/fr/" target="_blank" rel="noopener noreferrer" class="partner-card">
                    <img src="../assets/instituts/irad-1.jpg" alt="IRAD" loading="lazy">
                </a>
            </div>

            <div class="swiper-slide">
                <a href="https://impm-cm.org/" target="_blank" rel="noopener noreferrer" class="partner-card">
                    <img src="../assets/instituts/impm.png" alt="IMPM" loading="lazy">
                </a>
            </div>

            <div class="swiper-slide">
                <a href="http://www.inc.ayoos.com/" target="_blank" rel="noopener noreferrer" class="partner-card">
                    <img src="../assets/instituts/inc-1.jpg" alt="INC" loading="lazy">
                </a>
            </div>

            <div class="swiper-slide">
                <a href="http://www.mipromalo.cm/index.php/fr/" target="_blank" rel="noopener noreferrer" class="partner-card">
                    <img src="../assets/instituts/mipromalo-1.jpg" alt="MIPROMALO" loading="lazy">
                </a>
            </div>

            <div class="swiper-slide">
                <a href="http://195.24.207.248/~minresi/index.php/2015/07/21/accueil_principal/" target="_blank" rel="noopener noreferrer" class="partner-card">
                    <img src="../assets/instituts/cne-1.jpeg" alt="CNE" loading="lazy">
                </a>
            </div>

            <div class="swiper-slide">
                <a href="https://irgm-cameroun.org/" target="_blank" rel="noopener noreferrer" class="partner-card">
                    <img src="../assets/instituts/irgm-1.jpg" alt="IRGM" loading="lazy">
                </a>
            </div>

        </div>

        <!-- Navigation & Pagination -->
        <div class="swiper-pagination"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
    </div>
  </section>

  
@endsection

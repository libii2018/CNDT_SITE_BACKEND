@extends('layouts.site')

@section('title', "Publications et médias | CNDT Cameroun")

@section('description', "Publications, documents scientifiques et ressources audiovisuelles du CNDT Cameroun.")

@section('content')
  <main id="contenu">
    <section class="inner-hero simple-hero">
      <div class="container">
        <p class="page-kicker">Documentation scientifique</p>
        <h1>Publications &amp; médias</h1>
        <p>Les revues, rapports, documents techniques et contenus audiovisuels produits ou diffusés par le CNDT.</p>
      </div>
    </section>

    <section class="inner-section" aria-labelledby="publications-title">
      <div class="container publications-layout">
        <div class="page-heading">
          <div>
            <p class="page-kicker">Centre de ressources</p>
            <h2 id="publications-title">Consulter nos ressources</h2>
          </div>
          <p>Une entrée simple par catégorie pour retrouver rapidement les contenus utiles.</p>
        </div>

        <div class="publication-list">
          <article id="technomag" class="publication-row">
            <span class="publication-number">01</span>
            <div><h3>Technomag</h3><p>Magazine de diffusion de l’information scientifique, technologique et industrielle.</p></div>
            <a href="#technomag" aria-label="Consulter Technomag">Consulter <span aria-hidden="true">→</span></a>
          </article>
          <article id="rapports" class="publication-row">
            <span class="publication-number">02</span>
            <div><h3>Rapports et actes scientifiques</h3><p>Actes de conférences, rapports d’activités et synthèses issues des rencontres scientifiques.</p></div>
            <a href="#rapports" aria-label="Consulter les rapports et actes scientifiques">Consulter <span aria-hidden="true">→</span></a>
          </article>
          <article id="documents" class="publication-row">
            <span class="publication-number">03</span>
            <div><h3>Documents techniques</h3><p>Guides, notes techniques et ressources relatives au transfert et à l’adaptation des technologies.</p></div>
            <a href="#documents" aria-label="Consulter les documents techniques">Consulter <span aria-hidden="true">→</span></a>
          </article>
          <article id="emissions" class="publication-row">
            <span class="publication-number">04</span>
            <div><h3>Émissions TV</h3><p>Interviews, conférences et reportages consacrés aux activités du CNDT.</p></div>
            <a href="https://www.youtube.com/channel/UC3OfHkDXBNVGwiI8henjNww" target="_blank" rel="noopener">Regarder <span aria-hidden="true">↗</span></a>
          </article>
          <article id="phototheque" class="publication-row">
            <span class="publication-number">05</span>
            <div><h3>Photothèque</h3><p>Retour en images sur les conférences, ateliers et activités institutionnelles.</p></div>
            <a href="#phototheque" aria-label="Consulter la photothèque">Consulter <span aria-hidden="true">→</span></a>
          </article>
        </div>
      </div>
    </section>

    <section class="instituts">
        <div class="block-heading">
              <!-- <p>Le Secrétariat Permanent</p> -->
              <h2 class="sp-title">Instituts sous tutelle du MINRESI</h2>
        </div>

        <!-- Conteneur du Carrousel Swiper -->
        <div class="swiper instituts-carousel">
            <div class="swiper-wrapper">

                <div class="swiper-slide">
                    <a href="https://www.cndtcameroun.cm/" target="_blank" rel="noopener noreferrer" class="partner-card">
                        <img src="assets/logo-cndt.webp" alt="CNDT" loading="lazy">
                    </a>
                </div>

                <div class="swiper-slide">
                    <a href="http://anrp.cm/" target="_blank" rel="noopener noreferrer" class="partner-card">
                        <img src="assets/instituts/anrp-1.jpg" alt="ANRP" loading="lazy">
                    </a>
                </div>

                <div class="swiper-slide">
                    <a href="http://irad.cm/index.php/fr/" target="_blank" rel="noopener noreferrer" class="partner-card">
                        <img src="assets/instituts/irad-1.jpg" alt="IRAD" loading="lazy">
                    </a>
                </div>

                <div class="swiper-slide">
                    <a href="https://impm-cm.org/" target="_blank" rel="noopener noreferrer" class="partner-card">
                        <img src="assets/instituts/impm.png" alt="IMPM" loading="lazy">
                    </a>
                </div>

                <div class="swiper-slide">
                    <a href="http://www.inc.ayoos.com/" target="_blank" rel="noopener noreferrer" class="partner-card">
                        <img src="assets/instituts/inc-1.jpg" alt="INC" loading="lazy">
                    </a>
                </div>

                <div class="swiper-slide">
                    <a href="http://www.mipromalo.cm/index.php/fr/" target="_blank" rel="noopener noreferrer" class="partner-card">
                        <img src="assets/instituts/mipromalo-1.jpg" alt="MIPROMALO" loading="lazy">
                    </a>
                </div>

                <div class="swiper-slide">
                    <a href="http://195.24.207.248/~minresi/index.php/2015/07/21/accueil_principal/" target="_blank" rel="noopener noreferrer" class="partner-card">
                        <img src="assets/instituts/cne-1.jpeg" alt="CNE" loading="lazy">
                    </a>
                </div>

                <div class="swiper-slide">
                    <a href="https://irgm-cameroun.org/" target="_blank" rel="noopener noreferrer" class="partner-card">
                        <img src="assets/instituts/irgm-1.jpg" alt="IRGM" loading="lazy">
                    </a>
                </div>

            </div>

            <!-- Navigation & Pagination -->
            <div class="swiper-pagination"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
        
    </section>
  </main>

  
@endsection

<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="@yield('description', 'Site institutionnel du Comité National de Développement des Technologies du Cameroun.')">
  <meta name="theme-color" content="#1f663f">
  <title>@yield('title', 'CNDT Cameroun')</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
  <link rel="stylesheet" href="/site.css">
  <script defer src="/app.js"></script>
  <script defer src="/api-actualites.js?v=13"></script>
</head>
<body>
  <a class="skip-link" href="#contenu">Aller au contenu</a>


<header class="site-header" data-site-header data-root="">
  <div class="utility-bar">
    <div class="utility-inner">
      <p>République du Cameroun <span>Paix · Travail · Patrie</span></p>
      <div class="utility-links">
        <a href="mailto:contact@cndtcameroun.cm">contact@cndtcameroun.cm</a>
        <span aria-hidden="true">|</span>
        <a href="#" lang="en" aria-label="English version">English</a>
      </div>
    </div>
  </div>

  <div class="main-nav-wrap">
    <div class="main-nav">
      <a class="brand" href="/" aria-label="Accueil du CNDT">
        <img src="/assets/logo-cndt.webp" alt="Logo du CNDT">
        <span class="brand-copy">
          <strong>Comité National de Développement des Technologies</strong>
          <small>La technologie au service du développement</small>
        </span>
      </a>

      <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-nav">
        <span class="menu-toggle-lines" aria-hidden="true"><i></i><i></i><i></i></span>
        <span>Menu</span>
      </button>

      <nav id="primary-nav" class="primary-nav" aria-label="Navigation principale">
        <a href="/">Accueil</a>

        <button
          type="button"
          class="primary-nav__trigger"
          aria-expanded="false"
          aria-controls="mega-organisation"
          data-mega-trigger
        >
          <span>Secrétariats Permanents</span>
          <svg class="primary-nav__chevron" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M6.4 9.2 12 14.8l5.6-5.6-1.4-1.4L12 12l-4.2-4.2z"/>
          </svg>
        </button>

        <a href="/actualites">Actualités</a>
        <a href="/publications">Publications</a>
        <a href="/projets">Projets</a>
        <a href="/contact">Contact</a>

        <div class="nav-search" data-nav-search role="search">
          <form class="nav-search__form" autocomplete="off" novalidate>
            <label class="visually-hidden" for="nav-search-input">Rechercher sur le site</label>

            <svg class="nav-search__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
              <path d="M10 4a6 6 0 1 0 3.9 10.56l4.27 4.27 1.41-1.41-4.27-4.27A6 6 0 0 0 10 4Zm0 2a4 4 0 1 1 0 8 4 4 0 0 1 0-8Z"/>
            </svg>

            <input
              id="nav-search-input"
              class="nav-search__input"
              type="search"
              name="q"
              placeholder="Rechercher…"
              aria-autocomplete="list"
              aria-controls="nav-search-results"
              aria-expanded="false"
              data-nav-search-input
            >

            <button
              class="nav-search__clear"
              type="button"
              aria-label="Effacer la recherche"
              data-nav-search-clear
              hidden
            >
              <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path d="M18.3 5.7 12 12l6.3 6.3-1.4 1.4L10.6 13.4 4.3 19.7 2.9 18.3 9.2 12 2.9 5.7 4.3 4.3l6.3 6.3 6.3-6.3z"/>
              </svg>
            </button>
          </form>

          <div
            id="nav-search-results"
            class="nav-search__results"
            role="listbox"
            aria-label="Résultats de recherche"
            hidden
            data-nav-search-results
          ></div>
        </div>

        <ul class="nav-socials" aria-label="Nos réseaux sociaux">
          <li>
            <a href="https://www.facebook.com/cndtcameroun"
              target="_blank" rel="noopener"
              aria-label="Page Facebook du CNDT"
              class="nav-social nav-social--facebook">
              <i class="fa-brands fa-facebook-f" aria-hidden="true"></i>
            </a>
          </li>
          <li>
            <a href="https://www.youtube.com/channel/UC3OfHkDXBNVGwiI8henjNww"
              target="_blank" rel="noopener"
              aria-label="Chaîne YouTube du CNDT"
              class="nav-social nav-social--youtube">
              <i class="fa-brands fa-youtube" aria-hidden="true"></i>
            </a>
          </li>
        </ul>

        <div
          id="mega-organisation"
          class="mega-menu"
          aria-hidden="true"
          data-mega-menu
        >
          <div class="mega-menu__inner">
            <div class="container">

              <div class="mega-menu__head">
                <p class="mega-menu__eyebrow">Explorer l'organisation du CNDT</p>
                <button
                  type="button"
                  class="mega-menu__close"
                  aria-label="Fermer le menu Organisation"
                  data-mega-close
                >
                  Fermer
                  <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path d="M18.3 5.7 12 12l6.3 6.3-1.4 1.4L10.6 13.4 4.3 19.7 2.9 18.3 9.2 12 2.9 5.7 4.3 4.3l6.3 6.3 6.3-6.3z"/>
                  </svg>
                </button>
              </div>

              <div class="mega-menu__grid">

                <div class="mega-menu__col">
                  <ul class="mega-menu__list">
                    <li>
                      <a href="/#institution">
                        <span>Mot du Secrétaire Permanent</span>
                      </a>
                    </li>
                    <li>
                      <a href="/comite">
                        <span>Comité</span>
                      </a>
                    </li>
                    <li>
                      <a href="/cabinet">
                        <span>Cabinet du Secrétaire Permanent</span>
                      </a>
                    </li>
                  </ul>
                </div>

                <div class="mega-menu__col">
                  <ul class="mega-menu__list">
                    <li>
                      <a href="/crmp">
                        <span>Commission recherche et management des projets</span>
                      </a>
                    </li>
                    <li>
                      <a href="/caaf">
                        <span>Commission des affaires administratives, financières et des engagements</span>
                      </a>
                    </li>
                    <li>
                      <a href="/cdcrp">
                        <span>Commission documentation, communication et relations publiques</span>
                      </a>
                    </li>
                  </ul>
                </div>

                <div class="mega-menu__col">
                  <ul class="mega-menu__list">
                    <li>
                      <a href="/rhcaj">
                        <span>Commission des ressources humaines, de la coopération et des affaires juridiques</span>
                      </a>
                    </li>
                    <li>
                      <a href="/ct">
                        <span>Commission technique</span>
                      </a>
                    </li>
                    <li>
                      <a href="/personnel">
                        <span>Catalogue du personnel</span>
                      </a>
                    </li>
                    <li>
                      <a href="/galerie">
                        <span>Galerie photo</span>
                      </a>
                    </li>
                  </ul>
                </div>

              </div>

              <div class="mega-menu__foot">
                <a class="mega-menu__cta" href="/organigramme">
                  Voir l'organigramme complet
                  <span aria-hidden="true">→</span>
                </a>
              </div>

            </div>
          </div>
        </div>
      </nav>
    </div>
  </div>
</header>

@yield('content')


<footer id="contact" class="site-footer" data-site-footer data-root="">
  <div class="container footer-grid">
    <div class="footer-brand">
      <img src="/assets/logo-cndt.webp" alt="CNDT" width="120" height="97" loading="lazy">
      <p>Comité National de Développement des Technologies</p>
    </div>
    <div>
      <h2>Coordonnées</h2>
      <address>Centre administratif, Yaoundé<br>B.P. 1457 Yaoundé</address>
      <a href="tel:+237222222509">(+237) 222 22 25 09</a>
      <a href="mailto:contact@cndtcameroun.cm">contact@cndtcameroun.cm</a>

      <ul class="footer-socials" aria-label="Nos réseaux sociaux">
        <li>
          <a href="https://www.facebook.com/cndtcameroun"
            target="_blank" rel="noopener"
            aria-label="Page Facebook du CNDT"
            class="footer-social footer-social--facebook">
            <i class="fa-brands fa-facebook-f" aria-hidden="true"></i>
            <span>Facebook</span>
          </a>
        </li>
        <li>
          <a href="https://www.youtube.com/channel/UC3OfHkDXBNVGwiI8henjNww"
            target="_blank" rel="noopener"
            aria-label="Chaîne YouTube du CNDT"
            class="footer-social footer-social--youtube">
            <i class="fa-brands fa-youtube" aria-hidden="true"></i>
            <span>YouTube</span>
          </a>
        </li>
      </ul>
    </div>
    <div>
      <h2>Accès rapide</h2>
      <a href="/#institution">Présentation</a>
      <a href="/organigramme">Organigramme</a>
      <a href="/contact">Contact</a>
      <div>
        <a href="/actualites">Actualités</a>
        <a href="/publications">Publications</a>
        <a href="/projets">Projets</a>
      </div>
    </div>
    <div>
      <h2>Institution de tutelle</h2>
      <p>Ministère de la Recherche Scientifique et l’Innovation</p>
      <a class="footer-external" href="https://minresi.gov.cm" target="_blank" rel="noopener">Visiter le MINRESI <span aria-hidden="true">↗</span></a>
    </div>
  </div>
  <div class="container footer-bottom">
    <p>© 2026 CNDT Cameroun. Tous droits réservés.</p>
    <p>La technologie au service du développement</p>
  </div>
</footer>

  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
</body>
</html>

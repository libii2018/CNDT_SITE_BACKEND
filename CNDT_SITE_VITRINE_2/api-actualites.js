/* ============================================================
   api-actualites.js — rendu dynamique des actualités (site public)
   ------------------------------------------------------------
   Consomme l'API Laravel (GET /api/v1/articles) et alimente :
     1. [data-actualites-grid]  : liste complète + filtres + "Charger plus"
        (page actualites.html)
     2. [data-news-list]        : derniers titres (widget accueil index.html)
     3. [data-article-detail]   : page d'un article (?slug=…)
        (page actualite/article.html)
     4. [data-gallery-view="albums"] : albums + photos du dashboard
        (page galerie.html)

   À inclure APRÈS app.js :
     <script defer src="api-actualites.js"></script>
   ============================================================ */

window.CNDT_API = window.CNDT_API || {
  // Adresse relative : le frontal Apache proxifie /api/ vers l'API
  // (fonctionne en local et en production, sans origine figée).
  url: '/api/v1',
};

(() => {
  'use strict';

  const API = window.CNDT_API.url;
  const CATEGORY_LABELS = { ia: 'IA', evenement: 'Événements', formation: 'Formation', all: 'Toutes' };
  const PER_PAGE = 6;

  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];

  const esc = (str) => String(str ?? '').replace(/[&<>"']/g, (c) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
  }[c]));

  async function fetchApi(path) {
    const res = await fetch(API + path, { headers: { Accept: 'application/json' } });
    if (!res.ok) throw new Error('API indisponible (' + res.status + ')');
    return res.json();
  }

  function formatDate(iso) {
    if (!iso) return '';
    return new Date(iso).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
  }

  function articleLink(slug) {
    // Depuis les sous-dossiers (actualite/, membres/) les liens sont locaux,
    // depuis la racine ils passent par le sous-dossier.
    const inSubFolder = /\/(actualite|membres)\/[^/]*\.html/.test(location.pathname);
    const prefix = inSubFolder ? 'article.html?slug=' : 'actualite/article.html?slug=';
    return prefix + encodeURIComponent(slug);
  }

  function cardHtml(article) {
    const img = article.cover_image
      ? `<div class="archive-image"><img src="${esc(article.cover_image)}" alt="${esc(article.title)}" loading="lazy"></div>`
      : '<div class="archive-image"></div>';
    const cat = article.category || 'evenement';

    return `
      <article class="archive-card" data-category="${esc(cat)}" data-slug="${esc(article.slug)}">
        ${img}
        <div class="archive-body">
          <div class="archive-meta">
            <time datetime="${esc((article.published_at || '').slice(0, 10))}">${esc(formatDate(article.published_at))}</time>
            <span>${esc(CATEGORY_LABELS[cat] || cat)}</span>
          </div>
          <h2>${esc(article.title)}</h2>
          <p>${esc(article.excerpt || '')}</p>
          <a class="text-link" href="${articleLink(article.slug)}">Lire l'actualité <span aria-hidden="true">→</span></a>
        </div>
      </article>`;
  }

  /* ---------- 1. Liste complète (actualites.html) ---------- */
  async function renderActualitesGrid() {
    const grid = $('[data-actualites-grid]');
    if (!grid) return;

    const state = { page: 1, filter: 'all', loading: false, hasMore: true };
    const activeFilter = () =>
      $('[data-actualites-filter] .is-active')?.dataset.filter || 'all';

    // Filtres : les boutons existants de la page (data-filter)
    $$('[data-actualites-filter] [data-filter]').forEach((btn) => {
      btn.addEventListener('click', () => {
        $$('[data-actualites-filter] [data-filter]').forEach((b) =>
          b.classList.toggle('is-active', b === btn));
        state.filter = btn.dataset.filter;
        state.page = 1;
        load(true);
      });
    });

    async function load(reset) {
      if (state.loading || (reset === false && !state.hasMore)) return;
      state.loading = true;

      const more = $('#actualites-more');
      if (reset) grid.innerHTML = '<p class="archive-empty">Chargement des actualités…</p>';

      try {
        const params = new URLSearchParams({ page: state.page, per_page: PER_PAGE });
        if (state.filter !== 'all') params.set('category', state.filter);
        const res = await fetchApi('/articles?' + params.toString());
        const items = res.data || [];
        state.hasMore = !!res.links?.next;
        state.page += 1;

        if (reset) grid.innerHTML = '';
        if (!items.length && reset) {
          grid.innerHTML = '<p class="archive-empty">Aucune actualité publiée pour le moment.</p>';
        }
        items.forEach((a) => {
          grid.insertAdjacentHTML('beforeend', cardHtml(a));
        });

        if (more) more.hidden = !state.hasMore;
      } catch (err) {
        if (reset) grid.innerHTML =
          `<p class="archive-error">Impossible de charger les actualités (${esc(err.message)}).`;
      } finally {
        state.loading = false;
      }
    }

    // Bouton « Charger plus »
    const more = $('#actualites-more');
    if (more) {
      more.addEventListener('click', () => load(false));
    }

    await load(true);
  }

  /* ---------- 2. Derniers titres (index.html) ---------- */
  async function renderNewsList() {
    const list = $('[data-news-list]');
    if (!list) return;

    try {
      const res = await fetchApi('/articles?per_page=5');
      const items = res.data || [];
      if (!items.length) {
        list.innerHTML = '<p>Aucune actualité pour le moment.</p>';
        return;
      }
      list.innerHTML = items.map((a) => {
        const d = a.published_at ? new Date(a.published_at) : new Date();
        const day = String(d.getDate()).padStart(2, '0');
        const month = d.toLocaleDateString('fr-FR', { month: 'short' }).replace('.', '').toUpperCase();
        const dow = d.toLocaleDateString('fr-FR', { weekday: 'short' }).toUpperCase();
        const year = d.getFullYear();
        const iso = (a.published_at || '').slice(0, 10);
        return `
          <a href="${articleLink(a.slug)}">
            <time datetime="${esc(iso)}"><small>${esc(dow)}</small><strong>${esc(day)}</strong><span>${esc(month)}<br>${year}</span></time>
            <p>${esc(a.title)}</p>
          </a>`;
      }).join('');
    } catch (err) {
      // Le widget garde son contenu statique en cas d'indisponibilité
      console.warn('Actualités dynamiques indisponibles :', err.message);
    }
  }

  /* ---------- 3. Page article (actualite/article.html) ---------- */
  async function renderArticleDetail() {
    const root = $('[data-article-detail]');
    if (!root) return;

    const slug = new URLSearchParams(location.search).get('slug');
    const hero = $('[data-article-hero]', root);
    const body = $('[data-article-body]', root);

    if (!slug) {
      if (body) body.innerHTML =
        '<p class="archive-error">Aucun article demandé. <a class="text-link" href="../actualites.html">Voir toutes les actualités</a></p>';
      if (hero) hero.hidden = true;
      return;
    }

    try {
      const res = await fetchApi('/articles/' + encodeURIComponent(slug));
      const a = res.data;
      document.title = `${a.title} | CNDT Cameroun`;

      if (hero) {
        const img = $('img', hero);
        if (img && a.cover_image) {
          // cover_image est relative à la racine du site ; la page est dans un sous-dossier
          img.src = /^(https?:)?\//.test(a.cover_image)
            ? a.cover_image
            : '../' + a.cover_image;
          img.alt = a.title;
        }
        const h1 = $('h1', hero);
        if (h1) h1.textContent = a.title;
        const time = $('time', hero);
        if (time) {
          time.dateTime = (a.published_at || '').slice(0, 10);
          time.textContent = formatDate(a.published_at);
        }
        const cat = $('[data-article-category]', hero);
        if (cat) cat.textContent = CATEGORY_LABELS[a.category] || a.category || 'Actualité';
      }

      if (body) {
        const content = (a.content || a.excerpt || '');
        if (/<\/?[a-z][\s\S]*>/i.test(content)) {
          // Contenu HTML riche (éditeur WYSIWYG du dashboard)
          body.innerHTML = content;
        } else {
          // Contenu texte brut : paragraphes
          const paragraphs = content.split(/\n\s*\n|\r\n\s*\r\n/)
            .map((p) => p.trim()).filter(Boolean);
          body.innerHTML = paragraphs.length
            ? paragraphs.map((p) => `<p>${esc(p)}</p>`).join('')
            : '<p>(Contenu à venir.)</p>';
        }

        // Badge du créateur de l'article (comme la page d'origine « fracture numérique »)
        if (a.author_name) {
          const imgSrc2 = (p) => /^(https?:)?\//.test(p) ? p : '../' + p;
          const badge = document.createElement('a');
          badge.className = 'author-badge';
          badge.href = a.author_url ? (/^(https?:)?\//.test(a.author_url) ? a.author_url : '../' + a.author_url) : '#actualite';
          badge.innerHTML = `
            ${a.author_photo ? `<img src="${esc(imgSrc2(a.author_photo))}" alt="Portrait de ${esc(a.author_name)}" loading="lazy">` : ''}
            <span class="author-badge__body">
              <span class="author-badge__label">Rédigé par</span>
              <strong>${esc(a.author_name)}</strong>
              ${a.author_role ? `<span class="author-badge__role">${esc(a.author_role)}</span>` : ''}
            </span>`;
          body.appendChild(badge);
        }

        // Galerie d'images de l'article (gérées depuis le dashboard)
        // — mêmes classes que les pages d'origine (.project-gallery)
        if (Array.isArray(a.images) && a.images.length) {
          const imgSrc = (p) => /^(https?:)?\//.test(p) ? p : '../' + p;
          const gallerySection = document.createElement('section');
          gallerySection.id = 'galerie';
          gallerySection.className = 'project-section';
          gallerySection.setAttribute('aria-label', "Galerie de l'article");
          gallerySection.innerHTML = `
            <h2>Galerie</h2>
            <div class="project-gallery">
              ${a.images.map((im) => `
                <figure>
                  <img src="${esc(imgSrc(im.path))}" alt="${esc(im.caption || a.title)}">
                  ${im.caption ? `<figcaption>${esc(im.caption)}</figcaption>` : ''}
                </figure>`).join('')}
            </div>`;
          body.closest('.project-content')?.appendChild(gallerySection);
        }
      }
    } catch (err) {
      if (body) body.innerHTML =
        `<p class="archive-error">Actualité introuvable ou API indisponible. ` +
        `<a class="text-link" href="../actualites.html">Retour aux actualités</a></p>`;
      if (hero) hero.hidden = true;
    }
  }

  /* ---------- 4. Carrousel des instituts (alimenté par l'API) ---------- */
  async function renderInstituts() {
    const wrapper = $('.instituts-carousel .swiper-wrapper');
    if (!wrapper) return;

    try {
      const res = await fetchApi('/instituts');
      const items = (res.data || []).filter((i) => i.is_visible !== false);
      if (!items.length) return; // garde les slides statiques existants

      // Chemins relatifs : préfixe selon la profondeur de la page
      // (racine, ou sous-dossiers actualite/ et membres/)
      const prefix = /\/(actualite|membres)\/[^/]*\.html/.test(location.pathname) ? '../' : '';
      const imgSrc = (p) => /^(https?:)?\//.test(p) ? p : prefix + p;

      wrapper.innerHTML = items.map((i) => `
        <div class="swiper-slide">
          <a href="${esc(i.url || '#')}" target="_blank" rel="noopener noreferrer" class="partner-card">
            <img src="${esc(imgSrc(i.logo))}" alt="${esc(i.name)}" loading="lazy">
          </a>
        </div>`).join('');

      // Swiper fixe la largeur des slides à l'initialisation : les nouveaux
      // slides injectés gardent le width:100% par défaut → recalcul forcé.
      const swiperEl = $('.instituts-carousel');
      if (swiperEl && swiperEl.swiper) {
        swiperEl.swiper.update();
        if (typeof swiperEl.swiper.loopFix === 'function') swiperEl.swiper.loopFix();
      }
    } catch {
      // API indisponible : le carrousel statique d'origine est conservé
    }
  }

  /* ---------- 5. Carrousel de l'accueil (hero) — alimenté par l'API ---------- */
  async function renderHero() {
    const track = $('.hero-track');
    if (!track) return;

    try {
      const res = await fetchApi('/articles?hero=1');
      const items = res.data || [];
      if (!items.length) return; // garde les slides statiques

      track.innerHTML = items.map((a) => `
        <article class="hero-slide${''}" aria-hidden="true">
          <img src="${esc(a.cover_image || 'assets/logo-cndt.webp')}" alt="${esc(a.title)}" width="1100" height="825" fetchpriority="high">
          <div class="container hero-content">
            <p class="eyebrow">${esc(CATEGORY_LABELS[a.category] || a.category || 'CNDT')}</p>
            <h1>${esc(a.title)}</h1>
            <p>${esc(a.excerpt || '')}</p>
            <a class="button button-light" href="actualite/article.html?slug=${encodeURIComponent(a.slug)}">Pour plus d'informations <span aria-hidden="true">→</span></a>
          </div>
        </article>`).join('');

      // Points de navigation régénérés
      const dotsBox = $('.hero-dots');
      if (dotsBox) {
        dotsBox.innerHTML = items.map((_, i) =>
          `<button class="hero-dot" type="button" role="tab" aria-selected="false" aria-label="Afficher l'image ${i + 1}"></button>`).join('');
      }

      // La logique d'origine (app.js) travaille sur les slides statiques devenus
      // détachés : on réinitialise le carrousel sur les nouveaux éléments.
      startHero();
    } catch {
      // API indisponible : carrousel statique d'origine conservé
    }
  }

  function startHero() {
    const hero = $('.hero');
    const slides = $$('.hero-slide');
    const dots = $$('.hero-dot');
    const prev = $('.hero-prev');
    const next = $('.hero-next');
    if (!hero || !slides.length) return;

    let current = 0;
    let timer = null;

    const show = (index) => {
      current = (index + slides.length) % slides.length;
      slides.forEach((s, i) => {
        const active = i === current;
        s.classList.toggle('is-active', active);
        s.setAttribute('aria-hidden', String(!active));
      });
      dots.forEach((d, i) => {
        const active = i === current;
        d.classList.toggle('is-active', active);
        d.setAttribute('aria-selected', String(active));
      });
    };

    const stop = () => { clearInterval(timer); timer = null; };
    const start = () => {
      stop();
      if (slides.length > 1 && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        timer = setInterval(() => show(current + 1), 6500);
      }
    };

    // État initial : premier slide actif
    show(0);

    // Les flèches partagent leurs listeners avec app.js (inactifs sur les
    // éléments détachés) — les nôtres pilotent les nouveaux slides.
    prev?.addEventListener('click', () => { show(current - 1); start(); });
    next?.addEventListener('click', () => { show(current + 1); start(); });
    dots.forEach((d, i) => d.addEventListener('click', () => { show(i); start(); }));

    hero.addEventListener('mouseenter', stop);
    hero.addEventListener('mouseleave', start);
    start();
  }

  /* ---------- 6. Galerie photo dynamique (galerie.html) ---------- */
  async function renderGalerie() {
    const albumsView = $('[data-gallery-view="albums"]');
    if (!albumsView) return; // pas la page galerie

    let albums;
    try {
      const res = await fetchApi('/albums?per_page=100');
      albums = res.data || [];
    } catch {
      return; // API indisponible : galerie statique d'origine conservée
    }
    if (!albums.length) return;

    // Chemins : URL absolue ou racine (/storage/…) servies telles quelles
    const imgSrc = (p) => (!p ? '' : /^(https?:)?\//.test(p) ? p : '/' + p.replace(/^\/+/, ''));
    const moisAnnee = (iso) => iso
      ? new Date(iso).toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' })
      : '';

    // --- Vue 1 : grille des albums (mêmes classes que le markup d'origine) ---
    const grid = $('.gallery-albums', albumsView);
    if (grid) {
      grid.innerHTML = albums.map((al) => {
        const photos = Array.isArray(al.photos) ? al.photos : [];
        const cover = al.cover_image || photos[0]?.path || '';
        const date = moisAnnee(al.created_at);
        return `
          <button type="button" class="gallery-album" data-album-open="${esc(al.slug)}">
            <div class="gallery-album__cover">
              ${cover ? `<img src="${esc(imgSrc(cover))}" alt="${esc(al.title)}" loading="lazy" onerror="this.remove()">` : ''}
            </div>
            <div class="gallery-album__body">
              ${date ? `<p class="gallery-album__date">${esc(date)}</p>` : ''}
              <h3 class="gallery-album__title">${esc(al.title)}</h3>
            </div>
          </button>`;
      }).join('');
    }

    // --- Vues d'albums : les statiques sont remplacées par celles de l'API ---
    $$('[data-album-view]').forEach((v) => v.remove());
    albumsView.insertAdjacentHTML('afterend', albums.map((al) => {
      const photos = Array.isArray(al.photos) ? al.photos : [];
      const date = moisAnnee(al.created_at);
      return `
        <div class="gallery-view gallery-view--album" data-album-view="${esc(al.slug)}" hidden>
          <button type="button" class="gallery-back" data-album-back>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15.4 7.4 14 6l-6 6 6 6 1.4-1.4L10.8 12z"/></svg>
          </button>
          <div class="gallery-album-head">
            <h2 class="gallery-album-title">${esc(al.title)}</h2>
            <p class="gallery-album-meta">${esc(date)} · <span>${photos.length} photo${photos.length > 1 ? 's' : ''}</span></p>
            ${al.description ? `<p class="gallery-album-desc">${esc(al.description)}</p>` : ''}
          </div>
          <div class="gallery-photos">
            ${photos.map((ph) => `
              <button type="button" class="gallery-photo"
                      data-photo-src="${esc(imgSrc(ph.path))}"
                      data-photo-caption="${esc(ph.caption || '')}">
                <img src="${esc(imgSrc(ph.path))}" alt="${esc(ph.caption || al.title)}" loading="lazy" onerror="this.closest('.gallery-photo')?.classList.add('is-empty')">
              </button>`).join('')}
          </div>
        </div>`;
    }).join(''));

    initGalerie(albumsView);
  }

  // Réplique la logique de la galerie (app.js §GALERIE) sur les éléments
  // dynamiques : ceux d'origine ont été détachés, leurs listeners sont morts.
  function initGalerie(albumsView) {
    const views = $$('[data-album-view]');

    const showAlbums = () => {
      albumsView.hidden = false;
      views.forEach((v) => (v.hidden = true));
      history.replaceState({}, '', location.pathname);
    };

    const showAlbum = (slug) => {
      const target = views.find((v) => v.dataset.albumView === slug);
      if (!target) return;
      albumsView.hidden = true;
      views.forEach((v) => (v.hidden = v !== target));
      window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    $$('[data-album-open]', albumsView).forEach((btn) =>
      btn.addEventListener('click', () => showAlbum(btn.dataset.albumOpen)));
    $$('[data-album-back]').forEach((btn) =>
      btn.addEventListener('click', showAlbums));

    // Ouverture directe via ?album=…
    const slug = new URLSearchParams(location.search).get('album');
    if (slug) showAlbum(slug);

    /* ----- Lightbox : clonée pour détacher les listeners de app.js ----- */
    const oldLb = document.getElementById('lightbox');
    if (!oldLb) return;
    const lightbox = oldLb.cloneNode(true);
    oldLb.replaceWith(lightbox);

    const lbImg = lightbox.querySelector('[data-lb-img]');
    const lbCaption = lightbox.querySelector('[data-lb-caption]');
    const lbIndex = lightbox.querySelector('[data-lb-index]');
    const lbTotal = lightbox.querySelector('[data-lb-total]');
    const lbThumbs = lightbox.querySelector('[data-lb-thumbs]');

    let currentPhotos = [];
    let currentIndex = 0;

    const updateLightbox = () => {
      const photo = currentPhotos[currentIndex];
      if (!photo) return;
      lbImg.src = photo.src;
      lbImg.alt = photo.caption || `Photo ${currentIndex + 1}`;
      if (photo.caption) { lbCaption.textContent = photo.caption; lbCaption.hidden = false; }
      else lbCaption.hidden = true;
      lbIndex.textContent = currentIndex + 1;
      lbTotal.textContent = currentPhotos.length;
    };

    const gotoPhoto = (index) => {
      const total = currentPhotos.length;
      if (!total) return;
      currentIndex = (index + total) % total;
      updateLightbox();
      lbThumbs.querySelectorAll('.lightbox__thumb').forEach((btn, i) =>
        btn.classList.toggle('is-current', i === currentIndex));
      lbThumbs.querySelector('.lightbox__thumb.is-current')
        ?.scrollIntoView({ block: 'nearest', inline: 'center', behavior: 'smooth' });
    };

    const openLightbox = (photos, startIndex) => {
      currentPhotos = photos;
      currentIndex = startIndex;
      updateLightbox();
      lbThumbs.innerHTML = photos.map((photo, i) => `
        <button type="button" class="lightbox__thumb ${i === currentIndex ? 'is-current' : ''}"
                data-thumb-index="${i}" aria-label="Voir la photo ${i + 1}">
          <img src="${photo.src}" alt="" loading="lazy">
        </button>`).join('');
      lbThumbs.querySelectorAll('[data-thumb-index]').forEach((btn) =>
        btn.addEventListener('click', () => gotoPhoto(Number(btn.dataset.thumbIndex))));
      lbThumbs.querySelector('.lightbox__thumb.is-current')
        ?.scrollIntoView({ block: 'nearest', inline: 'center' });
      lightbox.classList.add('is-open');
      lightbox.setAttribute('aria-hidden', 'false');
      document.body.classList.add('lightbox-open');
    };

    const closeLightbox = () => {
      lightbox.classList.remove('is-open');
      lightbox.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('lightbox-open');
    };

    views.forEach((view) => {
      const btns = $$('.gallery-photo', view);
      const photos = btns.map((btn) => ({
        src: btn.dataset.photoSrc,
        caption: btn.dataset.photoCaption || '',
      }));
      btns.forEach((btn, i) =>
        btn.addEventListener('click', () => openLightbox(photos, i)));
    });

    lightbox.querySelector('[data-lb-close]')?.addEventListener('click', closeLightbox);
    lightbox.querySelector('[data-lb-prev]')?.addEventListener('click', () => gotoPhoto(currentIndex - 1));
    lightbox.querySelector('[data-lb-next]')?.addEventListener('click', () => gotoPhoto(currentIndex + 1));
    lightbox.addEventListener('click', (e) => { if (e.target === lightbox) closeLightbox(); });

    document.addEventListener('keydown', (e) => {
      if (!lightbox.classList.contains('is-open')) return;
      if (e.key === 'Escape') closeLightbox();
      if (e.key === 'ArrowLeft') gotoPhoto(currentIndex - 1);
      if (e.key === 'ArrowRight') gotoPhoto(currentIndex + 1);
    });

    let touchStartX = 0;
    lightbox.addEventListener('touchstart', (e) => {
      touchStartX = e.changedTouches[0].clientX;
    }, { passive: true });
    lightbox.addEventListener('touchend', (e) => {
      const diff = e.changedTouches[0].clientX - touchStartX;
      if (Math.abs(diff) < 60) return;
      gotoPhoto(diff > 0 ? currentIndex - 1 : currentIndex + 1);
    });
  }

  /* ---------- 7. Catalogue du personnel (personnel.html) ---------- */
  async function renderPersonnel() {
    const hierarchy = $('[data-staff-hierarchy]');
    if (!hierarchy) return; // pas la page personnel

    let members;
    try {
      const res = await fetchApi('/members?per_page=500');
      members = res.data || [];
    } catch {
      return; // API indisponible : catalogue statique d'origine conservé
    }
    if (!members.length) return;

    const imgSrc = (p) => (!p ? '' : /^(https?:)?\//.test(p) ? p : '/' + p.replace(/^\/+/, ''));
    const fullName = (m) => m.full_name || `${m.first_name || ''} ${m.last_name || ''}`.trim();

    /* ----- Cartes : membres regroupés par niveau hiérarchique ----- */
    const cartes = members.filter((m) => (m.display_type || 'card') !== 'table');
    if (cartes.length) {
      // Regroupement : Map préserve l'ordre d'insertion
      const niveaux = new Map();
      cartes.forEach((m) => {
        const cle = m.level || 'Autres';
        if (!niveaux.has(cle)) niveaux.set(cle, []);
        niveaux.get(cle).push(m);
      });
      // Ordre d'affichage : ordre de création (id), aucun positionnement à gérer
      const entrees = [...niveaux.entries()].sort((a, b) =>
        Math.min(...a[1].map((m) => m.id)) - Math.min(...b[1].map((m) => m.id)));

      hierarchy.innerHTML = entrees.map(([niveau, liste], i) => {
        liste.sort((a, b) => a.id - b.id);
        return `
          <section class="staff-level" data-staff-level aria-labelledby="dyn-level-${i}">
            <header class="staff-level__head">
              <h3 id="dyn-level-${i}" class="staff-level__title">${esc(niveau)}</h3>
              <span class="staff-level__count" data-level-count>${liste.length} personne${liste.length > 1 ? 's' : ''}</span>
            </header>
            <div class="staff-level__grid">
              ${liste.map((m) => `
                <a class="staff-person" href="${esc(m.page_url || 'membres/membre.html?slug=' + encodeURIComponent(m.slug))}"
                  data-staff-item
                  data-search="${esc((fullName(m) + ' ' + (m.role || '') + ' ' + niveau + ' ' + (m.degree || '') + ' ' + (m.unit || '')).toLowerCase())}">
                  ${m.photo ? `<img src="${esc(imgSrc(m.photo))}" alt="${esc(fullName(m))}" loading="lazy" onerror="this.remove()">` : ''}
                  <div class="staff-person__info">
                    <strong>${esc(fullName(m))}</strong>
                    ${m.role ? `<span>${esc(m.role)}</span>` : ''}
                    ${m.degree ? `<em class="staff-person__degree" title="${esc(m.degree)}">${esc(m.degree)}</em>` : ''}
                  </div>
                </a>`).join('')}
            </div>
          </section>`;
      }).join('');
    }

    /* ----- Tableau : personnels (n°, nom, grade, spécialité) ----- */
    const table = $('.staff-recherche-wrap table');
    const lignes = members.filter((m) => m.display_type === 'table');
    if (table && lignes.length) {
      lignes.sort((a, b) => (a.last_name || '').localeCompare(b.last_name || '', 'fr'));
      const tbody = $('tbody', table);
      if (tbody) {
        tbody.innerHTML = lignes.map((m, i) => `
          <tr data-staff-row data-search="${esc((fullName(m) + ' ' + (m.grade || '') + ' ' + (m.speciality || '')).toLowerCase())}">
            <td>${String(i + 1).padStart(2, '0')}</td>
            <td><strong>${esc(fullName(m))}</strong></td>
            <td><span class="staff-tag">${esc(m.grade || 'Personnel')}</span></td>
            <td>${esc(m.speciality || '')}</td>
          </tr>`).join('');
      }
      // Compteur de l'entête du tableau
      const entete = table.closest('.staff-level');
      const compte = entete?.querySelector('.staff-level__count');
      if (compte) compte.textContent = `${lignes.length} personne${lignes.length > 1 ? 's' : ''}`;
    }

    initStaffSearch(hierarchy, cartes.length + (table && lignes.length ? lignes.length : 0));
  }

  // Recherche du personnel : filtrage des cartes et des lignes du tableau
  // (le markup statique prévoyait la barre mais aucune logique ne l'implémentait).
  function initStaffSearch(hierarchy, total) {
    const input = $('[data-staff-search]');
    if (!input) return;

    const cartes = $$('[data-staff-item]', hierarchy);
    const niveaux = $$('[data-staff-level]', hierarchy);
    const lignes = $$('[data-staff-row]');
    const count = $('[data-staff-count]');
    const empty = $('[data-staff-empty]');
    const resets = $$('[data-staff-reset]');

    const appliquer = () => {
      const mots = input.value.trim().toLowerCase().split(/\s+/).filter(Boolean);
      const matche = (chaine) => mots.every((mot) => (chaine || '').includes(mot));
      let visibles = 0;

      cartes.forEach((el) => {
        const ok = !mots.length || matche(el.dataset.search);
        el.hidden = !ok;
        if (ok) visibles += 1;
      });

      niveaux.forEach((niv) => {
        const items = $$('[data-staff-item]', niv);
        const visiblesNiv = items.filter((el) => !el.hidden).length;
        niv.hidden = visiblesNiv === 0;
        const compte = niv.querySelector('[data-level-count]');
        if (compte) compte.textContent = `${visiblesNiv} personne${visiblesNiv > 1 ? 's' : ''}`;
      });

      lignes.forEach((el) => {
        const ok = !mots.length || matche(el.dataset.search);
        el.hidden = !ok;
        if (ok) visibles += 1;
      });

      if (count) count.textContent = visibles;
      resets.forEach((b) => { b.hidden = !mots.length; });
      if (empty) empty.hidden = visibles > 0;
    };

    input.addEventListener('input', appliquer);
    resets.forEach((b) => b.addEventListener('click', () => {
      input.value = '';
      appliquer();
      input.focus();
    }));
    appliquer();
  }

  /* ---------- 8. Fiche individuelle automatique (membres/membre.html) ---------- */
  async function renderMembreFiche() {
    const hero = $('[data-membre-hero]');
    if (!hero) return; // pas la page fiche

    const slug = new URLSearchParams(location.search).get('slug');
    const nom = $('[data-membre-nom]', hero);
    if (!slug) {
      if (nom) nom.textContent = 'Aucune fiche demandée.';
      return;
    }

    // Depuis membres/, les chemins relatifs du site remontent d'un niveau
    const imgSrc = (p) => (!p ? '' : /^(https?:)?\//.test(p) ? p : '../' + p.replace(/^\/+/, ''));

    try {
      const res = await fetchApi('/members/' + encodeURIComponent(slug));
      const m = res.data;
      document.title = `${m.full_name || (m.first_name + ' ' + m.last_name)} | CNDT Cameroun`;

      if (nom) nom.textContent = m.full_name || `${m.first_name} ${m.last_name}`;
      const fonction = $('[data-membre-fonction]', hero);
      if (fonction) fonction.textContent = m.role || '';

      const photo = $('[data-membre-photo]', hero);
      if (photo) {
        if (m.photo) { photo.src = imgSrc(m.photo); photo.alt = 'Portrait de ' + (m.full_name || m.slug); }
        else photo.hidden = true;
      }

      // Informations clés : seules les rubriques renseignées sont affichées
      const metas = {
        grade: m.grade,
        specialite: m.speciality,
        fonction: m.role,
        depuis: m.since,
      };
      Object.entries(metas).forEach(([cle, valeur]) => {
        const li = $(`[data-meta="${cle}"]`, hero);
        if (!li) return;
        if (valeur) {
          li.hidden = false;
          const strong = $('strong', li);
          if (strong) strong.textContent = valeur;
        } else {
          li.hidden = true;
        }
      });

      const bio = $('[data-membre-bio]');
      if (bio) {
        const contenu = m.biography || '';
        if (contenu) {
          if (/<\/?[a-z][\s\S]*>/i.test(contenu)) bio.outerHTML = contenu;
          else {
            const paragraphs = contenu.split(/\n\s*\n/).map((p) => p.trim()).filter(Boolean);
            bio.outerHTML = paragraphs.length
              ? paragraphs.map((p) => `<p>${esc(p)}</p>`).join('')
              : '';
          }
        } else {
          bio.textContent = "La biographie de cette personne n'a pas encore été renseignée.";
        }
      }

      // Centres d'intérêts : « chips » comme sur les fiches d'origine
      const sectionInterets = $('[data-membre-interets]');
      const chips = $('[data-membre-chips]');
      const interets = Array.isArray(m.interests) ? m.interests.filter(Boolean) : [];
      if (sectionInterets && chips) {
        if (interets.length) {
          chips.innerHTML = interets.map((i) => `<li class="chip">${esc(i)}</li>`).join('');
          sectionInterets.hidden = false;
        } else {
          sectionInterets.hidden = true;
        }
      }
    } catch {
      if (nom) nom.textContent = 'Fiche introuvable ou API indisponible.';
    }
  }

  /* ---------- Démarrage ---------- */
  const start = () => {
    renderActualitesGrid();
    renderNewsList();
    renderArticleDetail();
    renderInstituts();
    renderHero();
    renderGalerie();
    renderPersonnel();
    renderMembreFiche();
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})();

@extends('layouts.site')

@section('title', "Organigramme | CNDT Cameroun")

@section('description', "Organigramme du CNDT — Cabinet du Secrétaire Permanent, Commission Opérationnelle et Commission Support du Comité National de Développement des Technologies du Cameroun.")

@section('content')
  <main id="contenu">

    <section class="container ecart home-grid">

      <section class="inner-section">
        <div class="container">

          <!-- ---------- Titre de page ---------- -->
          <div class="page-heading">
            <div>
              <h2>Organigramme du CNDT</h2>
              <p>Structure du Secrétariat Permanent en trois chapitres&nbsp;: le Cabinet du Secrétaire Permanent, la Commission Opérationnelle et la Commission Support.</p>
            </div>
          </div>

          <div class="org-intro">
            <p>
              Le <strong>Comité National de Développement des Technologies</strong> est organisé autour du
              Secrétaire Permanent et de trois chapitres opérationnels. Chaque commission dispose d'une
              page dédiée&nbsp;; les unités rattachées y sont détaillées. Cliquez sur un élément pour
              explorer la structure.
            </p>
          </div>

          <!-- ---------- Arbre rendu depuis organisation-data.js ---------- -->
          <div data-org-tree>
            <noscript>
              <p>L'organigramme interactif nécessite JavaScript. Consultez les pages
              <a href="/cabinet">Cabinet du Secrétaire Permanent</a>,
              <a href="/crmp">Commission Recherche &amp; Management des Projets</a>,
              <a href="/cdcrp">Documentation, Communication &amp; Relations Publiques</a>,
              <a href="/rhcaj">RH, Coopération &amp; Affaires Juridiques</a> et
              <a href="/caaf">Affaires Administratives &amp; Financières</a>.</p>
            </noscript>
          </div>

        </div>
      </section>

    </section>

  </main>

  
@endsection

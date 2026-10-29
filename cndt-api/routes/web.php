<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Site vitrine — routes Laravel
|--------------------------------------------------------------------------
| Deux familles d'URLs servent les mêmes vues Blade (resources/views/pages) :
|
|   1. URLs propres (canonical)   : /actualites, /article/{slug}, /galerie…
|   2. URLs historiques (.html)    : /actualites.html, /membres/{page}.html…
|      conservées telles quelles — tous les liens internes du site
|      (app.js, api-actualites.js, vues) continuent de fonctionner.
|
| Les pages dynamiques (article, fiche membre, fiche projet) lisent le
| contenu via ?slug=… : les URLs propres redirigent donc vers la forme
| complète (ex. /article/{slug} → /actualite/article.html?slug={slug}).
|
| Le dashboard React (build statique) est servi depuis public/admin/ par
| Apache ; l'API reste sous /api/v1 (routes/api.php).
*/

/* ---------- Accueil ---------- */
Route::view('/', 'pages.index')->name('accueil');
Route::view('/index.html', 'pages.index');

/* ---------- Actualités ---------- */
Route::view('/actualites', 'pages.actualites')->name('actualites');
Route::view('/actualites.html', 'pages.actualites');
Route::get('/article/{slug}', fn (string $slug) =>
    redirect()->to('/actualite/article.html?slug='.urlencode($slug), 301)
)->name('article');
Route::view('/actualite/article.html', 'pages.actualite.article');

/* ---------- Galerie photo ---------- */
Route::view('/galerie', 'pages.galerie')->name('galerie');
Route::view('/galerie.html', 'pages.galerie');

/* ---------- Catalogue du personnel ---------- */
Route::view('/personnel', 'pages.personnel')->name('personnel');
Route::view('/personnel.html', 'pages.personnel');
Route::get('/membre/{slug}', fn (string $slug) =>
    redirect()->to('/membres/membre.html?slug='.urlencode($slug), 301)
)->name('membre');
Route::view('/membres/membre.html', 'pages.membres.membre');

/* ---------- Projets ---------- */
Route::view('/projets', 'pages.projets')->name('projets');
Route::view('/projets.html', 'pages.projets');
Route::get('/projet/{slug}', fn (string $slug) =>
    redirect()->to('/projets/projet.html?slug='.urlencode($slug), 301)
)->name('projet');
Route::view('/projets/projet.html', 'pages.projets.projet');

/* ---------- Publications & contact ---------- */
Route::view('/publications', 'pages.publication')->name('publications');
Route::view('/publication.html', 'pages.publication');
Route::view('/contact', 'pages.contact')->name('contact');
Route::view('/contact.html', 'pages.contact');

/* ---------- Organisation & commissions ---------- */
Route::view('/organigramme', 'pages.organigramme')->name('organigramme');
Route::view('/organigramme.html', 'pages.organigramme');
Route::view('/comite', 'pages.comite')->name('comite');
Route::view('/comite.html', 'pages.comite');
Route::view('/cabinet', 'pages.cabinet')->name('cabinet');
Route::view('/cabinet.html', 'pages.cabinet');
Route::view('/crmp', 'pages.crmp')->name('crmp');
Route::view('/crmp.html', 'pages.crmp');
Route::view('/caaf', 'pages.caaf')->name('caaf');
Route::view('/caaf.html', 'pages.caaf');
Route::view('/cdcrp', 'pages.cdcrp')->name('cdcrp');
Route::view('/cdcrp.html', 'pages.cdcrp');
Route::view('/rhcaj', 'pages.rhcaj')->name('rhcaj');
Route::view('/rhcaj.html', 'pages.rhcaj');
Route::view('/ct', 'pages.ct')->name('ct');
Route::view('/ct.html', 'pages.ct');

/* ---------- Pages historiques (vues Blade statiques) ----------
   /actualite/{page}.html, /membres/{page}.html, /projets/{page}.html —
   servies uniquement si la vue existe dans resources/views/pages/. */
$pageStatique = function (string $dossier) {
    return function (string $page) use ($dossier) {
        $vue = 'pages.'.$dossier.'.'.str_replace(['/', '\\'], '', $page);
        abort_unless(view()->exists($vue), 404);

        return view($vue);
    };
};

Route::get('/actualite/{page}.html', $pageStatique('actualite'))->where('page', '[A-Za-z0-9\-_]+');
Route::get('/membres/{page}.html', $pageStatique('membres'))->where('page', '[A-Za-z0-9\-_]+');
Route::get('/projets/{page}.html', $pageStatique('projets'))->where('page', '[A-Za-z0-9\-_]+');

/* ---------- Dashboard React (build statique servi par Apache) ---------- */
Route::redirect('/admin.html', '/admin/', 301);

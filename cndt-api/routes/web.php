<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Site vitrine (vues Blade)
|--------------------------------------------------------------------------
| Refonte : le site vitrine est servi par cette application Laravel.
| Les URLs historiques (.html) sont conservées à l'identique — tous les
| liens internes du site et les références existantes continuent de
| fonctionner. Le dashboard React (build statique) est servi depuis
| public/admin/ par Apache ; l'API reste sous /api/v1 (routes/api.php).
*/

// ---------- Page d'accueil ----------
Route::view('/', 'pages.index');
Route::view('/index.html', 'pages.index');

// ---------- Pages institutionnelles ----------
Route::view('/comite.html', 'pages.comite');
Route::view('/cabinet.html', 'pages.cabinet');
Route::view('/crmp.html', 'pages.crmp');
Route::view('/caaf.html', 'pages.caaf');
Route::view('/cdcrp.html', 'pages.cdcrp');
Route::view('/rhcaj.html', 'pages.rhcaj');
Route::view('/ct.html', 'pages.ct');
Route::view('/organigramme.html', 'pages.organigramme');
Route::view('/personnel.html', 'pages.personnel');
Route::view('/galerie.html', 'pages.galerie');
Route::view('/actualites.html', 'pages.actualites');
Route::view('/publication.html', 'pages.publication');
Route::view('/projets.html', 'pages.projets');
Route::view('/contact.html', 'pages.contact');

// ---------- Pages dynamiques (contenu alimenté par l'API côté client) ----------
Route::view('/actualite/article.html', 'pages.actualite.article');
Route::view('/membres/membre.html', 'pages.membres.membre');
Route::view('/projets/projet.html', 'pages.projets.projet');

// ---------- Articles et fiches historiques (vues Blade statiques) ----------
// Vue servie uniquement si elle existe dans resources/views/pages/
$pagesDynamiques = function (string $dossier) {
    return function (string $page) use ($dossier) {
        $vue = 'pages.'.$dossier.'.'.str_replace(['/', '\\'], '', $page);
        abort_unless(view()->exists($vue), 404);

        return view($vue);
    };
};

Route::get('/actualite/{page}.html', $pagesDynamiques('actualite'))->where('page', '[A-Za-z0-9\-_]+');
Route::get('/membres/{page}.html', $pagesDynamiques('membres'))->where('page', '[A-Za-z0-9\-_]+');
Route::get('/projets/{page}.html', $pagesDynamiques('projets'))->where('page', '[A-Za-z0-9\-_]+');

// ---------- Dashboard React (build statique servi par Apache) ----------
Route::redirect('/admin.html', '/admin/', 301);

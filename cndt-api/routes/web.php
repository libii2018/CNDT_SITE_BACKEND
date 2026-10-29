<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Site vitrine — routes Laravel (URLs propres, sans .html)
|--------------------------------------------------------------------------
| URLs canoniques :
|   /  /actualites  /article/{slug}  /galerie  /personnel  /membre/{slug}
|   /projets  /projet/{slug}  /publications  /contact  /organigramme
|   /comite  /cabinet  /crmp  /caaf  /cdcrp  /rhcaj  /ct
|   /actualite/{page}  /membres/{page}  /projets/{page}  (contenus historiques)
|
| Les anciennes URLs .html ne servent plus de contenu : elles redirigent
| en 301 vers l'URL propre correspondante (liens externes, favoris).
| Le dashboard React est servi depuis public/admin/ par Apache ;
| l'API reste sous /api/v1 (routes/api.php).
*/

/* ---------- Redirections .html → URL propre (redirections 301) ----------
   La chaîne de requête est préservée (ex. /galerie.html?album=x → /galerie?album=x). */
$versPropre = function (string $chemin) {
    return fn () => redirect($chemin.(request()->getQueryString() ? '?'.request()->getQueryString() : ''), 301);
};

/* ---------- Accueil ---------- */
Route::view('/', 'pages.index')->name('accueil');
Route::get('/index.html', $versPropre('/'));

/* ---------- Actualités ---------- */
Route::view('/actualites', 'pages.actualites')->name('actualites');
Route::get('/actualites.html', $versPropre('/actualites'));
Route::view('/article/{slug}', 'pages.actualite.article')->name('article');
Route::get('/actualite/article.html', fn () =>
    redirect('/article/'.request()->string('slug'), 301));

/* ---------- Galerie photo ---------- */
Route::view('/galerie', 'pages.galerie')->name('galerie');
Route::get('/galerie.html', $versPropre('/galerie'));

/* ---------- Catalogue du personnel ---------- */
Route::view('/personnel', 'pages.personnel')->name('personnel');
Route::get('/personnel.html', $versPropre('/personnel'));
Route::view('/membre/{slug}', 'pages.membres.membre')->name('membre');
Route::get('/membres/membre.html', fn () =>
    redirect('/membre/'.request()->string('slug'), 301));

/* ---------- Projets ---------- */
Route::view('/projets', 'pages.projets')->name('projets');
Route::get('/projets.html', $versPropre('/projets'));
Route::view('/projet/{slug}', 'pages.projets.projet')->name('projet');
Route::get('/projets/projet.html', fn () =>
    redirect('/projet/'.request()->string('slug'), 301));

/* ---------- Publications & contact ---------- */
Route::view('/publications', 'pages.publication')->name('publications');
Route::get('/publication.html', $versPropre('/publications'));
Route::view('/contact', 'pages.contact')->name('contact');
Route::get('/contact.html', $versPropre('/contact'));

/* ---------- Organisation & commissions ---------- */
Route::view('/organigramme', 'pages.organigramme')->name('organigramme');
Route::get('/organigramme.html', $versPropre('/organigramme'));
Route::view('/comite', 'pages.comite')->name('comite');
Route::get('/comite.html', $versPropre('/comite'));
Route::view('/cabinet', 'pages.cabinet')->name('cabinet');
Route::get('/cabinet.html', $versPropre('/cabinet'));
Route::view('/crmp', 'pages.crmp')->name('crmp');
Route::get('/crmp.html', $versPropre('/crmp'));
Route::view('/caaf', 'pages.caaf')->name('caaf');
Route::get('/caaf.html', $versPropre('/caaf'));
Route::view('/cdcrp', 'pages.cdcrp')->name('cdcrp');
Route::get('/cdcrp.html', $versPropre('/cdcrp'));
Route::view('/rhcaj', 'pages.rhcaj')->name('rhcaj');
Route::get('/rhcaj.html', $versPropre('/rhcaj'));
Route::view('/ct', 'pages.ct')->name('ct');
Route::get('/ct.html', $versPropre('/ct'));

/* ---------- Contenus historiques (vues Blade statiques) ----------
   /actualite/{page}, /membres/{page}, /projets/{page} — servis uniquement
   si la vue existe dans resources/views/pages/ (404 sinon). */
$pageStatique = function (string $dossier) {
    return function (string $page) use ($dossier) {
        $vue = 'pages.'.$dossier.'.'.str_replace(['/', '\\'], '', $page);
        abort_unless(view()->exists($vue), 404);

        return view($vue);
    };
};

Route::get('/actualite/{page}', $pageStatique('actualite'))->where('page', '[A-Za-z0-9\-_]+');
Route::get('/membres/{page}', $pageStatique('membres'))->where('page', '[A-Za-z0-9\-_]+');
Route::get('/projets/{page}', $pageStatique('projets'))->where('page', '[A-Za-z0-9\-_]+');

// Anciennes URLs .html → URL propre
Route::get('/actualite/{page}.html', fn (string $page) =>
    redirect('/actualite/'.$page.(request()->getQueryString() ? '?'.request()->getQueryString() : ''), 301))->where('page', '[A-Za-z0-9\-_]+');
Route::get('/membres/{page}.html', fn (string $page) =>
    redirect('/membres/'.$page.(request()->getQueryString() ? '?'.request()->getQueryString() : ''), 301))->where('page', '[A-Za-z0-9\-_]+');
Route::get('/projets/{page}.html', fn (string $page) =>
    redirect('/projets/'.$page.(request()->getQueryString() ? '?'.request()->getQueryString() : ''), 301))->where('page', '[A-Za-z0-9\-_]+');

/* ---------- Dashboard React (build statique servi par Apache) ---------- */
Route::redirect('/admin.html', '/admin/', 301);

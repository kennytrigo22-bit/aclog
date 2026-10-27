<?php

use App\Http\Controllers\Apprenant;
use App\Http\Controllers\PublicSite;
use Illuminate\Support\Facades\Route;

// ─── PUBLIC ───
Route::get('/', [PublicSite\HomeController::class, 'index'])->name('home');
Route::get('/formations', [PublicSite\FormationController::class, 'index'])->name('formations.index');
Route::get('/formations/{formation:slug}', [PublicSite\FormationController::class, 'show'])->name('formations.show');
Route::view('/nos-services', 'public.services')->name('services');
Route::get('/tests-de-niveau', [PublicSite\TestNiveauController::class, 'index'])->name('tests.index');
Route::post('/tests-de-niveau/questions', [PublicSite\TestNiveauController::class, 'questions'])->middleware('throttle:40,1,tests-questions')->name('tests.questions');
Route::post('/tests-de-niveau/soumettre', [PublicSite\TestNiveauController::class, 'soumettre'])->middleware('throttle:20,1,tests-soumettre')->name('tests.soumettre');
Route::get('/contact', [PublicSite\ContactController::class, 'index'])->name('contact');
Route::post('/contact', [PublicSite\ContactController::class, 'envoyer'])->middleware('throttle:5,1,contact')->name('contact.envoyer');
Route::get('/certificat/verifier/{code}', [Apprenant\CertificatController::class, 'verifier'])->name('certificat.verifier');
Route::get('/a-propos', [PublicSite\AnnuaireController::class, 'aPropos'])->name('a-propos');
Route::get('/annuaire-certifies', [PublicSite\AnnuaireController::class, 'annuaire'])->name('annuaire-certifies');
Route::view('/conditions-generales', 'public.cgv')->name('cgv');
Route::view('/politique-confidentialite', 'public.confidentialite')->name('confidentialite');
Route::view('/mentions-legales', 'public.mentions-legales')->name('mentions-legales');

// ─── PROFIL ───
Route::get('/dashboard', [Apprenant\DashboardController::class, 'redirection'])->middleware('auth')->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/profil', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profil', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::delete('/profil/avatar', [\App\Http\Controllers\ProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');

    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification_interne}/lire', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/tout-lire', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
});

// ─── APPRENANT ───
Route::middleware(['auth', 'verified', 'role:apprenant'])
    ->prefix('espace')
    ->name('apprenant.')
    ->group(function () {
        Route::get('/dashboard', [Apprenant\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/ping', [Apprenant\DashboardController::class, 'ping'])->name('ping');

        Route::get('/mes-formations', [Apprenant\CoursController::class, 'index'])->name('cours.index');
        Route::get('/cours/{inscription}/lecon/{lecon}', [Apprenant\CoursController::class, 'lecon'])->name('cours.lecon');
        Route::post('/progression', [Apprenant\CoursController::class, 'sauvegarder'])->middleware('throttle:120,1,progression')->name('progression.save');

        Route::get('/quiz/{quiz}', [Apprenant\ExamenController::class, 'quiz'])->name('quiz');
        Route::post('/quiz/{quiz}/soumettre', [Apprenant\ExamenController::class, 'soumettreQuiz'])->name('quiz.soumettre');
        Route::get('/examen/{examen}', [Apprenant\ExamenController::class, 'examen'])->name('examen');
        Route::post('/examen/{examen}/demarrer', [Apprenant\ExamenController::class, 'demarrer'])->name('examen.demarrer');
        Route::get('/examen/{examen}/passer', [Apprenant\ExamenController::class, 'passer'])->name('examen.passer');
        Route::post('/examen/{examen}/soumettre', [Apprenant\ExamenController::class, 'soumettreExamen'])->name('examen.soumettre');

        Route::get('/ressources/{ressource}', [Apprenant\RessourceController::class, 'show'])->name('ressources.show');
        Route::get('/ressources/{ressource}/telecharger', [Apprenant\RessourceController::class, 'telecharger'])->middleware('throttle:30,1,ressources')->name('ressources.telecharger');
        Route::get('/ressources/{ressource}/pdf', [Apprenant\RessourceController::class, 'afficherPdf'])->name('ressources.pdf');

        Route::get('/certifications', [Apprenant\CertificatController::class, 'index'])->name('certificats.index');
        Route::patch('/certifications/{certificat}/visibilite', [Apprenant\CertificatController::class, 'visibilite'])->name('certificats.visibilite');
        Route::get('/certifications/{certificat}/download', [Apprenant\CertificatController::class, 'download'])->name('certificats.download');

        Route::get('/challenges', [Apprenant\ChallengeController::class, 'index'])->name('challenges.index');
        Route::get('/challenges/{parcours:slug}', [Apprenant\ChallengeController::class, 'parcours'])->name('challenges.parcours');
        Route::get('/challenges/etape/{challenge}', [Apprenant\ChallengeController::class, 'etape'])->name('challenges.etape');
        Route::post('/challenges/etape/{challenge}', [Apprenant\ChallengeController::class, 'valider'])->middleware('throttle:30,1,challenges')->name('challenges.valider');

        Route::get('/paiement/{formation:slug}', [Apprenant\PaiementController::class, 'initier'])->name('paiement.initier');
        Route::post('/paiement/{formation:slug}/confirmer', [Apprenant\PaiementController::class, 'confirmer'])->middleware('throttle:6,1,paiement')->name('paiement.confirmer');

        Route::get('/support', [Apprenant\SupportController::class, 'index'])->name('support.index');
        Route::post('/support/ticket', [Apprenant\SupportController::class, 'creer'])->middleware('throttle:10,1,support-creer')->name('support.creer');
        Route::post('/support/ticket/{ticket}/message', [Apprenant\SupportController::class, 'repondre'])->middleware('throttle:20,1,support-repondre')->name('support.repondre');
    });

// ─── ADMIN ───
Route::middleware(['auth', 'role:admin,gestionnaire'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

        Route::get('/formations', [\App\Http\Controllers\Admin\FormationController::class, 'index'])->name('formations.index');
        Route::get('/formations/creer', [\App\Http\Controllers\Admin\FormationController::class, 'create'])->name('formations.create');
        Route::post('/formations', [\App\Http\Controllers\Admin\FormationController::class, 'store'])->name('formations.store');
        Route::get('/formations/{formation}/editer', [\App\Http\Controllers\Admin\FormationController::class, 'edit'])->name('formations.edit');
        Route::put('/formations/{formation}', [\App\Http\Controllers\Admin\FormationController::class, 'update'])->name('formations.update');
        Route::delete('/formations/{formation}', [\App\Http\Controllers\Admin\FormationController::class, 'destroy'])->name('formations.destroy');

        Route::get('/formations/{formation}/contenu', [\App\Http\Controllers\Admin\ContenuController::class, 'show'])->name('formations.contenu');
        Route::post('/formations/{formation}/modules', [\App\Http\Controllers\Admin\ContenuController::class, 'storeModule'])->name('modules.store');
        Route::put('/modules/{module}', [\App\Http\Controllers\Admin\ContenuController::class, 'updateModule'])->name('modules.update');
        Route::delete('/modules/{module}', [\App\Http\Controllers\Admin\ContenuController::class, 'destroyModule'])->name('modules.destroy');
        Route::post('/modules/{module}/lecons', [\App\Http\Controllers\Admin\ContenuController::class, 'storeLecon'])->name('lecons.store');
        Route::put('/lecons/{lecon}', [\App\Http\Controllers\Admin\ContenuController::class, 'updateLecon'])->name('lecons.update');
        Route::delete('/lecons/{lecon}', [\App\Http\Controllers\Admin\ContenuController::class, 'destroyLecon'])->name('lecons.destroy');
        Route::post('/modules/{module}/quiz', [\App\Http\Controllers\Admin\ContenuController::class, 'storeQuiz'])->name('quiz.store');
        Route::post('/quiz/{quiz}/questions', [\App\Http\Controllers\Admin\ContenuController::class, 'storeQuestion'])->name('questions.store');
        Route::delete('/questions/{question}', [\App\Http\Controllers\Admin\ContenuController::class, 'destroyQuestion'])->name('questions.destroy');

        Route::get('/formations/{formation}/examens', [\App\Http\Controllers\Admin\ExamenAdminController::class, 'index'])->name('formations.examens');
        Route::post('/formations/{formation}/examens', [\App\Http\Controllers\Admin\ExamenAdminController::class, 'storeOrUpdate'])->name('formations.examens.store');
        Route::post('/examens/{examen}/questions', [\App\Http\Controllers\Admin\ExamenAdminController::class, 'storeQuestion'])->name('examens.questions.store');
        Route::delete('/examens/questions/{question}', [\App\Http\Controllers\Admin\ExamenAdminController::class, 'destroyQuestion'])->name('examens.questions.destroy');
        Route::post('/examens/tentatives/{tentative_examen}/reactiver', [\App\Http\Controllers\Admin\ExamenAdminController::class, 'reactiverTentative'])->name('examens.tentatives.reactiver');

        Route::get('/formations/{formation}/import-ia', [\App\Http\Controllers\Admin\GeminiImportController::class, 'showUpload'])->name('formations.gemini.upload');
        Route::post('/formations/{formation}/import-ia', [\App\Http\Controllers\Admin\GeminiImportController::class, 'analyze'])->name('formations.gemini.analyze');
        Route::post('/formations/{formation}/import-ia/save', [\App\Http\Controllers\Admin\GeminiImportController::class, 'save'])->name('formations.gemini.save');

        Route::get('/paiements', [\App\Http\Controllers\Admin\PaiementController::class, 'index'])->name('paiements.index');
        Route::get('/paiements/{paiement}/capture', [\App\Http\Controllers\Admin\PaiementController::class, 'capture'])->name('paiements.capture');
        Route::post('/paiements/{paiement}/valider', [\App\Http\Controllers\Admin\PaiementController::class, 'valider'])->name('paiements.valider');
        Route::post('/paiements/{paiement}/rejeter', [\App\Http\Controllers\Admin\PaiementController::class, 'rejeter'])->name('paiements.rejeter');

        // Gestion des comptes : reservee aux administrateurs (un gestionnaire ne peut ni promouvoir ni reinitialiser un compte)
        Route::middleware('role:admin')->group(function () {
            Route::get('/utilisateurs', [\App\Http\Controllers\Admin\UtilisateurController::class, 'index'])->name('utilisateurs.index');
            Route::get('/utilisateurs/{user}/editer', [\App\Http\Controllers\Admin\UtilisateurController::class, 'edit'])->name('utilisateurs.edit');
            Route::put('/utilisateurs/{user}', [\App\Http\Controllers\Admin\UtilisateurController::class, 'update'])->name('utilisateurs.update');
            Route::post('/utilisateurs/{user}/reset-password', [\App\Http\Controllers\Admin\UtilisateurController::class, 'resetPassword'])->name('utilisateurs.resetPassword');
            Route::post('/utilisateurs/{user}/suspend', [\App\Http\Controllers\Admin\UtilisateurController::class, 'suspend'])->name('utilisateurs.suspend');
            Route::post('/utilisateurs/{user}/unsuspend', [\App\Http\Controllers\Admin\UtilisateurController::class, 'unsuspend'])->name('utilisateurs.unsuspend');
            Route::delete('/utilisateurs/{user}', [\App\Http\Controllers\Admin\UtilisateurController::class, 'softDelete'])->name('utilisateurs.destroy');
        });

        // Certificats : liste + generation pour les apprenants formes en presentiel
        Route::get('/certificats', [\App\Http\Controllers\Admin\CertificatAdminController::class, 'index'])->name('certificats.index');
        Route::post('/certificats/presentiel', [\App\Http\Controllers\Admin\CertificatAdminController::class, 'store'])->name('certificats.store');
        Route::post('/certificats/presentiel/lot', [\App\Http\Controllers\Admin\CertificatAdminController::class, 'storeLot'])->name('certificats.lot');
        Route::get('/certificats/{certificat}/editer', [\App\Http\Controllers\Admin\CertificatAdminController::class, 'edit'])->name('certificats.edit');
        Route::put('/certificats/{certificat}', [\App\Http\Controllers\Admin\CertificatAdminController::class, 'update'])->name('certificats.update');
        Route::get('/certificats/{certificat}/pdf', [\App\Http\Controllers\Admin\CertificatAdminController::class, 'pdf'])->name('certificats.pdf');
        Route::patch('/certificats/{certificat}/visibilite', [\App\Http\Controllers\Admin\CertificatAdminController::class, 'visibilite'])->name('certificats.visibilite');
        Route::delete('/certificats/{certificat}', [\App\Http\Controllers\Admin\CertificatAdminController::class, 'destroy'])->middleware('role:admin')->name('certificats.destroy');

        Route::get('/tickets', [\App\Http\Controllers\Admin\TicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/{ticket}', [\App\Http\Controllers\Admin\TicketController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{ticket}/repondre', [\App\Http\Controllers\Admin\TicketController::class, 'repondre'])->name('tickets.repondre');
        Route::patch('/tickets/{ticket}/statut', [\App\Http\Controllers\Admin\TicketController::class, 'changerStatut'])->name('tickets.statut');

        Route::get('/promos', [\App\Http\Controllers\Admin\PromoController::class, 'index'])->name('promos.index');
        Route::get('/promos/creer', [\App\Http\Controllers\Admin\PromoController::class, 'create'])->name('promos.create');
        Route::post('/promos', [\App\Http\Controllers\Admin\PromoController::class, 'store'])->name('promos.store');
        Route::get('/promos/{promo}/editer', [\App\Http\Controllers\Admin\PromoController::class, 'edit'])->name('promos.edit');
        Route::put('/promos/{promo}', [\App\Http\Controllers\Admin\PromoController::class, 'update'])->name('promos.update');
        Route::delete('/promos/{promo}', [\App\Http\Controllers\Admin\PromoController::class, 'destroy'])->name('promos.destroy');

        // Challenges / Parcours CTF
        Route::get('/challenges', [\App\Http\Controllers\Admin\ChallengeAdminController::class, 'index'])->name('challenges.index');
        Route::get('/challenges/parcours/creer', [\App\Http\Controllers\Admin\ChallengeAdminController::class, 'createParcours'])->name('challenges.parcours.create');
        Route::post('/challenges/parcours', [\App\Http\Controllers\Admin\ChallengeAdminController::class, 'storeParcours'])->name('challenges.parcours.store');
        Route::get('/challenges/parcours/{parcours}/editer', [\App\Http\Controllers\Admin\ChallengeAdminController::class, 'editParcours'])->name('challenges.parcours.edit');
        Route::put('/challenges/parcours/{parcours}', [\App\Http\Controllers\Admin\ChallengeAdminController::class, 'updateParcours'])->name('challenges.parcours.update');
        Route::delete('/challenges/parcours/{parcours}', [\App\Http\Controllers\Admin\ChallengeAdminController::class, 'destroyParcours'])->name('challenges.parcours.destroy');
        Route::get('/challenges/parcours/{parcours}', [\App\Http\Controllers\Admin\ChallengeAdminController::class, 'show'])->name('challenges.parcours.show');
        Route::post('/challenges/parcours/{parcours}/challenges', [\App\Http\Controllers\Admin\ChallengeAdminController::class, 'storeChallenge'])->name('challenges.store');
        Route::put('/challenges/challenge/{challenge}', [\App\Http\Controllers\Admin\ChallengeAdminController::class, 'updateChallenge'])->name('challenges.challenge.update');
        Route::delete('/challenges/challenge/{challenge}', [\App\Http\Controllers\Admin\ChallengeAdminController::class, 'destroyChallenge'])->name('challenges.challenge.destroy');

        // QCM Bulk Import
        Route::get('/qcm-import', [\App\Http\Controllers\Admin\QcmImportController::class, 'index'])->name('qcm-import.index');
        Route::post('/qcm-import', [\App\Http\Controllers\Admin\QcmImportController::class, 'analyze'])->name('qcm-import.analyze');
        Route::get('/qcm-import/apercu', [\App\Http\Controllers\Admin\QcmImportController::class, 'apercu'])->name('qcm-import.apercu');
        Route::post('/qcm-import/lancer', [\App\Http\Controllers\Admin\QcmImportController::class, 'lancer'])->name('qcm-import.lancer');
        Route::post('/qcm-import/lot', [\App\Http\Controllers\Admin\QcmImportController::class, 'lot'])->name('qcm-import.lot');
        Route::post('/qcm-import/terminer', [\App\Http\Controllers\Admin\QcmImportController::class, 'terminer'])->name('qcm-import.terminer');
        Route::post('/qcm-import/enregistrer', [\App\Http\Controllers\Admin\QcmImportController::class, 'save'])->name('qcm-import.save');

        // Test de Niveau
        Route::get('/test-niveau', [\App\Http\Controllers\Admin\TestNiveauAdminController::class, 'index'])->name('test-niveau.index');
        Route::post('/test-niveau/questions', [\App\Http\Controllers\Admin\TestNiveauAdminController::class, 'storeQuestion'])->name('test-niveau.questions.store');
        Route::delete('/test-niveau/questions/{question}', [\App\Http\Controllers\Admin\TestNiveauAdminController::class, 'destroyQuestion'])->name('test-niveau.questions.destroy');
        Route::get('/test-niveau/resultats', [\App\Http\Controllers\Admin\TestNiveauAdminController::class, 'resultats'])->name('test-niveau.resultats');

        Route::get('/partenaires', [\App\Http\Controllers\Admin\PartenaireController::class, 'index'])->name('partenaires.index');
        Route::post('/partenaires', [\App\Http\Controllers\Admin\PartenaireController::class, 'store'])->name('partenaires.store');
        Route::delete('/partenaires/{partenaire}', [\App\Http\Controllers\Admin\PartenaireController::class, 'destroy'])->name('partenaires.destroy');
        Route::patch('/partenaires/{partenaire}/toggle', [\App\Http\Controllers\Admin\PartenaireController::class, 'toggleActif'])->name('partenaires.toggle');

        // Parametres (numero Mobile Money, identite, scores) : administrateurs uniquement
        Route::middleware('role:admin')->group(function () {
            Route::get('/parametres', [\App\Http\Controllers\Admin\ParametreController::class, 'index'])->name('parametres');
            Route::post('/parametres', [\App\Http\Controllers\Admin\ParametreController::class, 'update'])->name('parametres.update');
            Route::post('/parametres/temoignages', [\App\Http\Controllers\Admin\TemoignageController::class, 'store'])->name('temoignages.store');
            Route::patch('/parametres/temoignages/{temoignage}', [\App\Http\Controllers\Admin\TemoignageController::class, 'toggle'])->name('temoignages.toggle');
            Route::delete('/parametres/temoignages/{temoignage}', [\App\Http\Controllers\Admin\TemoignageController::class, 'destroy'])->name('temoignages.destroy');
        });
    });

require __DIR__.'/auth.php';

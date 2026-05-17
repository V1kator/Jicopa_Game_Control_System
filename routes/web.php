<?php

use App\Http\Controllers\Admin\AlunoController;
use App\Http\Controllers\Admin\AvaliacaoConfigController;
use App\Http\Controllers\Admin\AvaliacaoNotaController;
use App\Http\Controllers\Admin\BoletimController;
use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\EsporteController;
use App\Http\Controllers\Admin\HistoricoController;
use App\Http\Controllers\Admin\JogoController;
use App\Http\Controllers\Admin\RelatorioController;
use App\Http\Controllers\Admin\ScoringConfigController;
use App\Http\Controllers\Admin\SumulaController;
use App\Http\Controllers\Admin\TurmaController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PenalidadeController;
use App\Http\Controllers\PresencaController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ResultadoController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'role:admin|professor', 'App\Http\Middleware\RestrictProfessorAccess'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
    Route::resource('turmas', TurmaController::class)->except(['show']);
    Route::resource('categorias', CategoriaController::class)->except(['show']);
    Route::resource('esportes', EsporteController::class)->except(['show']);
    Route::resource('alunos', AlunoController::class)->except(['show']);
    Route::post('users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');
    Route::post('turmas/{turma}/restore', [TurmaController::class, 'restore'])->name('turmas.restore');
    Route::post('categorias/{categoria}/restore', [CategoriaController::class, 'restore'])->name('categorias.restore');
    Route::post('esportes/{esporte}/restore', [EsporteController::class, 'restore'])->name('esportes.restore');
    Route::post('alunos/{aluno}/restore', [AlunoController::class, 'restore'])->name('alunos.restore');
    Route::resource('jogos', JogoController::class)->except(['show']);
    Route::get('jogos/{jogo}/sumula', [SumulaController::class, 'show'])->name('jogos.sumula');
    Route::get('turmas/{turma}/boletim', [BoletimController::class, 'show'])->name('turmas.boletim');
    Route::get('scoring-config', [ScoringConfigController::class, 'index'])->name('scoring-config.index');
    Route::put('scoring-config', [ScoringConfigController::class, 'update'])->name('scoring-config.update');
    Route::get('avaliacao-config', [AvaliacaoConfigController::class, 'index'])->name('avaliacao-config.index');
    Route::post('avaliacao-config', [AvaliacaoConfigController::class, 'store'])->name('avaliacao-config.store');
    Route::get('avaliacao-notas', [AvaliacaoNotaController::class, 'index'])->name('avaliacao-notas.index');
    Route::post('avaliacao-notas', [AvaliacaoNotaController::class, 'store'])->name('avaliacao-notas.store');
    Route::get('relatorios', [RelatorioController::class, 'index'])->name('relatorios.index');
    Route::get('relatorios/geral', [RelatorioController::class, 'geral'])->name('relatorios.geral');
    Route::get('relatorios/categoria/{categoria}', [RelatorioController::class, 'porCategoria'])->name('relatorios.categoria');
    Route::get('historico', [HistoricoController::class, 'index'])->name('historico.index');
});

Route::middleware(['auth', 'verified', 'role:admin|professor'])->group(function () {
    Route::get('/calendario', [JogoController::class, 'index'])->name('calendario.index');
    Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');
    Route::get('/jogos/{jogo}/resultado', [ResultadoController::class, 'edit'])->name('resultado.edit');
    Route::put('/jogos/{jogo}/resultado', [ResultadoController::class, 'update'])->name('resultado.update');
    Route::get('/jogos/{jogo}/presenca', [PresencaController::class, 'index'])->name('presenca.index');
    Route::post('/jogos/{jogo}/presenca', [PresencaController::class, 'store'])->name('presenca.store');
    Route::resource('penalidades', PenalidadeController::class)->except(['show', 'create', 'edit']);
});

require __DIR__.'/auth.php';

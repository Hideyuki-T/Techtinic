<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;
use App\Http\Controllers\MemoController;
use App\Http\Controllers\SudokuController;
use App\Http\Controllers\TetrisController;
use App\Http\Controllers\ECController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/main', [MainController::class, 'index'])->name('main.index');

Route::get('/memo', [MemoController::class, 'index'])->name('memo.index');

Route::get('/sudoku', [SudokuController::class, 'index'])->name('sudoku.index');

Route::get('/tetris', [TetrisController::class, 'index'])->name('tetris.index');

Route::get('/ec', [ECController::class, 'index'])->name('ec.index');




Route::get('/', function () {
    return view('welcome');
});


<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

//全ての始まりページ
Route::get('/main', function () {
    return view('main.index');
});

//TechtinicChat用のページ
Route::get('/chat', function () {
    return view('chat.index');
});

//Game用のページ
Route::get('/game', function () {
    return view('game.index');
});

//ECサイト用のページ
Route::get('/ec', function () {
    return view('ec.index');
});

// public/js/tetris/main.js
import { TetrisGame } from './game.js';
import { setupScoreSubmission } from './score.js';

let game = null;

window.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('gameCanvas');
    canvas.width = 10 * 30;
    canvas.height = 20 * 30;

    // 初期状態としてゲームインスタンスを生成
    game = new TetrisGame(canvas);

    // ゲーム制御ボタンのイベント設定
    document.getElementById('start-btn').addEventListener('click', () => {
        game.start();
    });

    document.getElementById('stop-btn').addEventListener('click', () => {
        game.stop();
    });

    document.getElementById('reset-btn').addEventListener('click', () => {
        game.reset();
    });

    // 方向ボタンのイベント設定
    document.getElementById('left-btn').addEventListener('click', () => {
        game.moveLeft();
    });

    document.getElementById('right-btn').addEventListener('click', () => {
        game.moveRight();
    });

    document.getElementById('down-btn').addEventListener('click', () => {
        game.moveDown();
    });

    document.getElementById('up-btn').addEventListener('click', () => {
        game.rotate();
    });

    // スコア送信機能の初期化
    setupScoreSubmission();
});

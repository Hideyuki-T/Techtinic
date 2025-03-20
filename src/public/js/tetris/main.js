import { TetrisGame } from './game.js';
import { setupScoreSubmission } from './score.js';

window.addEventListener('DOMContentLoaded', () => {
    // ゲームキャンバスの設定
    const canvas = document.getElementById('gameCanvas');
    canvas.width = 10 * 30;  // 10セル×30px
    canvas.height = 20 * 30; // 20セル×30px

    // テトリスゲームの開始
    const game = new TetrisGame(canvas);
    game.start();

    // スコア送信機能の初期化
    setupScoreSubmission();
});

// public/js/tetris/game.js
import { Piece } from './piece.js';
import { Board } from './board.js';
import { Renderer } from './renderer.js';

export class TetrisGame {
    constructor(canvas) {
        this.canvas = canvas;
        this.board = new Board(10, 20);
        this.renderer = new Renderer(canvas, this.board);
        this.currentPiece = this.generateRandomPiece();
        this.gameOver = false;
        this.interval = null;
        this.gameSpeed = 500; // ミリ秒
    }

    generateRandomPiece() {
        const pieces = [
            { shape: [[1, 1, 1, 1]], color: 'cyan' },
            { shape: [[1, 1], [1, 1]], color: 'yellow' },
            { shape: [[0, 1, 0], [1, 1, 1]], color: 'purple' }
        ];
        const random = pieces[Math.floor(Math.random() * pieces.length)];
        const piece = new Piece(random.shape, random.color);
        piece.x = Math.floor((this.board.width - piece.shape[0].length) / 2);
        piece.y = -piece.shape.length;
        return piece;
    }

    // ゲームループ処理
    gameLoop() {
        if (!this.board.isValidPosition(this.currentPiece, 0, 1)) {
            this.board.addPiece(this.currentPiece);
            this.currentPiece = this.generateRandomPiece();
            if (!this.board.isValidPosition(this.currentPiece, 0, 0)) {
                this.gameOver = true;
                clearInterval(this.interval);
                alert('Game Over!');
                return;
            }
        } else {
            this.currentPiece.y += 1;
        }
        this.renderer.render();
        this.renderer.renderPiece(this.currentPiece);
    }

    // ゲーム開始
    start() {
        if (!this.interval) {
            this.gameOver = false;
            this.interval = setInterval(() => this.gameLoop(), this.gameSpeed);
        }
    }

    // ゲーム停止
    stop() {
        if (this.interval) {
            clearInterval(this.interval);
            this.interval = null;
        }
    }

    // ゲームリセット（ボード・状態を初期化）
    reset() {
        this.stop();
        this.board = new Board(10, 20);
        this.renderer = new Renderer(this.canvas, this.board);
        this.currentPiece = this.generateRandomPiece();
        this.gameOver = false;
        this.start();
    }

    // 左に移動
    moveLeft() {
        if (this.board.isValidPosition(this.currentPiece, -1, 0)) {
            this.currentPiece.x -= 1;
            this.render();
        }
    }

    // 右に移動
    moveRight() {
        if (this.board.isValidPosition(this.currentPiece, 1, 0)) {
            this.currentPiece.x += 1;
            this.render();
        }
    }

    // 1セル下に移動
    moveDown() {
        if (this.board.isValidPosition(this.currentPiece, 0, 1)) {
            this.currentPiece.y += 1;
            this.render();
        }
    }

    // 回転
    rotate() {
        const clone = new Piece(
            this.currentPiece.shape.map(row => [...row]),
            this.currentPiece.color
        );
        clone.x = this.currentPiece.x;
        clone.y = this.currentPiece.y;
        clone.rotate();
        if (this.board.isValidPosition(clone, 0, 0)) {
            this.currentPiece.rotate();
            this.render();
        }
    }

    // 共通のレンダリング処理
    render() {
        this.renderer.render();
        this.renderer.renderPiece(this.currentPiece);
    }
}

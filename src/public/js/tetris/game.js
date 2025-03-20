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
    start() {
        this.interval = setInterval(() => this.gameLoop(), 500);
        this.setupControls();
    }
    gameLoop() {
        if (!this.board.isValidPosition(this.currentPiece, 0, 1)) {
            this.board.addPiece(this.currentPiece);
            this.currentPiece = this.generateRandomPiece();
            if (!this.board.isValidPosition(this.currentPiece, 0, 0)) {
                this.gameOver = true;
                clearInterval(this.interval);
                alert('Game Over!');
            }
        } else {
            this.currentPiece.y += 1;
        }
        this.renderer.render();
        this.renderer.renderPiece(this.currentPiece);
    }
    setupControls() {
        document.addEventListener('keydown', (e) => {
            if (this.gameOver) return;
            switch (e.key) {
                case 'ArrowLeft':
                    if (this.board.isValidPosition(this.currentPiece, -1, 0)) {
                        this.currentPiece.x -= 1;
                    }
                    break;
                case 'ArrowRight':
                    if (this.board.isValidPosition(this.currentPiece, 1, 0)) {
                        this.currentPiece.x += 1;
                    }
                    break;
                case 'ArrowDown':
                    if (this.board.isValidPosition(this.currentPiece, 0, 1)) {
                        this.currentPiece.y += 1;
                    }
                    break;
                case 'ArrowUp':
                    const clone = new Piece(
                        this.currentPiece.shape.map(row => [...row]),
                        this.currentPiece.color
                    );
                    clone.x = this.currentPiece.x;
                    clone.y = this.currentPiece.y;
                    clone.rotate();
                    if (this.board.isValidPosition(clone, 0, 0)) {
                        this.currentPiece.rotate();
                    }
                    break;
            }
            this.renderer.render();
            this.renderer.renderPiece(this.currentPiece);
        });
    }
}

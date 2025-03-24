export class Renderer {
    constructor(canvas, board, cellSize = 30) {
        this.canvas = canvas;
        this.context = canvas.getContext('2d');
        this.board = board;
        this.cellSize = cellSize;
    }
    render() {
        this.context.clearRect(0, 0, this.canvas.width, this.canvas.height);
        for (let y = 0; y < this.board.height; y++) {
            for (let x = 0; x < this.board.width; x++) {
                if (this.board.grid[y][x]) {
                    this.context.fillStyle = this.board.grid[y][x];
                    this.context.fillRect(x * this.cellSize, y * this.cellSize, this.cellSize, this.cellSize);
                    this.context.strokeRect(x * this.cellSize, y * this.cellSize, this.cellSize, this.cellSize);
                }
            }
        }
    }
    renderPiece(piece) {
        for (let y = 0; y < piece.shape.length; y++) {
            for (let x = 0; x < piece.shape[y].length; x++) {
                if (piece.shape[y][x]) {
                    this.context.fillStyle = piece.color;
                    this.context.fillRect(
                        (piece.x + x) * this.cellSize,
                        (piece.y + y) * this.cellSize,
                        this.cellSize,
                        this.cellSize
                    );
                    this.context.strokeRect(
                        (piece.x + x) * this.cellSize,
                        (piece.y + y) * this.cellSize,
                        this.cellSize,
                        this.cellSize
                    );
                }
            }
        }
    }
}

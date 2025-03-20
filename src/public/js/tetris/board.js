export class Board {
    constructor(width, height) {
        this.width = width;
        this.height = height;
        this.grid = this.createGrid();
    }
    createGrid() {
        const grid = [];
        for (let y = 0; y < this.height; y++) {
            grid[y] = [];
            for (let x = 0; x < this.width; x++) {
                grid[y][x] = 0;
            }
        }
        return grid;
    }
    isValidPosition(piece, offsetX, offsetY) {
        for (let y = 0; y < piece.shape.length; y++) {
            for (let x = 0; x < piece.shape[y].length; x++) {
                if (piece.shape[y][x]) {
                    const newX = piece.x + x + offsetX;
                    const newY = piece.y + y + offsetY;
                    if (newX < 0 || newX >= this.width || newY >= this.height) {
                        return false;
                    }
                    if (newY >= 0 && this.grid[newY][newX]) {
                        return false;
                    }
                }
            }
        }
        return true;
    }
    addPiece(piece) {
        for (let y = 0; y < piece.shape.length; y++) {
            for (let x = 0; x < piece.shape[y].length; x++) {
                if (piece.shape[y][x]) {
                    if (piece.y + y < 0) continue;
                    this.grid[piece.y + y][piece.x + x] = piece.color;
                }
            }
        }
        this.clearLines();
    }
    clearLines() {
        const newGrid = this.grid.filter(row => row.some(cell => cell === 0));
        const clearedLines = this.height - newGrid.length;
        for (let i = 0; i < clearedLines; i++) {
            newGrid.unshift(Array(this.width).fill(0));
        }
        this.grid = newGrid;
    }
}

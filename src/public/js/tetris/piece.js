export class Piece {
    constructor(shape, color) {
        this.shape = shape;
        this.color = color;
        this.x = 0;
        this.y = 0;
    }
    rotate() {
        // 90度時計回りの回転処理
        this.shape = this.shape[0].map((_, index) =>
            this.shape.map(row => row[index]).reverse()
        );
    }
}

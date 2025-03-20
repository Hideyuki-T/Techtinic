// CSRFトークンのセットアップ
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

export function setupScoreSubmission() {
    document.getElementById('submit-score').addEventListener('click', function() {
        const playerName = document.getElementById('player_name').value;
        const score = Math.floor(Math.random() * 1000); // 仮のスコア

        fetch('/tetris/score', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ player_name: playerName, score: score })
        })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
            })
            .catch(error => {
                console.error('Error:', error);
            });
    });
}

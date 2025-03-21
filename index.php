<?php
// Read stored scores
$scores = file_exists("scores.txt") ? file("scores.txt", FILE_IGNORE_NEW_LINES) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bubble Shooter Game</title>
    <style>
        body {
            text-align: center;
            background: linear-gradient(to bottom, #1e3c72, #2a5298);
            color: white;
            font-family: Arial, sans-serif;
        }
        h1 { font-size: 2em; margin: 20px 0; }
        canvas {
            background: radial-gradient(circle, #4facfe, #00f2fe);
            display: block;
            margin: auto;
            border: 4px solid white;
            border-radius: 10px;
        }
        #score-container { margin-top: 10px; font-size: 1.2em; }
        button {
            background: #ff9800; color: white; border: none; padding: 10px 20px;
            font-size: 1em; cursor: pointer; margin-top: 10px; border-radius: 5px;
        }
        button:hover { background: #e68900; }
    </style>
</head>
<body>
    <h1>Bubble Shooter Game</h1>
    <canvas id="gameCanvas" width="600" height="500"></canvas>
    <div id="score-container">Score: <span id="score">0</span></div>
    <button onclick="saveScore()">Save Score</button>

    <h2>Top Scores</h2>
    <ul id="score-list">
        <?php foreach ($scores as $score) { echo "<li>$score</li>"; } ?>
    </ul>

    <script>
        const canvas = document.getElementById("gameCanvas");
        const ctx = canvas.getContext("2d");
        let bubbles = [];
        let bullets = [];
        let shooter = { x: canvas.width / 2, y: canvas.height - 40, radius: 20, color: "white" };
        let score = 0;

        // Bubble Class
        class Bubble {
            constructor(x, y, radius, color) {
                this.x = x;
                this.y = y;
                this.radius = radius;
                this.color = color;
            }
            draw() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
                ctx.fillStyle = this.color;
                ctx.fill();
                ctx.closePath();
            }
        }

        // Bullet Class
        class Bullet {
            constructor(x, y) {
                this.x = x;
                this.y = y;
                this.radius = 5;
                this.speed = 5;
                this.color = "yellow";
            }
            move() {
                this.y -= this.speed;
            }
            draw() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
                ctx.fillStyle = this.color;
                ctx.fill();
                ctx.closePath();
            }
        }

        // Spawn random bubbles at the top
        function spawnBubble() {
            const x = Math.random() * canvas.width;
            const radius = 20;
            const colors = ["red", "green", "blue", "yellow", "purple"];
            const color = colors[Math.floor(Math.random() * colors.length)];
            bubbles.push(new Bubble(x, 50, radius, color));
        }

        // Update game frame
        function update() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Draw bubbles
            bubbles.forEach((bubble, bIndex) => {
                bubble.draw();
            });

            // Draw bullets
            bullets.forEach((bullet, index) => {
                bullet.move();
                bullet.draw();
                if (bullet.y < 0) bullets.splice(index, 1);

                // Check collision with bubbles
                bubbles.forEach((bubble, bIndex) => {
                    let dx = bubble.x - bullet.x;
                    let dy = bubble.y - bullet.y;
                    let distance = Math.sqrt(dx * dx + dy * dy);
                    if (distance < bubble.radius + bullet.radius) {
                        bubbles.splice(bIndex, 1);
                        bullets.splice(index, 1);
                        score++;
                        document.getElementById("score").innerText = score;
                    }
                });
            });

            // Draw shooter
            ctx.beginPath();
            ctx.arc(shooter.x, shooter.y, shooter.radius, 0, Math.PI * 2);
            ctx.fillStyle = shooter.color;
            ctx.fill();
            ctx.closePath();

            // Draw shooting arrow
            ctx.beginPath();
            ctx.moveTo(shooter.x, shooter.y - 20);
            ctx.lineTo(shooter.x - 10, shooter.y - 30);
            ctx.lineTo(shooter.x + 10, shooter.y - 30);
            ctx.fillStyle = "white";
            ctx.fill();
            ctx.closePath();

            requestAnimationFrame(update);
        }

        // Move shooter with mouse
        canvas.addEventListener("mousemove", (event) => {
            let rect = canvas.getBoundingClientRect();
            shooter.x = event.clientX - rect.left;
        });

        // Shoot bullets
        canvas.addEventListener("click", () => {
            bullets.push(new Bullet(shooter.x, shooter.y - 25));
        });

        // Save Score to PHP
        function saveScore() {
            fetch("save_score.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "score=" + score
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
                location.reload();
            });
        }

        setInterval(spawnBubble, 2000);
        update();
    </script>
</body>
</html>

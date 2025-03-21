<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $score = intval($_POST["score"]);

    // Read existing scores
    $scores = file_exists("scores.txt") ? file("scores.txt", FILE_IGNORE_NEW_LINES) : [];

    // Add new score and sort
    $scores[] = $score;
    rsort($scores);

    // Keep only top 5 scores
    $scores = array_slice($scores, 0, 5);

    // Save back to file
    file_put_contents("scores.txt", implode("\n", $scores));

    echo "Score saved successfully!";
}
?>

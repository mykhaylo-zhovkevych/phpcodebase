<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Protected Method Example</title>
</head>
<body>

<section>
<?php

require_once 'ScoreBoard.php';

function showBoards(string $title, array $boards): void
{
    echo "<br>$title<br>";

    foreach ($boards as $variableName => $board) {
        printf(
            "%-8s → ID: %-3d Team: %-7s Score: %d<br>",
            $variableName,
            spl_object_id($board),
            $board->getTeam(),
            $board->getScore()
        );
    }
}

$first = new ScoreBoard('Blue', 10);
$second = $first;

showBoards('Before modification:', [
    '$first' => $first,
    '$second' => $second,
]);

$second->addPoints(5);

showBoards('After modification:', [
    '$first' => $first,
    '$second' => $second,
]);

$first = new ScoreBoard('Red', 100);

showBoards('After assigning a new object to $first:', [
    '$first' => $first,
    '$second' => $second,
]);

echo "<h4>Reference assignment</h4>";

$third = new ScoreBoard('Green', 20);
$fourth = &$third;

showBoards('Before modification:', [
    '$third' => $third,
    '$fourth' => $fourth,
]);

$third->addPoints(5);

showBoards('After modification:', [
    '$third' => $third,
    '$fourth' => $fourth,
]);

$fourth = new ScoreBoard('Yellow', 200);

showBoards('After assigning a new object to $fourth:', [
    '$third' => $third,
    '$fourth' => $fourth,
]);

?>
</section>

</body>
</html>

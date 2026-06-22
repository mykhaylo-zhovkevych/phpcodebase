<?php

declare(strict_types=1);

require_once "Point.php";

$fst = new Point(12, 5);
$snd = new Point(1,1);
$thd = new Point(4, 10);


$arr = [$fst, $snd, $thd];

usort($arr, function (Point $a, Point $b): int {
    $distA = sqrt($a->x ** 2 + $a->y ** 2);
    $distB = sqrt($b->x ** 2 + $b->y ** 2);

    //It returns -1 if $distA is less than $distB, 0 if they are equal, and 1 if $distA is greater than $distB
    return $distA <=> $distB;
});

function distance(Point $point): float
{
    return sqrt($point->x ** 2 + $point->y ** 2);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Points</title>

    <style>
        body {
            padding: 30px;
        }

        article {
            padding: 15px;
        }
    </style>
</head>
<body>

<main>
    <h1>Points sorted by distance</h1>

    <section>
        <?php foreach ($arr as $index => $point): ?>
            <?php
                $number = $index + 1;
                $dist = distance($point);
            ?>

            <article>
                <h2>Point <?= $number ?></h2>

                <p>
                    X value: <?= $point->x ?>
                </p>

                <p>
                    Y value: <?= $point->y ?>
                </p>

                <p>
                    Distance from zero: <?= round($dist, 2) ?>
                </p>
            </article>

        <?php endforeach; ?>
    </section>
</main>

</body>
</html>
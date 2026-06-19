<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Protected Method Example</title>
</head>
<body>
<main>

<h1>Assignment and behavioral differences with OOP Example</h1>

    <?php

    // Independent copy
    require_once 'ScoreBoard.php';

    $original = new ScoreBoard('Blue', 10);
    $copy = clone $original;

    $copy->addPoints(5);

    echo "Original: {$original->getScore()}" . "<br>";
    echo "Copy: {$copy->getScore()}" . "<br>";
    echo "Original: {$original->getScore()}"  . "<br>";

    // Copy-on-write
    // PHP copies the array only when one variable changes it
    $array = [1, 2, 3, 4];
    $arrayCopy = $array;

    echo 'Original before change: ' . implode(', ', $array) . '<br>';
    echo 'Copy before change: ' . implode(', ', $arrayCopy) . '<br>';

    $arrayCopy[0] = 99;
    $arrayCopy[] = 5;

    echo 'Original after changing copy: ' . implode(', ', $array) . '<br>';
    echo 'Copy after change: ' . implode(', ', $arrayCopy) . '<br>';

    ?>

</main>

</body>
</html>

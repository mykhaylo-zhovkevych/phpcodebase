<?php

    declare(strict_types=1);

    $numbers = range(1,55);
    $divider = 2;

    // PHP close uses a use keyword for importing variables veras in js
     $isEvenPhp = function (int $number) use ($divider): bool {
         return $number % $divider === 0;
     };

    $isOddPhp = function (int $number) use ($isEvenPhp): bool {
        return !$isEvenPhp($number);
    };

    $evenPhp = array_filter($numbers, $isEvenPhp);
    $oddPhp = array_filter($numbers, $isOddPhp);

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

        section {
            padding: 15px;
        }
    </style>
</head>
<body>

<main>
    <h1>Even and Odd Numbers: PHP vs JavaScript</h1>

    <section>
        <h2>PHP result</h2>

        <p>
            Numbers:
            <?= implode(", ", $numbers) ?>
        </p>

        <p>
            Even numbers:
            <?= implode(", ", $evenPhp) ?>
        </p>

        <p>
            Odd numbers:
            <?= implode(", ", $oddPhp) ?>
        </p>
    </section>

    <section>
        <h2>JavaScript result</h2>

        <p>
            Numbers:
            <span id="jsNumbers"></span>
        </p>

        <p>
            Even numbers:
            <span id="jsEven"></span>
        </p>

        <p>
            Odd numbers:
            <span id="jsOdd"></span>
        </p>
    </section>

</main>

<script>
    const numbers = Array.from({ length: 55 }, (_, i) => i + 1);
    const divider = 2;

    const isEvenJs =function (number) {
        return number % divider === 0;
    };

    const isOddJs = function (number) {
        return !isEvenJs(number);
    }

    const evenJs = numbers.filter(isEvenJs);
    const oddJs = numbers.filter(isOddJs);

    document.getElementById("jsNumbers").textContent = numbers.join(", ");
    document.getElementById("jsEven").textContent = evenJs.join(", ");
    document.getElementById("jsOdd").textContent = oddJs.join(", ");
</script>

</body>
</html>
<?php
session_start();

if (!isset($_SESSION['rows'])) {
    $_SESSION['rows'] = $_SESSION['row'] ?? array_fill(0, 5, null);
    unset($_SESSION['row']);
}

if (isset($_POST['reset'])) {
    session_destroy();
    header("Location: ". $_SERVER['PHP_SELF']);
    exit();
}

function isPrime(int $number): bool {
    if ($number < 2) {
        return false;
    }
    // Simplify the number with sqrt
    for ($i = 2; $i <= sqrt($number); $i++) {
        if ($number % $i === 0) {
            return false;
        }
    }
    return true;
}


function countGreenButtons(array $rows): int {
    $count = 0;

    foreach ($rows as $row) {
        if ($row !== null && $row['is_prime'] === true) {
            $count++;
        }
    }

    return $count;
}

if (isset($_POST['clicked_row'])) {
    // _POST gets the value of the bth
    $clickedRow = (int) $_POST['clicked_row'];

    if ($_SESSION['rows'][$clickedRow] === null) {
        $number = random_int(0, 100);
        $isPrime = isPrime($number);

        $_SESSION['rows'][$clickedRow] = [
            'number' => $number,
            'is_prime' => $isPrime
        ];
    }
}


function createCorrectNumbersArray(array $rows): array {
    $correctNumbers = [];

    // Key as index and value as $row
    foreach ($rows as $index => $row) {
        if ($row !== null && $row['is_prime'] === true) {
            $correctNumbers[] = [
                'row' => $index,
                'number' => $row['number'],
                'result' => 'prime'
            ];
        }
    }

    return $correctNumbers;
}

$rows = $_SESSION['rows'];
$greenCount = countGreenButtons($rows);
$correctNumbers = createCorrectNumbersArray($rows);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prime Number Button Game</title>

    <style>
        .row {
            display: grid;
            grid-template-columns: 50% auto;
            gap: 10px;
            margin-bottom: 10px;
        }

        button {
            padding: 10px;
            cursor: pointer;
        }

        button:disabled {
            cursor: not-allowed;
        }

        .green {
            background-color: lightgreen;
        }

        .red {
            background-color: lightcoral;
        }

        pre {
            background: #eee;
            padding: 15px;
        }
    </style>
</head>
<body>


<main>
    <h1>Prime Number Button Game</h1>

    <form method="post">
        <?php for ($i = 0; $i < 5; $i++): ?>

            <?php
            $row = $rows[$i];

            if ($row === null) {
                $leftButtonText = "Generate number $i";
                $rightButtonText = "Waiting...";
                $class = "";
                $disabled = "";
            } else {
                $leftButtonText = "Number: " .$row['number'];
                $disabled = "disabled";

                switch (true) {
                    case ($row['is_prime'] === true):
                        $rightButtonText = "Prime";
                        $class = "green";
                        break;
                    case ($row['is_prime'] === false):
                        $rightButtonText = "Not Prime";
                        $class = "red";
                        break;
                    default: 
                        $rightButtonText = "Waiting...";
                        $class = "";
                        break;
                }
            }
        ?>

   <div class="row">
            <button type="submit" 
                    name="clicked_row" 
                    value="<?= $i ?>" 
                    <?= $disabled ?>
                >
                    <?= $leftButtonText ?>
                </button>

                <button 
                    type="button" 
                    class="<?= $class ?>"
                    <?= $disabled ?>>
                    <?= $rightButtonText ?>
                </button>               
    </div>         


    <?php endfor; ?>

    <button type="submit" name="reset">Reset</button>

</form>

<h2>Green Buttons Count: <?= $greenCount ?> / 5</h2>

<?php if ($greenCount === 5): ?>
    <h2>"2d array with prime numbers: </h2>

    <pre><?php print_r($correctNumbers); ?></pre>
<?php endif; ?>

</main>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Switch Cases Example</title>
</head>
<body>
<main>
    <h1>Switch Cases</h1>

    <?php
        global $number;
        $number = 25;

        $char = 'A';
        switch($char) {
            case 'php':
                ?>
                <h1>PHP</h1>
            <?php
            break;
            case 'js':
                ?>
                <h2>JavaScript</h2>
            <?php
            break;
        default:
                ?>
                <h3>Unknown</h3>
            <?php
        }
    ?>
    <hr>
    <h3><?php echo "Your Number: " . $number; ?></h3>
    <?php 
        // Switch with conditions can be used but it is not a common practice. It is generally better to use if-else statements for such cases.
        switch (true) {
            case ($number > 0 && $number <= 10):
                echo "is between 1 and 10";
                break;

            case ($number > 10 && $number <= 100):
                echo "is between 11 and 100";
                break;

            default:
                echo "Number is outside the range";
        }
    ?>

    <?php
        $homepage = file_get_contents('file.txt');
        $date = new DateTime();
        $startOfMonth = new DateTime(date('Y-m-01'));
        $twoWeeksAfterMonthStart = (clone $startOfMonth)->modify('+2 weeks');

        switch (true) {
            case ($date > $twoWeeksAfterMonthStart):
                $homepage .= "\n Additional content added an " . $date->format('Y-m-d H:i:s');
                break;
            default:
            // Skip 
            break;
        }
        file_put_contents('file_copy.txt', $homepage);

    ?>

</main>

</body>
</html>

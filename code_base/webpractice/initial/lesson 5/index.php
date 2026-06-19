<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Comments</title>
</head>
<body>
<h1>Build-in functions In PHP</h1>
    <?php
        $user = "Sergay";
        if (isset($user))
            echo "<p>$user</p>";
        else
            echo "<p>Not set</p>";
    ?>
    <?php
        echo gettype($user) . "\n";

        $tests = array(
            "42",
            1337,
            0x539,
            02471,
            0b10100111001,
            1377e0,
            "0x549",
            "ob10100111001",
            "not numeric",
            array(),
            9.1,
            null
        );
        foreach ($tests as $element) {
            if (is_numeric($element)) {
                echo var_export($element, true) . " - number" ."<br>". PHP_EOL;
        } else {
                echo var_export($element, true) . " - string" ."<br>". PHP_EOL;
            }
    }
    ?>

</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Constant Example</title>
</head>
<body>
<main>
    <h1>Constant and Files Paths</h1>

    <?php
//        define('NUMBER',1);
//        echo NUMBER;

        $num = mt_rand(1, 10);
        $name = "CONST($num)";
        define($name, $num);
        echo constant($name);

        echo '<br>' . 'Name of file'. __FILE__ . PHP_EOL;
        echo '<br>' . 'Lines' . __LINE__ . PHP_EOL;
    ?>
</main>

</body>
</html>

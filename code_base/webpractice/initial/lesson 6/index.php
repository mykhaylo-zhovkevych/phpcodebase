<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Comments</title>
</head>
<body>
<h1>Intro to OOP with PHP</h1>

<?php
    require_once 'Point.php';
    $point1 = new Point;
    // Deprecated, from php8.2+ a dynamic properties
    $point1->x = 13;
    $point2 = 11;

    echo $point1->x;
    // Delete object
    unset($point1);
    echo $point2;

?>

</body>
</html>

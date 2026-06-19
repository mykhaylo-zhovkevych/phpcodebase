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
        require_once __DIR__ . '/../lesson 7/Point.php';

        // Assignment by value
        $first = $second = 1;
        $second = 2;
        echo $first . '<br>' . $second . '<br>';

        // Assignment by reference
        $var = 4;
        $var2 = &$var;
        echo $var . '<br>' . $var2 . '<br>';

        $var2 = 5;
        echo $var . '<br>' . $var2 . '<br>';

        // Object assignment
        $third = new Point(3, 4);
        $fourth = new Point(5, 5);

        $third = $fourth;
        unset($third);
        echo $fourth->getX();
        // The object remains because $fourth still refers to it

        $fifth= new Point(5, 5);
        $sixth = &$fifth;

        unset($sixth);
        echo $fifth->getX();
        // The name $fourth still reaches the variable container and therefore the object

        // Modifying the object affects both in either case
        include "childModifyingObj.php"

    ?>
</main>

</body>
</html>

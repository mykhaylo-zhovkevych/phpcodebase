<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= "Lesson 1" ?></title>
</head>
<body>
    <h1>Hello, HTML!</h1>
    <?php
        echo 'Hello PHP!';
    ?>

    <?php
    if (mt_rand(0,1)){
        ?><div style="color: blue">Blue text</div>
        <?php
    } else {
        ?><div style="color: green">Green text</div>
        <?php
    }
    ?>

</body>
</html>

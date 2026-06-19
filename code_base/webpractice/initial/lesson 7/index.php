<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Protected Method Example</title>
</head>
<body>
<h1>Protected Method Example</h1>

<?php
require_once __DIR__ . '/Point.php';

class TeachingPoint extends Point
{
    public function averageFromChild(float ...$values): float
    {
        return $this->mean(...$values);
    }
}

$pointA = new Point(2, 1);
$pointB =  new Point(8,10);
$pointC = new Point(4,5);
$teachingPoint = new TeachingPoint(0, 0);

$middlePoint = $pointA->midpoint($pointB->midpoint($pointC));
$valuesInput = $_GET['values'] ?? '10.10.10';

$values = array_map(
    fn ($value) => (float) trim($value),
    array_filter(
        explode('.', $valuesInput),
        fn ($value) => trim($value) !== ''
    )
);
$averageFromChild = $teachingPoint->averageFromChild(...$values);

?>

<h4>Public methods can be called from here</h4>

<p>
    Point A:
    <?= $pointA->describe() ?>
</p>

<p>
    Point B:
    <?= $pointB->describe() ?>
</p>

<p>
    Point C:
    <?= $pointC->describe() ?>
</p>

<p>
    Middle point: <?= $middlePoint->describe() ?>
</p>

<h4>Child classe</h4>

<form method="get">
    <input type="text"
           name="values"
           value="<?= $valuesInput ?>"
           placeholder="1,2,3"
    >
    <button type="submit">Calculate average</button>
</form>

<p>
    Average calculated by the child class: <?= $averageFromChild ?>
</p>

</body>
</html>

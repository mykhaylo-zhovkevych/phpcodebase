<?php

class Point
{
    private float $x;
    private float $y;
    public static $tmp = 10;

    public function __construct(float $x, float $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    public function midpoint(Point $other): Point
    {
        $newX = $this->mean($this->x, $other->x);
        $newY = $this->mean($this->y, $other->y);

        return new Point($newX, $newY);
    }

    public function getX(): float {
        return $this->x;
    }

    public function getY(): float {
        return $this->y;
    }

    protected function mean(float ...$values): float
    {
        if (count($values) === 0) {
            throw new InvalidArgumentException(
                "At least one value must be provided."
            );
        }

        $result = array_sum($values) / count($values);

        return $this->normalized($result);

    }

    private function normalized(float $value): float
    {
        return round($value, 2);
    }

    // PHP treats this method as public
    function describe(): string
    {
        return "[{$this->x}, {$this->y}]";
    }
}

<?php

class ScoreBoard
{
    public function __construct(
        private int $score
    ) {
    }

    public function addPoints(int $points): void
    {
        $this->score += $points;
    }

    public function getScore(): int
    {
        return $this->score;
    }
}

<?php

class ScoreBoard
{
    public function __construct(
        private readonly string $team,
        private int $score
    ) {
    }

    public function addPoints(int $points): void
    {
        $this->score += $points;
    }

    public function getTeam(): string
    {
        return $this->team;
    }

    public function getScore(): int
    {
        return $this->score;
    }
}

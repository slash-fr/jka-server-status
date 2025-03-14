<?php

declare(strict_types=1);

class PlayerData
{
    public readonly string $name;
    public readonly int $score;
    public readonly int $ping;

    public function __construct(string $name, int $score, int $ping)
    {
        $this->name = $name;
        $this->score = $score;
        $this->ping = $ping;
    }
}

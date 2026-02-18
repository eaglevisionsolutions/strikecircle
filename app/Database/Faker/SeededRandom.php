<?php
// app/Database/Faker/SeededRandom.php

declare(strict_types=1);

namespace App\Database\Faker;

use DateTime;
use DateTimeImmutable;

class SeededRandom
{
    private int $seed;
    private int $state;

    public function __construct(int $seed = 1337)
    {
        $this->seed = $seed;
        $this->state = $seed;
    }

    // Deterministic int in [min, max]
    public function int(int $min, int $max): int
    {
        $this->state = ($this->state * 9301 + 49297) % 233280;
        $rnd = $this->state / 233280.0;
        return $min + (int)floor($rnd * ($max - $min + 1));
    }

    // Pick a value from array
    public function pick(array $values)
    {
        if (empty($values)) return null;
        $idx = $this->int(0, count($values) - 1);
        return $values[$idx];
    }

    public function strFrom(array $values)
    {
        return $this->pick($values);
    }

    public function dateBetween(DateTime $from, DateTime $to): DateTime
    {
        $start = $from->getTimestamp();
        $end = $to->getTimestamp();
        $ts = $this->int($start, $end);
        $dt = clone $from;
        $dt->setTimestamp($ts);
        return $dt;
    }

    public function uuid(): string
    {
        // Deterministic UUID-like string
        $hex = '';
        for ($i = 0; $i < 32; $i++) {
            $hex .= dechex($this->int(0, 15));
        }
        return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4) . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20, 12);
    }

    // Compatibility helpers used in factories
    public function numberBetween(int $min, int $max): int
    {
        return $this->int($min, $max);
    }

    public function dateTimeBetween(string $from, string $to): DateTimeImmutable
    {
        $start = new DateTimeImmutable($from);
        $end = new DateTimeImmutable($to);
        $ts = $this->int($start->getTimestamp(), $end->getTimestamp());
        return $start->setTimestamp($ts);
    }

    public function word(): string
    {
        $words = [
            'strike','spare','split','turkey','lane','gutter','pin','hook','frame','ball',
            'league','tournament','alley','oil','pattern','anchor','approach','release','swing','spindle'
        ];
        return (string)$this->pick($words);
    }

    public function sentence(int $min = 4, int $max = 12): string
    {
        $n = $this->int($min, $max);
        $w = [];
        for ($i = 0; $i < $n; $i++) $w[] = $this->word();
        $s = ucfirst(implode(' ', $w)) . '.';
        return $s;
    }

    public function city(): string
    {
        $cities = [
            'Los Angeles','New York','Chicago','Houston','Miami','Seattle','Denver','Boston','San Francisco','Dallas',
            'Atlanta','Phoenix','Portland','Philadelphia','San Diego','Austin','Orlando','Detroit','Minneapolis','Tampa'
        ];
        return (string)$this->pick($cities);
    }
}

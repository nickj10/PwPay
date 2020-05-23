<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Model;

final class PendingRequest
{
    private string $dest_email;
    private float $amount;

    public function __construct($dest_email, $amount)
    {
        $this->dest_email = $dest_email;
        $this->amount = $amount;
    }

    public function dest_email(): string
    {
        return $this->dest_email;
    }

    public function amount(): float
    {
        return $this->amount;
    }
}

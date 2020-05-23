<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Model;

final class PendingRequest
{
    private string $org_email;
    private float $amount;

    public function __construct($org_email, $amount)
    {
        $this->org_email = $org_email;
        $this->amount = $amount;
    }

    public function org_email(): string
    {
        return $this->org_email;
    }

    public function amount(): float
    {
        return $this->amount;
    }
}

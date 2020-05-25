<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Model;

final class PendingRequest
{
    private int $request_id;
    private string $org_email;
    private float $amount;
    private string $status;

    public function __construct($request_id, $org_email, $amount, $status)
    {
        $this->request_id = $request_id;
        $this->org_email = $org_email;
        $this->amount = $amount;
        $this->status = $status;
    }

    public function org_email(): string
    {
        return $this->org_email;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function amount(): float
    {
        return $this->amount;
    }
    public function request_id(): int
    {
        return $this->request_id;
    }
}

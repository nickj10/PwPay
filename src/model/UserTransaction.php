<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Model;

use DateTime;

final class UserTransaction
{
    private string $description;
    private string $action;
    private float $amount;

    public function __construct($description, $action, $amount) 
    {
        $this->description = $description;
        $this->action = $action;
        $this->amount = $amount;
    }     

    public function description(): string
    {
        return $this->description;
    }

    public function action(): string
    {
        return $this->action;
    }

    public function amount(): float
    {
        return $this->amount;
    }

    public function showAccountInfo() {
        $info = "<h1>Account information:</h1>";
        $info.= "Owner name: ".$this->owner_name;
        $info.= "<br/> IBAN: ".$this->iban;
        $info.= "<br/> Balance: ".$this->balance;
         
        return $info;

    }
}
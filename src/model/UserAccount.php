<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Model;

use DateTime;

final class UserAccount
{
    private int $account_id;
    private int $user_id;
    private string $owner_name;
    private string $iban;

    public function __construct($account_id, $user_id, $owner_name, $iban) 
    {
        $this->account_id = $account_id;
        $this->user_id = $user_id;
        $this->owner_name = $owner_name;
        $this->iban = $iban;   
    }     

    public function account_id(): int
    {
        return $this->account_id;
    }

    public function user_id(): int
    {
        return $this->user_id;
    }

    public function owner_name(): string
    {
        return $this->owner_name;
    }

    public function iban(): string
    {
        return $this->iban;
    }

    public function showAccountInfo() {
        $info = "<h1>Account information:</h1>";
        $info.= "Owner name: ".$this->owner_name;
        $info.= "<br/> IBAN: ".$this->iban;
         
        return $info;

    }
}
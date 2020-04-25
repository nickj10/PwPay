<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Model;

use DateTime;

final class User
{
    private int $id;
    private string $email;
    private string $password;
    private Date $birthday;
    private double $phone;

    public function __construct(
        string $email,
        string $password,
        Date $birthday,
        double $phone
    ) {
        if (!isset($phone)) {
            $this->email = $email;
            $this->password = $password;
            $this->birthday = $birthday;
        }
        else {
            $this->email = $email;
            $this->password = $password;
            $this->birthday = $birthday;
            $this->phone = $phone;   
        }     
    }

    public function id(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function password(): string
    {
        return $this->password;
    }

    public function birthday(): string
    {
        return $this->birthday;
    }
    
    public function phone(): string
    {
        return $this->phone;
    }
}
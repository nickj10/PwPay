<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Model;

use DateTime;

final class User
{
    private int $id;
    private string $email;
    private string $password;
    private string $birthday;
    private int $phone;

    public function __construct($email, $password, $birthday, $phone = 0) 
    {
        $this->email = $email;
        $this->password = $password;
        $this->birthday = $birthday;
        $this->phone = $phone;   
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
    
    public function phone(): int
    {
        return $this->phone;
    }

    public function showInfo() {
        $info = "<h1>User information:</h1>";
        $info.= "Email: ".$this->email;
        $info.= "<br/> Password: ".$this->password;
        $info.= "<br/> Birthday: ".$this->birthday->format('y-m-d');
        $info.= "<br/> Phone: ".$this->phone;
         
        return $info;

    }
}
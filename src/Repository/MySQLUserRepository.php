<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Repository;

use PDO;
use SallePW\SlimApp\Model\User;
use SallePW\SlimApp\Model\UserRepository;

final class MySQLUserRepository implements UserRepository
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    private PDOSingleton $database;

    public function __construct(PDOSingleton $database)
    {
        $this->database = $database;
    }

    public function save(User $user): void
    {
        $query = "INSERT INTO user(email, password, birthday, phone)
        values (:email, md5(:pass), :birthday, :phone);";
        $statement = $this->database->connection()->prepare($query);

        $email = $user->email();
        $password = $user->password();
        $bday = $user->birthday();
        $phone = $user->phone();

        $statement->bindParam(':email', $email, PDO::PARAM_STR);
        $statement->bindParam(':pass', $password, PDO::PARAM_STR);
        $statement->bindParam(':birthday', $bday, PDO::PARAM_STR);
        $statement->bindParam(':phone', $phone, PDO::PARAM_STR);

        $statement->execute();
    }

    public function isEmailTaken($email) {
        $query = "SELECT * FROM user WHERE email = :email;";
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':email', $email, PDO::PARAM_STR);
        
        $statement->execute();
        $count = $statement->rowCount();
        if ($count>0) {
            return true;
        }
        return false;

    }

    public function getUserByEmail($email) {
        $query = "SELECT * FROM user WHERE email = :email;";
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':email', $email, PDO::PARAM_STR);
        
        $statement->execute();
        $count = $statement->rowCount();
        if ($count > 0) {
            $row = $statement->fetch();
        }
        return $row;
    }

    public function getUserById($id) {
        $query = "SELECT * FROM user WHERE user_id = :id;";
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':id', $id, PDO::PARAM_STR);
        
        $statement->execute();
        $count = $statement->rowCount();
        if ($count > 0) {
            $row = $statement->fetch();
            if ($row['status'] == 'inactive') {
                $this->updateActivatingUser($id);
                return true;
            }
        }
        return false;
    }

    public function updateActivatingUser($id) {
        $query = "UPDATE user SET status = 'active', balance=20.00 WHERE user_id = :id;";
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':id', $id, PDO::PARAM_STR);
        $statement->execute();
    }

    

}
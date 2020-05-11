<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Repository;

use PDO;
use SallePW\SlimApp\Model\User;
use SallePW\SlimApp\Model\UserRepository;
use Ramsey\Uuid\Uuid;


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

    public function getUserInformationById($id) {
        $query = "SELECT * FROM user WHERE user_id = :id;";
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':id', $id);
        
        $statement->execute();
        $count = $statement->rowCount();
        if ($count > 0) {
            $row = $statement->fetch();
        }
        return $row;
    }

    public function updateActivatingUser($id) {
        $query = "UPDATE user SET status = 'active', balance=20.00 WHERE user_id = :id;";
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':id', $id, PDO::PARAM_STR);
        $statement->execute();
    }

    public function generateUuid($user_id) {
        $query = "INSERT INTO AuthToken(uuid, user_id) VALUES (:uuid, :user_id);";
        $statement = $this->database->connection()->prepare($query);
        $uuid = Uuid::uuid4();
        $statement->bindParam(':uuid', $uuid);
        $statement->bindParam(':user_id', $user_id);
        $statement->execute();
        return $uuid;
    }

    public function updateAuthToken ($token) {
        $query = "UPDATE AuthToken SET used = true WHERE uuid = :token;";
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':token', $token);

        $statement->execute();
    }

    public function isTokenValid($token) {
        $query = "SELECT * FROM AuthToken WHERE uuid = :token;";
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':token', $token);

        $statement->execute();
        $row = $statement->fetch();
        if ($row['used'] == 0) {
            $this->updateActivatingUser($row['user_id']);
            $this->updateAuthToken($token);
            return true;
        }
        return false;
    }

    public function insertImage($file, $user_id) {
        $query = "UPDATE user SET profile_picture = :file WHERE user_id = :id;";
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':file', $file);
        $statement->bindParam(':id', $user_id);

        $statement->execute();
    }

    public function updatePhone ($phone, $user_id) {
        $query = "UPDATE user SET phone = :phone WHERE user_id = :id;";
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':phone', $phone, PDO::PARAM_INT);
        $statement->bindParam(':id', $user_id);

        $statement->execute();
    }

    public function updatePassword ($password, $user_id) {
        $query = "UPDATE user SET password = :pass WHERE user_id = :id;";
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':pass', $password);
        $statement->bindParam(':id', $user_id);
        $statement->execute();
    }
}
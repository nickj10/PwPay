<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Repository;

use PDO;
use SallePW\SlimApp\Model\User;
use SallePW\SlimApp\Model\UserAccount;
use SallePW\SlimApp\Model\UserRepository;
use Ramsey\Uuid\Uuid;
use SallePW\SlimApp\Model\UserTransaction;
use SallePW\SlimApp\Model\PendingRequest;

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

    public function saveAccount($id, $owner, $iban): void
    {
        $query = "INSERT INTO Accounts(user_id, owner_name, iban)
        values (:userId, :ownerName, :iban);";
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam(':userId', $id, PDO::PARAM_STR);
        $statement->bindParam(':ownerName', $owner, PDO::PARAM_STR);
        $statement->bindParam(':iban', $iban, PDO::PARAM_STR);

        $statement->execute();
    }

    public function userHasAssociatedAccount($id)
    {
        $query = "SELECT * FROM Accounts WHERE user_id = :userId;";
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':userId', $id, PDO::PARAM_STR);

        $statement->execute();
        $count = $statement->rowCount();
        if ($count > 0) {
            return true;
        }
        return false;
    }

    public function getBankAccountInformation($userId)
    {
        $query = "SELECT * FROM Accounts WHERE user_id = :userId AND activity_status = 1;";
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':userId', $userId, PDO::PARAM_INT);

        $statement->execute();
        $count = $statement->rowCount();
        if ($count > 0) {
            $row = $statement->fetch();
            if ($row['user_id'] == $userId) {
                $userInfo = new UserAccount(intval($row['account_id']), intval($row['user_id']), $row['owner_name'], $row['iban']);
                return $userInfo;
            }
        }
    }

    public function updateAccountBalance($id, $amount, $action)
    {
        $userAccountInfo = $this->getUserInformationById($id);
        if ($action == "add") {
            $new_amount = floatval($userAccountInfo['balance']) + $amount;
        } else {
            $new_amount = floatval($userAccountInfo['balance']) - $amount;
        }
        $query = "UPDATE user SET balance = :amount WHERE user_id = :userId;";
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':userId', $id, PDO::PARAM_STR);
        $statement->bindParam(':amount', $new_amount, PDO::PARAM_STR);
        $statement->execute();
    }

    public function isEmailTaken($email)
    {
        $query = "SELECT * FROM user WHERE email = :email;";
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':email', $email, PDO::PARAM_STR);

        $statement->execute();
        $count = $statement->rowCount();
        if ($count > 0) {
            return true;
        }
        return false;
    }

    public function getUserByEmail($email)
    {
        $query = "SELECT * FROM user WHERE email = :email;";
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':email', $email, PDO::PARAM_STR);

        $statement->execute();
        $count = $statement->rowCount();
        if ($count > 0) {
            $row = $statement->fetch();
            return $row;
        }
    }

    public function getUserById($id)
    {
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

    public function isUserActive($email)
    {
        $query = "SELECT * FROM user WHERE email = :email;";
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':email', $email, PDO::PARAM_STR);

        $statement->execute();
        $count = $statement->rowCount();
        if ($count > 0) {
            $row = $statement->fetch();
            if ($row['status'] == 'active') {
                return true;
            }
        }
        return false;
    }

    public function getUserInformationById($id)
    {
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

    public function updateActivatingUser($id)
    {
        $query = "UPDATE user SET status = 'active', balance=20.00 WHERE user_id = :id;";
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':id', $id, PDO::PARAM_STR);
        $statement->execute();
    }

    public function generateUuid($user_id)
    {
        $query = "INSERT INTO AuthToken(uuid, user_id) VALUES (:uuid, :user_id);";
        $statement = $this->database->connection()->prepare($query);
        $uuid = Uuid::uuid4();
        $statement->bindParam(':uuid', $uuid);
        $statement->bindParam(':user_id', $user_id);
        $statement->execute();
        return $uuid;
    }

    public function updateAuthToken($token)
    {
        $query = "UPDATE AuthToken SET used = true WHERE uuid = :token;";
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':token', $token);

        $statement->execute();
    }

    public function isTokenValid($token)
    {
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

    public function insertImage($file, $user_id)
    {
        $query = "UPDATE user SET profile_picture = :file WHERE user_id = :id;";
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':file', $file);
        $statement->bindParam(':id', $user_id);

        $statement->execute();
    }

    public function updatePhone($phone, $user_id)
    {
        $query = "UPDATE user SET phone = :phone WHERE user_id = :id;";
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':phone', $phone, PDO::PARAM_INT);
        $statement->bindParam(':id', $user_id);

        $statement->execute();
    }

    public function updatePassword($password, $user_id)
    {
        $query = "UPDATE user SET password = :pass WHERE user_id = :id;";
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':pass', $password);
        $statement->bindParam(':id', $user_id);
        $statement->execute();
    }

    public function createTransaction($userId, $type, $amount, $action)
    {
        $query = "INSERT INTO Transactions (user_id, description, amount, action)
                    VALUES (:userId, :type, :amount, :action);";
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':userId', $userId);
        $statement->bindParam(':amount', $amount);
        $statement->bindParam(':type', $type);
        $statement->bindParam(':action', $action);
        $statement->execute();
    }

    public function accountExists($userId, $owner, $iban)
    {
        $query = "SELECT * FROM Accounts WHERE user_id = :userId;";
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':userId', $userId);
        $statement->execute();
        $count = $statement->rowCount();
        if ($count > 0) {
            $rows = $statement->fetchAll();
            for ($i = 0; $i < $count; $i++) {
                if ($rows[$i]['owner_name'] == $owner && $rows[$i]['iban'] == $iban) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getAccountTransactions($userId)
    {
        $query = "SELECT * FROM Transactions WHERE user_id = :userId ORDER BY created_at DESC LIMIT 5;";
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':userId', $userId);

        $statement->execute();
        $count = $statement->rowCount();
        if ($count > 0) {
            $rows = $statement->fetchAll();
            $transactions = [];
            for ($i = 0; $i < $count; $i++) {
                $transaction = new UserTransaction($rows[$i]['description'], $rows[$i]['action'], floatval($rows[$i]['amount']));
                array_push($transactions, $transaction);
            }
            return $transactions;
        }
    }

    public function createRequest($orgId, $destId, $amount, $status)
    {
        $query = "INSERT INTO Requests (org_user_id, dest_user_id, amount, status)
                    VALUES (:orgId, :destId, :amount, :status);";
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':orgId', $orgId);
        $statement->bindParam(':destId', $destId);
        $statement->bindParam(':amount', $amount);
        $statement->bindParam(':status', $status);
        $statement->execute();
    }

    public function getPendingIncomingRequests($userId)
    {
        $query = "SELECT * FROM Requests WHERE dest_user_id = :userId && status = 'PENDING';";
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':userId', $userId);

        $statement->execute();
        $count = $statement->rowCount();
        if ($count > 0) {
            $rows = $statement->fetchAll();
            $requests = [];
            for ($i = 0; $i < $count; $i++) {
                $user = $this->getUserInformationById(($rows[$i]['org_user_id']));
                $request = new PendingRequest($user['email'], floatval($rows[$i]['amount']));
                array_push($requests, $request);
            }
            return $requests;
        }
    }

    public function getPendingOutgoingRequests($userId)
    {
        $query = "SELECT * FROM Requests WHERE org_user_id = :userId && status = 'PENDING';";
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':userId', $userId);

        $statement->execute();
        $count = $statement->rowCount();
        if ($count > 0) {
            $rows = $statement->fetchAll();
            $requests = [];
            for ($i = 0; $i < $count; $i++) {
                $user = $this->getUserInformationById(($rows[$i]['dest_user_id']));
                $request = new PendingRequest($user['email'], floatval($rows[$i]['amount']));
                array_push($requests, $request);
            }
            return $requests;
        }
    }

    public function getAllAccountTransactions($userId)
    {
        $query = "SELECT * FROM Transactions WHERE user_id = :userId ORDER BY created_at DESC;";
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':userId', $userId);

        $statement->execute();
        $count = $statement->rowCount();
        if ($count > 0) {
            $rows = $statement->fetchAll();
            $transactions = [];
            for ($i = 0; $i < $count; $i++) {
                $transaction = new UserTransaction($rows[$i]['description'], $rows[$i]['action'], floatval($rows[$i]['amount']));
                array_push($transactions, $transaction);
            }
            return $transactions;
        }
    }
}

<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Service;

use Iban\Validation\Validator;
use Iban\Validation\Iban;

class FieldsValidator
{

    private const REGEX_PASSWORD = "/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/";
    private const REGEX_PHONE = "/^6[0-9]{8}$/";
    private $errors = array();

    public function validateRegister(array $data): array
    {
        $this->validateEmail($data);
        $this->validatePassword($data);
        $this->validateBirthday($data);
        if (!empty($data['phone'])) {
            $this->validatePhone($data);
        }
        return $this->errors;
    }

    public function validateLogin(array $data)
    {
        $this->validateEmail($data);
        $this->validatePassword($data);
        return $this->errors;
    }

    public function validateMoneyRequest(array $data)
    {
        $this->validateEmail($data);
        $this->validateAmount($data);
        return $this->errors;
    }

    public function validateProfile (array $data) {
        $this->validatePhone($data);
        return $this->errors;
    }

    public function validateSecurityPassword (array $data) {
        $this->errors = [];
        $new_password1 = filter_var($data['new_password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $new_password2 = filter_var($data['repeat_password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (!empty($data['old_password']) && !empty($data['new_password'] && !empty($data['repeat_password']))) {
            if ($new_password1 != $new_password2) {
                $this->errors['pass_match'] = "Passwords don't match";
            }
            else {
                if (!preg_match(self::REGEX_PASSWORD,$new_password1)) {
                    $this->errors['pass_pattern'] = 'The password must contain both letters and numbers with more than 5 characters.';
                }
            }
        }
        else {
            $this->errors['empty_fields'] = "All fields are required if you want to change your password";
        }
        return $this->errors;
    }

    public function validateTransaction (array $data) {
        $this->errors = [];
        $email = filter_var($data['email'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $amount = $data['amount'];
        if (empty($email) && empty($amount)) {
            $this->errors['empty_fields'] = "All fields are required to send money.";
        }
        else {
            if (!empty($email) && !empty($amount)) {
                $this->validateEmail($data);
                $this->validateAmount($data);
            }
        }
        if (empty($email) && !empty($amount)) {
            $this->errors['empty_email'] = "Please indicate a destination email.";
        }
        else {
            if (!empty($email) && empty($amount)) {
                $this->errors['empty_amount'] = "Please indicate an amount to send.";
            }
        }
        return $this->errors;
    }

    public function validateLoadMoney (array $data) {
        $this->errors = [];
        $this->validateAmount($data);
        return $this->errors;

    }

    public function validateAmount(array $data) {
        if (empty($data['amount'])) {
            $this->errors['amount_error'] = "Please indicate an amount to load.";
        }
        else {
            if ($data['amount'] <= 0) {
                $this->errors['amount_negative'] = "Please indicate a valid amount.";
            }
        }
    }

    private function validateEmail ($data) {
        $email = filter_var($data['email'],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (empty($email)) {
            $this->errors['email'] = 'The email cannot be empty';
        } else {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->errors['email'] = 'Email is not valid';
            } else {
                $email_aux = explode('@', $email);
                $domain = array_pop($email_aux);
                if ($domain != 'salle.url.edu') {
                    $this->errors['email'] = 'We only accept emails with domain salle.url.edu';
                }
            }
        }
    }

    private function validatePassword($data)
    {
        $password = filter_var($data['password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (empty($password)) {
            $this->errors['password'] = 'The password cannot be empty';
        } else {
            if (!preg_match(self::REGEX_PASSWORD, $password)) {
                $this->errors['password'] = 'The password must contain both letters and numbers with more than 5 characters.';
            }
        }
    }

    private function validateBirthday($data)
    {
        $bday = $data['birthday'];
        if (empty($bday)) {
            $this->errors['birthday'] = 'The birthday cannot be empty.';
        } else {
            $bday_aux = explode('-', $bday);
            $year = $bday_aux[0];
            if ((date('Y') - $year) <= 18) {
                $this->errors['birthday'] = 'Sorry, you are underage.';
            }
        }
    }

    private function validatePhone($data)
    {
        if (!preg_match(self::REGEX_PHONE, $data['phone'])) {
            $this->errors['phone'] = 'Phone is not valid.';
        }
    }

    public function validateBankAssociation($data)
    {
        $this->validateOwnerName($data);
        $this->validateIban($data);
        return $this->errors;
    }

    private function validateOwnerName($data)
    {
        $owner = filter_var($data['owner'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (empty($owner)) {
            $this->errors['owner'] = 'The owner name cannot be empty';
        }
    }

    private function validateIban($data)
    {
        $iban = new Iban(filter_var($data['iban'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $validator = new Validator([
            'violation.unsupported_country' => 'The requested country is not supported!',
            'violation.invalid_length' => 'The length of the given Iban is not valid!',
            'violation.invalid_format' => 'The format of the given Iban is not valid!',
            'violation.invalid_checksum' => 'The checksum of the given Iban is not valid!',
        ]);

        if (empty($data['iban'])) {
            $this->errors['iban'] = 'The IBAN cannot be empty';
        } else {
            if (!$validator->validate($iban)) {
                foreach ($validator->getViolations() as $violation) {
                    $this->errors['iban'] = $violation;
                }
            }
        }
    }
}

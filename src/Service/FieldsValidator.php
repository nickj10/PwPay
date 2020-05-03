<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Service;

class FieldsValidator {

    private const REGEX_PASSWORD = "/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/";
    private const REGEX_PHONE = "/^6[0-9]{8}$/";
    private $errors = array();

    public function validateRegister(array $data): array {
        $this->validateEmail($data);
        $this->validatePassword($data);
        $this->validateBirthday($data);
        if (!empty($data['phone'])) {
            $this->validatePhone($data);

        }
        return $this->errors;
    }

    public function validateLogin (array $data) {
        $this->validateEmail($data);
        $this->validatePassword($data);
        return $this->errors;
    }

    public function validateProfile (array $data) {
        $this->validatePhone($data);
        return $this->errors;
    }

    private function validateEmail ($data) {
        $email = filter_var($data['email'],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (empty($email)) {
            $this->errors['email'] = 'The email cannot be empty';
        }
        else {
            if (!filter_var($email,FILTER_VALIDATE_EMAIL)) {
                $this->errors['email'] = 'Email is not valid';
            }
            else {
                $email_aux = explode ('@', $email);
                $domain = array_pop($email_aux);
                if ($domain != 'salle.url.edu') {
                    $this->errors['email'] = 'We only accept emails with domain salle.url.edu';
                }
            }
        }
    }

    private function validatePassword ($data) {
        $password = filter_var($data['password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (empty($password)) {
            $this->errors['password'] = 'The password cannot be empty';
        }
        else {
            if (!preg_match(self::REGEX_PASSWORD,$password)) {
                $this->errors['password'] = 'The password must contain both letters and numbers with more than 5 characters.';
            }
        }
    }

    private function validateBirthday ($data) {
        $bday = $data['birthday'];
        if (empty($bday)) {
            $this->errors['birthday'] = 'The birthday cannot be empty.';
        }
        else {
            $bday_aux = explode('-', $bday);
            $year = $bday_aux[0];
            if ((date('Y')-$year) <= 18) {
                $this->errors['birthday'] = 'Sorry, you are underage.';
            }
        }
    }

    private function validatePhone($data) {
        if (!preg_match(self::REGEX_PHONE, $data['phone'])) {
            $this->errors['phone'] = 'Phone is not valid.';
        }
    }

}
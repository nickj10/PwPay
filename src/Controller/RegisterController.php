<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use SallePW\SlimApp\model\User;
use SallePW\SlimApp\model\Mailer;


final class RegisterController
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showRegisterFormAction(Request $request, Response $response): Response
    {
        return $this->container->get('view')->render($response, 'register.twig', []);
    }
    public function registerAction(Request $request, Response $response): Response
    {        
        $data = $request->getParsedBody();
        $errors = [];
        $errors = $this->validate($data);
        try {
            //Check if user data already exists
            if ($this->container->get('user_repository')->isEmailTaken($data['email'])) {
                $errors['emailTaken'] = 'This email is already taken';
            }
            if (count($errors) == 0) {
                //$birthdate = date_create($data['birthday']);
                $email = filter_var($data['email'],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $password = filter_var($data['password'],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $user = new User ($email, $password, $data['birthday'], intval($data['phone']));
                $this->container->get('user_repository')->save($user);
                //We have to retreive its id for the activation link
                $user = $this->container->get('user_repository')->getUserByEmail($email);
                $uuid = $this->container->get('user_repository')->generateUuid($user['user_id']);
                $emailSent = $this->container->get('mailer')->sendEmail($user['user_id'], $user['email'], $uuid);
                if ($emailSent) {
                    $errors['activation_link'] = "Great! Don't forget to check your email to validate your account.";
                }
                else {
                    $errors['activation_link'] = "Something wrong happened. Please try again.";
                }
            }
            
            return $this->container->get('view')->render (
                $response,
                'register.twig',
                [
                    'form_errors' => $errors,
                    'data' => $data
                ]
            );
        } catch (Exception $e) {
            $response->getBody()->write('Unexpected error: ' . $e->getMessage());
            return $response->withStatus(500);
        }
        return $response->withStatus(201);
    }
    private function validate (array $data): array 
    {
        $errors = [];
        $errors = $this->validateEmail($errors, $data);
        $errors = $this->validatePassword($errors, $data);
        $errors = $this->validateBirthday($errors, $data);
        if (!empty($data['phone'])) {
            $errors = $this->validatePhone($errors, $data);

        }
        return $errors;
    
    }

    private function validateEmail ($errors, $data) : array {
        $email = filter_var($data['email'],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (empty($email)) {
            $errors['email'] = 'The email cannot be empty';
        }
        else {
            if (!filter_var($email,FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Email is not valid';
            }
            else {
                $email_aux = explode ('@', $email);
                $domain = array_pop($email_aux);
                if ($domain != 'salle.url.edu') {
                    $errors['email'] = 'We only accept emails with domain salle.url.edu';
                }
            }
        }
        return $errors;
    }

    private function validatePassword ($errors, $data) : array {
        $password = filter_var($data['password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (empty($password)) {
            $errors['password'] = 'The password cannot be empty';
        }
        else {
            if (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/",$password)) {
                $errors['password'] = 'The password must contain both letters and numbers with more than 5 characters.';
            }
        }
        return $errors;
    }

    private function validateBirthday ($errors, $data) : array {
        $bday = $data['birthday'];
        if (empty($bday)) {
            $errors['birthday'] = 'The birthday cannot be empty.';
        }
        else {
            $bday_aux = explode('-', $bday);
            $year = $bday_aux[0];
            if ((date('Y')-$year) <= 18) {
                $errors['birthday'] = 'Sorry, you are underage.';
            }
        }
        return $errors;
    }

    private function validatePhone ($errors, $data) : array {
        $regex_mobile = "/^6[0-9]{8}$/";
        if (!preg_match($regex_mobile, $data['phone'])) {
            $errors['phone'] = 'Phone is not valid.';
        }
        return $errors;
    
    }
}
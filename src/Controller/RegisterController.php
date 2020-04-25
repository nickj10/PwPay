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
            if (count($errors) == 0) {
                $birthdate = date_create($data['birthday']);
                $user = new User ($data['email'], $data['password'], $birthdate, intval($data['phone']));
                $this->container->get('user_repository')->save($user);
                //The user should be created
            }
            else {
                $mail = new Mailer();
                $mail->sendEmail();
                return $this->container->get('view')->render (
                    $response,
                    'register.twig',
                    [
                        'form_errors' => $errors,
                        'data' => $data
                    ]
                    );
            }
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
        $email = $data['email'];
        if (empty($email)) {
            $errors['email'] = 'The email cannot be empty';
        }
        else {
            $email_aux = explode ('@', $email);
            $domain = array_pop($email_aux);
            if ($domain != 'salle.url.edu') {
                $errors['email'] = 'Email is not valid';
            }
        }
        return $errors;
    }

    private function validatePassword ($errors, $data) : array {
        $password = $data['password'];
        if (empty($password)) {
            $errors['password'] = 'The password cannot be empty';
        }
        else {
            if (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{5,}$/",$password)) {
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
                $errors['birthday'] = 'Sorry, you are underage';
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
<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class LoginController
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showLoginFormAction(Request $request, Response $response): Response
    {
        return $this->container->get('view')->render(
            $response,
            'login.twig',
            [
                "id" => $request->getAttribute('id')
            ]
        );
    }

    public function loginAction(Request $request, Response $response): Response
    {
        // This method decodes the received json
        $data = $request->getParsedBody();
        $errors = [];
        $errors = $this->validate($data);
        try {
            if (count($errors) == 0) {
                $email = filter_var($data['email'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $password = filter_var($data['password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                if ($this->container->get('user_repository')->isEmailTaken($email)) {
                    $userInfo = $this->container->get('user_repository')->getUserByEmail($email);
                    if ($userInfo['password'] == md5($password) && $userInfo['status'] == 'active') {
                        $_SESSION['user_id'] = $userInfo['user_id'];
                        return $response->withHeader('Location', '/account/summary')->withStatus(302);
                    } else {
                        if ($userInfo['password'] != md5($password)) {
                            $errors['passwordIncorrect'] = 'Password incorrect.';
                        } else {
                            if ($userInfo['status'] == 'inactive') {
                                $errors['not_active'] = 'Check your mail to activate your account.';
                            }
                        }
                    }
                } else {
                    $errors['nonexistingUser'] = 'This email is not associated to any user.';
                }
            }
            return $this->container->get('view')->render(
                $response,
                'login.twig',
                [
                    'errors' => $errors,
                    'data' => $data
                ]
            );
        } catch (Exception $e) {
            $response->getBody()->write('Unexpected error: ' . $e->getMessage());
            return $response->withStatus(500);
        }

        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    private function validate(array $data): array
    {
        $errors = [];
        $errors = $this->validateEmail($errors, $data);
        $errors = $this->validatePassword($errors, $data);
        return $errors;
    }

    private function validateEmail($errors, $data): array
    {
        $email = filter_var($data['email'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (empty($email)) {
            $errors['email'] = 'The email cannot be empty';
        } else {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Email is not valid';
            } else {
                $email_aux = explode('@', $email);
                $domain = array_pop($email_aux);
                if ($domain != 'salle.url.edu') {
                    $errors['email'] = 'We only accept emails with domain salle.url.edu';
                }
            }
        }
        return $errors;
    }

    private function validatePassword($errors, $data): array
    {
        $password = filter_var($data['password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (empty($password)) {
            $errors['password'] = 'The password cannot be empty';
        } else {
            if (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/", $password)) {
                $errors['password'] = 'The password must contain both letters and numbers with more than 5 characters.';
            }
        }
        return $errors;
    }

    public function logoutAction(Request $request, Response $response): Response
    {
        unset($_SESSION['user_id']);
        return $this->container->get('view')->render(
            $response,
            'logout.twig',
            []
        );
    }
}

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
                $response->getBody()->write(json_encode([]));
                // If there are no errors, we check the user 
                if ($this->container->get('user_repository')->isEmailTaken($data['email'])) {
                    $userInfo = $this->container->get('user_repository')->getUserByEmail($data['email']);
                    if ($userInfo['password'] == $data['password']) {
                        return $response->withHeader('Location', '/account/summary')->withStatus(302);
                    } else {
                        $errors['passwordIncorrect'] = 'Password incorrect.';
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
        $email = $data['email'];

        if (empty($data['email'])) {
            $errors['email'] = 'The email cannot be empty.';
        } else {
            $email_aux = explode('@', $email);
            $domain = array_pop($email_aux);
            if ($domain != 'salle.url.edu') {
                $errors['email'] = 'Email is not valid.';
            }
        }

        return $errors;
    }

    private function validatePassword($errors, $data): array
    {
        $password = $data['password'];
        if (empty($password)) {
            $errors['password'] = 'The password cannot be empty.';
        } else {
            if (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/", $password)) {
                $errors['password'] = 'The password must contain both letters and numbers with more than 5 characters.';
            }
        }
        return $errors;
    }

    public function logoutAction(Request $request, Response $response): Response
    {
        return $this->container->get('view')->render(
            $response,
            'hello.twig',
            []
        );
    }
}

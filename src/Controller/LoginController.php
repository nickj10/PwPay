<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class LoginController
{
    private ContainerInterface $container;
    private $errors = array();
    private const WRONG_PASSWORD_MESSAGE = "Password incorrect.";
    private const NOT_ACTIVE_MESSAGE = "Check you email and activate your account!";
    private const WRONG_CREDENTIALS_MESSAGE = "This email is not associated to any user";

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
        //$errors = $this->validate($data);
        $this->errors = $this->container->get('validator')->validateLogin($data);
        try {
            if (count($this->errors) == 0) {
                $email = filter_var($data['email'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $password = filter_var($data['password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                if ($this->container->get('user_repository')->isEmailTaken($email)) {
                    $userInfo = $this->container->get('user_repository')->getUserByEmail($email);
                    if ($userInfo['password'] == md5($password) && $userInfo['status'] == 'active') {
                        $_SESSION['user_id'] = $userInfo['user_id'];
                        return $response->withHeader('Location', '/account/summary')->withStatus(302);
                    } else {
                        if ($userInfo['password'] != md5($password)) {
                            $this->errors['passwordIncorrect'] = self::WRONG_PASSWORD_MESSAGE;
                        } else {
                            if ($userInfo['status'] == 'inactive') {
                                $this->errors['not_active'] = self::NOT_ACTIVE_MESSAGE;
                            }
                        }
                    }
                } else {
                    $this->errors['nonexistingUser'] = self::WRONG_CREDENTIALS_MESSAGE;
                }
            }
            return $this->container->get('view')->render(
                $response,
                'login.twig',
                [
                    'errors' => $this->errors,
                    'data' => $data
                ]
            );
        } catch (Exception $e) {
            $response->getBody()->write('Unexpected error: ' . $e->getMessage());
            return $response->withStatus(500);
        }

        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }
}

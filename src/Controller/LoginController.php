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
            ]);
    }

    public function loginAction(Request $request, Response $response): Response
    {

        $id = $request->getAttribute('id');
        echo $id;
        return $response->withStatus(201);
    }

    private function validate(array $data): array
    {
        $errors = [];

        if (empty($data['username'])) {
            $errors['username'] = 'The username cannot be empty.';
        }

        if (empty($data['password']) || strlen($data['password']) < 6) {
            $errors['password'] = 'The password must contain at least 6 characters.';
        }

        return $errors;
    }
}
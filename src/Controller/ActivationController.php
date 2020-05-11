<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class ActivationController
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showLoginFormAction(Request $request, Response $response, $args): Response
    {
        $token = $_GET['token'];
        $valid = $this->container->get('user_repository')->isTokenValid($token);
        
        if ($valid) {
            return $response->withHeader('Location', '/sign-in')->withStatus(302);
        }
        else {
            return $this->container->get('view')->render($response, 'pageNotFound.twig',[]);
        }
    }
}
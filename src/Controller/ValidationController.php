<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class ValidationController
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showLoginFormAction(Request $request, Response $response, $args): Response
    {
        //If the id is still inactive = change status
        //If it's already active, show home page with flash message.
        $id = $request->getAttribute('id');
        if ($this->container->get('user_repository')->getUserById($id)) {
            return $response->withHeader('Location', '/sign-in')->withStatus(302);
        }
        else {
            return $this->container->get('view')->render($response, 'pageNotFound.twig',[]);
        }
    }
}
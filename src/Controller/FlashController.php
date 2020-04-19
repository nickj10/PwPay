<?php

namespace SallePW\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class FlashController
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function addMessage(Request $request, Response $response): Response
    {
        $this->container->get('flash')->addMessage(
            'notifications',
            'Flash messages in action!'
        );
        $this->container->get('flash')->addMessage(
            'notifications',
            'Second flash messages in action!'
        );


        return $response->withHeader('Location', '/')->withStatus(302);
    }
}
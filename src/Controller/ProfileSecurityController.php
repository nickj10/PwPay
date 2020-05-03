<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class ProfileSecurityController
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showProfileSecurity(Request $request, Response $response): Response
    {
        if (empty($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/sign-in')->withStatus(403);
        }
        return $this->container->get('view')->render(
            $response,
            'profile_security.twig',
            [
                'session' => $_SESSION['user_id']
            ]
        );
    }

    public function profileSecurityAction (Request $request, Response $response): Response 
    {
        return $this->container->get('view')->render($response, 'profile_security.twig', []);
    }
}

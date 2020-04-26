<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class AccountController
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showDashboard(Request $request, Response $response): Response
    {
        //If user is active and has logged in
        $id = $request->getAttribute('id');
        if ($this->container->get('user_repository')->getUserById($id)) {
            return $response->withHeader('Location', '/account/summary')->withStatus(302);
        }
        else {
            $warning = "Link is not valid anymore.";
            return $this->container->get('view')->render(
                $response, 
                'hello.twig', 
                [
                    'warning' => $warning
                ]);

        }
    }
}
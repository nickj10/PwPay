<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class TransactionsController
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showAssociateAccount(Request $request, Response $response): Response
    {
        if (empty($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/sign-in')->withStatus(403);
        }
        return $this->container->get('view')->render(
            $response,
            'associateAccount.twig',
            [
                'session' => $_SESSION['user_id']
            ]
        );
    }

    public function associateAccountAction(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        //$errors = $this->validate($data);
        $this->errors = $this->container->get('validator')->validateLogin($data);
    }
}

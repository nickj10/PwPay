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
        if (empty($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/sign-in')->withStatus(403);
        }
        $user = $this->container->get('user_repository')->getUserInformationById($_SESSION['user_id']);
        $transactions = $this->container->get('user_repository')->getAccountTransactions($_SESSION['user_id']);
        return $this->container->get('view')->render(
            $response,
            'dashboard.twig',
            [
                'transactions' => $transactions,
                'session' => $_SESSION['user_id'],
                'user' => $user
            ]
        );
    }
}

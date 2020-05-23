<?php

namespace SallePW\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class HomeController
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showHomePage(Request $request, Response $response): Response
    {
        $messages = $this->container->get('flash')->getMessages();
        //If there's something in the notifications envialo sino nada
        $notifications = $messages['notifications'] ?? [];
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['user_id'] = [];
            return $this->container->get('view')->render(
                $response,
                'home.twig',
                [
                    'session' => $_SESSION['user_id'],
                    'notifications' => $notifications
                ]
            );
        }
        else {
            if (!empty($_SESSION['user_id'])) {
                $user = $this->container->get('user_repository')->getUserInformationById($_SESSION['user_id']);
                return $this->container->get('view')->render(
                    $response,
                    'home.twig',
                    [
                        'session' => $_SESSION['user_id'],
                        'notifications' => $notifications
                    ]
                );
            }
        }
        return $this->container->get('view')->render(
            $response,
            'home.twig',
            [
                'session' => $_SESSION['user_id'],
                'notifications' => $notifications
            ]
        );
    }
}

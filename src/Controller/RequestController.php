<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class RequestController
{
    private ContainerInterface $container;
    private const REQUESTED = 'REQUESTED';
    private const NO_EMAIL_DDBB = 'This email is not in the ddbb';
    private const INACTIVE_USER = 'The user from whom you want to request money is inactive';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showRequest(Request $request, Response $response): Response
    {
        if (empty($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/sign-in')->withStatus(403);
        }
        return $this->container->get('view')->render($response, 'request.twig', []);
    }

    public function requestAction(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $userId = $_SESSION['user_id'];
        $errors = [];
        $errors = $this->container->get('validator')->validateMoneyRequest($data);
        try {
            //Check if user data already exists
            if (!($this->container->get('user_repository')->isEmailTaken($data['email']))) {
                $errors['nonExistingEmail'] = self::NO_EMAIL_DDBB;
            }
            // Check if user is active
            if (!($this->container->get('user_repository')->isUserActive($data['email']))) {
                $errors['emailInactive'] = self::INACTIVE_USER;
            }
            if (count($errors) == 0) {
                $email = filter_var($data['email'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $amount = filter_var($data['amount'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                
                // Get user dest id
                $user = $this->container->get('user_repository')->isUserActive($data['email']);
                $destId = $user['user_id'];

                // Create the request for money
                $this->container->get('user_repository')->createRequest($userId,$destId,$amount,self::REQUESTED);
                return $response->withHeader('Location', '/account/summary')->withStatus(302);
            }

            return $this->container->get('view')->render(
                $response,
                'request.twig',
                [
                    'form_errors' => $errors,
                    'data' => $data
                ]
            );
        } catch (Exception $e) {
            $response->getBody()->write('Unexpected error: ' . $e->getMessage());
            return $response->withStatus(500);
        }
        return $response->withStatus(201);
    }
}

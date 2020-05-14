<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class RequestController
{
    private ContainerInterface $container;

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
        $errors = [];
        $errors = $this->container->get('validator')->validateMoneyRequest($data);
        try {
            //Check if user data already exists
            if (!($this->container->get('user_repository')->isEmailTaken($data['email']))) {
                $errors['emailTaken'] = 'This email is not in the ddbb';
            }
            if (count($errors) == 0) {
                $email = filter_var($data['email'],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $amount = filter_var($data['amount'],FILTER_SANITIZE_FULL_SPECIAL_CHARS);

                //We have to retreive its id for the activation link
                // $user = $this->container->get('user_repository')->getUserByEmail($email);
                // $uuid = $this->container->get('user_repository')->generateUuid($user['user_id']);
                // $emailSent = $this->container->get('mailer')->sendEmail($user['user_id'], $user['email'], $uuid);
                // if ($emailSent) {
                //     $errors['activation_link'] = "Great! Don't forget to check your email to validate your account.";
                // }
                // else {
                //     $errors['activation_link'] = "Something wrong happened. Please try again.";
                // }
            }
            
            return $this->container->get('view')->render (
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

<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class RequestController
{
    private ContainerInterface $container;
    private const PAID = 'PAID';
    private const REQUESTED = 'PENDING';
    private const NO_EMAIL_DDBB = 'This email is not in the ddbb';
    private const SAME_EMAIL = 'You cannot request money from yourself';
    private const INACTIVE_USER = 'The user from whom you want to request money is inactive';
    private const INCORRECT_USER = 'You are not the one to whom the request is assigned to';
    private const NOT_ENOUGH_MONEY = 'You do not have enough balance in your wallet to send %s€';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showRequestsPending(Request $request, Response $response): Response
    {
        if (empty($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/sign-in')->withStatus(403);
        }
        $messages = $this->container->get('flash')->getMessages();
        $notifications = $messages['notifications'] ?? [];

        $incoming_requests = $this->container->get('user_repository')->getPendingIncomingRequests($_SESSION['user_id']);
        $outgoing_requests = $this->container->get('user_repository')->getPendingOutgoingRequests($_SESSION['user_id']);
        return $this->container->get('view')->render($response, 'pending_requests.twig', [
            'session' => $_SESSION['user_id'],
            'notifications' => $notifications,
            'incoming' => $incoming_requests,
            'outgoing' => $outgoing_requests
        ]);
    }

    public function showRequest(Request $request, Response $response): Response
    {
        if (empty($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/sign-in')->withStatus(403);
        }
        return $this->container->get('view')->render($response, 'request.twig', [
            'session' => $_SESSION['user_id']
        ]);
    }

    public function acceptRequest(Request $request, Response $response): Response
    {
        if (empty($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/sign-in')->withStatus(403);
        }
        // Check for errors
        $errors = [];
        $userId = $_SESSION['user_id'];
        $requestId = $request->getAttribute('id');

        // Get information from the ddbb
        $moneyRequest = $this->container->get('user_repository')->getRequestById($requestId);
        $userInfo = $this->container->get('user_repository')->getUserInformationById($userId);
        $amount = floatval($moneyRequest['amount']);
        $destId = $moneyRequest['org_user_id'];

        // Check if the user is the one to whom the request is assigned to
        if ($moneyRequest['dest_user_id'] != $userId) {
            $errors['incorrectUser'] = self::INCORRECT_USER;
            $this->container->get('flash')->addMessage('notifications', self::INCORRECT_USER);
        } else {
            // Check if the user has enough money to fulfil the request
            if (floatval($userInfo['balance']) <= $amount) {
                $errors['insuficientBalance'] = self::NOT_ENOUGH_MONEY;
                $this->container->get('flash')->addMessage('notifications', sprintf(self::NOT_ENOUGH_MONEY, $amount));
            }
        }

        // Return to pending requests page if there are errors
        if (count($errors) > 0) {
            return $response->withHeader('Location', '/account/money/requests/pending')->withStatus(302);
        } else {
            $this->container->get('user_repository')->updateRequest($requestId, self::PAID);
            $this->container->get('user_repository')->updateAccountBalance($destId, $amount, "add");
            //update user new balance & create transaction
            $this->container->get('user_repository')->updateAccountBalance($userId, $amount, "sub");
            $this->container->get('user_repository')->createTransaction($userId, 'Send Money', $amount, 'send');
            return $response->withHeader('Location', '/account/money/requests/pending')->withStatus(200);
        }
    }

    public function requestAction(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $userId = $_SESSION['user_id'];
        $errors = [];
        $errors = $this->container->get('validator')->validateMoneyRequest($data);
        try {
            $email = filter_var($data['email'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $amount = filter_var($data['amount'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            //Check if user data already exists
            if (!($this->container->get('user_repository')->isEmailTaken($data['email']))) {
                $errors['nonExistingEmail'] = self::NO_EMAIL_DDBB;
            } else {
                // Get user dest id
                $user = $this->container->get('user_repository')->getUserByEmail($email);
                $destId = $user['user_id'];
                if ($destId == $userId) {
                    $errors['sameEmail'] = self::SAME_EMAIL;
                } else {
                    // Check if user is active
                    if (!($this->container->get('user_repository')->isUserActive($data['email']))) {
                        $errors['emailInactive'] = self::INACTIVE_USER;
                    }
                }
            }
            if (count($errors) == 0) {
                // Get user dest id
                $user = $this->container->get('user_repository')->getUserByEmail($email);
                $destId = $user['user_id'];

                // Create the request for money
                $this->container->get('user_repository')->createRequest($userId, $destId, $amount, self::REQUESTED);
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

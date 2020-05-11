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
        $this->errors = $this->container->get('validator')->validateBankAssociation($data);
        try {
            if (count($this->errors) == 0) {
                $owner = filter_var($data['owner'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $iban = filter_var($data['iban'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $userId = $_SESSION['user_id'];
                $this->container->get('user_repository')->saveAccount($userId, $owner, $iban);
                $userAccount = $this->container->get('user_repository')->getBankAccountInformation($_SESSION['user_id']);
                return $this->container->get('view')->render(
                    $response,
                    'loadMoney.twig',
                    [
                        'account' => $userAccount,
                        'session' => $_SESSION['user_id']
                    ]
                );
            }
            return $this->container->get('view')->render(
                $response,
                'associateAccount.twig',
                [
                    'errors' => $this->errors,
                    'data' => $data
                ]
            );
        } catch (Exception $e) {
            $response->getBody()->write('Unexpected error: ' . $e->getMessage());
            return $response->withStatus(500);
        }

        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function showLoadMoney(Request $request, Response $response): Response
    {
        if (empty($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/sign-in')->withStatus(403);
        } else {
            // If user does not have an account, redirect to Associate Bank Account
            if (!($this->container->get('user_repository')->userHasAssociatedAccount($_SESSION['user_id']))) {
                return $this->container->get('view')->render(
                    $response,
                    'associateAccount.twig',
                    [
                        'session' => $_SESSION['user_id']
                    ]
                );
            }
        }
        $userAccount = $this->container->get('user_repository')->getBankAccountInformation($_SESSION['user_id']);
        // Show Load Money page
        return $this->container->get('view')->render(
            $response,
            'loadMoney.twig',
            [
                'account' => $userAccount,
                'session' => $_SESSION['user_id']
            ]
        );
    }

    public function loadMoneyAction(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        try {
            if (count($this->errors) == 0) {
                $amount = filter_var($data['amount']);
                $userId = $_SESSION['user_id'];
                $this->container->get('user_repository')->updateAccountBalance($userId, $amount);
                $userAccount = $this->container->get('user_repository')->getBankAccountInformation($_SESSION['user_id']);

                return $this->container->get('view')->render(
                    $response,
                    'loadMoney.twig',
                    [
                        'account' => $userAccount,
                        'session' => $userId
                    ]
                );
            }

            // Return errors for validations
            return $this->container->get('view')->render(
                $response,
                'loadMoney.twig',
                [
                    'errors' => $this->errors,
                    'data' => $data
                ]
            );
        } catch (Exception $e) {
            $response->getBody()->write('Unexpected error: ' . $e->getMessage());
            return $response->withStatus(500);
        }

        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }
}

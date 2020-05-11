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
        $errors = $this->container->get('validator')->validateBankAssociation($data);
        try {
            if (count($errors) == 0) {
                $owner = filter_var($data['owner'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $iban = filter_var($data['iban'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $userId = $_SESSION['user_id'];
                $this->container->get('user_repository')->saveAccount($userId, $owner, $iban);
                $user = $this->container->get('user_repository')->getBankAccountInformation($_SESSION['user_id']);
                $userAccount['owner_name'] = $user->owner_name();
                $newIban = substr($user->iban(),0,6);
                $userAccount['iban'] = $newIban;
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
                    'session' => $_SESSION['user_id'],
                    'errors' => $errors,
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
        $user = $this->container->get('user_repository')->getBankAccountInformation($_SESSION['user_id']);
        $userAccount['owner_name'] = $user->owner_name();
        $newIban = substr($user->iban(),0,6);
        $userAccount['iban'] = $newIban;
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
        if (empty($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/sign-in')->withStatus(403);
        }
        else {
            $data = $request->getParsedBody();
            $errors = $this->container->get('validator')->validateAmount($data);
            $user = $this->container->get('user_repository')->getBankAccountInformation($_SESSION['user_id']);
            $userAccount['owner_name'] = $user->owner_name();
            $newIban = substr($user->iban(),0,6);
            $userAccount['iban'] = $newIban;
            if (count($errors) == 0) {
                try {
                    $userId = $_SESSION['user_id'];
                    $amount = $data['amount'];
                    //Create transaction
                    $this->container->get('user_repository')->updateAccountBalance($userId, $amount);
                    $this->container->get('user_repository')->createTransaction(intval($userId), $user->account_id(), 'Load Money', intval($amount), 'load');
                    $info['success'] = "Money has been loaded to your wallet.";
                    return $this->container->get('view')->render(
                        $response,
                        'loadMoney.twig',
                        [
                            'account' => $userAccount,
                            'info' => $info['success'],
                            'session' => $userId
                        ]
                    );
                } catch (Exception $e) {
                    $response->getBody()->write('Unexpected error: ' . $e->getMessage());
                    return $response->withStatus(500);
                }
            }
             // Return errors for validations
             return $this->container->get('view')->render(
                $response,
                'loadMoney.twig',
                [
                    'errors' => $errors,
                    'account' =>$userAccount,
                    'session' => $_SESSION['user_id'],
                    'data' => $data
                ]
            );
        }       
    }
}

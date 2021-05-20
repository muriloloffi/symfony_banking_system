<?php

namespace App\Controller;

use App\Business\AccountBusiness;
use App\Business\TransactionBusiness;
use App\Entity\Account;
use App\Entity\Transaction;
use App\Form\TransactionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TransactionController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/transacion/statement/{account_id}", name="statement")
     */
    public function statement(int $account_id, Request $request)
    {
        $accountStatement = $this->entityManager->getRepository(Transaction::class)->findBy(['account' => $account_id]);
        $account = $this->entityManager->getRepository(Account::class)->findOneBy(['id' => $account_id]);

        return $this->render('transaction/index.html.twig', [
            'accountStatement' => $accountStatement,
            'balance' => $account->getBalance(),
            'account' => $account
        ]);
    }

    /**
    * @Route("/transaction/create", name="create_transaction")
    */
    public function transaction(Request $request, TransactionBusiness $transactionBusiness, AccountBusiness $accountBusiness): Response
    {
        $transaction = new Transaction();

        $form = $this->createForm(TransactionType::class, $transaction);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            
            if (!$transactionBusiness->isValidAmount($transaction->getValue())){
                $this->get('session')->getFlashbag()
                    ->set('warning', 'Montante inválido!');
                return $this->redirectToRoute('create_transaction');
            }

            if ($transaction->getAction() === "Sacar" && !$transactionBusiness->hasBalance($transaction->getAccount(), $transaction->getValue())){
                $this->get('session')->getFlashbag()
                    ->set('warning', 'Saldo insuficiente!');
                return $this->redirectToRoute('create_transaction');
            }

            $transaction->getAction() === "Depositar"
                ? $accountBusiness->balanceDeposit($transaction->getAccount(), $transaction->getValue())
                : $accountBusiness->balanceWithdraw($transaction->getAccount(), $transaction->getValue());

            $transaction->setCreatedAt(new \DateTime());
            $this->entityManager->persist($transaction);
            $this->entityManager->flush();

            $this->get('session')->getFlashbag()->set('success', 'Transação realizada com sucesso!');

            return $this->redirectToRoute('statement', [
                'account_id' => $transaction->getAccount()->getId()
            ]);
        }

        return $this->render('transaction/create.html.twig', [
            'form' => $form->createView()
        ]);
    }
}

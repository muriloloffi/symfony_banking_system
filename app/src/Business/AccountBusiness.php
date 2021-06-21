<?php

namespace App\Business;

use App\Entity\Account;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;

class AccountBusiness
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function isValidAccNumber(Account $account): bool
    {
        $number = $account->getNumber();
        $number = preg_replace('/[^0-9]/is', '', $number);

        if (strlen($number) > 10)
            return false;

        if (preg_match('/(\d)\1{10}/', $number))
            return false;

        return true;
    }

    public function isUnique(Account $account): bool
    {
        $receivedAccount = $this->entityManager
            ->getRepository(Account::class)
            ->findBy(['number' => $account->getNumber()]);

        if ($receivedAccount) {
            return false;
        }
        return true;
    }

    public function hasTransaction(Account $account): bool
    {
        $isTransaction = $this->entityManager
            ->getRepository(Transaction::class)
            ->findOneBy(['account' => $account]);

        if ($isTransaction)
            return true;

        return false;
    }

    public function balanceWithdraw(Account $account, float $amount): void
    {
        $account->setBalance($account->getBalance() - $amount);

        $this->entityManager->persist($account);
        $this->entityManager->flush();
    }

    public function balanceDeposit(Account $account, float $amount): void
    {
        $account->setBalance($account->getBalance() + $amount);

        $this->entityManager->persist($account);
        $this->entityManager->flush();
    }
}

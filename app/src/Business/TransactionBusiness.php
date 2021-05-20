<?php

namespace App\Business;

use App\Entity\Account;

class TransactionBusiness
{

    public function hasBalance(Account $account, float $amount): bool
    {
        if ($account->getBalance() > 0 && $account->getBalance() >= $amount)
            return true;
        
        return false;
    }
    
    public function isValidAmount(float $amount): bool
    {
        if ($amount >= 0.01)
            return true;

        return false;
    }

}
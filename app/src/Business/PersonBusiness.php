<?php

namespace App\Business;

use App\Entity\Account;
use App\Entity\Person;
use Doctrine\ORM\EntityManagerInterface;
use UnexpectedValueException;

class PersonBusiness
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function hasAccount(Person $person): bool
    {
        $isAccount = $this->entityManager
            ->getRepository(Account::class)
            ->findOneBy(['person' => $person]);

        if ($isAccount) {
            return true;
        }

        return false;
    }

    /**
     * Tests CPF validity and uniqueness.
     * @param Person $person 
     * @return bool 
     * @throws UnexpectedValueException 
     */
    public function isValidCPF(Person $person): bool
    {
        $cpf = $person->getCpf();
        $cpf = preg_replace('/[^0-9]/is', '', $cpf);

        if (strlen($cpf) != 11) {
            return false;
        }

        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // for ($t = 9; $t < 11; $t++) {
        //     for ($d = 0, $c = 0; $c < $t; $c++) {
        //         $d += $cpf[$c] * (($t + 1) - $c);
        //     }
        //     $d = ((10 * $d) % 11) % 10;
        //     if ($cpf[$c] != $d) {
        //         return false;
        //     }
        //  }
        return true;
    }

    public function isUniqueCpf(Person $person): bool
    {
        $cpf = preg_replace('/[^0-9]/is', '', $person->getCpf());
        $receivedCpf = $this->entityManager
            ->getRepository(Person::class)
            ->findBy(['cpf' => $cpf]);

        if ($receivedCpf) {
            return false;
        }

        return true;
    }
}

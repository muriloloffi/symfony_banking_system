<?php

namespace App\Entity;

use App\Repository\PersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=PersonRepository::class)
 * @UniqueEntity("cpf",
 *      message="Este CPF já pertence a outro usuário.")
 * @ORM\HasLifecycleCallbacks
 */
class Person
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=200)
     * @Assert\NotBlank(message="Campo nome não pode ser vazio!")
     * @Assert\Regex(
     *      pattern="/.[a-z]+$/i",
     *      message="Apenas letras e espaços são permitidos no campo nome."
     * )
     */
    private $name;

    /**
     * @ORM\Column(name="cpf", type="string", length=11, unique=true)
     * @Assert\NotBlank(message="Campo cpf não pode ser vazio!")
     */
    private $cpf;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Campo endereço não pode ser vazio!")
     */
    private $addressOne;

    /**
     * @ORM\OneToMany(targetEntity=Account::class, mappedBy="person")
     */
    private $accounts;

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function normalizeCpf(): void
    {
        $this->setCpf(
            preg_replace('/[^0-9]/is', '', $this->getCpf())
        );
    }

    public function __construct()
    {
        $this->accounts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCpf(): ?string
    {
        return $this->cpf;
    }

    public function setCpf(string $cpf): self
    {
        $this->cpf = $cpf;

        return $this;
    }

    public function getAddressOne(): ?string
    {
        return $this->addressOne;
    }

    public function setAddressOne(string $address): self
    {
        $this->addressOne = $address;

        return $this;
    }

    /**
     * @return Collection|Account[]
     */
    public function getAccounts(): Collection
    {
        return $this->accounts;
    }

    public function addAccount(Account $account): self
    {
        if (!$this->accounts->contains($account)) {
            $this->accounts[] = $account;
            $account->setPerson($this);
        }

        return $this;
    }

    public function removeAccount(Account $account): self
    {
        if ($this->accounts->removeElement($account)) {
            // set the owning side to null (unless already changed)
            if ($account->getPerson() === $this) {
                $account->setPerson(null);
            }
        }

        return $this;
    }

    public function cpfMask($val, $mask = '###.###.###-##')
    {
        $masked = '';
        $k = 0;
        for ($i = 0; $i <= strlen($mask) - 1; $i++) {
            if ($mask[$i] == '#') {
                if (isset($val[$k])) $masked .= $val[$k++];
            } else {
                if (isset($mask[$i])) $masked .= $mask[$i];
            }
        }
        return $masked;
    }
}

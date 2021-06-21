<?php

namespace App\Form;

use App\Entity\Account;
use App\Entity\Person;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Intl\NumberFormatter\NumberFormatter;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('person', EntityType::class, [
                'class' => Person::class,
                'placeholder' => 'Selecione o cliente',
                'label' => "Pessoa:",
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->orderBy('p.name', 'ASC');
                },
                'choice_label' => function ($person) {
                    return $person->getName() . ' - ' . $person->cpfMask($person->getCpf());
                },
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('value', MoneyType::class, [
                'label' => "Valor:",
                'currency' => "BRL",
                'data' => '.0',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('action', ChoiceType::class, [
                'label' => "Depositar / Sacar:",
                'choices' => [
                    'Depositar' => 'Depositar',
                    'Sacar' => 'Sacar'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => "Salvar",
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ]);

        $formModifier = function (FormInterface $form, Person $person = null) {
            $accounts = null === $person ? [] : $person->getAccounts();


            $form->add('account', EntityType::class, [
                'class' => Account::class,
                'placeholder' => 'Selecione um número de conta',
                'label' => 'Conta:',
                'choices' => $accounts,
                'choice_label' => function (Account $account) {
                    return $account->getNumber() . ' - Saldo: R$ ' . $account->getBalance();
                },
                'attr' => [
                    'class' => 'form-control'
                ]
            ]);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $data = $event->getData();

                $formModifier($event->getForm(), $data->getPerson());
            }
        );

        $builder->get('person')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $person = $event->getForm()->getData();

                $formModifier($event->getForm()->getParent(), $person);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}

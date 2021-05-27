<?php

namespace App\Controller;

use App\Business\AccountBusiness;
use App\Entity\Account;
use App\Form\AccountType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class AccountController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/account", name="account")
     */
    public function index(): Response
    {
        $accountRepository = $this->getDoctrine()
            ->getRepository(Account::class);
        $accountList = $accountRepository
            ->findBy(array(), array('person' => 'ASC'));

        return $this->render('account/index.html.twig', [
            'accounts' => $accountList,
        ]);
    }

    /**
     * @Route("/account/create", name="create_account")
     */
    public function create(Request $request, AccountBusiness $accountBusiness, TranslatorInterface $translator): Response
    {
        $account = new Account();

        $form = $this->createForm(AccountType::class, $account);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$accountBusiness->isUnique($account)) {
                $this->get('session')->getFlashbag()
                    ->set('warning', $translator->trans('account.has.unavailable_number'));

                return $this->render('account/update.html.twig', [
                    'account' => $account,
                    'form' => $form->createView()
                ]);
            }

            $this->entityManager->persist($account);
            $this->entityManager->flush();

            $this->get('session')->getFlashbag()
                ->set('success', $translator->trans('account.did.create'));

            return $this->redirectToRoute('account');
        }

        return $this->render('account/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("account/update/{account_id}", name="update_account")
     */
    public function update(Request $request, int $account_id, AccountBusiness $accountBusiness, TranslatorInterface $translator): Response
    {
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository(Account::class)->find($account_id);

        $form = $this->createForm(AccountType::class, $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$accountBusiness->isUnique($account)) {
                $this->get('session')->getFlashbag()
                    ->set('warning', $translator->trans('account.has.unavailable_number'));

                return $this->render('account/update.html.twig', [
                    'account' => $account,
                    'form' => $form->createView()
                ]);
            }

            $em->persist($account);
            $em->flush();

            $this->addFlash('success', $translator->trans('account.did.update'));
            return $this->redirectToRoute("account");
        }

        return $this->render('account/update.html.twig', [
            'account' => $account,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/account/delete/{account_id}", name="delete_account")
     */
    public function delete(int $account_id, AccountBusiness $accountBusiness, TranslatorInterface $translator): Response
    {
        $account = $this->entityManager
            ->getRepository(Account::class)
            ->find($account_id);

        if (!$account) {
            $tipo = "warning";
            $mensagem = $translator->trans("account.is.absent");
        } elseif ($accountBusiness->hasTransaction($account)) {
            $tipo = "warning";
            $mensagem = $translator->trans("account.has.transactions");
        } else {
            $this->entityManager->remove($account);
            $this->entityManager->flush();
            $tipo = "success";
            $mensagem = $translator->trans("account.did.delete");
        }

        $this->get('session')->getFlashbag()->set($tipo, $mensagem);
        return $this->redirectToRoute("account");
    }
}

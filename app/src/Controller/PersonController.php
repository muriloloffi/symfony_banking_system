<?php

namespace App\Controller;

use App\Business\PersonBusiness;
use App\Entity\Person;
use App\Form\PersonType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PersonController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/person", name="persons", methods={"GET"})
     */
    public function index(): Response
    {
        $personRepository = $this->getDoctrine()
            ->getRepository(Person::class);
        $personList = $personRepository
            ->findBy([], ['name' => 'ASC']);

        return $this->render('person/index.html.twig', [
            'persons' => $personList,
        ]);
    }

    /**
     * @Route("/person/register", name="person_registration", methods={"GET", "POST"})
     */
    public function newPerson(Request $request, PersonBusiness $personBusiness, TranslatorInterface $translator): Response
    {
        $person = new Person();

        $form = $this->createForm(PersonType::class, $person);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$personBusiness->validateCPF($person)) {
                $this->get('session')->getFlashbag()
                    ->set('warning', $translator->trans('cpf.is.invalid'));

                return $this->render('person/create.html.twig', [
                    'person' => $person,
                    'form' => $form->createView()
                ]);
            }

            $this->entityManager->persist($person);
            $this->entityManager->flush();

            $this->get('session')->getFlashbag()
                ->set('success', $translator->trans('person.is.registered'));

            return $this->redirectToRoute('persons');
        }

        return $this->render('person/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("person/update/{person_id}", name="update_person")
     */
    public function update(Request $request, int $person_id, PersonBusiness $personBusiness, TranslatorInterface $translator): Response
    {
        $em = $this->getDoctrine()->getManager();
        $person = $em->getRepository(Person::class)->find($person_id);

        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$personBusiness->validateCPF($person)) {
                $this->get('session')->getFlashbag()
                    ->set('warning', $translator->trans('cpf.is.invalid'));
                return $this->render('person/update.html.twig', [
                    'person' => $person,
                    'form' => $form->createView()
                ]);
            }

            $em->persist($person);
            $em->flush();

            $this->addFlash('success', $translator->trans("person.is.updated"));
            return $this->redirectToRoute("persons");
        }

        return $this->render('person/update.html.twig', [
            'person' => $person,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/person/delete/{person_id}", name="delete_person")
     */
    public function delete(int $person_id, PersonBusiness $personBusiness, TranslatorInterface $translator): Response
    {
        $person = $this->entityManager
            ->getRepository(Person::class)
            ->find($person_id);

        if (!$person) {
            $tipo = "warning";
            $mensagem = $translator->trans("person.is.absent");
        } elseif ($personBusiness->hasAccount($person)) {
            $tipo = "warning";
            $mensagem = $translator->trans("person.has.account");
        } else {
            $this->entityManager->remove($person);
            $this->entityManager->flush();
            $tipo = "success";
            $mensagem = $translator->trans("person.is.deleted");
        }

        $this->get('session')->getFlashbag()->set($tipo, $mensagem);
        return $this->redirectToRoute("persons");
    }
}

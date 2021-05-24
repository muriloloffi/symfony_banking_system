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
    public function newPerson(Request $request, PersonBusiness $personBusiness): Response
    {
        $person = new Person();

        $form = $this->createForm(PersonType::class, $person);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$personBusiness->validateCPF($person)) {
                $this->get('session')->getFlashbag()
                    ->set('warning', 'Número de CPF inválido!');

                return $this->render('person/create.html.twig', [
                    'person' => $person,
                    'form' => $form->createView()
                ]);
            }

            $this->entityManager->persist($person);
            $this->entityManager->flush();

            $this->get('session')->getFlashbag()
                ->set('success', 'Pessoa cadastrada com sucesso!');

            return $this->redirectToRoute('persons');
        }

        return $this->render('person/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("person/update/{person_id}", name="update_person")
     */
    public function update(Request $request, int $person_id, PersonBusiness $personBusiness): Response
    {
        $em = $this->getDoctrine()->getManager();
        $person = $em->getRepository(Person::class)->find($person_id);

        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$personBusiness->validateCPF($person)) {
                $this->get('session')->getFlashbag()
                    ->set('warning', 'Número de CPF inválido!');
                return $this->render('person/update.html.twig', [
                    'person' => $person,
                    'form' => $form->createView()
                ]);
            }

            $em->persist($person);
            $em->flush();

            $this->addFlash('success', "Pessoa atualizada!");
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
    public function delete(int $person_id, PersonBusiness $personBusiness): Response
    {
        $person = $this->entityManager
            ->getRepository(Person::class)
            ->find($person_id);

        if (!$person) {
            $tipo = "warning";
            $mensagem = "Pessoa não encontrada.";
        } elseif ($personBusiness->hasAccount($person)) {
            $tipo = "warning";
            $mensagem = "Esta pessoa possui conta e não pode ser excluída.";
        } else {
            $this->entityManager->remove($person);
            $this->entityManager->flush();
            $tipo = "success";
            $mensagem = "Pessoa foi excluída com sucesso!";
        }

        $this->get('session')->getFlashbag()->set($tipo, $mensagem);
        return $this->redirectToRoute("persons");
    }
}

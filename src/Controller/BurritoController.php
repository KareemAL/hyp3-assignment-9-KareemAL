<?php

namespace App\Controller;

use App\Entity\Burrito;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Doctrine\Persistence\ManagerRegistry;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class BurritoController extends AbstractController
{
    #[Route('/burrito', name: 'burrito_index')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $burrito = $doctrine->getRepository(Burrito::class)->findAll();

        return $this->render('burrito/index.html.twig', [
            'controller_name' => 'BurritoController',
            'burrito' => $burrito,
        ]);
    }

    private function buildForm($burrito) {
        return $this->createFormBuilder($burrito)
            ->add('name', TextType::class)
            ->add('address', TextType::class)
            ->add('phoneNumber', TextType::class)
            ->add('email', TextType::class)
            ->add('size', ChoiceType::class, [
                'choices' => [
                    "Small" => "Small",
                    "Medium" => "Medium",
                    "Large" => "Large"
                ],
                "required" => true
            ])
            ->add('ingredients', ChoiceType::class, [
                'choices' => [
                    "Cheese" => "Cheese",
                    "Guacamole" => "Guacamole",
                    "Salsa" => "Salsa",
                    "Coriander" => "Coriander"
                ],
                "expanded" => true,
                "required" => true,
                "multiple" => true
            ])
            ->add('deliveryMethod', ChoiceType::class, [
                'choices' => [
                    "Delivery" => "Delivery",
                    "Takeout" => "Takeout"
                ],
                "expanded" => true,
                "required" => true,
                "multiple" => false
            ])
            ->add('save', SubmitType::class)
            ->getForm();
    }

    #[Route('/new', name: 'burrito_new')]
    public function newBurrito(Request $request, ManagerRegistry $doctrine): Response
    {
        $burrito = new Burrito();
        $form = $this->buildForm($burrito);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $burrito = $form->getData();
            $em = $doctrine->getManager();
            $em->persist($burrito);
            $em->flush();
            return $this->redirectToRoute('burrito_index');
        }
        return $this->render('burrito/form.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/edit/{id}', name: 'burrito_edit')]
    public function editBurrito($id, Request $request, ManagerRegistry $doctrine): Response
    {
        $burrito = $doctrine->getRepository(Burrito::class)->find($id);
        if ($burrito) {
            $form = $this->buildForm($burrito);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $burrito = $form->getData();
                $em = $doctrine->getManager();
                //$em->persist($issue);
                $em->flush();
                return $this->redirectToRoute('burrito_index');
            }
            return $this->render('burrito/form.html.twig', [
                'form' => $form,
            ]);
        }
        return $this->redirectToRoute('burrito_index');
    }


    #[Route('/delete/{id}', name: 'burrito_delete')]
    public function deleteBurrito($id, ManagerRegistry $doctrine): Response
    {
        $burrito = $doctrine->getRepository(Burrito::class)->find($id);
        if ($burrito) {
            $em = $doctrine->getManager();
            $em->remove($burrito);
            $em->flush();
        }
        return $this->redirectToRoute('burrito_index');
    }
}

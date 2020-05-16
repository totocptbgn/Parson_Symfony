<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\DocBlock\Tags\See;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login", methods={"GET", "POST"})
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/signin", name="app_signin",  methods={"GET","POST"})
     */
    public function signin(Request $request, UserPasswordEncoderInterface $encoder, EntityManagerInterface $em)
    {
        $form = $this ->createFormBuilder()
            ->add('username', TextType::class, ['attr' => 
                ['class' => 'form-control ', 'placeholder' => 'Username']
            ])
            ->add('password', PasswordType::class, ['attr' => 
                ['class' => 'form-control ', 'placeholder' => 'Password']
            ])
            ->add('type', ChoiceType::class, [
                'choices'  => [
                    'Student' => 'student',
                    'Teacher' => 'teacher',
                ], 'attr' => [
                    'class' => 'form-control custom-select'
                ]
            ])
            ->add('submit', SubmitType::class)
            ->getForm()
        ;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user = new User();
            $user->setUsername($data['username']);
            $user->setPassword($encoder->encodePassword($user, $data['password']));
            $user->setType($data['type']);
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('app_login');
        }

        return $this->render("security/newuser.html.twig",[
            'form' => $form->createView()
        ]);
    }
    
    /**
     * @Route("/testUsername", name="app_test_username", methods={"POST"})
     */

    public function testUsername(EntityManagerInterface $em, Request $request)
    {   
        $user = $em->getRepository(User::class)->findOneBy(['username' => $request->request->get('username')]);
        if ($user) {
            return $this->json(['AlreadyExist' => true]);
        }
        return $this->json(['AlreadyExist' => false]);
    }

    /**
     * @Route("/home", name="home", methods={"GET"})
     */
    public function index()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        dump($user);
        if ($user->getType() == 'student') {
            return $this->redirectToRoute('home_student');
        }

        if ($user->getType() == 'teacher' || $user->getType() == 'dev') {
            return $this->redirectToRoute('home_teacher');
        }

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'username' => $user->getUsername(),
            'type' => $user->getType()
        ]);
    }
}

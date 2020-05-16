<?php

namespace App\Controller;

use App\Entity\Attempt;
use App\Entity\Course;
use App\Entity\Exercice;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TeacherController extends AbstractController
{

    /**
     * @Route("/teacher/home", name="home_teacher", methods={"GET", "POST"})
     */
    public function index(Request $request, EntityManagerInterface $em)
    {      
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($this->getUser()->getType() == "student") {
            return $this->redirectToRoute('teacher_forbidden');
        }

        $form = $this ->createFormBuilder()
            ->add('title', TextType::class, ['attr' => 
                ['class' => 'form-control', 'placeholder' => 'Title', 'autocomplete'=> 'off', 'value' => '']
            ])
            ->add('submit', SubmitType::class, ['attr' => 
                ['class' => 'btn btn-primary mb-2']
            ])
            ->getForm()
        ;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $course = new Course();
            $course->setTitle($data['title']);
            $course->setTeacher($this->getUser()->getUsername());
            $em->persist($course);
            $em->flush();
        }

        $repository = $this->getDoctrine()->getRepository(Course::class);
        $courses = $repository->findBy(
            ['Teacher' => $this->getUser()->getUsername()]
        );

        $repository = $this->getDoctrine()->getRepository(Attempt::class);
        $stat = [];
        $res = [];
        foreach($courses as $i => $c) {
            $stat[$i] = $repository->findBy(
                ['courseID' => $c->getId()]
            );
        }
        foreach($stat as $i => $s) {
            $tot = 0;
            $succ = 0;
            foreach($s as $attempt) {
                $tot++;
                if ($attempt->getSuccessfull()) {
                    $succ++;
                }
            }
            if ($tot == 0) {
                $res[$i] = -1;
            } else {
                $res[$i] = (int) ($succ / $tot * 100); 
            }
        }

        $repository = $this->getDoctrine()->getRepository(User::class);
        $users = $repository->findBy(
            ['type' => 'student']
        );
        foreach($users as $i => $u) {
            $usernames[$i] = $u->getUsername();
        }
        
        return $this->render('teacher/index.html.twig', [
            'username' => $this->getUser()->getUsername(),
            'form' => $form->createView(),
            'courses' => $courses,
            'res' => $res,
            'usernames' => $usernames
        ]);
    }

    /**
     * @Route("/teacher/course/{id<\d+>}", name="teacher_courses", methods={"GET"})
     */
    public function course(int $id)
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($this->getUser()->getType() == "student") {
            return $this->redirectToRoute('teacher_forbidden');
        }

        $repository = $this->getDoctrine()->getRepository(Course::class);
        $course = $repository->findBy(
            ['Teacher' => $this->getUser()->getUsername(),
             'id' => $id]  
        );

        $no_access = ($course == []);
        if (!$no_access) {
            $c = $course[0];
        } else {
            $c = null;
        }
        $exos = [];
        $res = [];
        if (!$no_access) {
            $repository = $this->getDoctrine()->getRepository(Exercice::class);
            $exos = $repository->findBy(
                ['course_id' => $id]
            );

            $repository = $this->getDoctrine()->getRepository(Attempt::class);
            $stat = [];
            $res = [];
            foreach($exos as $i => $e) {
                $stat[$i] = $repository->findBy(
                    ['exoID' => $e->getId()]
                );
            }
            foreach($stat as $i => $s) {
                $tot = 0;
                $succ = 0;
                foreach($s as $attempt) {
                    $tot++;
                    if ($attempt->getSuccessfull()) {
                        $succ++;
                    }
                }
                if ($tot == 0) {
                    $res[$i] = -1;
                } else {
                    $res[$i] = (int) ($succ / $tot * 100); 
                }
            }
        }
        
        return $this->render('teacher/course.html.twig', [
            'username' => $this->getUser()->getUsername(),
            'id' => $id,
            'no_access' => $no_access,
            'course' => $c,
            'exos' => $exos,
            'res' => $res
        ]);

    }

    /**
     * @Route("/teacher/course/{id<\d+>}/delete", name="teacher_course_delete", methods={"GET"})
     */
    public function course_delete(int $id, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($this->getUser()->getType() == "student") {
            return $this->redirectToRoute('teacher_forbidden');
        }

        $repository = $this->getDoctrine()->getRepository(Course::class);
        $course = $repository->findBy(
            ['Teacher' => $this->getUser()->getUsername(),
             'id' => $id]  
        );

        if ($course == []) {
            return $this->redirectToRoute('teacher_courses', ['id' => $id]); 
        }

        $repository = $this->getDoctrine()->getRepository(Exercice::class);
        $exos = $repository->findBy(
            ['course_id' => $id]
        );

        foreach ($exos as $exo) {
            $em->remove($exo);
        }
        $em->remove($course[0]);
        $em->flush();

        return $this->redirectToRoute('home_teacher'); 
    }

    /**
     * @Route("/teacher/course/{id<\d+>}/new_exo", name="teacher_new_exo", methods={"GET", "POST"})
     */
    public function new_exo(int $id, Request $request, EntityManagerInterface $em)
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($this->getUser()->getType() == "student") {
            return $this->redirectToRoute('teacher_forbidden');
        }

        $form = $this ->createFormBuilder()
            ->add('title', TextType::class, ['attr' => 
                ['class' => 'form-control',
                'placeholder' => 'Title',
                'autocomplete'=> 'off',
                'value' => '',
                'required' => true]
            ])
            ->add('code', TextareaType::class, ['attr' => 
                ['rows' => '10',
                'cols' => '75',
                'maxlength' => '300']
            ])
            ->add('submit', SubmitType::class, ['attr' => 
                ['class' => 'btn btn-success']
            ])
            ->getForm()
        ;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $exo = new Exercice();
            $exo->setCode($data['code']);
            $exo->setTitle($data['title']);
            $exo->setCourseId($id);
            $em->persist($exo);
            $em->flush();
            return $this->redirectToRoute('teacher_courses', ['id' => $id]);
        }

        $repository = $this->getDoctrine()->getRepository(Course::class);
        $course = $repository->findBy(
            ['Teacher' => $this->getUser()->getUsername(),
             'id' => $id]  
        );

        $no_access = ($course == []);
        if (!$no_access) {
            $c = $course[0];
        } else {
            $c = null;
        }

        return $this->render('teacher/newexo.html.twig', [
            'username' => $this->getUser()->getUsername(),
            'id' => $id,
            'no_access' => $no_access,
            'course' => $c,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/teacher/exo/{id<\d+>}/", name="teacher_exo", methods={"GET"})
     */
    public function see_exo(int $id)
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($this->getUser()->getType() == "student") {
            return $this->redirectToRoute('teacher_forbidden');
        }

        $repository = $this->getDoctrine()->getRepository(Exercice::class);
        $exo = $repository->find($id);

        $exists = ($exo != null);

        if ($exists) {
            $repository = $this->getDoctrine()->getRepository(Course::class);
            $course = $repository->find($exo->getCourseId());
            if ($course->getTeacher() != $this->getUser()->getUsername()) {
                $exists = false;
            } 
        } else {
            $course = null;
        }

        return $this->render('teacher/exo.html.twig', [
            'username' => $this->getUser()->getUsername(),
            'id' => $id,
            'exists' => $exists,
            'exo' => $exo,
            'course' => $course
        ]);
    }


    /**
     * @Route("/teacher/exo/{id<\d+>}/delete", name="teacher_exo_delete", methods={"GET"})
     */
    public function exo_delete(int $id, EntityManagerInterface $em)
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($this->getUser()->getType() == "student") {
            return $this->redirectToRoute('teacher_forbidden');
        }

        $repository = $this->getDoctrine()->getRepository(Exercice::class);
        $exo = $repository->find($id);

        $exists = ($exo != null);

        if ($exists) {
            $repository = $this->getDoctrine()->getRepository(Course::class);
            $course = $repository->find($exo->getCourseId());
            if ($course->getTeacher() != $this->getUser()->getUsername()) {
                $exists = false;
            } 
        }
    
        if (!$exists) {
            return $this->redirectToRoute('home_teacher');
        }

        $em->remove($exo);
        $em->flush();
        
        return $this->redirectToRoute('home_teacher'); 
    }

    /**
     * @Route("/teacher/result", name="teacher_result_redirect", methods={"POST"})
     */
    public function redirectToResult(Request $request)
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($this->getUser()->getType() == "student") {
            return $this->redirectToRoute('teacher_forbidden');
        }
        
        return $this->redirectToRoute('teacher_results', ['student' => $request->request->get('username')]);
    }

    /**
     * @Route("/teacher/result/{student}", name="teacher_results", methods={"GET"})
     */
    public function studentResult(String $student)
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($this->getUser()->getType() == "student") {
            return $this->redirectToRoute('teacher_forbidden');
        }

        $repository = $this->getDoctrine()->getRepository(User::class);
        $attempts = $repository->findBy(
            ['username' => $student]
        );

        $exists = ($attempts != []);
        $results = [];

        if ($exists) {
            $repository = $this->getDoctrine()->getRepository(Attempt::class);
            $attempts = $repository->findBy(
                ['studentID' => $student]
            );
            
            $repository = $this->getDoctrine()->getRepository(Exercice::class);
            foreach($attempts as $i => $att) {
                $exo = $repository->find($att->getExoID());
                $results[$i]["exo"] = $exo->getTitle();
            }

            $repository = $this->getDoctrine()->getRepository(Course::class);
            foreach($attempts as $i => $att) {
                $course = $repository->find($att->getCourseID());
                $results[$i]["course"] = $course->getTitle();
            }

            foreach($attempts as $i => $att) {
                $results[$i]["succ"] = $att->getSuccessfull();
            }
        }
        
        return $this->render('teacher/results.html.twig', [
            'username' => $this->getUser()->getUsername(),
            'student' => $student,
            'exists' => $exists,
            'results' => $results
        ]);
    }

    /**
     * @Route("/teacher/forbidden", name="teacher_forbidden", methods={"GET"})
     */
    public function forbidden()
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->render('forbidden.html.twig', [
            'username' => $this->getUser()->getUsername(),
            'role' => 'teachers',
            'link' => '/student/home'
        ]);
    }
}

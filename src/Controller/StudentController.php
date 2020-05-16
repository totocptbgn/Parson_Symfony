<?php

namespace App\Controller;

use App\Entity\Attempt;
use App\Entity\Course;
use App\Entity\Exercice;
use App\Entity\User;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class StudentController extends AbstractController
{

    private function isCourseComplete(ObjectRepository $repo, array $exos, User $usr)
    {
        foreach ($exos as $e) {
            $a = $repo->findBy(
                ['exoID' => $e->getID(), 'studentID' => $usr->getUsername()]
            );
            if ($a == null) {
                return 1;
            }
        }
        return 3;
    }

    /**
     * @Route("/student/home", name="home_student", methods={"GET"})
     */
    public function index()
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($this->getUser()->getType() == "teacher") {
            return $this->redirectToRoute('student_forbidden');
        }

        $repository = $this->getDoctrine()->getRepository(Course::class);
        $courses = $repository->findAll();


        $state = [];
        $usr = $this->getUser();
        $repository = $this->getDoctrine()->getRepository(Exercice::class);
        $repo = $this->getDoctrine()->getRepository(Attempt::class);

        foreach($courses as $i => $c) {
            $exos = $repository->findBy(
                ['course_id' => $c->getId()]
            );
            if ($exos == []) {
                $state[$i] = 2;
            } else {
                $state[$i] = $this->isCourseComplete($repo, $exos, $usr);
            }
        }
        
        return $this->render('student/index.html.twig', [
            'username' => $this->getUser()->getUsername(),
            'courses' => $courses,
            'state' => $state
        ]);
    }

    /**
     * @Route("/student/course/{id<\d+>}", name="student_courses", methods={"GET"})
     */
    public function course(int $id)
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($this->getUser()->getType() == "teacher") {
            return $this->redirectToRoute('student_forbidden');
        }

        $repository = $this->getDoctrine()->getRepository(Course::class);
        $course = $repository->find($id);

        $no_access = ($course == null);
        $isComplete = [];
        $repository = $this->getDoctrine()->getRepository(Exercice::class);
        $repo = $this->getDoctrine()->getRepository(Attempt::class);
        if (!$no_access) {
            $exos = $repository->findBy(
                ['course_id' => $id]
            );
            foreach($exos as $i => $e) {
                $usr = $this->getUser()->getUsername();
                $isComplete[$i] = ($repo->findBy(
                    ['exoID' => $e->getId(), 'studentID' => $usr]
                ) != []);
            }
        }
        
        return $this->render('student/course.html.twig', [
            'username' => $this->getUser()->getUsername(),
            'id' => $id,
            'no_access' => $no_access,
            'course' => $course,
            'exos' => $exos,
            'att' => $isComplete
        ]);
    }

    /**
     * @Route("/student/exo/{id<\d+>}/", name="student_exo", methods={"GET"})
     */
    public function see_exo(int $id)
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($this->getUser()->getType() == "teacher") {
            return $this->redirectToRoute('student_forbidden');
        }

        $repository = $this->getDoctrine()->getRepository(Exercice::class);
        $exo = $repository->find($id);
        $exists = ($exo != null);

        $repository = $this->getDoctrine()->getRepository(Attempt::class);
        $attempt = $repository->findBy(['exoID' => $id, 'studentID' => $this->getUser()->getUsername()]);
        $att = ($attempt != null);

        if ($exists) {
            $repository = $this->getDoctrine()->getRepository(Course::class);
            $course = $repository->find($exo->getCourseId());
        } else {
            $course = null;
        }   

        return $this->render('student/exo.html.twig', [
            'username' => $this->getUser()->getUsername(),
            'id' => $id,
            'exists' => $exists,
            'done' => $att,
            'exo' => $exo,
            'course' => $course
        ]);
    }

    /**
     * @Route("/attempt", name="student_attempt", methods={"POST"})
     */

    public function confirmAttempt(EntityManagerInterface $em, Request $request)
    {   
        $att = new Attempt();
        $att->setCourseID((int) $request->request->get('course'));
        $att->setExoID((int) $request->request->get('exo'));
        $att->setStudentID($request->request->get('username'));
        $att->setSuccessfull($request->request->get('success') == "true");
        $em->persist($att);
        $em->flush();
        return $this->json(['persist' => 'ok']);
    }

    /**
     * @Route("/student/forbidden", name="student_forbidden", methods={"GET"})
     */
    public function forbidden()
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->render('forbidden.html.twig', [
            'username' => $this->getUser()->getUsername(),
            'role' => 'students',
            'link' => '/teacher/home'
        ]);
    }
}

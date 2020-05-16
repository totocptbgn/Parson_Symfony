<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Throwable;

class ErrorController extends AbstractController
{
    public function show(Throwable $exception)
    {   
        dump($exception);
        return $this->render('error/index.html.twig', [
            'error' => $exception->getMessage(),
        ]);
    }
}

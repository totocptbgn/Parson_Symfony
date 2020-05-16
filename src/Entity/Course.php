<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CourseRepository")
 */
class Course
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Teacher;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Title;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTeacher(): ?string
    {
        return $this->Teacher;
    }

    public function setTeacher(string $Teacher): self
    {
        $this->Teacher = $Teacher;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->Title;
    }

    public function setTitle(string $Title): self
    {
        $this->Title = $Title;

        return $this;
    }
}

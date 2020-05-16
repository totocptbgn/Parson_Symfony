<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AttemptRepository")
 */
class Attempt
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $courseID;

    /**
     * @ORM\Column(type="integer")
     */
    private $exoID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $studentID;

    /**
     * @ORM\Column(type="boolean")
     */
    private $successfull;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCourseID(): ?int
    {
        return $this->courseID;
    }

    public function setCourseID(int $courseID): self
    {
        $this->courseID = $courseID;

        return $this;
    }

    public function getExoID(): ?int
    {
        return $this->exoID;
    }

    public function setExoID(int $exoID): self
    {
        $this->exoID = $exoID;

        return $this;
    }

    public function getStudentID(): ?string
    {
        return $this->studentID;
    }

    public function setStudentID(string $studentID): self
    {
        $this->studentID = $studentID;

        return $this;
    }

    public function getSuccessfull(): ?bool
    {
        return $this->successfull;
    }

    public function setSuccessfull(bool $successfull): self
    {
        $this->successfull = $successfull;

        return $this;
    }
}

<?php

namespace AppBundle\Repository\Helper\Validator;

use AppBundle\Entity;
use AppBundle\Exception\Http\BadRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraints\Email;

class User
{

    /** @var BadRequest */
    private $badRequest;
    /** @var  Entity\User */
    private $user;

    /**
     * User constructor.
     * @param Entity\User $user
     */
    function __construct(Entity\User $user)
    {
        $this->user = $user;
        $this->badRequest = new BadRequest();
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param $ctrlValidator
     * @throws BadRequest
     */
    public function validate(EntityManagerInterface $entityManager, $ctrlValidator)
    {
        $this->validateEmail($entityManager, $ctrlValidator);
        $this->validateSecret();
        $this->validateName();
        $this->validateBirthDate();

        if (!empty($this->badRequest->getErrors())) {
            throw $this->badRequest;
        }
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param $ctrlValidator
     */
    private function validateEmail(EntityManagerInterface $entityManager, $ctrlValidator)
    {
        if (empty($this->user->getEmail())) {
            $this->badRequest->addError('email', BadRequest::BLANK_VALUE);
        } else {
            $emailError = $ctrlValidator->validate(
                $this->user->getEmail(),
                new Email()
            );

            if (!empty((string) $emailError)) {
                $this->badRequest->addError('email', BadRequest::INVALID_FORMAT);
            } else {
                $otherUser = $entityManager
                    ->getRepository(Entity\User::CLASS_NAME)
                    ->findByEmail($this->user->getEmail());

                if (!empty($otherUser)) {
                    $this->badRequest->addError('email', BadRequest::UNIQUE_KEY_CONSTRAINT_ERROR);
                }
            }
        }
    }

    /**
     *
     */
    private function validateSecret()
    {
        if (empty($this->user->getSecret())) {
            $this->badRequest->addError('secret', BadRequest::BLANK_VALUE);
        } else {

            // Checking if secret hash is a valid hex string of lenght 32
            if (!preg_match('/^[a-f0-9]{32}$/', $this->user->getSecret())) {
                $this->badRequest->addError('secret', BadRequest::INVALID_FORMAT);
            }
        }
    }

    /**
     *
     */
    private function validateName()
    {
        if (empty($this->user->getName())) {
            $this->badRequest->addError('name', BadRequest::BLANK_VALUE);
        }
    }

    /**
     *
     */
    private function validateBirthDate()
    {
        if ($this->user->getBirthDate()) {
            if (!$this->user->getBirthDate() instanceof \DateTime) {
                try {
                    $this->user->setBirthDate(new \DateTime((string) $this->user->getBirthDate()));
                } catch (\Exception $e) {
                    $this->badRequest->addError('birthDate', BadRequest::INVALID_FORMAT);
                }
            }
        }
    }
}
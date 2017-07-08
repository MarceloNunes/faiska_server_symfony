<?php

namespace AppBundle\Repository\Helper\Validator;

use AppBundle\Controller\Helper;
use AppBundle\Entity;
use AppBundle\Exception\Http\BadRequestException;
use Doctrine\ORM\EntityManagerInterface;

class UserValidator
{
    /** @var BadRequestException */
    private $badRequest;
    /** @var  Helper\UnifiedRequest */
    private $request;

    /**
     * User constructor.
     * @param Helper\UnifiedRequest $request
     */
    function __construct(Helper\UnifiedRequest $request)
    {
        $this->request = $request;
        $this->badRequest = new BadRequestException();
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param int $userId
     * @throws BadRequestException
     */
    public function validateFormData(EntityManagerInterface $entityManager, $userId = 0)
    {
        $this->validateEmail($entityManager, $userId);
        $this->validatePassword();
        $this->validateName();
        $this->validateBirthDate();

        if (!empty($this->badRequest->getErrors())) {
            throw $this->badRequest;
        }
    }

    /**
     * @throws BadRequestException
     */
    public function validateLoginData()
    {
        if (!$this->request->isProvided('email')) {
            $this->badRequest->addError('email', BadRequestException::MISSING_VALUE);
        } elseif (!$this->checkValidEmail()) {
            $this->badRequest->addError('email', BadRequestException::INVALID_FORMAT);
        }

        if (!$this->request->isProvided('password')) {
            $this->badRequest->addError('password', BadRequestException::MISSING_VALUE);
        } elseif (!$this->checkValidPassword()) {
            $this->badRequest->addError('password', BadRequestException::INVALID_FORMAT);
        }

        if (!empty($this->badRequest->getErrors())) {
            throw $this->badRequest;
        }
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param int $userId
     */
    private function validateEmail(EntityManagerInterface $entityManager, $userId)
    {
        if (!$this->request->isUpdateColumn() && !$this->request->isProvided('email')) {
            $this->badRequest->addError('email', BadRequestException::MISSING_VALUE);
        } else {
            if (!$this->checkValidEmail()) {
                $this->badRequest->addError('email', BadRequestException::INVALID_FORMAT);
            } else {
                /** @var Entity\User $otherUser */
                $otherUser = $entityManager
                    ->getRepository(Entity\User::CLASS_NAME)
                    ->findOneBy(array(
                        'email' => $this->request->get('email')
                    ));

                if (!empty($otherUser) && $otherUser->getId() != $userId) {
                    $this->badRequest->addError('email', BadRequestException::UNIQUE_KEY_CONSTRAINT_ERROR);
                }
            }
        }
    }

    /**
     *
     */
    private function validatePassword()
    {
        if ($this->request->isProvided('password')) {
            if ($this->checkValidPassword()) {
                $this->badRequest->addError('password', BadRequestException::INVALID_FORMAT);
            }
        } else {
            if ($this->request->isInsert()) {
                $this->badRequest->addError('password', BadRequestException::MISSING_VALUE);
            }
        }
    }

    /**
     *
     */
    private function validateName()
    {
        if (!$this->request->isUpdateColumn() && !$this->request->isProvided('name')) {
            $this->badRequest->addError('name', BadRequestException::MISSING_VALUE);
        }
    }

    /**
     *
     */
    private function validateBirthDate()
    {
        if ($this->request->isProvided('birthDate')) {
            try {
                new \DateTime((string) $this->request->get('birthDate'));
            } catch (\Exception $e) {
                $this->badRequest->addError('birthDate', BadRequestException::INVALID_FORMAT);
            }
        }
    }

    /**
     * @return bool
     */
    private function checkValidEmail()
    {
        return filter_var( $this->request->get('email'), FILTER_VALIDATE_EMAIL);
    }

    /**
     * @return bool
     */
    private function checkValidPassword()
    {
        return preg_match('/^[a-f0-9]{32}$/', $this->request->get('password'));
    }
}

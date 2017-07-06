<?php

namespace AppBundle\Repository\Helper\Validator;

use AppBundle\Controller\Helper;
use AppBundle\Entity;
use AppBundle\Exception\Http\BadRequestException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraints;

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
     * @param $ctrlValidator
     * @param int $userId
     * @throws BadRequestException
     */
    public function validate(EntityManagerInterface $entityManager, $ctrlValidator, $userId = 0)
    {
        $this->validateEmail($entityManager, $ctrlValidator, $userId);
        $this->validatePassword();
        $this->validateName();
        $this->validateBirthDate();

        if (!empty($this->badRequest->getErrors())) {
            throw $this->badRequest;
        }
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param $ctrlValidator
     * @param int $userId
     */
    private function validateEmail(EntityManagerInterface $entityManager, $ctrlValidator, $userId)
    {
        if ($this->request->getRequest()->getMethod() !== 'PATCH'
            && !$this->request->isProvided('email')
        ) {
            $this->badRequest->addError('email', BadRequestException::BLANK_VALUE);
        } else {
            $emailError = $ctrlValidator->validate(
                $this->request->get('email'),
                new Constraints\Email()
            );

            if (!empty((string) $emailError)) {
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
        if ($this->request->getRequest()->getMethod() !== 'PATCH' &&
            !$this->request->isProvided('password')
        ) {
            $this->badRequest->addError('password', BadRequestException::BLANK_VALUE);
        } else {

            // Checking if password hash is a valid hex string of lenght 32
            if (!preg_match('/^[a-f0-9]{32}$/', $this->request->get('password'))) {
                $this->badRequest->addError('password', BadRequestException::INVALID_FORMAT);
            }
        }
    }

    /**
     *
     */
    private function validateName()
    {
        if ($this->request->getRequest()->getMethod() !== 'PATCH' &&
            !$this->request->isProvided('name')
        ) {
            $this->badRequest->addError('name', BadRequestException::BLANK_VALUE);
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
}
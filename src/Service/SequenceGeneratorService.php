<?php

namespace App\Service;

use App\Entity\SequenceCounter;
use App\Repository\SequenceCounterRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;

class SequenceGeneratorService
{
    public function __construct(
        private readonly EntityManagerInterface    $em,
        private readonly SequenceCounterRepository $repository,
    )
    {

    }

    public function next(string $scope): int
    {
        return $this->em->wrapInTransaction(function () use ($scope) {
            $counter = $this->repository->findOneBy(['scope' => $scope]);

            if (!$counter) {
                $counter = (new SequenceCounter())
                    ->setScope($scope)
                    ->setLastNumber(0);
                $this->em->persist($counter);
                $this->em->flush(); // flush required before locking
            }

            $this->em->lock($counter, LockMode::PESSIMISTIC_WRITE);

            $next = $counter->getLastNumber() + 1;
            $counter->setLastNumber($next);
            $this->em->flush();

            return $next;
        });
    }
}

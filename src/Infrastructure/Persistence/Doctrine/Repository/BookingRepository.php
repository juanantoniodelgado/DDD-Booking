<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use \DateTimeImmutable;
use App\Domain\Model\Booking;
use App\Domain\Repository\BookingRepositoryInterface;
use App\Infrastructure\Exception\EntityNotFoundException;
use App\Infrastructure\Exception\WritingException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\UnexpectedResultException;
use Doctrine\Persistence\ManagerRegistry;

class BookingRepository extends ServiceEntityRepository implements BookingRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    /**
     * @param int $roomId
     * @param DateTimeImmutable $arrival
     * @param DateTimeImmutable $departure
     *
     * @return bool
     */
    public function checkAvailability(int $roomId, DateTimeImmutable $arrival, DateTimeImmutable $departure) : bool
    {
        $result = $this->getEntityManager()->createQueryBuilder()
            ->select('b')
            ->from(Booking::class, 'b')
            ->where('IDENTITY(b.room) = :roomId')
            ->andWhere('b.departure > :arrival AND b.arrival < :departure')

            ->setParameter('roomId', $roomId)
            ->setParameter('arrival', $arrival)
            ->setParameter('departure', $departure)
            ->getQuery()->execute();

        return (is_null($result) || empty($result));
    }

    /**
     * @param int $bookingId
     *
     * @return Booking
     *
     * @throws EntityNotFoundException
     */
    public function byId(int $bookingId): Booking
    {
        try {
            return $this->getEntityManager()->createQueryBuilder()
                ->select('b')
                ->from(Booking::class, 'b')
                ->where('b.id = :id')
                ->setParameter('id', $bookingId)
                ->getQuery()
                ->getSingleResult()
            ;
        } catch (UnexpectedResultException) {
            throw new EntityNotFoundException();
        }
    }

    /**
     * @param Booking $booking
     *
     * @return void
     *
     * @throws WritingException
     */
    public function save(Booking $booking): void
    {
        try {
            $this->getEntityManager()->persist($booking);
            $this->getEntityManager()->flush();
        } catch (ORMException $exception) {
            throw new WritingException($exception->getMessage());
        }
    }
}

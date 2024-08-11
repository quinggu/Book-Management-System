<?php

namespace App\EventListener;

use App\Entity\Book;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;

final readonly class BookListener
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Book) {
            return;
        }
        // Log the event of a new book being added
        $this->logger->info('New book added: ' . $entity->getTitle());
    }
}

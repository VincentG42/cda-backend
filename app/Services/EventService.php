<?php

namespace App\Services;

use App\DTOs\CreateEventDTO;
use App\DTOs\UpdateEventDTO;
use App\Models\Event;
use App\Repositories\EventRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EventService
{
    public function __construct(
        private EventRepositoryInterface $eventRepository
    ) {}

    public function getUpcomingEvents(): LengthAwarePaginator
    {
        return $this->eventRepository->getUpcomingEvents(['author', 'tags']);
    }

    public function createEvent(CreateEventDTO $dto): Event
    {
        return $this->eventRepository->create($dto);
    }

    public function updateEvent(Event $event, UpdateEventDTO $dto): bool
    {
        return $this->eventRepository->update($event, $dto);
    }

    public function deleteEvent(Event $event): bool
    {
        return $this->eventRepository->delete($event);
    }
}

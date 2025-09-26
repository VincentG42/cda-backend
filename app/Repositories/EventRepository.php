<?php

namespace App\Repositories;

use App\DTOs\CreateEventDTO;
use App\DTOs\UpdateEventDTO;
use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EventRepository implements EventRepositoryInterface
{
    public function getUpcomingEvents(array $relations = []): LengthAwarePaginator
    {
        return Event::with($relations)
            ->where('start_at', '>=', now())
            ->orderBy('start_at')
            ->paginate(15);
    }

    public function create(CreateEventDTO $dto): Event
    {
        return Event::create($dto->toArray());
    }

    public function update(Event $event, UpdateEventDTO $dto): bool
    {
        return $event->update($dto->toArray());
    }

    public function delete(Event $event): bool
    {
        return $event->delete();
    }
}

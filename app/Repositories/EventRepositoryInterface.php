<?php

namespace App\Repositories;

use App\DTOs\CreateEventDTO;
use App\DTOs\UpdateEventDTO;
use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EventRepositoryInterface
{
    public function getUpcomingEvents(array $relations = []): LengthAwarePaginator;

    public function create(CreateEventDTO $dto): Event;

    public function update(Event $event, UpdateEventDTO $dto): bool;

    public function delete(Event $event): bool;
}

<?php

namespace App\Http\Controllers\Api;

use App\DTOs\CreateEventDTO;
use App\DTOs\UpdateEventDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EventController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private EventService $eventService) {}

    public function index()
    {
        $this->authorize('viewAny', Event::class);
        $events = $this->eventService->getUpcomingEvents();

        return EventResource::collection($events);
    }

    public function store(Request $request): EventResource
    {
        $this->authorize('create', Event::class);
        $dto = CreateEventDTO::fromRequest($request);
        $event = $this->eventService->createEvent($dto);

        return new EventResource($event);
    }

    public function show(Event $event): EventResource
    {
        $this->authorize('view', $event);

        return new EventResource($event->load(['author', 'tags']));
    }

    public function update(Request $request, Event $event): EventResource
    {
        $this->authorize('update', $event);
        $dto = UpdateEventDTO::fromRequest($request);
        $this->eventService->updateEvent($event, $dto);

        return new EventResource($event->fresh()->load(['author', 'tags']));
    }

    public function destroy(Event $event): Response
    {
        $this->authorize('delete', $event);
        $this->eventService->deleteEvent($event);

        return response(null, Response::HTTP_NO_CONTENT);
    }
}

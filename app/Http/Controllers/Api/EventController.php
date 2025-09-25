<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EventController extends Controller
{
    use AuthorizesRequests;

    /**
     * @group Événements
     *
     * @summary Lister tous les événements
     *
     * Récupère une liste de tous les événements, triés par date de début la plus récente.
     */
    public function index()
    {
        $this->authorize('viewAny', Event::class);

        return Event::with(['author', 'tags'])
            ->where('start_at', '>=', now())
            ->orderBy('start_at')
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Event::class);
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'start_at' => 'required|date',
            'close_at' => 'nullable|date|after_or_equal:start_at',
            'place' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'additionnal_info' => 'nullable|string',
        ]);

        $event = Event::create($validatedData);

        return response()->json($event, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        $this->authorize('view', $event);

        return $event;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);
        $validatedData = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'start_at' => 'sometimes|required|date',
            'close_at' => 'nullable|date|after_or_equal:start_at',
            'place' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string|max:255',
            'additionnal_info' => 'nullable|string',
        ]);

        $event->update($validatedData);

        return response()->json($event);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);
        $event->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}

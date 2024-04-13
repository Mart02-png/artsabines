<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::all();
        return response()->json($events);
    }

    public function store(Request $request)
    { 
        $validatedData = $request->validate([
            'title' => 'required',
            'email' => 'required',
            'start' => 'required|date',
            'end' => 'required|date',
            'user' => 'required|in:clientApproval,clientApproved,admin,adminLimit',
            'allDay' => 'required|boolean',
        ]);

        $event = Event::create($validatedData);
        return response()->json($event, 201);
    }

    public function update(Request $request, Event $event)
    {
        $validatedData = $request->validate([
           
            'start' => 'required|date',
            'end' => 'required|date',
        ]);

        $event->update($validatedData);
        return response()->json($event, 200);
    }

    public function updateClient(Request $request, Event $event)
    {
        $validatedData = $request->validate([
            'user' => 'required|in:clientApproval,clientApproved,admin,adminLimit',
        ]);

        $event->update($validatedData);
        return response()->json($event, 200);
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return response()->json(null, 204); 
    }   
}

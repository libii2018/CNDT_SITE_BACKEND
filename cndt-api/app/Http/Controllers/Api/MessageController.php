<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MessageController extends Controller
{
    /**
     * Liste des messages reçus (administration uniquement).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $messages = Message::query()
            ->when($request->filled('unread') && $request->boolean('unread'), fn ($q) => $q->where('is_read', false))
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return MessageResource::collection($messages);
    }

    /**
     * Formulaire de contact public.
     */
    public function store(Request $request): MessageResource
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'sometimes|nullable|string|max:50',
            'subject' => 'sometimes|nullable|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        return new MessageResource(Message::create($data));
    }

    public function show(Message $message): MessageResource
    {
        $message->update(['is_read' => true]);

        return new MessageResource($message);
    }

    public function update(Request $request, Message $message): MessageResource
    {
        $message->update($request->validate(['is_read' => 'required|boolean']));

        return new MessageResource($message);
    }

    public function destroy(Message $message): JsonResponse
    {
        $message->delete();

        return response()->json(['message' => 'Message supprimé.']);
    }
}

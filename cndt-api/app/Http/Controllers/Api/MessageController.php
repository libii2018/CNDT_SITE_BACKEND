<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Mail;

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
     * Formulaire de contact public (limité par le throttle "contact").
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

        $message = Message::create($data);

        // Alerte e-mail vers l'adresse configurée (MAIL_ALERT_TO) :
        // sans cette variable, l'alerte part dans les logs (MAIL_MAILER=log).
        if ($to = config('mail.alert_to')) {
            try {
                Mail::raw(
                    "Nouveau message reçu via le formulaire de contact du site CNDT.\n\n"
                    ."De : {$data['name']} <{$data['email']}>\n"
                    ."Téléphone : ".($data['phone'] ?? '—')."\n"
                    ."Objet : ".($data['subject'] ?? '—')."\n\n"
                    .$data['message']."\n\n"
                    ."— Répondez directement à cet e-mail ou consultez le dashboard (Messages).",
                    function ($mail) use ($to, $data): void {
                        $mail->to($to)
                            ->replyTo($data['email'], $data['name'])
                            ->subject('[CNDT] Contact site : '.($data['subject'] ?: 'nouveau message'));
                    }
                );
            } catch (\Throwable $e) {
                // L'alerte ne doit jamais faire échouer l'enregistrement
                report($e);
            }
        }

        return new MessageResource($message);
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

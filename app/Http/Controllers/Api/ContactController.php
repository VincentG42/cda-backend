<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ContactFormMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        try {
            Mail::to(config('mail.from.address'))
                ->send(new ContactFormMail($validatedData));

            return response()->json(['message' => 'Votre message a été envoyé avec succès.'], 200);
        } catch (\Exception $e) {
            // Log the error for debugging
            // Log::error('Failed to send contact form email: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de l\'envoi de votre message.'], 500);
        }
    }
}

<?php

namespace Tests\Feature\Feature;

use App\Mail\ContactFormMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_guest_can_submit_a_contact_form(): void
    {
        Mail::fake();

        $formData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'subject' => 'Test Subject',
            'message' => 'This is a test message.',
        ];

        $response = $this->postJson('/api/contact', $formData);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Votre message a été envoyé avec succès.']);

        Mail::assertSent(ContactFormMail::class, function ($mail) use ($formData) {
            return $mail->hasTo(config('mail.from.address')) &&
                   $mail->validatedData['email'] === $formData['email'] &&
                   $mail->validatedData['subject'] === $formData['subject'];
        });
    }

    /** @test */
    public function contact_form_requires_valid_data(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/contact', []);

        $response->assertStatus(422); // Unprocessable Entity
        $response->assertJsonValidationErrors(['name', 'email', 'subject', 'message']);

        Mail::assertNothingSent();
    }
}

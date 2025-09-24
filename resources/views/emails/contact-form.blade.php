@component('mail::message')
# Nouveau message de contact

**De :** {{ $validatedData['name'] }} ({{ $validatedData['email'] }})

**Sujet :** {{ $validatedData['subject'] }}

**Message :**
{{ $validatedData['message'] }}

Merci,
{{ config('app.name') }}
@endcomponent

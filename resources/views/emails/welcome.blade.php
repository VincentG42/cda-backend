<!DOCTYPE html>
<html>
<head>
    <title>Bienvenue au Club !</title>
</head>
<body>
    <h1>Bienvenue, {{ $user->firstname }} !</h1>
    <p>Votre compte a été créé avec succès.</p>
    <p>Voici vos identifiants temporaires :</p>
    <ul>
        <li><strong>Email :</strong> {{ $user->email }}</li>
        <li><strong>Mot de passe temporaire :</strong> {{ $password }}</li>
    </ul>
    <p>Veuillez vous connecter et changer votre mot de passe dès que possible.</p>
    <p><a href="{{ $loginUrl }}">Se connecter</a></p>
</body>
</html>

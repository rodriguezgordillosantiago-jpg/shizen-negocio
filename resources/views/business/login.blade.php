<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión | Shizen Negocio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/business.css') }}">
</head>
<body class="login-page">
    <section class="login-left">
        <div class="login-pitch">
            <img src="{{ asset('img/logo.png') }}" alt="Shizen Negocio">
            <h1>Gestiona tu negocio con <span>inteligencia</span></h1>
            <p>Controla productos, promociones y pedidos desde un solo lugar.</p>
        </div>
    </section>
    <section class="login-right">
        <div class="login-form-wrap">
            <img class="login-logo" src="{{ asset('img/logo.png') }}" alt="Shizen Negocio">
            <h2>Bienvenido de vuelta</h2>
            <p>Ingresa tus credenciales para continuar</p>
            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            <form method="post" action="{{ route('login.store') }}">
                @csrf
                <label class="form-label" for="email">Correo electrónico</label>
                <input class="form-control" id="email" type="email" name="email" value="{{ old('email') }}" placeholder="tu@correo.com" required autofocus>
                <label class="form-label" for="password">Contraseña</label>
                <input class="form-control" id="password" type="password" name="password" placeholder="••••••••" required>
                <button class="btn btn-primary btn-lg" type="submit">Iniciar sesión</button>
            </form>
        </div>
    </section>
</body>
</html>

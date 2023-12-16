<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>

<body>

    @php
        $routeName = request()
            ->route()
            ->getName();
    @endphp
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Blog</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarColor01"
                aria-controls="navbarColor01" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarColor01">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item active">
                        <a @class(['nav-link', 'active' => Str::startsWith($routeName, 'blog.')]) href="{{ route('blog.index') }}">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Link</a>
                    </li>
                </ul>
                <div class="navbar-nav ms-auto mb-2 mb-lg-0 text-white">
                    @auth
                        {{ Auth::user()->name }}
                        <form class="nav-item" action="{{ route('auth.logout') }}" method="post">
                            @method("delete")
                            @csrf
                            <button class="nav-link">Se déconnecter</button>
                        </form>
                    @endauth
                    @guest
                        <div class="nav-item">
                            <a href="{{ route('auth.login') }}">Se connecter</a>
                        </div>
                    @endguest
                </div>
            </div>
        </div>

    </nav>

    <div class="container">
        @if (session('success'))
            <div class="alert alert-success" >
                {{ session('success') }}
            </div>
        @endif
        @yield('content')
    </div>
</body>

</html>

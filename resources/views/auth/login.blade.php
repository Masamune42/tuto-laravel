@extends('base')


@section("content")

    <h1>Se connecter</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('auth.login') }}" method="post" class="vstack gap-3">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}">
                    @error('email')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" name="password" id="password" class="form-control" >
                    @error('password')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <button class="btn btn-primary">
                    Se Connecter
                </button>

            </form>
        </div>
    </div>
@endsection
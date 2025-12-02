@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Link wygasł</div>

                <div class="card-body">
                    <div class="alert alert-warning" role="alert">
                        <h4 class="alert-heading">Link do ustawienia hasła wygasł</h4>
                        <p>
                            Link, którego użyłeś, wygasł lub jest nieprawidłowy. Linki do ustawienia hasła są ważne przez 24 godziny ze względów bezpieczeństwa.
                        </p>
                        <hr>
                        <p class="mb-0">
                            <strong>Co teraz?</strong><br>
                            Skontaktuj się z administratorem, który utworzył Twoje konto, aby otrzymać nowy link do ustawienia hasła.
                        </p>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('login') }}" class="btn btn-primary">
                            Powrót do logowania
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

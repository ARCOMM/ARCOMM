<!DOCTYPE html>
<html lang="en">
    <head>
        <title>
            @yield('title')
            &mdash;
            {{ env('SITE_NAME', 'ARCHUB') }}
        </title>

        <meta name="viewport" content="width=device-width, initial-scale=1" />

        {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha/css/bootstrap.min.css" /> --}}
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <link rel="stylesheet" href="/css/app.css">

        <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0-rc.2/jquery-ui.min.js"></script>
    </head>

    <body>
        <header>
            @yield('header')
            <div class="nav container">
                <a href="/" style="float:left">ARCHUB</a>
                <a href="/join">Submit Join Request</a>
                <a href="/join/list">View Join Requests</a>
                @yield('nav')
            </div>
        </header>
        <main>
            <div class="container">
                @yield('content')
            </div>
        </main>
        <footer>
            @yield('footer')
        </footer>
        @yield('scripts')
    </body>
</html>
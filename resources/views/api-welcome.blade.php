<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
            /* @layer theme {} */
        </style>
    @endif
</head>

<body
    class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#F1f1f1]
    flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">

    <body
        class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#F1f1f1] flex flex-col items-center justify-center min-h-screen p-6 lg:p-8">

        <h1 class="text-4xl font-semibold mb-4">🚀 DevLearn API</h1>
        <p class="text-center mb-6">
            Bem-vindo à DevLearn API! Uma API RESTful para gestão de cursos e progresso de aprendizado. 🎉
        </p>

        <!-- Links Úteis -->
        <div
            class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-lg rounded-xl p-6 mb-6 max-w-md w-full">
            <h3 class="text-xl font-medium mb-3">🔗 Links Úteis</h3>
            <p class="mb-2"><a href="{{ url('/api/documentation') }}" target="_blank"
                    class="text-blue-600 hover:underline">📄 Documentação da API (Swagger)</a></p>
            <p class="mb-2"><a href="https://dev-learn-front-ua6z.vercel.app/" target="_blank"
                    class="text-blue-600 hover:underline">🏠 Front-end da Aplicação</a></p>
        </div>

        <!-- Dicas Rápidas -->
        <div
            class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-lg rounded-xl p-6 mb-6 max-w-md w-full">
            <h3 class="text-xl font-medium mb-3">💡 Dicas Rápidas</h3>
            <p class="mb-2">Use o token retornado pelo auth/register ou auth/login para acessar rotas autenticadas:
            </p>
            <pre class="bg-gray-100 dark:bg-gray-800 p-3 rounded-md overflow-x-auto"><code>Authorization: Bearer {seu_token_aqui}</code></pre>
        </div>

        <!-- Status da API -->
        <div
            class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-lg rounded-xl p-6 max-w-md w-full">
            <h3 class="text-xl font-medium mb-3">⚡ Status da API</h3>
            <p class="mb-2">Todos os sistemas estão funcionando normalmente. Você pode começar a testar os endpoints!
            </p>
            <p>URL Base: <strong>{{ url('/api') }}</strong></p>
        </div>
    </body>

</html>

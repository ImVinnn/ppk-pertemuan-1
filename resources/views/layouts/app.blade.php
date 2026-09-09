<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JARA - @yield('title', 'Advanced Todo List')</title>
    <!-- Tailwind CSS CDN untuk styling instan & rapi -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">
    <header class="bg-indigo-600 text-white shadow">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ route('lists.index') }}" class="text-xl font-bold tracking-wide">
                📝 JARA Todo List
            </a>
            <div class="flex items-center space-x-4 text-sm">
                <a href="{{ route('lists.index') }}" class="hover:underline">Daftar List</a>
                <a href="{{ route('lists.create') }}" class="bg-indigo-700 hover:bg-indigo-800 px-3 py-1.5 rounded-md font-medium transition">
                    + Buat List Baru
                </a>
                @auth
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.users.index') }}" class="hover:underline">Kelola User</a>
                    @endif
                    <span class="text-indigo-200">|</span>
                    <span class="text-indigo-100">{{ auth()->user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="hover:underline text-indigo-100">Logout</button>
                    </form>
                @endauth
            </div>
        </div>
    </header>

    <main class="flex-grow max-w-6xl w-full mx-auto px-4 py-8">
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded shadow-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="bg-white border-t border-gray-200 py-4 text-center text-sm text-gray-500">
        Praktikum PPK - JARA (Advanced Todo List)
    </footer>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar User - JARA</title>
</head>
<body>
    <h1>Daftar User</h1>

            @if (session('success'))
            <p>{{ session('success') }}</p>
        @endif

        <p>
            <a href="{{ route('admin.users.create') }}">Tambah Akun User</a>
        </p>

    <p>Login sebagai: {{ auth()->user()->name }}</p>

    <table border="1" cellpadding="8">
        <thead>
            <tr>
                <th>No.</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->role }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Belum ada user.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p>
        <a href="{{ route('dashboard') }}">Kembali ke dashboard</a>
    </p>
</body>
</html>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Daftar User - JARA</title>
</head>
<body>
    <h1>Daftar User</h1>

    <p>Login sebagai: {{ auth()->user()->name }}</p>

    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif

    @if (session('error'))
        <p>{{ session('error') }}</p>
    @endif

    <p>
        <a href="{{ route('admin.users.create') }}">
            Tambah Akun User
        </a>
    </p>

    <table border="1" cellpadding="8">
        <thead>
            <tr>
                <th>No.</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->role }}</td>

                    <td>
                        @if (auth()->user()->isNot($user))
                            <form
                                method="POST"
                                action="{{ route('admin.users.destroy', $user) }}"
                                onsubmit="return confirm(
                                    'Yakin ingin menghapus akun ini?'
                                )"
                            >
                                @csrf
                                @method('DELETE')

                                <button type="submit">
                                    Hapus
                                </button>
                            </form>
                        @else
                            Akun Anda
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        Belum ada user.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p>
        <a href="{{ route('dashboard') }}">
            Kembali ke dashboard
        </a>
    </p>
</body>
</html>
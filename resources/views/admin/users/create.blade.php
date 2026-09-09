<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah User - JARA</title>
</head>
<body>
    <h1>Tambah Akun User</h1>

    @if ($errors->any())
        <div>
            <strong>Data belum valid:</strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf

        <div>
            <label for="name">Nama</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
            >
        </div>

        <div>
            <label for="email">Email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
            >
        </div>

        <div>
            <label for="password">Password</label>
            <input
                id="password"
                type="password"
                name="password"
                minlength="8"
                required
            >
        </div>

        <div>
            <label for="password_confirmation">Konfirmasi Password</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                minlength="8"
                required
            >
        </div>

        <div>
            <label for="role">Hak akses</label>

            <select id="role" name="role" required>
                <option value="user" @selected(old('role') === 'user')>
                    User
                </option>
                <option value="admin" @selected(old('role') === 'admin')>
                    Admin
                </option>
            </select>
        </div>

        <button type="submit">Simpan Akun</button>
        <a href="{{ route('admin.users.index') }}">Batal</a>
    </form>
</body>
</html>
<!DOCTYPE html>
<html>
<body>

    <h2>Password Akun Berhasil Diperbarui</h2>

    <p>Halo {{ $customer->name }},</p>

    <p>Password akun Anda telah diperbarui oleh admin.</p>

    <p>
        Email: {{ $customer->email }}
    </p>

    <p>
        Password Baru:
        <strong>{{ $password }}</strong>
    </p>

    <p>
        Demi keamanan, segera login dan ubah password Anda.
    </p>

</body>
</html>

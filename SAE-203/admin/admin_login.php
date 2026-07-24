<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Connexion Admin - SAÉ 203</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background-color: rgb(238, 213, 144)">

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow" style="background-color: rgb(214, 184, 102);">
                    <div class="card-body p-4">
                        <h3 class="card-title text-white text-center mb-4">Connexion Admin</h3>

                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger">Identifiants incorrects.</div>
                        <?php endif; ?>

                        <form action="admin_auth.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label text-white">Email</label>
                                <input type="email" class="form-control" name="email" required
                                    placeholder="admin@gmail.com" />
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-white">Mot de passe</label>
                                <input type="password" class="form-control" name="mdp" required
                                    placeholder="••••••••" />
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-dark">Se connecter</button>
                            </div>
                        </form>
                        <p class="text-center mt-3"><a href="index.php" class="text-white">← Retour au site</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('input').forEach(function(input) {
    var originalPlaceholder = input.placeholder;
    input.addEventListener('focus', function() {
        this.placeholder = '';
    });
    input.addEventListener('blur', function() {
        if (this.value === '') {
            this.placeholder = originalPlaceholder;
        }
    });
});
    </script>

</body>

</html>
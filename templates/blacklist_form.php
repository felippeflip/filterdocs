<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar à Blacklist</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container { max-width: 600px; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4 text-center">Adicionar à Blacklist</h1>
        
        <?php if (isset($status)): ?>
            <div class="alert alert-success" role="alert">
                <?= htmlspecialchars($status) ?>
            </div>
        <?php endif; ?>

        <form action="/blacklist" method="POST">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" required>
            </div>
            <div class="mb-3">
                <label for="telefone" class="form-label">Telefone</label>
                <input type="text" class="form-control" id="telefone" name="telefone" required>
            </div>
            <div class="mb-3">
                <label for="telefone1" class="form-label">Telefone 1</label>
                <input type="text" class="form-control" id="telefone1" name="telefone1">
            </div>
            <div class="mb-3">
                <label for="telefone2" class="form-label">Telefone 2</label>
                <input type="text" class="form-control" id="telefone2" name="telefone2">
            </div>
            <div class="mb-3">
                <label for="telefone3" class="form-label">Telefone 3</label>
                <input type="text" class="form-control" id="telefone3" name="telefone3">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control" id="email" name="email">
            </div>
            <div class="g-recaptcha" data-sitekey="SUA_CHAVE_DO_SITE_AQUI"></div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary">Adicionar</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</body>
</html>
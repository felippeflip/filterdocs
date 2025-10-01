<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar à Blacklist</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container { max-width: 800px; }
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
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="nome" class="form-label">Nome</label>
                    <input type="text" class="form-control" id="nome" name="nome" required>
                </div>
                <div class="col-md-6">
                    <label for="bairro" class="form-label">Bairro</label>
                    <input type="text" class="form-control" id="bairro" name="bairro">
                </div>
               <div class="col-md-6">
                    <label for="telefone" class="form-label">Telefone (com DDD)</label>
                    <input type="tel" class="form-control" id="telefone" name="telefone" pattern="[0-9]{10,11}" inputmode="numeric">
                </div>
                <div class="col-md-6">
                    <label for="telefone1" class="form-label">Telefone 1 (com DDD)</label>
                    <input type="tel" class="form-control" id="telefone1" name="telefone1" pattern="[0-9]{10,11}" inputmode="numeric">
                </div>
                <div class="col-md-6">
                    <label for="telefone2" class="form-label">Telefone 2 (com DDD)</label>
                    <input type="tel" class="form-control" id="telefone2" name="telefone2" pattern="[0-9]{10,11}" inputmode="numeric">
                </div>
                <div class="col-md-6">
                    <label for="telefone3" class="form-label">Telefone 3 (com DDD)</label>
                    <input type="tel" class="form-control" id="telefone3" name="telefone3" pattern="[0-9]{10,11}" inputmode="numeric">
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email">
                </div>
                <div class="col-md-6">
                    <label for="solicitante" class="form-label">Solicitante</label>
                    <input type="text" class="form-control" id="solicitante" name="solicitante">
                </div>
                <div class="col-md-6">
                    <label for="canal_solicitacao" class="form-label">Canal Solicitação</label>
                    <input type="text" class="form-control" id="canal_solicitacao" name="canal_solicitacao">
                </div>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary">Adicionar</button>
            </div>
            <div class="d-grid mt-4">
                <a href="/" class="btn btn-secondary">Voltar</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
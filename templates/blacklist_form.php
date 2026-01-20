<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar à Blacklist</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 800px;
        }
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

        <form action="<?= $basePath ?>/blacklist" method="POST">
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
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="tel" class="form-control phone-mask" id="telefone" name="telefone" maxlength="15">
                </div>
                <div class="col-md-6">
                    <label for="telefone1" class="form-label">Telefone 1</label>
                    <input type="tel" class="form-control phone-mask" id="telefone1" name="telefone1" maxlength="15">
                </div>
                <div class="col-md-6">
                    <label for="telefone2" class="form-label">Telefone 2</label>
                    <input type="tel" class="form-control phone-mask" id="telefone2" name="telefone2" maxlength="15">
                </div>
                <div class="col-md-6">
                    <label for="telefone3" class="form-label">Telefone 3</label>
                    <input type="tel" class="form-control phone-mask" id="telefone3" name="telefone3" maxlength="15">
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
                <a href="<?= $basePath ?>/" class="btn btn-secondary">Voltar</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const phoneInputs = document.querySelectorAll('.phone-mask');

            const applyMask = (value) => {
                if (!value) return "";
                value = value.replace(/\D/g, ''); // Remove tudo o que não é dígito
                value = value.replace(/^(\d{2})(\d)/g, '($1) $2'); // Coloca parênteses em volta dos dois primeiros dígitos
                value = value.replace(/(\d)(\d{4})$/, '$1-$2'); // Coloca hífen entre o quarto e o quinto dígitos
                return value;
            };

            phoneInputs.forEach(input => {
                input.addEventListener('input', function (e) {
                    e.target.value = applyMask(e.target.value);
                });
            });
        });
    </script>
</body>

</html>
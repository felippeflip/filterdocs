<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado da Filtragem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 600px;
        }

        .card-stats {
            background-color: #f8f9fa;
            border-left: 5px solid #0d6efd;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1 class="mb-4 text-center">Processamento Concluído</h1>

        <div class="card card-stats p-4 mb-4 shadow-sm">
            <h4 class="mb-3">Estatísticas do Arquivo</h4>
            <div class="row text-center">
                <div class="col-4">
                    <h2 class="text-primary">
                        <?= $totalRows ?>
                    </h2>
                    <small class="text-muted">Total Linhas</small>
                </div>
                <div class="col-4">
                    <h2 class="text-danger">
                        <?= $removedRows ?>
                    </h2>
                    <small class="text-muted">Removidas</small>
                </div>
                <div class="col-4">
                    <h2 class="text-success">
                        <?= $finalRows ?>
                    </h2>
                    <small class="text-muted">Finais</small>
                </div>
            </div>
        </div>

        <div class="d-grid gap-2">
            <a href="<?= $basePath ?>/upload/download" class="btn btn-primary btn-lg">
                📥 Baixar Planilha Filtrada
            </a>
            <a href="<?= $basePath ?>/" class="btn btn-outline-secondary">
                Voltar ao Início
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
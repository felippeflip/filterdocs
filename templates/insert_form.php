<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carregar Planilha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container { max-width: 600px; }
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            z-index: 1000;
            display: none; /* Inicia escondido */
        }
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
        <h3 class="mt-3">Processando...</h3>
        <p>Aguarde enquanto os dados são inseridos no banco de dados...</p>
    </div>

    <div class="container mt-5">
        <h1 class="mb-4 text-center">Carregar Planilha do Excel (BlackList)</h1>
        
        <?php if (isset($status)): ?>
            <div class="alert alert-info" role="alert">
                <?= htmlspecialchars($status) ?>
            </div>
        <?php endif; ?>

        <form id="uploadForm" action="/blacklist/insert" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="excelFile" class="form-label">Selecione o arquivo Excel (.xlsx ou .xls)</label>
                <input class="form-control" type="file" id="excelFile" name="excelFile" accept=".xlsx, .xls" required>
            </div>
            
            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary">Processar</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('uploadForm').addEventListener('submit', function() {
            document.getElementById('loadingOverlay').style.display = 'flex';
            
            // Adiciona um tempo limite para remover o overlay
            // após o início do download. Ajuste o tempo se necessário.
           // setTimeout(function() {
           //     document.getElementById('loadingOverlay').style.display = 'none';
           // }, 5000); // 5000 milissegundos = 5 segundos
        });
    </script>
</body>
</html>
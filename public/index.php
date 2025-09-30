<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\BlacklistService;
use Slim\Views\PhpRenderer;


require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/database.php'; // Inclui a conexão PDO

$app = AppFactory::create();

// Adiciona este middleware de roteamento para evitar problemas
$app->addRoutingMiddleware();

// Rota principal (página inicial)
$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Bem-vindo ao app de filtragem de documentos!");
    return $response;
});

// Rota de teste para a blacklist
$app->get('/blacklist', function (Request $request, Response $response, $args) use ($pdo) {
    
    // Instancia a sua classe de serviço, passando a conexão PDO
    $blacklistService = new BlacklistService($pdo);
    
    // Chama o método para obter a lista de telefones
    $blacklist = $blacklistService->getBlacklistPhones();

    // Retorna a lista em formato JSON para verificação
    $response->getBody()->write(json_encode($blacklist));
    return $response->withHeader('Content-Type', 'application/json');
});

// Rota GET para exibir o formulário da blacklist
$app->get('/blacklist/create', function (Request $request, Response $response, $args) {
    $view = new PhpRenderer(__DIR__ . '/../templates/'); // Instancia a classe correta
    return $view->render($response, 'blacklist_form.php');
});

// Rota POST para processar os dados
$app->post('/blacklist', function (Request $request, Response $response, $args) use ($pdo) {

    // Dados do formulário
    $data = $request->getParsedBody();
    $nome = $data['nome'] ?? '';
    $telefone = $data['telefone'] ?? '';
    $telefone1 = $data['telefone1'] ?? '';
    $telefone2 = $data['telefone2'] ?? '';
    $telefone3 = $data['telefone3'] ?? '';
    $email = $data['email'] ?? '';

    // Insere os dados no banco
    $sql = "INSERT INTO blacklist (nome, telefone, telefone1, telefone2, telefone3, email, dt_inclusao) VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute([$nome, $telefone, $telefone1, $telefone2, $telefone3, $email]);
        $status = 'Item adicionado com sucesso!';
    } catch (\PDOException $e) {
        $status = 'Erro ao adicionar item: ' . $e->getMessage();
    }
    
    // Retorna para o formulário com uma mensagem de status
    $view = new \Slim\Views\PhpRenderer(__DIR__ . '/../templates/');
    return $view->render($response, 'blacklist_form.php', ['status' => $status]);
});


// Rota GET para exibir o formulário de upload
$app->get('/upload', function (Request $request, Response $response, $args) {
    $view = new PhpRenderer(__DIR__ . '/../templates/');
    return $view->render($response, 'upload_form.php');
});



// Rota POST para processar o upload e filtrar o arquivo
$app->post('/upload', function (Request $request, Response $response, $args) use ($pdo) {

    // 1. Obter o arquivo enviado
    $uploadedFiles = $request->getUploadedFiles();
    $uploadedFile = $uploadedFiles['excelFile'];

    if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
        $status = "Erro no upload do arquivo.";
        $view = new PhpRenderer(__DIR__ . '/../templates/');
        return $view->render($response, 'upload_form.php', ['status' => $status]);
    }

    // 2. Pré-carregar e otimizar a blacklist do banco de dados
    $blacklistService = new BlacklistService($pdo);
    $rawBlacklist = $blacklistService->getBlacklistPhones();
    $optimizedBlacklist = array_flip($rawBlacklist); // Otimiza para busca O(1)

    // 3. Carregar o arquivo Excel
    $spreadsheet = IOFactory::load($uploadedFile->getFilePath());
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray(null, true, true, true);
    
    // 4. Iniciar a nova planilha para o resultado filtrado
    $filteredSpreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $filteredWorksheet = $filteredSpreadsheet->getActiveSheet();
    
    // 5. Adicionar o cabeçalho (primeira linha) na nova planilha
    $header = array_shift($rows);
    $filteredWorksheet->fromArray($header, null, 'A1');

    // 6. Iterar sobre as linhas e filtrar
    $currentRow = 2; // Começa na segunda linha do novo arquivo
    foreach ($rows as $row) {
        // A coluna "celular" é a primeira, índice 0
        $celular = $row['A']; 
        
        // Verifica se o celular está na blacklist otimizada
        if (!isset($optimizedBlacklist[$celular])) {
            // Se NÃO estiver, adicione a linha na nova planilha
            $filteredWorksheet->fromArray([$row], null, 'A' . $currentRow);
            $currentRow++;
        }
    }

    // 7. Salvar e enviar a planilha filtrada para download
    $writer = IOFactory::createWriter($filteredSpreadsheet, 'Xlsx');
    $tempFileName = tempnam(sys_get_temp_dir(), 'filtered_excel_');

    $writer->save($tempFileName);

    // Configurar os headers para o download
    $response = $response
        ->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
        ->withHeader('Content-Disposition', 'attachment; filename="planilha_filtrada.xlsx"')
        ->withHeader('Content-Length', filesize($tempFileName));
        
    // Enviar o arquivo e remover o temporário
    $response->getBody()->write(file_get_contents($tempFileName));
    unlink($tempFileName);

    return $response;
});

$app->run();
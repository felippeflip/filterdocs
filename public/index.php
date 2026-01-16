<?php

session_start();

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\BlacklistService;
use Slim\Views\PhpRenderer;


require __DIR__ . '/../vendor/autoload.php';

// Carregar variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

require __DIR__ . '/../src/database.php'; // Inclui a conexão PDO

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$app = AppFactory::create();

// Auto-detect base path
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$app->setBasePath($basePath);

// Adiciona este middleware de roteamento para evitar problemas
$app->addRoutingMiddleware();


// Adicionar o Middleware de Erro
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Manipulador de Erro para rotas não encontradas
$errorMiddleware->setErrorHandler(
    \Slim\Exception\HttpNotFoundException::class,
    function (
        \Psr\Http\Message\ServerRequestInterface $request,
        \Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ) use ($app, $basePath) {
        $response = $app->getResponseFactory()->createResponse();
        // Redireciona para a rota principal (/) com status 302
        return $response->withHeader('Location', $basePath . '/')->withStatus(302);
    }
);


// Rota GET para exibir o formulário da blacklist
$app->get('/blacklist/create', function (Request $request, Response $response, $args) use ($basePath) {
    $view = new PhpRenderer(__DIR__ . '/../templates/'); // Instancia a classe correta
    return $view->render($response, 'blacklist_form.php', ['basePath' => $basePath]);
});

// Rota POST para processar os dados
$app->post('/blacklist', function (Request $request, Response $response, $args) use ($pdo, $basePath) {

    // Funcao para limpar caracteres especiais de numeros de telefone
    $cleanPhoneNumber = function(string $phoneNumber): string {
        return preg_replace('/[^0-9]/', '', $phoneNumber);
    };

    // Funcao para validar o formato do telefone
    $validatePhoneNumber = function(string $phoneNumber): bool {
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);
        return !empty($cleaned) && (strlen($cleaned) >= 10);
    };

    // Dados do formulário
    $data = $request->getParsedBody();
    
    // Validar cada campo de telefone se não estiver vazio
    $phoneFields = ['telefone', 'telefone1', 'telefone2', 'telefone3'];
    $invalidFields = [];

    foreach ($phoneFields as $field) {
        $value = $data[$field] ?? '';
        if (!empty($value) && !$validatePhoneNumber($value)) {
            $invalidFields[] = $field;
        }
    }

    if (!empty($invalidFields)) {
        $status = "Erro: Os seguintes campos de telefone são inválidos: " . implode(', ', $invalidFields) . ". Verifique se eles contêm pelo menos 10 dígitos.";
        $view = new \Slim\Views\PhpRenderer(__DIR__ . '/../templates/');
        return $view->render($response, 'blacklist_form.php', ['status' => $status, 'basePath' => $basePath]);
    }

    // Se a validacao passar, continue com o processamento
    $nome = mb_strtoupper(strval($data['nome'] ?? ''));
    $telefone = $cleanPhoneNumber($data['telefone'] ?? '');
    $telefone1 = $cleanPhoneNumber($data['telefone1'] ?? '');
    $telefone2 = $cleanPhoneNumber($data['telefone2'] ?? '');
    $telefone3 = $cleanPhoneNumber($data['telefone3'] ?? '');
    $email = strtolower(strval($data['email'] ?? ''));
    $bairro = $data['bairro'] ?? '';
    $solicitante = mb_strtoupper(strval($data['solicitante'] ?? ''));
    $canal_solicitacao = mb_strtoupper(strval($data['canal_solicitacao'] ?? ''));

    // Insere os dados no banco
    $sql = "INSERT INTO blacklist (nome, telefone, telefone1, telefone2, telefone3, email, bairro, solicitante, canal_solicitacao, dt_inclusao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute([$nome, $bairro, $telefone, $telefone1, $telefone2, $telefone3, $email, $solicitante, $canal_solicitacao]);
        $status = 'Item adicionado com sucesso!';
    } catch (\PDOException $e) {
        $status = 'Erro ao adicionar item: ' . $e->getMessage();
    }
    
    // Retorna para o formulário com uma mensagem de status
    $view = new \Slim\Views\PhpRenderer(__DIR__ . '/../templates/');
    return $view->render($response, 'blacklist_form.php', ['status' => $status, 'basePath' => $basePath]);
});


// Rota GET para exibir o formulário de upload
$app->get('/', function (Request $request, Response $response, $args) use ($basePath) {
    $view = new PhpRenderer(__DIR__ . '/../templates/');
    return $view->render($response, 'upload_form.php', ['basePath' => $basePath]);
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

    // 2. Pré-carregar e otimizar a blacklist com todos os critérios
    $blacklistService = new BlacklistService($pdo);
    $optimizedBlacklist = $blacklistService->getBlacklistData();

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
        // Assume que a linha NÃO está na blacklist
        $isBlacklisted = false;
        
        // 6.1. Verifica o número do celular (coluna A)
        $celular = $row['A'] ?? '';
        if (!empty($celular) && isset($optimizedBlacklist['phones'][$celular])) {
            $isBlacklisted = true;
        }

        // 6.2. Se não estiver, verifica o email (coluna E)
        if (!$isBlacklisted) {
            $email = strtolower(strval($row['E'] ?? ''));
            if (!empty($email) && isset($optimizedBlacklist['emails'][$email])) {
                $isBlacklisted = true;
            }
        }
        
        // 6.3. Se ainda não estiver, verifica o nome (coluna C)
       // if (!$isBlacklisted) {
       //     $nome = strtolower($row['C'] ?? '');
       //     if (!empty($nome) && isset($optimizedBlacklist['names'][$nome])) {
       //         $isBlacklisted = true;
       //    }
       // }

        // 7. Se a linha não for encontrada na blacklist, adicione-a à nova planilha
        if (!$isBlacklisted) {
            $filteredWorksheet->fromArray([$row], null, 'A' . $currentRow);
            $currentRow++;
        }
    }

    // 8. Salvar e enviar a planilha filtrada para download
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


// Rota GET para exibir o formulário de inserção
$app->get('/blacklist/insert/form', function (Request $request, Response $response) use ($basePath) {
    $view = new \Slim\Views\PhpRenderer(__DIR__ . '/../templates/');

    // Pega a mensagem de status da sessão, se existir
    $status = $_SESSION['status'] ?? null;
    // Limpa a variável da sessão para não mostrar a mensagem novamente
    //unset($_SESSION['status']);

    return $view->render($response, 'insert_form.php', ['status' => $status, 'basePath' => $basePath]);
});


// Rota POST para realizar o upload e inserir os dados da planilha na blacklist
$app->post('/blacklist/insert', function (Request $request, Response $response, $args) use ($pdo, $basePath) {

    // 1. Obter o arquivo enviado
    $uploadedFiles = $request->getUploadedFiles();
    $uploadedFile = $uploadedFiles['excelFile'] ?? null;

    if (!$uploadedFile || $uploadedFile->getError() !== UPLOAD_ERR_OK) {
        $status = "Erro no upload do arquivo. Por favor, selecione um arquivo válido.";
        $view = new PhpRenderer(__DIR__ . '/../templates/');
        return $view->render($response, 'insert_form.php', ['status' => $status, 'basePath' => $basePath]);
    }
    
    // Funcao para limpar caracteres especiais de numeros de telefone
    $cleanPhoneNumber = function(string $phoneNumber): string {
        return preg_replace('/[^0-9]/', '', $phoneNumber);
    };

    // 2. Carregar o arquivo Excel
    try {
        $spreadsheet = IOFactory::load($uploadedFile->getFilePath());
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray(null, true, true, true);
    } catch (\Exception $e) {
        $status = "Erro ao ler o arquivo Excel: " . $e->getMessage();
        $view = new PhpRenderer(__DIR__ . '/../templates/');
        return $view->render($response, 'insert_form.php', ['status' => $status, 'basePath' => $basePath]);
    }
    
    // 3. Preparar a query de inserção para segurança, com as novas colunas
    $sql = "INSERT INTO blacklist (nome, bairro, telefone, telefone1, telefone2, telefone3, email, solicitante, canal_solicitacao, dt_inclusao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);

    // 4. Pular o cabeçalho e iterar sobre as linhas da planilha
    $totalRows = count($rows);
    $insertedRows = 0;
    
    // Remove o cabeçalho
    array_shift($rows);

    foreach ($rows as $row) {
        // Mapear os dados da planilha para as colunas do banco, de acordo com a nova estrutura
        $nome = mb_strtoupper(strval($row['A'] ?? ''));
        $bairro = $row['B'] ?? '';
        $telefone = $cleanPhoneNumber($row['C'] ?? '');
        $telefone1 = $cleanPhoneNumber($row['D'] ?? '');
        $telefone2 = $cleanPhoneNumber($row['E'] ?? '');
        $telefone3 = $cleanPhoneNumber($row['F'] ?? '');
        $email = strtolower(strval($row['G'] ?? ''));
        $solicitante = mb_strtoupper(strval($row['I'] ?? ''));
        $canalSolicitacao = mb_strtoupper(strval($row['J'] ?? ''));
        
        // Verificar se os dados criticos nao estao vazios antes de inserir
        if (!empty($telefone) || !empty($telefone1) || !empty($telefone2) || !empty($telefone3) || !empty($email)) {
            $stmt->execute([
                $nome,
                $bairro,
                $telefone,
                $telefone1,
                $telefone2,
                $telefone3,
                $email,
                $solicitante,
                $canalSolicitacao
            ]);
            $insertedRows++;
        }
    }
    
    if ($insertedRows > 0) {
        $_SESSION['status'] = "Processamento concluído. {$insertedRows} registros foram inseridos na blacklist.";
    } else {
        $_SESSION['status'] = "Erro: nenhum registro foi inserido. Verifique o arquivo.";
    }
    
    // Redireciona para a rota GET
    return $response->withHeader('Location', $basePath . '/blacklist/insert/form')->withStatus(302);
});

$app->run();
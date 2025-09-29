<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\BlacklistService; // Importa a sua nova classe

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/database.php'; // Inclui a conexão PDO

$app = AppFactory::create();

// Adicione este middleware de roteamento para evitar problemas
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

// Outras rotas virão aqui...

$app->run();
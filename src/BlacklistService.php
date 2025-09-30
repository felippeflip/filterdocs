<?php
// src/BlacklistService.php

namespace App;

use PDO;

class BlacklistService
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Busca todos os números de telefone da tabela blacklist.
     * @return array
     */
    public function getBlacklistPhones(): array
    {
        // 1. Busque todos os telefones de todas as colunas
        $stmt = $this->pdo->query('SELECT telefone, telefone1, telefone2, telefone3 FROM blacklist');
        $all_phones_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Crie um array simples e limpo com todos os números
        $blacklist = [];
        foreach ($all_phones_raw as $row) {
            if (!empty($row['telefone'])) {
                $blacklist[] = $row['telefone'];
            }
            if (!empty($row['telefone1'])) {
                $blacklist[] = $row['telefone1'];
            }
            if (!empty($row['telefone2'])) {
                $blacklist[] = $row['telefone2'];
            }
            if (!empty($row['telefone3'])) {
                $blacklist[] = $row['telefone3'];
            }
        }

      //  var_dump($blacklist); // Debug: Verifique os números coletados
        
        // Remova duplicatas e reindexe o array
        return array_values(array_unique($blacklist));
    }
}
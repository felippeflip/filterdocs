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
     * Busca todos os telefones, emails e nomes da blacklist,
     * e retorna um array de listas otimizadas para busca.
     * @return array
     */
    public function getBlacklistData(): array
    {
        $stmt = $this->pdo->query('SELECT nome, email, telefone, telefone1, telefone2, telefone3 FROM blacklist');
        $all_data_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $phoneBlacklist = [];
        $emailBlacklist = [];
      //  $nameBlacklist = [];

        foreach ($all_data_raw as $row) {
            // Adiciona telefones à lista
            if (!empty($row['telefone'])) {
                $phoneBlacklist[] = $row['telefone'];
            }
            if (!empty($row['telefone1'])) {
                $phoneBlacklist[] = $row['telefone1'];
            }
            if (!empty($row['telefone2'])) {
                $phoneBlacklist[] = $row['telefone2'];
            }
            if (!empty($row['telefone3'])) {
                $phoneBlacklist[] = $row['telefone3'];
            }
            
            // Adiciona email à lista
            if (!empty($row['email'])) {
                $emailBlacklist[] = strtolower($row['email']); // Converte para minúsculas para busca
            }
            
            // Adiciona nome à lista
          //  if (!empty($row['nome'])) {
          //      $nameBlacklist[] = strtolower($row['nome']); // Converte para minúsculas para busca
          //  }
        }

        return [
            'phones' => array_flip(array_unique($phoneBlacklist)),
            'emails' => array_flip(array_unique($emailBlacklist)),
          //  'names' => array_flip(array_unique($nameBlacklist))
        ];
    }
}
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
     * Remove caracteres não numéricos do telefone via regex.
     * Static para poder ser reutilizado em outros locais se necessário.
     * @param string|null $phone
     * @return string
     */
    public static function normalizePhoneNumber(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }
        return preg_replace('/[^0-9]/', '', $phone);
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
            // Adiciona telefones à lista (Normalizados)
            $phonesToCheck = [
                $row['telefone'] ?? '',
                $row['telefone1'] ?? '',
                $row['telefone2'] ?? '',
                $row['telefone3'] ?? ''
            ];

            foreach ($phonesToCheck as $rawPhone) {
                $cleanPhone = self::normalizePhoneNumber($rawPhone);
                if (!empty($cleanPhone)) {
                    $phoneBlacklist[] = $cleanPhone;
                }
            }
            
            // Adiciona email à lista
            if (!empty($row['email'])) {
                $emailBlacklist[] = strtolower(trim(strval($row['email']))); // Converte para minúsculas e remove espaços
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
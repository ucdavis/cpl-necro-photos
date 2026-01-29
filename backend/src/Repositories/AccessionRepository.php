<?php

namespace App\Repositories;

use App\Core\Repository;

class AccessionRepository extends Repository
{
    public function getAccessionByNumYear(int $cpl_num, int $cplYear): ?array
    {
        $sql = "SELECT id, cpl_num, suffix, cpl_year
                FROM cpl_billing.cpl_request
                WHERE cpl_num = ? AND cpl_year = ?";
        
        return $this->queryOne($sql, [$cpl_num, $cplYear]);
    }
}
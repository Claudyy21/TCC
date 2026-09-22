<?php

namespace App\Models;

use CodeIgniter\Model;

class ModoAtendimentoModel extends Model
{
    protected $table            = 'modos_atendimento';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = ['nome'];

    protected $useTimestamps = false;

    protected $validationRules = [
        'nome' => 'required|max_length[255]',
    ];
}

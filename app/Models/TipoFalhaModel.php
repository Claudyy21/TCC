<?php

namespace App\Models;

use CodeIgniter\Model;

class TipoFalhaModel extends Model
{
    protected $table            = 'tipos_falha';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = ['nome', 'descricao', 'status'];

    protected $useTimestamps = false;

    protected $validationRules = [
        'nome'      => 'required|max_length[255]',
        'descricao' => 'permit_empty',
        'status'    => 'permit_empty|in_list[0,1]',
    ];
}

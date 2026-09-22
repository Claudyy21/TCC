<?php

namespace App\Controllers;

use App\Models\AtendimentoModel;

/**
 * Controller temporário, só para validar a conexão com o Supabase.
 * Acesse http://localhost:8080/teste-conexao
 *
 * Depois que confirmar que está tudo certo, pode apagar este
 * arquivo e a rota correspondente em Routes.php.
 */
class TesteConexao extends BaseController
{
    public function index()
    {
        $db = db_connect();

        try {
            $db->connect();
        } catch (\Throwable $e) {
            return 'Falha ao conectar no banco: ' . esc($e->getMessage());
        }

        $model = new AtendimentoModel();
        $total = $model->countAllResults();

        return 'Conexão OK. Total de registros na tabela atendimentos: ' . $total;
    }
}

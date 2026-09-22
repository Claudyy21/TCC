<?php

namespace App\Models;

use CodeIgniter\Model;

class ModuloModel extends Model
{
    protected $table            = 'modulos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = ['nome', 'status'];

    protected $useTimestamps = false;

    protected $validationRules = [
        'nome'   => 'required|max_length[255]',
        'status' => 'permit_empty|in_list[0,1]',
    ];

    /**
     * Um módulo por linha, já com o total de atendimentos, total de
     * rotinas distintas e tempo médio de atendimento (em minutos).
     * Base de dados do gráfico de pizza do Dashboard Operacional.
     */
    public function comEstatisticas(): array
    {
        return $this->db->table('modulos m')
            ->select("
                m.id,
                m.nome,
                COUNT(DISTINCT r.id) as total_rotinas,
                COUNT(a.id) as total_atendimentos,
                COALESCE(AVG(minutos_uteis(a.data_resposta, a.data_fechamento)), 0) as tempo_medio_min
            ", false)
            ->join('rotinas r', 'r.modulo_id = m.id', 'left')
            ->join('atendimentos a', 'a.rotina_id = r.id', 'left')
            ->where('m.status', true)
            ->groupBy('m.id, m.nome')
            ->orderBy('m.nome', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * As mesmas estatísticas de comEstatisticas(), mas para um único
     * módulo. Alimenta a tela "Módulo Industrial — Visão Geral".
     */
    public function estatisticasPorId(int $id): ?array
    {
        $resultado = $this->db->table('modulos m')
            ->select("
                m.id,
                m.nome,
                COUNT(DISTINCT r.id) as total_rotinas,
                COUNT(a.id) as total_atendimentos,
                COALESCE(SUM(minutos_uteis(a.data_resposta, a.data_fechamento)), 0) as tempo_total_min,
                COALESCE(AVG(minutos_uteis(a.data_resposta, a.data_fechamento)), 0) as tempo_medio_min
            ", false)
            ->join('rotinas r', 'r.modulo_id = m.id', 'left')
            ->join('atendimentos a', 'a.rotina_id = r.id', 'left')
            ->where('m.id', $id)
            ->groupBy('m.id, m.nome')
            ->get()
            ->getRowArray();

        return $resultado ?: null;
    }
}

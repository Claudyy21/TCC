<?php

namespace App\Models;

use CodeIgniter\Model;

class RotinaModel extends Model
{
    protected $table            = 'rotinas';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = ['modulo_id', 'nome', 'status'];

    protected $useTimestamps = false;

    protected $validationRules = [
        'modulo_id' => 'required|is_natural_no_zero',
        'nome'      => 'required|max_length[255]',
        'status'    => 'permit_empty|in_list[0,1]',
    ];

    /**
     * Rotinas com o nome do módulo já resolvido (join), útil para
     * combos e listagens sem N+1 queries.
     */
    public function comModulo()
    {
        return $this->select('rotinas.*, modulos.nome as modulo_nome')
                     ->join('modulos', 'modulos.id = rotinas.modulo_id');
    }

    /**
     * Uma rotina por linha, com total de atendimentos, tempo total e
     * tempo médio (em minutos), ordenadas da mais atendida para a
     * menos atendida. Alimenta a tela de módulo (top 3, paginado de
     * 3 em 3) e a tela "todas as rotinas" (grid completo).
     */
    public function comEstatisticas(int $moduloId): array
    {
        return $this->db->table('rotinas r')
            ->select("
                r.id,
                r.nome,
                COUNT(a.id) as total_atendimentos,
                COALESCE(SUM(minutos_uteis(a.data_resposta, a.data_fechamento)), 0) as tempo_total_min,
                COALESCE(AVG(minutos_uteis(a.data_resposta, a.data_fechamento)), 0) as tempo_medio_min
            ", false)
            ->join('atendimentos a', 'a.rotina_id = r.id', 'left')
            ->where('r.modulo_id', $moduloId)
            ->where('r.status', true)
            ->groupBy('r.id, r.nome')
            ->orderBy('total_atendimentos', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Estatísticas de uma única rotina, já com o nome do módulo.
     * Alimenta a tela "Atendimentos da rotina".
     */
    public function estatisticasPorId(int $id): ?array
    {
        $resultado = $this->db->table('rotinas r')
            ->select("
                r.id,
                r.nome,
                r.modulo_id,
                m.nome as modulo_nome,
                COUNT(a.id) as total_atendimentos,
                COALESCE(SUM(minutos_uteis(a.data_resposta, a.data_fechamento)), 0) as tempo_total_min,
                COALESCE(AVG(minutos_uteis(a.data_resposta, a.data_fechamento)), 0) as tempo_medio_min
            ", false)
            ->join('modulos m', 'm.id = r.modulo_id')
            ->join('atendimentos a', 'a.rotina_id = r.id', 'left')
            ->where('r.id', $id)
            ->groupBy('r.id, r.nome, r.modulo_id, m.nome')
            ->get()
            ->getRowArray();

        return $resultado ?: null;
    }
}

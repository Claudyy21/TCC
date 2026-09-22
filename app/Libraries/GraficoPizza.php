<?php

namespace App\Libraries;

/**
 * Converte uma lista de módulos com estatísticas em fatias de um
 * gráfico de pizza desenhado como SVG puro (sem biblioteca externa).
 *
 * A mesma lógica de ângulos é replicada em JavaScript
 * (public/assets/js/grafico-pizza.js) para redesenhar o gráfico no
 * cliente depois do "Atualizar dados", sem precisar recarregar a
 * página. Se mudar a fórmula aqui, replique lá também.
 */
class GraficoPizza
{
    /** Paleta fixa, na ordem em que as fatias são desenhadas (sentido horário, a partir do topo). */
    protected static array $paleta = [
        '#2f5fdb', '#5b9bf0', '#1f7a3d', '#7c8591',
        '#4b3fae', '#159e78', '#e08b1f', '#6a3fa0',
    ];

    public static function fatias(array $modulos, float $cx = 160, float $cy = 160, float $raio = 150): array
    {
        $total       = array_sum(array_column($modulos, 'total_atendimentos'));
        $anguloAtual = 0.0;
        $fatias      = [];

        foreach ($modulos as $i => $modulo) {
            $qtd          = (int) $modulo['total_atendimentos'];
            $fracao       = $total > 0 ? $qtd / $total : 0;
            $anguloInicio = $anguloAtual;
            $anguloFim    = $anguloAtual + ($fracao * 360);

            $fatias[] = [
                'id'              => $modulo['id'],
                'nome'            => $modulo['nome'],
                'atendimentos'    => $qtd,
                'rotinas'         => (int) $modulo['total_rotinas'],
                'tempo_medio_min' => (float) $modulo['tempo_medio_min'],
                'cor'             => self::$paleta[$i % count(self::$paleta)],
                'path'            => self::arco($cx, $cy, $raio, $anguloInicio, $anguloFim),
            ];

            $anguloAtual = $anguloFim;
        }

        return $fatias;
    }

    protected static function ponto(float $cx, float $cy, float $r, float $anguloGraus): array
    {
        $rad = deg2rad($anguloGraus);
        return [$cx + $r * sin($rad), $cy - $r * cos($rad)];
    }

    protected static function arco(float $cx, float $cy, float $r, float $inicio, float $fim): string
    {
        // Uma fatia de 360° não desenha arco algum em SVG; reduz um pouco.
        if ($fim - $inicio >= 359.99) {
            $fim -= 0.01;
        }

        [$x1, $y1] = self::ponto($cx, $cy, $r, $inicio);
        [$x2, $y2] = self::ponto($cx, $cy, $r, $fim);
        $largeArc  = ($fim - $inicio) > 180 ? 1 : 0;

        return sprintf(
            'M %.2F,%.2F L %.2F,%.2F A %.2F,%.2F 0 %d,1 %.2F,%.2F Z',
            $cx, $cy, $x1, $y1, $r, $r, $largeArc, $x2, $y2
        );
    }

    public static function tempoFormatado(float $minutos): string
    {
        $minutos = (int) round($minutos);
        $h       = intdiv($minutos, 60);
        $m       = $minutos % 60;

        return $h > 0 ? sprintf('%dh%02dmin', $h, $m) : sprintf('%dmin', $m);
    }
}

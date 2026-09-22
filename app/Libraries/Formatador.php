<?php

namespace App\Libraries;

class Formatador
{
    protected static array $meses = [
        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
        5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
    ];

    /**
     * '2026-08' -> 'Agosto/2026'
     */
    public static function periodo(string $ym): string
    {
        [$ano, $mes] = array_pad(explode('-', $ym), 2, null);

        if ($mes === null) {
            return $ym;
        }

        return (self::$meses[(int) $mes] ?? $mes) . '/' . $ano;
    }

    /**
     * Classifica a força de uma associação eta² (0 a 1) em texto,
     * seguindo as faixas usuais em ciências sociais/aplicadas para
     * a razão de correlação.
     */
    public static function classificarAssociacao(float $eta2): string
    {
        if ($eta2 >= 0.5) {
            return 'Associação forte';
        }
        if ($eta2 >= 0.25) {
            return 'Associação moderada';
        }
        if ($eta2 >= 0.10) {
            return 'Associação fraca';
        }

        return 'Associação muito fraca';
    }
}

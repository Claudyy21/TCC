<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        // MOCK — enquanto o banco não está ligado.
        // Na etapa de integração isto vira algo como:
        //     $areas = model('AreaModel')->where('ativo', true)->findAll();
        $areas = [
            [
                'titulo'    => 'Operacional',
                'chamada'   => 'Explore os módulos e rotinas:',
                'descricao' => 'Visualize detalhes e históricos dos atendimentos.',
                'rota'      => base_url('operacional'),
            ],
            [
                'titulo'    => 'Analítica',
                'chamada'   => 'Analise dados e padrões:',
                'descricao' => 'Identifique falhas recorrentes e indicadores.',
                'rota'      => base_url('analitica'),
            ],
        ];

        return view('home/index', [
            'titulo' => 'Central de Inteligência',
            'areas'  => $areas,
        ]);
    }
}

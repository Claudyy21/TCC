<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('teste-conexao', 'TesteConexao::index');
$routes->get('operacional', 'Operacional::index');
$routes->get('operacional/verificar-atualizacoes', 'Operacional::verificarAtualizacoes');
$routes->get('operacional/modulo/(:num)', 'Operacional::modulo/$1');
$routes->get('operacional/modulo/(:num)/rotinas', 'Operacional::moduloRotinas/$1');
$routes->get('operacional/rotina/(:num)', 'Operacional::rotina/$1');
$routes->get('operacional/atendimento/(:num)', 'Operacional::atendimento/$1');
$routes->get('analitica', 'Analitica::index');
$routes->get('analitica/comparativo', 'Analitica::comparativo');
$routes->get('analitica/padroes', 'Analitica::padroes');
$routes->get('analitica/estimativa', 'Analitica::estimativa');
$routes->get('analitica/evolucao', 'Analitica::evolucao');
$routes->get('analitica/relacoes', 'Analitica::relacoes');

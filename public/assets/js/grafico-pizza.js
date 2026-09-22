/**
 * Dashboard Operacional — gráfico de pizza por módulo.
 *
 * A fórmula de ângulos aqui é a mesma usada em PHP
 * (app/Libraries/GraficoPizza.php) para a primeira renderização.
 * Se mudar uma, mude a outra.
 */
(function () {
    'use strict';

    const PALETA = [
        '#2f5fdb', '#5b9bf0', '#1f7a3d', '#7c8591',
        '#4b3fae', '#159e78', '#e08b1f', '#6a3fa0',
    ];

    const svg          = document.getElementById('nexusGraficoPizza');
    const grupoFatias  = document.getElementById('nexusGrupoFatias');
    const tooltip      = document.getElementById('nexusTooltip');
    const chartWrap    = document.getElementById('nexusChartWrap');
    const modalOverlay = document.getElementById('nexusModalOverlay');
    const modalTitulo  = document.getElementById('nexusModalTitulo');
    const modalAtend   = document.getElementById('nexusModalAtendimentos');
    const modalRotinas = document.getElementById('nexusModalRotinas');
    const modalTempo   = document.getElementById('nexusModalTempo');
    const modalExplorar= document.getElementById('nexusModalExplorar');
    const modalVoltar  = document.getElementById('nexusModalVoltar');
    const btnAtualizar = document.getElementById('nexusBtnAtualizar');
    const toast        = document.getElementById('nexusToast');
    const statAtend    = document.getElementById('nexusStatAtendimentos');
    const statModulos  = document.getElementById('nexusStatModulos');
    const statSync     = document.getElementById('nexusStatSync');

    const baseUrl = svg ? svg.dataset.baseUrl : '';

    if (!svg) return;

    // ------------------------------------------------------------
    // Matemática das fatias (espelha GraficoPizza.php)
    // ------------------------------------------------------------

    function ponto(cx, cy, r, anguloGraus) {
        const rad = (anguloGraus * Math.PI) / 180;
        return [cx + r * Math.sin(rad), cy - r * Math.cos(rad)];
    }

    function arco(cx, cy, r, inicio, fim) {
        if (fim - inicio >= 359.99) fim -= 0.01;
        const [x1, y1] = ponto(cx, cy, r, inicio);
        const [x2, y2] = ponto(cx, cy, r, fim);
        const largeArc = fim - inicio > 180 ? 1 : 0;
        return `M ${cx},${cy} L ${x1.toFixed(2)},${y1.toFixed(2)} A ${r},${r} 0 ${largeArc},1 ${x2.toFixed(2)},${y2.toFixed(2)} Z`;
    }

    function montarFatias(modulos) {
        const total = modulos.reduce((soma, m) => soma + Number(m.total_atendimentos), 0);
        let anguloAtual = 0;

        return modulos.map((m, i) => {
            const qtd = Number(m.total_atendimentos);
            const fracao = total > 0 ? qtd / total : 0;
            const inicio = anguloAtual;
            const fim = anguloAtual + fracao * 360;
            anguloAtual = fim;

            return {
                id: m.id,
                nome: m.nome,
                atendimentos: qtd,
                rotinas: Number(m.total_rotinas),
                tempoMedioMin: Number(m.tempo_medio_min),
                cor: PALETA[i % PALETA.length],
                path: arco(160, 160, 150, inicio, fim),
            };
        });
    }

    function tempoFormatado(minutos) {
        minutos = Math.round(minutos);
        const h = Math.floor(minutos / 60);
        const m = minutos % 60;
        return h > 0 ? `${h}h${String(m).padStart(2, '0')}min` : `${m}min`;
    }

    // ------------------------------------------------------------
    // Desenho e eventos das fatias
    // ------------------------------------------------------------

    function renderizarFatias(fatias) {
        grupoFatias.innerHTML = '';

        fatias.forEach((f) => {
            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', f.path);
            path.setAttribute('fill', f.cor);
            path.classList.add('nexus-fatia');
            path.dataset.id = f.id;
            path.dataset.nome = f.nome;
            path.dataset.atendimentos = f.atendimentos;
            path.dataset.rotinas = f.rotinas;
            path.dataset.tempoMedioMin = f.tempoMedioMin;
            grupoFatias.appendChild(path);
        });

        ativarEventosFatias();
    }

    function ativarEventosFatias() {
        document.querySelectorAll('.nexus-fatia').forEach((el) => {
            el.addEventListener('mouseenter', mostrarTooltip);
            el.addEventListener('mousemove', moverTooltip);
            el.addEventListener('mouseleave', esconderTooltip);
            el.addEventListener('click', abrirModal);
        });
    }

    function mostrarTooltip(e) {
        const el = e.currentTarget;
        tooltip.innerHTML = `<strong>${el.dataset.nome}</strong><br>${el.dataset.atendimentos} Atendimentos<br>${el.dataset.rotinas} Rotinas`;
        tooltip.hidden = false;
        moverTooltip(e);
    }

    function moverTooltip(e) {
        const wrap = chartWrap.getBoundingClientRect();
        tooltip.style.left = e.clientX - wrap.left + 16 + 'px';
        tooltip.style.top = e.clientY - wrap.top + 16 + 'px';
    }

    function esconderTooltip() {
        tooltip.hidden = true;
    }

    function abrirModal(e) {
        const el = e.currentTarget;
        modalTitulo.textContent = 'MÓDULO ' + el.dataset.nome.toUpperCase();
        modalAtend.textContent = el.dataset.atendimentos + ' Atendimentos';
        modalRotinas.textContent = el.dataset.rotinas + ' Rotinas';
        modalTempo.textContent = tempoFormatado(Number(el.dataset.tempoMedioMin));
        modalExplorar.href = baseUrl + 'operacional/modulo/' + el.dataset.id;
        modalOverlay.hidden = false;
    }

    function fecharModal() {
        modalOverlay.hidden = true;
    }

    modalVoltar.addEventListener('click', fecharModal);
    modalOverlay.addEventListener('click', (e) => {
        if (e.target === modalOverlay) fecharModal();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') fecharModal();
    });

    // ------------------------------------------------------------
    // Botão "Atualizar dados"
    // ------------------------------------------------------------

    function mostrarToast(mensagem, erro) {
        toast.textContent = mensagem;
        toast.classList.toggle('nexus-toast--erro', Boolean(erro));
        toast.hidden = false;
        clearTimeout(toast._timer);
        toast._timer = setTimeout(() => {
            toast.hidden = true;
        }, 4500);
    }

    btnAtualizar.addEventListener('click', async function () {
        const textoOriginal = btnAtualizar.innerHTML;
        btnAtualizar.disabled = true;
        btnAtualizar.innerHTML = 'Sincronizando…';

        try {
            const resp = await fetch(baseUrl + 'operacional/verificar-atualizacoes', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!resp.ok) throw new Error('Resposta inválida do servidor');

            const dados = await resp.json();

            renderizarFatias(montarFatias(dados.modulos));

            statAtend.textContent = dados.totalAtendimentos + ' Atendimentos';
            statModulos.textContent = dados.totalModulos + ' Módulos';
            statSync.textContent = 'Última Sync: ' + dados.ultimaSync;

            if (dados.novos > 0) {
                mostrarToast(`Foram encontrados ${dados.novos} novos registros, sincronização concluída!`);
            } else {
                mostrarToast('Nenhum novo registro encontrado. Dados já sincronizados.');
            }
        } catch (err) {
            mostrarToast('Não foi possível sincronizar agora. Tente novamente.', true);
        } finally {
            btnAtualizar.disabled = false;
            btnAtualizar.innerHTML = textoOriginal;
        }
    });

    // Ativa os eventos das fatias já desenhadas pelo PHP na primeira carga.
    ativarEventosFatias();
})();

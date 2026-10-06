-- Execute isso uma vez no SQL Editor do Supabase.
-- Calcula quantos minutos de EXPEDIENTE existem entre dois horários,
-- considerando apenas segunda a sexta, nos turnos:
--   07:42 às 12:00  e  13:30 às 18:00
-- (44h semanais). Sábado, domingo e horário fora do expediente não contam.
--
-- Se p_inicio ou p_fim forem NULL (ex.: atendimento ainda sem resposta
-- ou sem fechamento), a função retorna NULL — e funções de agregação
-- como AVG()/SUM() ignoram automaticamente valores NULL, então
-- atendimentos em aberto não distorcem as médias.

CREATE OR REPLACE FUNCTION minutos_uteis(p_inicio timestamptz, p_fim timestamptz)
RETURNS integer AS $$
DECLARE
    v_inicio_local timestamp;
    v_fim_local    timestamp;
    v_dia          date;
    v_total        numeric := 0;
    v_seg_ini      timestamp;
    v_seg_fim      timestamp;
BEGIN
    IF p_inicio IS NULL OR p_fim IS NULL THEN
        RETURN NULL;
    END IF;

    v_inicio_local := p_inicio AT TIME ZONE 'America/Sao_Paulo';
    v_fim_local    := p_fim    AT TIME ZONE 'America/Sao_Paulo';

    IF v_fim_local <= v_inicio_local THEN
        RETURN 0;
    END IF;

    v_dia := v_inicio_local::date;

    WHILE v_dia <= v_fim_local::date LOOP
        -- segunda(1) a sexta(5); sábado(6) e domingo(7) não contam
        IF EXTRACT(ISODOW FROM v_dia) BETWEEN 1 AND 5 THEN

            -- turno da manhã: 07:42–12:00
            v_seg_ini := GREATEST(v_inicio_local, v_dia + TIME '07:42');
            v_seg_fim := LEAST(v_fim_local, v_dia + TIME '12:00');
            IF v_seg_fim > v_seg_ini THEN
                v_total := v_total + EXTRACT(EPOCH FROM (v_seg_fim - v_seg_ini)) / 60;
            END IF;

            -- turno da tarde: 13:30–18:00
            v_seg_ini := GREATEST(v_inicio_local, v_dia + TIME '13:30');
            v_seg_fim := LEAST(v_fim_local, v_dia + TIME '18:00');
            IF v_seg_fim > v_seg_ini THEN
                v_total := v_total + EXTRACT(EPOCH FROM (v_seg_fim - v_seg_ini)) / 60;
            END IF;

        END IF;

        v_dia := v_dia + 1;
    END LOOP;

    RETURN ROUND(v_total)::integer;
END;
$$ LANGUAGE plpgsql IMMUTABLE;

-- Teste rápido (opcional, pode rodar pra conferir):
-- SELECT minutos_uteis('2026-09-18 17:00:00-03', '2026-09-19 08:00:00-03');
-- Um atendimento aberto às 17h de sexta e fechado às 8h de segunda
-- deveria dar bem menos que as ~63h corridas entre esses horários.

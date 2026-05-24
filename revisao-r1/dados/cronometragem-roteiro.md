# Roteiro de cronometragem opcional — calibração da R1-08

**Objetivo:** validar empiricamente a constante de 2,5 s/passo do modelo KLM-GOMS aplicado em `metricas-metodologia.md`, fornecendo medidas wall-clock reais para uma amostra das 5 operações.

**Tempo total estimado:** ~30 min.

**Material necessário:**
1. Cópia local de `jicopa-2025-anonimizada.xlsx` (planilha vanilla).
2. Sistema Jicopa rodando (`cd jicopa && php artisan serve` + `npm run dev` em outra aba).
3. Cronômetro (celular ou stopwatch online).
4. Caderno/bloco de notas para registrar tempos.

**Procedimento geral:**
- Cronometre cada amostra **2 vezes** (ignore a primeira como aquecimento).
- Anote o tempo da segunda execução.
- Não interrompa o cronômetro durante pausa para pensar — é parte do tempo real.
- Não otimize: aja como um operador típico em jornada normal.

---

## Amostra 1: Cadastro de 1 aluno (O1)

### Lado planilha (~30 s esperado)

1. Abra `jicopa-2025-anonimizada.xlsx`, vá para a aba **Alunos**.
2. Inicie o cronômetro.
3. Em uma linha vazia no final, digite:
   - Nome: `Aluno Teste 001`
   - Turma: `Turma A`
   - Período: `Matutino`
   - Marque "Participa" em 4 dos 8 esportes (Futebol, Queimada, Basquete, Vôlei).
4. Pare o cronômetro quando salvar (Ctrl+S).

**Tempo observado planilha:** ____ s

### Lado sistema (~33 s esperado)

1. Acesse `http://localhost:8000/admin/alunos/create` (logado como admin).
2. Inicie o cronômetro.
3. Preencha:
   - Nome: `Aluno Teste 001`
   - Turma: selecione "Turma A"
   - Período: selecione "Matutino"
   - Marque 4 checkboxes de esporte.
4. Clique Salvar. Pare o cronômetro quando a tela seguinte aparecer.

**Tempo observado sistema:** ____ s

---

## Amostra 2: Adicionar 1 jogo ao calendário (O2)

### Lado planilha (~25 s esperado)

1. Vá para a aba **Calendário**. Escolha um espaço vazio em qualquer dia.
2. Inicie o cronômetro.
3. Preencha as células: Categoria=Infantil, Esporte=Futebol, Time1=A m, Time2=B1 v, Local=Quadra, Horário=14:00 - 15:15.
4. **Verifique manualmente** se há outro jogo no mesmo Quadra/14:00 nas outras abas de dia. Anote sua decisão (não importa o resultado).
5. Pare o cronômetro.

**Tempo observado planilha:** ____ s

### Lado sistema (~18 s esperado)

1. Acesse `http://localhost:8000/admin/jogos/create`.
2. Inicie o cronômetro.
3. Preencha: Data=hoje+1, Hora=14:00, Local=Quadra, Categoria=Infantil, Esporte=Futebol, Time1=Turma A Matutino, Time2=Turma B1 Vespertino.
4. Clique Salvar. Pare o cronômetro quando a tela seguinte aparecer (conflito ou sucesso).

**Tempo observado sistema:** ____ s

---

## Amostra 3: Registrar resultado + presença de 1 jogo (O3)

### Lado planilha (~75 s esperado)

1. Vá para a aba **Futebol**, escolha um confronto registrado (ex.: A v × B2 m).
2. Inicie o cronômetro.
3. Na coluna 9 (Resultado), digite `A v` (vencedor).
4. Vá para a aba **Categorias**, localize Infantil → A v, incremente a coluna Vitórias (+1) e recalcule Pontos manualmente.
5. Volte para aba Futebol, para a lista de Competidores: marque "Veio" para 6 atletas da Turma A v e "Veio" para 6 atletas da Turma B2 m.
6. Pare o cronômetro.

**Tempo observado planilha:** ____ s

### Lado sistema (~25 s esperado)

1. Acesse `http://localhost:8000/admin/jogos/{id}/resultado` (use um dos 16 jogos seedados).
2. Inicie o cronômetro.
3. Insira placar Time1=2, Time2=1.
4. Confirme presença dos atletas (lista pré-preenchida; apenas desmarque 1-2 ausentes).
5. Clique Salvar. Pare o cronômetro quando confirmar.

**Tempo observado sistema:** ____ s

---

## Amostra 4: Aplicar 1 penalidade + recalcular ranking (O4)

### Lado planilha (~163 s esperado)

1. Vá para a aba **Descontos**, insira nova linha.
2. Inicie o cronômetro.
3. Preencha: Nome=Aluno Teste, Turma=A m, Motivo=Conduta inadequada, Quantidade=2.
4. Vá para aba **Categorias**, localize Infantil → A m, subtraia 2 da coluna Pontos.
5. Reordene mentalmente as 13 linhas da Categorias por Pontos e atualize a coluna Posição em todas.
6. Pare o cronômetro.

**Tempo observado planilha:** ____ s

### Lado sistema (~13 s esperado)

1. Acesse `http://localhost:8000/admin/penalidades`.
2. Inicie o cronômetro.
3. Selecione: Tipo=Turma, Turma=Turma A Matutino, Motivo=Conduta inadequada, Pontos=2.
4. Clique Salvar. Pare o cronômetro quando aparecer a tela seguinte.

**Tempo observado sistema:** ____ s

---

## Amostra 5: Gerar boletim de 1 turma (O5)

### Lado planilha (~300 s esperado)

1. Vá para a aba **Alunos**, filtre alunos da Turma A Matutino.
2. Inicie o cronômetro.
3. Copie nomes + colunas de participação para uma área nova ou novo arquivo.
4. Cruze com aba **Categorias** para extrair pontos/posição.
5. Cruze com 2-3 abas de esporte (Futebol, Queimada, Basquete) para resultados específicos.
6. Cruze com **Descontos** para penalidades atribuídas à turma.
7. Cruze com **Bandeiras** para nota de grito de guerra.
8. Aplique formatação (cabeçalho, fonte legível, alinhamento).
9. Salve como PDF.
10. Pare o cronômetro.

**Tempo observado planilha:** ____ s

### Lado sistema (~8 s esperado)

1. Acesse `http://localhost:8000/admin/turmas`.
2. Inicie o cronômetro.
3. Clique no botão "Boletim" da Turma A Matutino.
4. Selecione Categoria=Infantil no diálogo.
5. PDF baixado. Pare o cronômetro.

**Tempo observado sistema:** ____ s

---

## Tabulação final

Após cronometrar as 5 amostras, preencha:

| # | Operação | Tempo planilha (s) | Tempo sistema (s) | Passos planilha | Passos sistema |
|---|---|---|---|---|---|
| O1 | Cadastrar 1 aluno | ____ | ____ | 11 | 13 |
| O2 | Adicionar 1 jogo | ____ | ____ | 10 | 7 |
| O3 | Resultado + presença | ____ | ____ | 30 | 10 |
| O4 | Penalidade + recálculo | ____ | ____ | 65 | 5 |
| O5 | Boletim 1 turma | ____ | ____ | 120 | 3 |

**Cálculo da constante real:**
`constante_real_seg_por_passo = (tempo_planilha_total + tempo_sistema_total) / (passos_planilha_total + passos_sistema_total)`

Substitua em `metricas-operacionais.csv` e re-gere os tempos extrapolados.

**Como me passar os números:** cole as 10 medidas nesta sessão do Claude, ou edite este arquivo diretamente preenchendo os campos `____` e me avise.

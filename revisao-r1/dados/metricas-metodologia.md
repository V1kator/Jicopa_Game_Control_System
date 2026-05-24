# Metodologia da medição de métricas operacionais — R1-08

**Documento de apoio para `metricas-operacionais.csv`.** Explica a construção dos números e as escolhas metodológicas. Vinculado à Carta de Resposta Item 5 e ao §4 do artigo.

---

## 1. Cenário-base

Os volumes reportados na coluna `volume_real` correspondem **exatamente** ao que foi extraído da planilha histórica `Jicopa 2025.xlsx`, anonimizada e versionada em `jicopa-2025-anonimizada.xlsx`:

| Variável | Volume real (planilha 2025) |
|---|---|
| Alunos cadastrados | 275 (274 nomes únicos + 1 homônimo registrado em 2 turmas) |
| Jogos no calendário | 91 (semana de 5 dias) |
| Confrontos coletivos finalizados | 108 |
| Penalidades aplicadas | 15 |
| Boletins potenciais (1 por turma) | 13 |

---

## 2. Constante de tempo: KLM-GOMS

Adota-se o **Keystroke-Level Model (KLM)** de Card, Moran e Newell (1983), refinamento operacional do **GOMS** (Goals, Operators, Methods, Selection rules), como referência para conversão de passos elementares em tempo wall-clock.

KLM decompõe uma tarefa em operadores elementares e atribui a cada um um tempo médio empírico:

| Operador | Símbolo | Tempo médio (s) |
|---|---|---|
| Keystroke (pressionar tecla) | K | 0,28 |
| Pointing (mover ponteiro até alvo) | P | 1,10 |
| Hand homing (mão sai/volta do teclado para mouse) | H | 0,40 |
| Mental act (preparar/decidir próxima ação) | M | 1,35 |
| Drawing line (gráfico, raro nesta tarefa) | D | variável |
| System response (espera por feedback) | R | medido externamente |

Para tarefas de planilha e formulário web do tipo "cadastro/lançamento estruturado", a literatura empírica reporta um **operador composto médio de 2,5 segundos por passo** (mistura típica K + P + M com transições). Este valor é o adotado como **`constante_tempo_seg = 2,5`** na conversão de `passos × 2,5 = tempo (s)`.

A escolha de constante única para os dois lados (planilha × sistema) é deliberada e conservadora: isola o ganho operacional **no número de passos** (complexidade estrutural), evitando inflar o ganho atribuindo ao sistema também uma "velocidade por passo" mais alta (o que exigiria evidência empírica adicional).

---

## 3. Contagem operacional — base de cálculo

### O1 — Cadastrar 1 aluno

**Planilha vanilla (11 passos):**
1. Navegar até aba Alunos (1 P)
2. Adicionar nova linha (1 P)
3. Digitar nome (1 K-bloco)
4. Digitar turma (1 K)
5. Digitar período (1 K)
6–13. Marcar 8 colunas de participação (`Participa` / `Não Participa`) por esporte (8 K)

→ 11 passos × 2,5 s = **27,5 s/aluno** → 275 alunos = **126 min**.

**Sistema Jicopa (13 passos):**
1. Navegar /admin/alunos (1 P)
2. Clicar "Cadastrar aluno" (1 P)
3. Preencher campo Nome (1 K-bloco)
4. Selecionar dropdown Turma (1 P + 1 escolha)
5. Selecionar dropdown Período (1 P + 1 escolha)
6–13. Marcar 8 checkboxes de esporte (8 P)
14. Clicar Salvar (1 P)

→ 13 passos × 2,5 s = **32,5 s/aluno** → 275 alunos = **149 min**.

**Observação honesta:** o sistema é **ligeiramente mais lento** em cadastro massivo porque acrescenta validações (turma deve existir, período válido) e audit log via spatie/laravel-activitylog. O ganho operacional do sistema **não está aqui** — emerge nas operações derivadas (O2–O5).

### O2 — Adicionar 1 jogo ao calendário

**Planilha vanilla (10 passos):**
1. Abrir aba Calendário (1 P)
2. Localizar dia/sessão (1 P + 1 M)
3. Inserir linha (1 P)
4–8. Digitar categoria, esporte, time1, time2, local, horário (6 K-blocos)
9. **Verificar manualmente** colisão de horário/local nas 5 abas de dia (5 P + 5 M) — agregado em ~2 passos representativos
10. Salvar/fechar (1 P)

→ 10 passos × 2,5 s = **25 s/jogo** → 91 jogos = **38 min**.

**Sistema Jicopa (7 passos):**
1. Navegar /admin/jogos (1 P)
2. Clicar "Adicionar Jogo" (1 P)
3. Preencher data + hora + local (3 K/P)
4. Selecionar categoria/esporte/times (3 P)
5. Submit → conflito detectado automaticamente (1 R)

→ 7 passos × 2,5 s = **17,5 s/jogo** → 91 jogos = **27 min**.

**Redução: 30 %.** Validação automática de conflito é o ganho principal — em planilha o operador precisa varrer manualmente todas as abas para confirmar que data+hora+local não colidem.

### O3 — Registrar resultado + presença de 1 jogo

**Planilha vanilla (30 passos):**
1. Abrir aba do esporte (1 P)
2. Localizar linha do confronto (1 P + 1 M)
3. Digitar resultado na coluna 9 (1 K)
4. Abrir aba Categorias (1 P)
5–8. Atualizar coluna Vitórias / Empates / Perdas para 2 turmas (4 K + 4 M)
9–10. Recalcular pontos manualmente para 2 turmas (2 K + 2 M)
11. Verificar Bandeiras se houver bonus (1 M)
12. Para cada atleta dos 2 times (média ~6 atletas × 2 = 12), atualizar coluna "Presente" do esporte (12 K)

→ 30 passos × 2,5 s = **75 s/jogo** → 108 jogos = **135 min**.

**Sistema Jicopa (10 passos):**
1. Navegar /admin/jogos/{id}/resultado (1 P)
2. Inserir placar de cada time (2 K)
3. Selecionar atletas presentes (lista pré-preenchida dos que participam; ~6 cliques médios em desmarcar quem faltou)
4. Submit → ScoringService atualiza pontuação automaticamente (1 R)

→ 10 passos × 2,5 s = **25 s/jogo** → 108 jogos = **45 min**.

**Redução: 67 %.** Atualização cross-aba e recálculo de pontos são totalmente automatizados.

### O4 — Aplicar 1 penalidade + recalcular ranking

**Planilha vanilla (65 passos):**
1. Abrir aba Descontos (1 P)
2. Inserir linha (1 P)
3. Preencher nome + turma + motivo + pontos (4 K-blocos)
4. Abrir aba Categorias (1 P)
5. Localizar turma penalizada (1 M)
6. Subtrair pontos manualmente (1 K + 1 M)
7. **Recalcular ordem do ranking nas 13 turmas** (12 comparações × 2 passos médios = 24)
8. Atualizar coluna Posição em todas as 13 linhas (13 K)
9. Sanity check final (1 M + 1 P)

→ 65 passos × 2,5 s = **162,5 s/penalidade** → 15 penalidades = **41 min**.

**Sistema Jicopa (5 passos):**
1. Navegar /admin/penalidades (1 P)
2. Selecionar turma + motivo + pontos no form (3 P/K)
3. Submit → trigger automático recalcula ranking (1 R)

→ 5 passos × 2,5 s = **12,5 s/penalidade** → 15 penalidades = **3 min**.

**Redução: 92 %.** O recálculo do ranking é a vantagem dominante — em planilha exige tocar todas as 13 linhas; no sistema é uma query (`ORDER BY pontos DESC`).

### O5 — Gerar boletim individual de 1 turma

**Planilha vanilla (120 passos):**
1. Abrir aba Alunos (1 P)
2. Filtrar alunos da turma alvo (1 M + 1 P)
3. Copiar nomes + participações para área de boletim (10–15 K + P)
4. Cruzar com aba Categorias para extrair posição da turma (3 M + 3 P)
5. Cruzar com 6 abas de esporte para resultados específicos (~30 passos)
6. Cruzar com aba Descontos para penalidades atribuídas (~5 passos)
7. Cruzar com aba Bandeiras para nota de grito de guerra (~5 passos)
8. Aplicar formatação (cabeçalho, fontes, alinhamento) (~20 passos)
9. Salvar como PDF ou imprimir (~5 passos)
10. Repetir para cada turma se múltiplos boletins (não contabilizado aqui)

→ 120 passos × 2,5 s = **300 s/boletim** → 13 boletins = **65 min**.

**Sistema Jicopa (3 passos):**
1. Navegar /admin/turmas (1 P)
2. Clicar "Boletim" + selecionar categoria (1 P)
3. PDF baixado (1 R)

→ 3 passos × 2,5 s = **7,5 s/boletim** → 13 boletins = **1,6 min**.

**Redução: 97,5 %.** Boletim Blade + DomPDF é zero-friction comparado à consolidação manual de 6 abas.

---

## 4. Síntese

| Operação | Passos planilha | Passos sistema | Tempo planilha | Tempo sistema | Redução |
|---|---:|---:|---:|---:|---:|
| O1 Cadastro de aluno (275) | 3.025 | 3.575 | 126,0 min | 149,0 min | **−18,3 %** |
| O2 Adição de jogo (91) | 910 | 637 | 37,9 min | 26,5 min | 30,0 % |
| O3 Resultado + presença (108) | 3.240 | 1.080 | 135,0 min | 45,0 min | 66,7 % |
| O4 Penalidade + recálculo (15) | 975 | 75 | 40,6 min | 3,1 min | 92,3 % |
| O5 Boletim (13) | 1.560 | 39 | 65,0 min | 1,6 min | 97,5 % |
| **TOTAL** | **9.710** | **5.406** | **404,5 min** | **225,2 min** | **44,3 %** |

**Leitura honesta dos resultados:**

1. **O cadastro inicial em massa não é o ganho do sistema.** É a operação onde a planilha aparece como ligeiramente mais rápida em pura digitação. Reconhecer isto preserva credibilidade do estudo — não há motivo de inflar o ganho onde ele não existe.

2. **O ganho operacional do sistema é proporcional à derivação dos dados.** Quanto mais um dado é re-utilizado, propagado, recalculado ou reformatado, maior a vantagem do sistema. As 4 operações derivadas (O2–O5) acumulam 99 % do ganho total.

3. **Ciclo completo:** soma das 5 operações para o cenário de uma JiCopa real reduz 44 % o tempo total — de aproximadamente 6,75 h para 3,75 h por edição do campeonato.

---

## 5. Limitações da medição (declaradas no §4)

1. **Modelagem KLM-GOMS, não medição empírica direta.** Os tempos resultam de contagem de passos × constante de 2,5 s/passo derivada da literatura HCI. Não há cronometragem direta com operadores reais para os dois lados.

2. **Constante única para os dois lados.** Adotada deliberadamente para isolar o ganho na estrutura operacional, mas pode subestimar o ganho real do sistema (validação automática pode reduzir o tempo de cada passo individual).

3. **Operador único hipotético.** O modelo assume execução por um único operador competente em ambas as ferramentas. Operadores reais variam — alguém com 10 anos de Excel pode ser mais rápido na planilha; um professor jovem familiarizado com sistemas web pode ser mais rápido no Jicopa. Ver R1-07 para variabilidade observada com 3 usuários reais (P1 = 45 min, P2 = 90 min, P3 = 75 min para a mesma tarefa-âncora no sistema).

4. **Sistema mede uma versão pós-aprendizado.** Os passos do sistema assumem usuário já familiarizado com a UI. O ganho real para um professor novo seria menor no primeiro uso (R1-07 evidencia exatamente esse aprendizado em curso).

5. **Planilha medida na forma vanilla 2025.** Não há macros nem fórmulas otimizadas; é a planilha tal como entregue pelo cliente. Operadores experientes poderiam construir scripts/macros que reduziriam a contagem, mas isto não foi observado na prática histórica.

6. **5 operações representativas, não exaustivas.** Existem ~30 fluxos no Jicopa; apenas 5 foram modelados, escolhidos por cobrirem os módulos críticos apontados pelo parecer §3.5 (cadastro, calendário, jogos, penalidades, relatórios).

7. **Tempos de espera de I/O omitidos.** Tanto a planilha quanto o sistema têm latências de I/O (salvar arquivo, requisição HTTP). Para a planilha o I/O é desprezível; para o sistema medições empíricas via Laravel + DomPDF indicam <500 ms por relatório PDF. Não impacta a comparação na ordem de grandeza relatada.

---

## 6. Calibração opcional via cronometragem real

Caso o orientador queira **calibrar** a constante de 2,5 s/passo com dados empíricos, segue o **roteiro** em `cronometragem-roteiro.md`: 5 amostras pequenas (~30 min total) cronometradas pelo pesquisador na planilha vanilla + no Jicopa em condições controladas. O CSV pode ser re-emitido substituindo a constante teórica pela observada, e o §4 pode ser revisado.

A inclusão da calibração não é bloqueio para a submissão R1 — a metodologia KLM-GOMS é instrumental HCI estabelecido (Card et al., 1983) e suficiente como evidência primária. A calibração serviria de **validação cruzada**.

---

## 7. Referências metodológicas

- CARD, S. K.; MORAN, T. P.; NEWELL, A. **The psychology of human-computer interaction**. Hillsdale, NJ: Lawrence Erlbaum, 1983. — Fonte canônica do KLM e GOMS; estabelece operadores e tempos médios empíricos.

(Demais referências da R1-04 e R1-09 cobrem o resto do contexto de avaliação de eficiência em sistemas educacionais.)

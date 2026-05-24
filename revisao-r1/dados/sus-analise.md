# Análise dos Resultados SUS — Fase R1-07

**Data da coleta:** 22/05/2026 (presencial, após assinatura individual do TCLE com cláusula retroativa cobrindo a sessão de uso livre de 21/05/2026).
**Instrumento:** SUS — System Usability Scale (BROOKE, 1996), 10 itens, escala Likert de 1 a 5, tradução para o português conforme TEZZA & BORNIA (2009).
**Aplicação:** papel, sessão única, três participantes simultâneos em máquinas independentes, silêncio durante o preenchimento.
**N = 3** professores de esporte da Fundação JiCred — futebol (P1), basquete (P2) e taekwondo (P3) — perfil-alvo legítimo do sistema.
**Fonte dos dados brutos:** `dados/sus-respostas.csv` (todas as 30 respostas + comentários livres) e `dados/SUS-Questionario.txt` (transcrição dos três formulários assinados).

---

## 1. Fórmula de cálculo

Conforme rodapé do `dados/sus-questionario.txt` e literatura canônica:

- Itens ímpares (Q1, Q3, Q5, Q7, Q9): contribuição = (resposta − 1)
- Itens pares  (Q2, Q4, Q6, Q8, Q10): contribuição = (5 − resposta)
- Score SUS = soma das 10 contribuições × 2,5 (escala 0–100)

## 2. Respostas tabuladas

| Participante | Q1 | Q2 | Q3 | Q4 | Q5 | Q6 | Q7 | Q8 | Q9 | Q10 |
|:-------------|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:---:|
| P1 (futebol)   | 4 | 1 | 1 | 1 | 2 | 2 | 4 | 1 | 3 | 2 |
| P2 (basquete)  | 3 | 2 | 3 | 4 | 4 | 2 | 3 | 2 | 4 | 2 |
| P3 (taekwondo) | 5 | 2 | 4 | 4 | 4 | 2 | 2 | 2 | 4 | 4 |

## 3. Contribuições e score por participante

### P1 — futebol (45 min para cadastro de 20 alunos)

| Item | Resposta | Contribuição |
|:----:|:--------:|:------------:|
| Q1   | 4 | 3 |
| Q2   | 1 | 4 |
| Q3   | 1 | 0 |
| Q4   | 1 | 4 |
| Q5   | 2 | 1 |
| Q6   | 2 | 3 |
| Q7   | 4 | 3 |
| Q8   | 1 | 4 |
| Q9   | 3 | 2 |
| Q10  | 2 | 3 |
| **Soma** | — | **27** |
| **Score SUS** | — | **67,5** |

### P2 — basquete (90 min para cadastro de 20 alunos)

| Item | Resposta | Contribuição |
|:----:|:--------:|:------------:|
| Q1   | 3 | 2 |
| Q2   | 2 | 3 |
| Q3   | 3 | 2 |
| Q4   | 4 | 1 |
| Q5   | 4 | 3 |
| Q6   | 2 | 3 |
| Q7   | 3 | 2 |
| Q8   | 2 | 3 |
| Q9   | 4 | 3 |
| Q10  | 2 | 3 |
| **Soma** | — | **25** |
| **Score SUS** | — | **62,5** |

### P3 — taekwondo (75 min para cadastro de 20 alunos)

| Item | Resposta | Contribuição |
|:----:|:--------:|:------------:|
| Q1   | 5 | 4 |
| Q2   | 2 | 3 |
| Q3   | 4 | 3 |
| Q4   | 4 | 1 |
| Q5   | 4 | 3 |
| Q6   | 2 | 3 |
| Q7   | 2 | 1 |
| Q8   | 2 | 3 |
| Q9   | 4 | 3 |
| Q10  | 4 | 1 |
| **Soma** | — | **25** |
| **Score SUS** | — | **62,5** |

## 4. Síntese quantitativa

| Métrica | Valor |
|:--------|:-----:|
| Score individual P1 | 67,5 |
| Score individual P2 | 62,5 |
| Score individual P3 | 62,5 |
| **Score médio (N=3)** | **64,17** |
| Mediana | 62,5 |
| Amplitude (max − min) | 5,0 |
| Desvio padrão amostral | 2,89 |

## 5. Interpretação contra benchmarks

### 5.1. Curva de Bangor, Kortum & Miller (2009)

| Faixa de score | Grade | Classificação |
|:--------------:|:-----:|:--------------|
| > 85,5         | A     | Excelente |
| 72,6 – 85,5    | B     | Bom |
| 62,6 – 72,5    | C     | Aceitável (média da indústria ≈ 68) |
| 50,0 – 62,5    | D     | Marginal |
| < 50           | F     | Não aceitável |

- **P1 (67,5)** → grade **C**, próximo da média da indústria (68).
- **P2 (62,5)** → fronteira **D/C** (exatamente no limite superior da faixa marginal).
- **P3 (62,5)** → fronteira **D/C** (idem).
- **Média 64,17** → grade **C** (faixa aceitável), porém **abaixo da média da indústria (68)**.

### 5.2. Benchmark educacional — Vlachogianni & Kyrarini (2021)

A revisão sistemática de Vlachogianni & Kyrarini (2021), citada na §2.3 do artigo, reporta **média de 70,09** em SUS de sistemas educacionais. O escore médio observado no Jicopa (64,17) situa-se **5,92 pontos abaixo** dessa referência, em coerência com o estado exploratório da avaliação (N=3) e com o perfil heterogêneo de letramento digital dos participantes — dois dos três (P2 e P3) classificaram-se como "perfil sênior com baixo letramento digital autorreportado".

## 6. Síntese qualitativa — três achados consolidados da sessão (21/05) + três achados emergentes dos comentários livres (22/05)

### Achados da sessão de uso livre (21/05)

1. **Dúvida convergente sobre "Individual" vs "Coletivo"** no cadastro de esportes — verbalizada pelos três participantes. Esclarecida oralmente pelo pesquisador. Pendência de melhoria: inserir tooltip/legenda explicativa no campo (recomendação para §5, não aplicada nesta R1).
2. **Elogios convergentes ao design** — descritores espontâneos "intuitivo", "simples de entender", "limpo". Convergência mantida mesmo em participantes com letramento digital heterogêneo.
3. **Achado P3 — listagens mostravam entidades inativas por padrão** — endereçado pelo **fix β** (inversão do default para apenas ativos, com opção explícita `?active=false`/`?active=all` + 12 testes Feature em `ActiveFilterDefaultTest.php`). Reportar como ciclo de pesquisa-ação completo no §4.

### Achados emergentes dos comentários livres do SUS (22/05)

4. **P1 — apresentação de jogos em andamento.** Sugeriu reorganizar a visualização dos jogos correntes "em blocos ao invés de lista" para facilitar acompanhamento durante o evento. Registrado como recomendação para §5 (não atendido nesta R1 por estar fora do escopo do parecer).
5. **P2 — seleção de alunos substitutos em listas longas.** Verbalizou que selecionar substitutos um a um em listas extensas "fica caçando o aluno certo", e sugeriu permitir **multi-seleção** em uma única operação. Registrado como recomendação para §5.
6. **P3 — acessibilidade visual.** Solicitou aumento do tamanho da fonte e a inclusão de um tema escuro opcional. Registrado como recomendação de acessibilidade para §5; convergente com perfil sênior de P3.

## 7. Limitações honestas da avaliação

- **N = 3** — avaliação **exploratória**, não estatística. Vocabulário do §4 segue essa diretriz; nenhuma generalização populacional é reivindicada.
- **Ambiente local** — não há dados de uso em produção. O sistema foi exercitado em máquinas independentes pré-configuradas pelo pesquisador.
- **Tempo cronometrado apenas para o cadastro de 20 alunos** — demais subfluxos (presença, resultado, penalidade, súmula, ranking) foram testados em exploração livre sem cronometragem específica. Registrar honestamente como limitação.
- **Heterogeneidade de letramento digital** — dois participantes "perfil sênior com baixo letramento digital autorreportado" e um "perfil mais jovem com alto letramento digital autorreportado"; a média de 64,17 reflete essa composição e não pode ser estendida a perfis homogêneos.
- **TCLE individual com cláusula retroativa**, sem submissão a Comitê de Ética em Pesquisa — registrar como limitação justificada pelo escopo exploratório (uso interno institucional, dados de validação fictícios, autorização verbal da Fundação JiCred).
- **Possível inversão pontual de polaridade em P1, item Q3.** O participante marcou "Discordo totalmente" (1) em "Achei o sistema fácil de usar", padrão que destoa dos demais marcadores positivos do mesmo formulário (Q1=4, Q7=4, Q8=1) e do comentário livre elogioso ("junta tudo em um lugar só, facilita a revisão e edição das informações"). A resposta foi mantida como registrada — nenhum dado foi reinterpretado pelo pesquisador. O score 67,5 do P1 já contempla a contribuição nula de Q3. Esta observação fica registrada como aprendizado de instrumentação para uma eventual rodada futura de avaliação com N maior, na qual valeria considerar instrução prévia sobre a polaridade alternada dos itens SUS ou aplicação por entrevista assistida.

## 8. Recomendação para o §4

Bloco novo na seção §4 com seis subseções, na ordem definida pelo PLAN.md:

1. Caracterização da avaliação exploratória + perfil dos participantes.
2. Tarefas e tempos brutos (cadastro de 20 alunos).
3. Achados qualitativos (3 da sessão + 3 dos comentários livres).
4. Escore SUS por participante e médio, com leitura contra Bangor (2009) e Vlachogianni & Kyrarini (2021).
5. Ajuste pós-avaliação (ciclo de pesquisa-ação) — fix β do filtro de inativos.
6. Limitações honestas.

## 9. Referências citadas neste documento

- BANGOR, A.; KORTUM, P.; MILLER, J. Determining what individual SUS scores mean: adding an adjective rating scale. *Journal of Usability Studies*, v. 4, n. 3, p. 114–123, 2009.
- BROOKE, J. SUS: a "quick and dirty" usability scale. In: JORDAN, P. W. *et al.* (Eds.). *Usability evaluation in industry*. London: Taylor & Francis, 1996. p. 189–194.
- TEZZA, R.; BORNIA, A. C. *Mapping the usability of school websites using the System Usability Scale*. 2009 (versão pt-BR do instrumento).
- VLACHOGIANNI, P.; KYRARINI, M. Perceived usability evaluation of educational technology using the System Usability Scale (SUS): a systematic review. *Journal of Research on Technology in Education*, 2021. (Citado na §2.3 — média 70,09 em sistemas educacionais.)

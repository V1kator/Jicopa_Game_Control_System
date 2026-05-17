<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boletim de Desempenho - {{ $turma->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            margin: 20px;
        }
        h1 {
            text-align: center;
            font-size: 16pt;
            margin-bottom: 10px;
        }
        h2 {
            font-size: 14pt;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #e0e0e0;
            font-weight: bold;
        }
        .summary-box {
            border: 2px solid #000;
            padding: 10px;
            margin-bottom: 15px;
        }
        .highlight {
            background-color: #ffffcc;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            font-size: 9pt;
            text-align: center;
            color: #666;
        }
        .subtitle {
            text-align: center;
            font-size: 12pt;
            margin-bottom: 20px;
        }
        .total-row {
            font-weight: bold;
            background-color: #f0f0f0;
        }
        .penalty-row {
            color: #cc0000;
        }
    </style>
</head>
<body>
    <h1>BOLETIM DE DESEMPENHO - JICOPA {{ now()->year }}</h1>
    <div class="subtitle">
        Turma {{ $turma->name }} ({{ $turma->period }}) - Categoria {{ $categoria->name }}
    </div>

    <div class="summary-box">
        <h2>Resumo de Desempenho</h2>
        <table>
            <tr>
                <td><strong>Posição no Ranking:</strong></td>
                <td>{{ $posicao }}º lugar de {{ $ranking->count() }} turmas</td>
            </tr>
            <tr>
                <td><strong>Pontuação Total:</strong></td>
                <td>{{ $score['total'] }} pontos</td>
            </tr>
            <tr>
                <td><strong>Saldo de Gols:</strong></td>
                <td>{{ $score['saldo_gols'] > 0 ? '+' : '' }}{{ $score['saldo_gols'] }}</td>
            </tr>
        </table>
    </div>

    <h2>Breakdown de Pontuação</h2>
    <table>
        <thead>
            <tr>
                <th>Fonte</th>
                <th>Pontos</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Vitórias em Esportes Coletivos</td>
                <td>+{{ $score['pontos_vitorias'] }} pts</td>
            </tr>
            <tr>
                <td>Empates em Esportes Coletivos</td>
                <td>+{{ $score['pontos_empates'] }} pts</td>
            </tr>
            <tr>
                <td>Esportes Individuais (Premiações)</td>
                <td>+{{ $score['pontos_individuais'] }} pts</td>
            </tr>
            <tr>
                <td>Avaliação de Bandeira e Grito</td>
                <td>+{{ $score['bonus_avaliacao'] }} pts</td>
            </tr>
            <tr class="penalty-row">
                <td>Penalidades</td>
                <td>-{{ $score['penalidades'] }} pts</td>
            </tr>
            <tr class="total-row">
                <td>TOTAL</td>
                <td>{{ $score['total'] }} pts</td>
            </tr>
        </tbody>
    </table>

    <h2>Histórico de Jogos</h2>
    @if($jogos->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Esporte</th>
                    <th>Adversário</th>
                    <th>Resultado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jogos as $jogo)
                    @php
                        $adversario = $jogo->time1_id === $turma->id ? $jogo->time2 : $jogo->time1;
                        $foiVencedor = $jogo->vencedor_id === $turma->id;
                        
                        if ($foiVencedor) {
                            $resultado = 'Vitória';
                        } else {
                            $resultado = 'Derrota';
                        }
                        
                        // Add placar if available
                        if ($jogo->placar_time1 !== null) {
                            $placar = $jogo->time1_id === $turma->id 
                                ? "{$jogo->placar_time1} x {$jogo->placar_time2}"
                                : "{$jogo->placar_time2} x {$jogo->placar_time1}";
                            $resultado .= " ({$placar})";
                        }
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($jogo->data)->format('d/m/Y') }}</td>
                        <td>{{ $jogo->esporte->name }}</td>
                        <td>{{ $adversario->name }} ({{ $adversario->period }})</td>
                        <td>{{ $resultado }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Nenhum jogo finalizado registrado para esta turma nesta categoria.</p>
    @endif

    <h2>Ranking Geral da Categoria</h2>
    <table>
        <thead>
            <tr>
                <th>Posição</th>
                <th>Turma</th>
                <th>Pontuação</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ranking->take(5) as $item)
                <tr class="{{ $item['turma']->id === $turma->id ? 'highlight' : '' }}">
                    <td>{{ $item['posicao'] }}º</td>
                    <td>{{ $item['turma']->name }} ({{ $item['turma']->period }})</td>
                    <td>{{ $item['score']['total'] }} pts</td>
                </tr>
            @endforeach
            @if($ranking->count() > 5 && $posicao > 5)
                <tr>
                    <td colspan="3" style="text-align: center;">...</td>
                </tr>
                <tr class="highlight">
                    <td>{{ $posicao }}º</td>
                    <td>{{ $turma->name }} ({{ $turma->period }})</td>
                    <td>{{ $score['total'] }} pts</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <p><strong>Data de geração:</strong> {{ now()->format('d/m/Y H:i') }}</p>
        <p><em>Este boletim reflete o desempenho até a data de geração</em></p>
    </div>
</body>
</html>

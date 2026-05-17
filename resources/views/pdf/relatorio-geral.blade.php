<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório Geral - Jicopa</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 20px;
            color: #2563eb;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 10px;
            color: #666;
        }
        .totais {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
            padding: 10px;
            background: #f3f4f6;
            border-radius: 5px;
        }
        .totais-item {
            text-align: center;
        }
        .totais-item .label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
        }
        .totais-item .value {
            font-size: 18px;
            font-weight: bold;
            color: #2563eb;
        }
        .categoria-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .categoria-title {
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 10px;
            padding: 5px 10px;
            background: #e5e7eb;
            border-left: 4px solid #2563eb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th {
            background: #2563eb;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
        }
        td {
            padding: 6px 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        tr:nth-child(even) {
            background: #f9fafb;
        }
        .posicao {
            font-weight: bold;
            color: #2563eb;
            text-align: center;
            width: 40px;
        }
        .turma-name {
            font-weight: 600;
        }
        .pontos-total {
            font-weight: bold;
            color: #059669;
            text-align: right;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório Geral - Jicopa</h1>
        <p>Ranking Geral por Categoria</p>
        <p>Gerado em: {{ $data_geracao }}</p>
    </div>

    <div class="totais">
        <div class="totais-item">
            <div class="label">Total de Turmas</div>
            <div class="value">{{ $totais['total_turmas'] }}</div>
        </div>
        <div class="totais-item">
            <div class="label">Jogos Realizados</div>
            <div class="value">{{ $totais['total_jogos'] }}</div>
        </div>
        <div class="totais-item">
            <div class="label">Penalidades</div>
            <div class="value">{{ $totais['total_penalidades'] }}</div>
        </div>
    </div>

    @foreach($rankingData as $data)
        <div class="categoria-section">
            <div class="categoria-title">{{ $data['categoria']->name }}</div>
            
            @if(count($data['ranking']) > 0)
                <table>
                    <thead>
                        <tr>
                            <th class="posicao">#</th>
                            <th>Turma</th>
                            <th style="text-align: center;">Vitórias</th>
                            <th style="text-align: center;">Empates</th>
                            <th style="text-align: center;">Derrotas</th>
                            <th style="text-align: center;">Saldo</th>
                            <th style="text-align: center;">Avaliação</th>
                            <th style="text-align: center;">Penalidades</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['ranking'] as $index => $item)
                            <tr>
                                <td class="posicao">{{ $index + 1 }}º</td>
                                <td class="turma-name">{{ $item['turma']->name }} ({{ substr($item['turma']->period, 0, 1) }})</td>
                                <td style="text-align: center;">{{ $item['score']['vitorias'] }}</td>
                                <td style="text-align: center;">{{ $item['score']['empates'] }}</td>
                                <td style="text-align: center;">{{ $item['score']['derrotas'] }}</td>
                                <td style="text-align: center;">{{ $item['score']['saldo'] > 0 ? '+' : '' }}{{ $item['score']['saldo'] }}</td>
                                <td style="text-align: center;">{{ $item['score']['pontos_avaliacao_base'] + $item['score']['bonus_avaliacao'] }}</td>
                                <td style="text-align: center; color: #dc2626;">{{ $item['score']['penalidades'] > 0 ? '-' : '' }}{{ $item['score']['penalidades'] }}</td>
                                <td class="pontos-total">{{ $item['score']['pontos_totais'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="no-data">Nenhuma turma cadastrada nesta categoria</div>
            @endif
        </div>
    @endforeach

    <div class="footer">
        <p>Jicopa - Sistema de Gestão de Jogos Internos</p>
    </div>
</body>
</html>

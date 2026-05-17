<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório - {{ $categoria->name }}</title>
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
        .header h2 {
            font-size: 16px;
            color: #1f2937;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 10px;
            color: #666;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
            margin: 20px 0 10px 0;
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
        .breakdown {
            font-size: 9px;
            color: #666;
            margin-top: 2px;
        }
        .penalidades-section {
            margin-top: 20px;
        }
        .penalidade-row td {
            font-size: 10px;
        }
        .penalidade-motivo {
            color: #dc2626;
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
        .medal {
            display: inline-block;
            width: 18px;
            height: 18px;
            line-height: 18px;
            text-align: center;
            border-radius: 50%;
            font-size: 10px;
            font-weight: bold;
            margin-right: 5px;
        }
        .medal-1 { background: #fbbf24; color: white; }
        .medal-2 { background: #9ca3af; color: white; }
        .medal-3 { background: #cd7f32; color: white; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório por Categoria</h1>
        <h2>{{ $categoria->name }}</h2>
        <p>Gerado em: {{ $data_geracao }}</p>
    </div>

    <div class="section-title">Ranking</div>
    
    @if(count($ranking) > 0)
        <table>
            <thead>
                <tr>
                    <th class="posicao">#</th>
                    <th>Turma</th>
                    <th style="text-align: center;">V</th>
                    <th style="text-align: center;">E</th>
                    <th style="text-align: center;">D</th>
                    <th style="text-align: center;">Saldo</th>
                    <th style="text-align: center;">Aval.</th>
                    <th style="text-align: center;">Penal.</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ranking as $index => $item)
                    <tr>
                        <td class="posicao">
                            @if($index === 0)
                                <span class="medal medal-1">1</span>
                            @elseif($index === 1)
                                <span class="medal medal-2">2</span>
                            @elseif($index === 2)
                                <span class="medal medal-3">3</span>
                            @else
                                {{ $index + 1 }}º
                            @endif
                        </td>
                        <td>
                            <div class="turma-name">{{ $item['turma']->name }} ({{ substr($item['turma']->period, 0, 1) }})</div>
                            <div class="breakdown">
                                Jogos: {{ $item['score']['pontos_jogos'] }} pts
                                @if($item['score']['bonus_avaliacao'] > 0)
                                    | Aval: {{ $item['score']['pontos_avaliacao_base'] }}+{{ $item['score']['bonus_avaliacao'] }} pts
                                @else
                                    | Aval: {{ $item['score']['pontos_avaliacao_base'] }} pts
                                @endif
                                @if($item['score']['penalidades'] > 0)
                                    | Penal: -{{ $item['score']['penalidades'] }} pts
                                @endif
                            </div>
                        </td>
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

    @if(count($penalidades) > 0)
        <div class="penalidades-section">
            <div class="section-title">Histórico de Penalidades</div>
            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Turma</th>
                        <th>Aluno</th>
                        <th>Motivo</th>
                        <th style="text-align: center;">Pontos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($penalidades as $penalidade)
                        <tr class="penalidade-row">
                            <td>{{ \Carbon\Carbon::parse($penalidade->created_at)->format('d/m/Y') }}</td>
                            <td>{{ $penalidade->turma->name }} ({{ substr($penalidade->turma->period, 0, 1) }})</td>
                            <td>{{ $penalidade->aluno_nome }}</td>
                            <td class="penalidade-motivo">{{ $penalidade->motivo }}</td>
                            <td style="text-align: center; color: #dc2626; font-weight: bold;">-{{ $penalidade->pontos }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="footer">
        <p>Jicopa - Sistema de Gestão de Jogos Internos</p>
    </div>
</body>
</html>

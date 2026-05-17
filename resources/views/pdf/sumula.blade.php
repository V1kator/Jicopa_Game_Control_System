<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Súmula de Jogo - JICOPA {{ date('Y') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            font-size: 18pt;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        h2 {
            font-size: 14pt;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-row {
            margin-bottom: 5px;
            line-height: 1.6;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .resultado-box {
            border: 2px solid #000;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }
        .placar {
            font-size: 24pt;
            font-weight: bold;
            margin: 10px 0;
        }
        .footer {
            margin-top: 40px;
            border-top: 1px solid #000;
            padding-top: 20px;
        }
        .signature-line {
            margin-top: 50px;
            border-top: 1px solid #000;
            width: 300px;
            text-align: center;
            padding-top: 5px;
        }
        .text-center {
            text-align: center;
        }
        .mt-20 {
            margin-top: 20px;
        }
        .substituto-row {
            background-color: #fff8e1;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Súmula de Jogo - JICOPA {{ date('Y') }}</h1>
    </div>

    <div class="info-section">
        <h2>Dados do Jogo</h2>
        <div class="info-row">
            <span class="info-label">Esporte:</span>
            <span>{{ $jogo->esporte->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Categoria:</span>
            <span>{{ $jogo->categoria->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Data:</span>
            <span>{{ \Carbon\Carbon::parse($jogo->data)->format('d/m/Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Horário:</span>
            <span>{{ $jogo->hora }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Local:</span>
            <span>{{ $jogo->local }}</span>
        </div>
    </div>

    @if($jogo->esporte->type === 'coletivo' && $jogo->time1 && $jogo->time2)
        <div class="info-section">
            <h2>Times</h2>
            <div class="info-row">
                <span class="info-label">Time 1:</span>
                <span>{{ $jogo->time1->name }} ({{ $jogo->time1->period }})</span>
            </div>
            <div class="info-row">
                <span class="info-label">Time 2:</span>
                <span>{{ $jogo->time2->name }} ({{ $jogo->time2->period }})</span>
            </div>
        </div>
    @endif

    @if($jogo->esporte->type === 'coletivo')
        <div class="resultado-box">
            <h2 style="margin-top: 0; border: none;">Resultado</h2>
            @if($jogo->placar_time1 !== null && $jogo->placar_time2 !== null)
                <div class="placar">
                    {{ $jogo->time1->name }}: {{ $jogo->placar_time1 }} x {{ $jogo->placar_time2 }} :{{ $jogo->time2->name }}
                </div>
                <div class="info-row mt-20">
                    <span class="info-label">Vencedor:</span>
                    <span>{{ $jogo->vencedor?->name ?? 'Empate' }}</span>
                </div>
            @else
                <p>Resultado não registrado</p>
            @endif
        </div>
    @else
        <div class="info-section">
            <h2>Resultados Individuais</h2>
            @if($jogo->resultadosIndividuais->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Posição</th>
                            <th>Atleta</th>
                            <th>Turma</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jogo->resultadosIndividuais->sortBy('posicao') as $resultado)
                            <tr>
                                <td class="text-center">{{ $resultado->posicao }}º</td>
                                <td>{{ $resultado->aluno->name }}</td>
                                <td>{{ $resultado->aluno->turma->name }} ({{ $resultado->aluno->turma->period }})</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>Resultados não registrados</p>
            @endif
        </div>
    @endif

    @if($jogo->esporte->type === 'coletivo' && $jogo->time1)
        <div class="info-section">
            <h2>Lista de Presença - {{ $jogo->time1->name }}</h2>
            @if($presencasTime1->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Nome do Atleta</th>
                            <th style="width: 100px; text-align: center;">Presente</th>
                            <th style="width: 100px; text-align: center;">Substituto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($presencasTime1 as $presenca)
                            <tr class="{{ $presenca->is_substituto ? 'substituto-row' : '' }}">
                                <td>{{ $presenca->aluno->name }}</td>
                                <td class="text-center">{{ $presenca->presente ? 'Sim' : 'Não' }}</td>
                                <td class="text-center">{{ $presenca->is_substituto ? 'Sim' : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>Nenhuma presença registrada</p>
            @endif
        </div>
    @endif

    @if($jogo->esporte->type === 'coletivo' && $jogo->time2)
        <div class="info-section">
            <h2>Lista de Presença - {{ $jogo->time2->name }}</h2>
            @if($presencasTime2->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Nome do Atleta</th>
                            <th style="width: 100px; text-align: center;">Presente</th>
                            <th style="width: 100px; text-align: center;">Substituto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($presencasTime2 as $presenca)
                            <tr class="{{ $presenca->is_substituto ? 'substituto-row' : '' }}">
                                <td>{{ $presenca->aluno->name }}</td>
                                <td class="text-center">{{ $presenca->presente ? 'Sim' : 'Não' }}</td>
                                <td class="text-center">{{ $presenca->is_substituto ? 'Sim' : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>Nenhuma presença registrada</p>
            @endif
        </div>
    @endif

    @if($jogo->esporte->type === 'individual')
        <div class="info-section">
            <h2>Lista de Presença</h2>
            @if($jogo->presencas->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Nome do Atleta</th>
                            <th>Turma</th>
                            <th style="width: 100px; text-align: center;">Presente</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jogo->presencas as $presenca)
                            <tr>
                                <td>{{ $presenca->aluno->name }}</td>
                                <td>{{ $presenca->aluno->turma->name }} ({{ $presenca->aluno->turma->period }})</td>
                                <td class="text-center">{{ $presenca->presente ? 'Sim' : 'Não' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>Nenhuma presença registrada</p>
            @endif
        </div>
    @endif

    <div class="footer">
        <div class="info-row">
            <span class="info-label">Data de Geração:</span>
            <span>{{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</span>
        </div>
        
        <div class="signature-line">
            Assinatura do Responsável
        </div>
    </div>
</body>
</html>

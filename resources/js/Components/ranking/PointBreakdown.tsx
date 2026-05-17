interface Score {
    pontos_vitorias: number;
    pontos_empates: number;
    pontos_individuais: number;
    pontos_avaliacao_base?: number; // informativo, não exibido no breakdown
    bonus_avaliacao: number;
    penalidades: number;
    total: number;
    saldo_gols: number;
}

interface Props {
    score: Score;
}

export default function PointBreakdown({ score }: Props) {
    return (
        <div className="max-w-2xl">
            <h4 className="font-semibold text-gray-700 mb-3">Breakdown de Pontuação</h4>
            <div className="grid grid-cols-2 gap-x-8 gap-y-2 text-sm">
                <div className="text-gray-600">Vitórias:</div>
                <div className="text-right font-medium text-green-600">+{score.pontos_vitorias} pts</div>
                
                <div className="text-gray-600">Empates:</div>
                <div className="text-right font-medium text-blue-600">+{score.pontos_empates} pts</div>
                
                <div className="text-gray-600">Esportes Individuais:</div>
                <div className="text-right font-medium text-purple-600">+{score.pontos_individuais} pts</div>
                
                {score.bonus_avaliacao > 0 && (
                    <>
                        <div className="text-gray-600">Bônus Bandeira/Grito:</div>
                        <div className="text-right font-medium text-yellow-600">+{score.bonus_avaliacao} pts</div>
                    </>
                )}
                
                <div className="text-gray-600 text-red-600">Penalidades:</div>
                <div className="text-right font-medium text-red-600">-{score.penalidades} pts</div>
                
                <div className="font-bold text-gray-900 border-t pt-2 mt-2">Total:</div>
                <div className="text-right font-bold text-gray-900 border-t pt-2 mt-2 text-lg">
                    {score.total} pts
                </div>
            </div>
        </div>
    );
}

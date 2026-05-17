import { router } from '@inertiajs/react';
import { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';

interface Jogo {
    id: number;
    resultadosIndividuais?: Array<{ aluno_id: number; posicao: number }>;
}

interface Aluno {
    id: number;
    name: string;
    turma: { name: string; period: string };
}

interface Props {
    jogo: Jogo;
    atletas: Aluno[];
}

export default function IndividualResultForm({ jogo, atletas }: Props) {
    // Initialize positions from existing results or empty
    const initialPositions = atletas.reduce((acc, atleta) => {
        const existing = jogo.resultadosIndividuais?.find(r => r.aluno_id === atleta.id);
        acc[atleta.id] = existing?.posicao ?? '';
        return acc;
    }, {} as Record<number, number | ''>);

    const [positions, setPositions] = useState(initialPositions);
    const [processing, setProcessing] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        // Convert positions object to array format expected by backend
        const resultados = Object.entries(positions)
            .filter(([, posicao]) => posicao !== '')
            .map(([aluno_id, posicao]) => ({
                aluno_id: parseInt(aluno_id),
                posicao: posicao as number,
            }));

        setProcessing(true);
        setErrors({});
        router.put(route('resultado.update', jogo.id), { resultados } as any, {
            onError: (errs) => setErrors(errs as Record<string, string>),
            onFinish: () => setProcessing(false),
        });
    };

    const handlePositionChange = (alunoId: number, value: string) => {
        setPositions(prev => ({
            ...prev,
            [alunoId]: value === '' ? '' : parseInt(value),
        }));
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle>Classificação</CardTitle>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="space-y-2">
                        {atletas.map(atleta => (
                            <div key={atleta.id} className="flex items-center gap-4">
                                <div className="w-20">
                                    <Input
                                        type="number"
                                        min="1"
                                        placeholder="Pos."
                                        value={positions[atleta.id]}
                                        onChange={(e) => handlePositionChange(atleta.id, e.target.value)}
                                    />
                                </div>
                                <div className="flex-1">
                                    <span className="font-medium">{atleta.name}</span>
                                    <span className="text-sm text-gray-600 ml-2">
                                        ({atleta.turma.name} {atleta.turma.period})
                                    </span>
                                </div>
                            </div>
                        ))}
                    </div>

                    {errors.resultados && (
                        <p className="text-sm text-red-600">{errors.resultados}</p>
                    )}

                    <Button type="submit" disabled={processing}>
                        Salvar Resultado
                    </Button>
                </form>
            </CardContent>
        </Card>
    );
}

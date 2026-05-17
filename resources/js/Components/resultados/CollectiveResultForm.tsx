import { useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';

interface Jogo {
    id: number;
    time1?: { name: string; period: string };
    time2?: { name: string; period: string };
    placar_time1?: number;
    placar_time2?: number;
}

interface Props {
    jogo: Jogo;
}

export default function CollectiveResultForm({ jogo }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        placar_time1: jogo.placar_time1 ?? ('' as number | ''),
        placar_time2: jogo.placar_time2 ?? ('' as number | ''),
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('resultado.update', jogo.id));
    };

    const handleQuickDraw = () => {
        setData({
            placar_time1: 0,
            placar_time2: 0,
        });
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle>Placar</CardTitle>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <Label htmlFor="placar_time1">
                                {jogo.time1?.name} {jogo.time1?.period}
                            </Label>
                            <Input
                                id="placar_time1"
                                type="number"
                                min="0"
                                value={data.placar_time1}
                                onChange={(e) => setData('placar_time1', e.target.value === '' ? '' : parseInt(e.target.value))}
                            />
                            {errors.placar_time1 && (
                                <p className="text-sm text-red-600 mt-1">{errors.placar_time1}</p>
                            )}
                        </div>

                        <div>
                            <Label htmlFor="placar_time2">
                                {jogo.time2?.name} {jogo.time2?.period}
                            </Label>
                            <Input
                                id="placar_time2"
                                type="number"
                                min="0"
                                value={data.placar_time2}
                                onChange={(e) => setData('placar_time2', e.target.value === '' ? '' : parseInt(e.target.value))}
                            />
                            {errors.placar_time2 && (
                                <p className="text-sm text-red-600 mt-1">{errors.placar_time2}</p>
                            )}
                        </div>
                    </div>

                    <div className="flex gap-2">
                        <Button type="button" variant="outline" onClick={handleQuickDraw}>
                            Empate (0 x 0)
                        </Button>
                        <Button type="submit" disabled={processing}>
                            Salvar Resultado
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}

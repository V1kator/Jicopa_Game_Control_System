import { useForm } from '@inertiajs/react';
import { Settings, Save } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';

interface ScoringConfig {
    id: number;
    points_per_win: number;
    points_per_draw: number;
    points_per_extra: number;
}

interface Props {
    config: ScoringConfig;
}

export default function ScoringConfigIndex({ config }: Props) {
    const form = useForm({
        points_per_win:   config.points_per_win,
        points_per_draw:  config.points_per_draw,
        points_per_extra: config.points_per_extra,
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        form.put(route('admin.scoring-config.update'));
    }

    return (
        <AdminLayout title="Configuração de Pontuação">
            <div className="max-w-lg">
                <Card className="rounded-xl shadow-sm bg-surface">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-gray-800">
                            <Settings size={20} className="text-primary" />
                            Valores de Pontuação
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-5">

                            {/* Pontos por Vitória */}
                            <div className="space-y-1">
                                <Label htmlFor="points_per_win">Pontos por Vitória</Label>
                                <Input
                                    id="points_per_win"
                                    type="number"
                                    min={1}
                                    max={100}
                                    value={form.data.points_per_win}
                                    onChange={(e) => form.setData('points_per_win', parseInt(e.target.value, 10))}
                                    className="rounded-md"
                                />
                                {form.errors.points_per_win && (
                                    <p className="text-sm text-red-500">{form.errors.points_per_win}</p>
                                )}
                            </div>

                            {/* Pontos por Empate */}
                            <div className="space-y-1">
                                <Label htmlFor="points_per_draw">Pontos por Empate</Label>
                                <Input
                                    id="points_per_draw"
                                    type="number"
                                    min={1}
                                    max={100}
                                    value={form.data.points_per_draw}
                                    onChange={(e) => form.setData('points_per_draw', parseInt(e.target.value, 10))}
                                    className="rounded-md"
                                />
                                {form.errors.points_per_draw && (
                                    <p className="text-sm text-red-500">{form.errors.points_per_draw}</p>
                                )}
                            </div>

                            {/* Pontos por Jogo Extra */}
                            <div className="space-y-1">
                                <Label htmlFor="points_per_extra">Pontos por Jogo Extra</Label>
                                <Input
                                    id="points_per_extra"
                                    type="number"
                                    min={1}
                                    max={100}
                                    value={form.data.points_per_extra}
                                    onChange={(e) => form.setData('points_per_extra', parseInt(e.target.value, 10))}
                                    className="rounded-md"
                                />
                                {form.errors.points_per_extra && (
                                    <p className="text-sm text-red-500">{form.errors.points_per_extra}</p>
                                )}
                            </div>

                            <Button
                                type="submit"
                                disabled={form.processing}
                                className="bg-primary text-primary-foreground rounded-md flex items-center gap-2 hover:bg-primary/90"
                            >
                                <Save size={16} />
                                {form.processing ? 'Salvando...' : 'Salvar Configuração'}
                            </Button>

                        </form>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}

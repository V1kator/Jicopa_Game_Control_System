import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { router } from '@inertiajs/react';
import { format } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import type { Jogo } from '@/types';

interface Props {
    nextGame: Jogo | null;
}

export default function NextGameCard({ nextGame }: Props) {
    if (!nextGame) {
        return (
            <Card>
                <CardHeader>
                    <CardTitle>Próximo Jogo</CardTitle>
                </CardHeader>
                <CardContent>
                    <p className="text-gray-600">Nenhum jogo agendado.</p>
                </CardContent>
            </Card>
        );
    }

    return (
        <Card className="border-green-600 border-2">
            <CardHeader>
                <CardTitle>Próximo Jogo</CardTitle>
            </CardHeader>
            <CardContent className="space-y-2">
                <div className="flex gap-2">
                    <Badge>{nextGame.categoria?.name ?? '—'}</Badge>
                    <Badge variant="outline">{nextGame.esporte?.name ?? '—'}</Badge>
                </div>
                {nextGame.esporte?.type === 'coletivo' && nextGame.time1 && nextGame.time2 ? (
                    <p className="font-medium">
                        {nextGame.time1?.name ?? '—'} {nextGame.time1?.period ?? ''} vs {nextGame.time2?.name ?? '—'} {nextGame.time2?.period ?? ''}
                    </p>
                ) : (
                    <p className="font-medium">{nextGame.esporte?.name ?? '—'} - {nextGame.categoria?.name ?? '—'}</p>
                )}
                <p className="text-sm">
                    {(() => {
                        const dateStr = nextGame.data?.slice(0, 10);
                        const timeStr = nextGame.hora?.slice(0, 5);
                        if (!dateStr || !timeStr) return '—';
                        const dt = new Date(`${dateStr}T${timeStr}`);
                        return isNaN(dt.getTime()) ? '—' : format(dt, "dd/MM/yyyy 'às' HH:mm", { locale: ptBR });
                    })()}
                </p>
                <p className="text-sm text-gray-600">Local: {nextGame.local}</p>
            </CardContent>
            <CardFooter className="flex gap-2">
                <Button
                    size="sm"
                    onClick={() => router.visit(`/jogos/${nextGame.id}/resultado`)}
                >
                    Registrar Resultado
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => router.visit(`/jogos/${nextGame.id}/presenca`)}
                >
                    Marcar Presença
                </Button>
            </CardFooter>
        </Card>
    );
}

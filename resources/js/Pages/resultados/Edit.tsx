import { Head } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import ProfessorLayout from '@/Layouts/ProfessorLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import CollectiveResultForm from '@/Components/resultados/CollectiveResultForm';
import IndividualResultForm from '@/Components/resultados/IndividualResultForm';
import { PageProps } from '@/types';

interface Jogo {
    id: number;
    categoria: { name: string };
    esporte: { name: string; type: 'coletivo' | 'individual' };
    time1?: { name: string; period: string };
    time2?: { name: string; period: string };
    data: string;
    hora: string;
    local: string;
    placar_time1?: number;
    placar_time2?: number;
    vencedor_id?: number;
    resultadosIndividuais?: Array<{ aluno_id: number; posicao: number }>;
}

interface Aluno {
    id: number;
    name: string;
    turma: { name: string; period: string };
}

interface Props extends PageProps {
    jogo: Jogo;
    atletas: Aluno[];
}

export default function Edit({ auth, jogo, atletas }: Props) {
    const isColetivo = jogo.esporte.type === 'coletivo';
    const Layout = auth.user.roles.includes('admin') ? AdminLayout : ProfessorLayout;

    return (
        <Layout>
            <Head title="Registrar Resultado" />

            <div className="space-y-6">
                <h1 className="text-3xl font-bold">Registrar Resultado</h1>

                <Card>
                    <CardHeader>
                        <CardTitle>Informações do Jogo</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="flex gap-2 mb-4">
                            <Badge>{jogo.categoria.name}</Badge>
                            <Badge variant="outline">{jogo.esporte.name}</Badge>
                        </div>
                        <div className="space-y-2">
                            <p><strong>Data:</strong> {new Date(jogo.data).toLocaleDateString('pt-BR')}</p>
                            <p><strong>Hora:</strong> {jogo.hora}</p>
                            <p><strong>Local:</strong> {jogo.local}</p>
                            {isColetivo && jogo.time1 && jogo.time2 && (
                                <p><strong>Times:</strong> {jogo.time1.name} {jogo.time1.period} vs {jogo.time2.name} {jogo.time2.period}</p>
                            )}
                        </div>
                    </CardContent>
                </Card>

                {isColetivo ? (
                    <CollectiveResultForm jogo={jogo} />
                ) : (
                    <IndividualResultForm jogo={jogo} atletas={atletas} />
                )}
            </div>
        </Layout>
    );
}

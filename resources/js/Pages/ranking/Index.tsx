import { useEffect } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import ProfessorLayout from '@/Layouts/ProfessorLayout';
import RankingTable from '@/Components/ranking/RankingTable';
import { Card, CardContent } from '@/Components/ui/card';

interface Turma {
    id: number;
    name: string;
    period: 'Matutino' | 'Vespertino';
}

interface Score {
    pontos_vitorias: number;
    pontos_empates: number;
    pontos_individuais: number;
    pontos_avaliacao_base: number;
    bonus_avaliacao: number;
    pontos_avaliacao: number;
    penalidades: number;
    total: number;
    saldo_gols: number;
}

interface RankingItem {
    posicao: number;
    turma: Turma;
    score: Score;
}

interface Categoria {
    id: number;
    name: string;
    ativo: boolean;
}

interface Props {
    ranking: RankingItem[];
    categorias: Categoria[];
    categoriaAtual: number | null;
    lastUpdated: string;
    auth: {
        user: {
            roles: string[];
        };
    };
}

export default function Index({ ranking, categorias, categoriaAtual, lastUpdated, auth }: Props) {
    // Polling: reload ranking and lastUpdated every 15 seconds
    useEffect(() => {
        const interval = setInterval(() => {
            router.reload({ only: ['ranking', 'lastUpdated'] });
        }, 15000);
        
        return () => clearInterval(interval);
    }, []);
    
    // Format last updated timestamp
    const formatLastUpdated = (isoString: string) => {
        const date = new Date(isoString);
        const now = new Date();
        const diffMs = now.getTime() - date.getTime();
        const diffMinutes = Math.floor(diffMs / 60000);
        
        if (diffMinutes < 1) return 'agora mesmo';
        if (diffMinutes === 1) return 'há 1 minuto';
        if (diffMinutes < 60) return `há ${diffMinutes} minutos`;
        
        return date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    };
    
    // Handle category filter change
    const handleCategoriaChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
        router.get(route('ranking.index'), { categoria_id: e.target.value }, { preserveState: true });
    };
    
    const Layout = auth.user.roles.includes('admin') ? AdminLayout : ProfessorLayout;
    
    return (
        <Layout>
            <div className="p-6">
                <div className="flex justify-between items-center mb-6">
                    <h1 className="text-3xl font-bold">Ranking Geral</h1>
                    <span className="text-sm text-gray-500">
                        Última atualização: {formatLastUpdated(lastUpdated)}
                    </span>
                </div>
                
                {categorias.length === 0 ? (
                    <Card>
                        <CardContent className="p-6">
                            <p className="text-gray-500">Nenhuma categoria ativa encontrada.</p>
                        </CardContent>
                    </Card>
                ) : (
                    <>
                        <div className="mb-4">
                            <label htmlFor="categoria" className="block text-sm font-medium text-gray-700 mb-1">
                                Categoria
                            </label>
                            <select
                                id="categoria"
                                value={categoriaAtual || ''}
                                onChange={handleCategoriaChange}
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                            >
                                {categorias.map((cat) => (
                                    <option key={cat.id} value={cat.id}>
                                        {cat.name}
                                    </option>
                                ))}
                            </select>
                        </div>
                        
                        {ranking.length === 0 ? (
                            <Card>
                                <CardContent className="p-6">
                                    <p className="text-gray-500">Nenhuma turma nesta categoria ainda.</p>
                                </CardContent>
                            </Card>
                        ) : (
                            <RankingTable data={ranking} categoriaAtual={categoriaAtual} />
                        )}
                    </>
                )}
            </div>
        </Layout>
    );
}

import AdminLayout from '@/Layouts/AdminLayout';
import ProfessorLayout from '@/Layouts/ProfessorLayout';
import { Head, usePage } from '@inertiajs/react';
import type { PageProps } from '@/types';
import { BarChart, Bar, PieChart, Pie, Cell, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';
import { Calendar, Trophy, AlertTriangle, CheckCircle } from 'lucide-react';

interface Metrics {
    jogos_agendados: number;
    jogos_realizados: number;
    jogos_cancelados: number;
    total_penalidades: number;
}

interface TopTurma {
    turma_id: number;
    turma_name: string;
    turma_period: string;
    pontos_totais: number;
}

interface TopPorCategoria {
    categoria_id: number;
    categoria_name: string;
    top3: TopTurma[];
}

interface PenalidadePorTurma {
    name: string;
    value: number;
}

interface Jogo {
    id: number;
    data: string;
    hora: string;
    local: string;
    categoria: { name: string };
    esporte: { name: string };
    time1?: { name: string; period: string };
    time2?: { name: string; period: string };
}

interface DashboardProps extends PageProps {
    metrics?: Metrics;
    topTurmasPorCategoria?: TopPorCategoria[];
    penalidadesPorTurma?: PenalidadePorTurma[];
    proximosJogos?: Jogo[];
}

const COLORS = ['#2563eb', '#7c3aed', '#dc2626', '#059669', '#ea580c', '#0891b2'];

export default function Dashboard() {
    const { auth, metrics, topTurmasPorCategoria, penalidadesPorTurma, proximosJogos } = usePage<DashboardProps>().props;
    const isAdmin = auth.user?.roles?.includes('admin');

    const Layout = isAdmin ? AdminLayout : ProfessorLayout;

    // Prepare data for bar chart
    const jogosData = metrics ? [
        { name: 'Agendados', value: metrics.jogos_agendados, fill: '#3b82f6' },
        { name: 'Realizados', value: metrics.jogos_realizados, fill: '#10b981' },
        { name: 'Cancelados', value: metrics.jogos_cancelados, fill: '#ef4444' },
    ] : [];

    return (
        <Layout title="Dashboard">
            <Head title="Dashboard" />

            <div className="space-y-6">
                {/* Metrics Cards */}
                {metrics && (
                    <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                        <div className="bg-white rounded-lg shadow p-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm text-gray-600">Jogos Agendados</p>
                                    <p className="text-3xl font-bold text-blue-600">{metrics.jogos_agendados}</p>
                                </div>
                                <Calendar className="w-10 h-10 text-blue-600 opacity-20" />
                            </div>
                        </div>

                        <div className="bg-white rounded-lg shadow p-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm text-gray-600">Jogos Realizados</p>
                                    <p className="text-3xl font-bold text-green-600">{metrics.jogos_realizados}</p>
                                </div>
                                <CheckCircle className="w-10 h-10 text-green-600 opacity-20" />
                            </div>
                        </div>

                        <div className="bg-white rounded-lg shadow p-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm text-gray-600">Jogos Cancelados</p>
                                    <p className="text-3xl font-bold text-red-600">{metrics.jogos_cancelados}</p>
                                </div>
                                <Calendar className="w-10 h-10 text-red-600 opacity-20" />
                            </div>
                        </div>

                        <div className="bg-white rounded-lg shadow p-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm text-gray-600">Penalidades</p>
                                    <p className="text-3xl font-bold text-orange-600">{metrics.total_penalidades}</p>
                                </div>
                                <AlertTriangle className="w-10 h-10 text-orange-600 opacity-20" />
                            </div>
                        </div>
                    </div>
                )}

                {/* Charts Row */}
                {metrics && (
                    <div className="grid gap-6 md:grid-cols-2">
                        {/* Bar Chart - Jogos por Status */}
                        <div className="bg-white rounded-lg shadow p-6">
                            <h3 className="text-lg font-semibold text-gray-800 mb-4">Jogos por Status</h3>
                            <ResponsiveContainer width="100%" height={250}>
                                <BarChart data={jogosData}>
                                    <CartesianGrid strokeDasharray="3 3" />
                                    <XAxis dataKey="name" />
                                    <YAxis />
                                    <Tooltip />
                                    <Bar dataKey="value" />
                                </BarChart>
                            </ResponsiveContainer>
                        </div>

                        {/* Pie Chart - Penalidades por Turma */}
                        <div className="bg-white rounded-lg shadow p-6">
                            <h3 className="text-lg font-semibold text-gray-800 mb-4">Penalidades por Turma</h3>
                            {penalidadesPorTurma && penalidadesPorTurma.length > 0 ? (
                                <ResponsiveContainer width="100%" height={250}>
                                    <PieChart>
                                        <Pie
                                            data={penalidadesPorTurma}
                                            cx="50%"
                                            cy="50%"
                                            labelLine={false}
                                            label={(entry) => `${entry.name}: ${entry.value}`}
                                            outerRadius={80}
                                            fill="#8884d8"
                                            dataKey="value"
                                        >
                                            {penalidadesPorTurma.map((entry, index) => (
                                                <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                                            ))}
                                        </Pie>
                                        <Tooltip />
                                    </PieChart>
                                </ResponsiveContainer>
                            ) : (
                                <div className="flex items-center justify-center h-[250px] text-gray-400">
                                    Nenhuma penalidade registrada
                                </div>
                            )}
                        </div>
                    </div>
                )}

                {/* Top 3 por Categoria */}
                {topTurmasPorCategoria && topTurmasPorCategoria.length > 0 && (
                    <div className="bg-white rounded-lg shadow p-6">
                        <h3 className="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <Trophy className="w-5 h-5 text-primary" />
                            Top 3 por Categoria
                        </h3>
                        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            {topTurmasPorCategoria.map((categoria) => (
                                <div key={categoria.categoria_id} className="border border-gray-200 rounded-lg p-4">
                                    <h4 className="font-semibold text-gray-900 mb-3">{categoria.categoria_name}</h4>
                                    {categoria.top3.length > 0 ? (
                                        <div className="space-y-2">
                                            {categoria.top3.map((turma, index) => (
                                                <div key={turma.turma_id} className="flex items-center justify-between">
                                                    <div className="flex items-center gap-2">
                                                        <span className={`w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white ${
                                                            index === 0 ? 'bg-yellow-500' : index === 1 ? 'bg-gray-400' : 'bg-orange-600'
                                                        }`}>
                                                            {index + 1}
                                                        </span>
                                                        <span className="text-sm font-medium">
                                                            {turma.turma_name} ({turma.turma_period.charAt(0)})
                                                        </span>
                                                    </div>
                                                    <span className="text-sm font-bold text-primary">{turma.pontos_totais} pts</span>
                                                </div>
                                            ))}
                                        </div>
                                    ) : (
                                        <p className="text-sm text-gray-400">Sem dados</p>
                                    )}
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                {/* Próximos Jogos */}
                {proximosJogos && proximosJogos.length > 0 && (
                    <div className="bg-white rounded-lg shadow p-6">
                        <h3 className="text-lg font-semibold text-gray-800 mb-4">Próximos Jogos</h3>
                        <div className="space-y-3">
                            {proximosJogos.map((jogo) => (
                                <div key={jogo.id} className="flex items-start gap-4 p-3 border border-gray-200 rounded-lg">
                                    <div className="text-sm font-bold text-gray-900 min-w-[80px]">
                                        {new Date(jogo.data).toLocaleDateString('pt-BR')}
                                        <br />
                                        {jogo.hora.substring(0, 5)}
                                    </div>
                                    <div className="flex-1">
                                        <div className="flex gap-2 mb-1">
                                            <span className="text-xs bg-primary/10 text-primary px-2 py-0.5 rounded">
                                                {jogo.categoria.name}
                                            </span>
                                            <span className="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">
                                                {jogo.esporte.name}
                                            </span>
                                        </div>
                                        <div className="text-sm font-medium text-gray-900">
                                            {jogo.time1 && jogo.time2 ? (
                                                <span>{jogo.time1.name} vs {jogo.time2.name}</span>
                                            ) : (
                                                <span className="text-gray-400">Times a definir</span>
                                            )}
                                        </div>
                                        <div className="text-xs text-gray-500 mt-0.5">Local: {jogo.local}</div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                {/* Fallback for non-admin or no data */}
                {!metrics && (
                    <div className="bg-white rounded-lg shadow p-6">
                        <h3 className="text-lg font-semibold text-gray-800 mb-2">Bem-vindo ao Jicopa!</h3>
                        <p className="text-gray-600">
                            Sistema de gestão de jogos esportivos escolares.
                        </p>
                    </div>
                )}
            </div>
        </Layout>
    );
}

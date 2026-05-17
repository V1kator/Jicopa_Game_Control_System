import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';

interface Activity {
    id: number;
    log_name: string;
    description: string;
    event: string;
    subject_type: string;
    subject_id: number;
    causer: {
        id: number;
        name: string;
    } | null;
    properties: {
        attributes?: Record<string, any>;
        old?: Record<string, any>;
    };
    created_at: string;
}

interface PaginatedActivities {
    data: Activity[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

interface Filtros {
    tipo?: string;
    evento?: string;
    data_inicio?: string;
    data_fim?: string;
}

interface Props {
    atividades: PaginatedActivities;
    filtros: Filtros;
}

export default function HistoricoIndex({ atividades, filtros }: Props) {
    const [filters, setFilters] = useState<Filtros>(filtros);

    function handleFilterChange(key: keyof Filtros, value: string) {
        const newFilters = { ...filters, [key]: value || undefined };
        setFilters(newFilters);
        router.get('/admin/historico', newFilters, { preserveState: true });
    }

    function clearFilters() {
        setFilters({});
        router.get('/admin/historico', {}, { preserveState: true });
    }

    function getEventLabel(event: string): string {
        const labels: Record<string, string> = {
            created: 'Criado',
            updated: 'Atualizado',
            deleted: 'Excluído',
        };
        return labels[event] || event;
    }

    function getEventColor(event: string): string {
        const colors: Record<string, string> = {
            created: 'bg-green-100 text-green-700',
            updated: 'bg-blue-100 text-blue-700',
            deleted: 'bg-red-100 text-red-700',
        };
        return colors[event] || 'bg-gray-100 text-gray-700';
    }

    function getModelLabel(model: string): string {
        const labels: Record<string, string> = {
            Turma: 'Turma',
            Categoria: 'Categoria',
            Jogo: 'Jogo',
            Esporte: 'Esporte',
            Aluno: 'Atleta',
            Penalidade: 'Penalidade',
        };
        return labels[model] || model;
    }

    return (
        <AdminLayout title="Histórico de Alterações">
            <Head title="Histórico de Alterações" />

            <div className="mb-6">
                <p className="text-gray-500 text-sm mb-4">
                    Registro de todas as alterações em turmas, categorias, esportes, atletas, jogos e penalidades
                </p>

                <div className="bg-surface rounded-xl shadow-sm p-4 mb-4">
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Tipo
                            </label>
                            <select
                                value={filters.tipo || ''}
                                onChange={(e) => handleFilterChange('tipo', e.target.value)}
                                className="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                            >
                                <option value="">Todos</option>
                                <option value="turmas">Turmas</option>
                                <option value="categorias">Categorias</option>
                                <option value="esportes">Esportes</option>
                                <option value="alunos">Atletas</option>
                                <option value="jogos">Jogos</option>
                                <option value="penalidades">Penalidades</option>
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Evento
                            </label>
                            <select
                                value={filters.evento || ''}
                                onChange={(e) => handleFilterChange('evento', e.target.value)}
                                className="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                            >
                                <option value="">Todos</option>
                                <option value="created">Criado</option>
                                <option value="updated">Atualizado</option>
                                <option value="deleted">Excluído</option>
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Data Início
                            </label>
                            <input
                                type="date"
                                value={filters.data_inicio || ''}
                                onChange={(e) => handleFilterChange('data_inicio', e.target.value)}
                                className="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Data Fim
                            </label>
                            <input
                                type="date"
                                value={filters.data_fim || ''}
                                onChange={(e) => handleFilterChange('data_fim', e.target.value)}
                                className="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                            />
                        </div>
                    </div>

                    {(filters.tipo || filters.evento || filters.data_inicio || filters.data_fim) && (
                        <div className="mt-4">
                            <button
                                onClick={clearFilters}
                                className="text-sm text-primary hover:underline"
                            >
                                Limpar filtros
                            </button>
                        </div>
                    )}
                </div>
            </div>

            <div className="bg-surface rounded-xl shadow-sm overflow-hidden">
                <table className="w-full text-sm">
                    <thead className="bg-gray-50 border-b">
                        <tr>
                            <th className="text-left px-6 py-3 text-gray-600 font-medium">Data/Hora</th>
                            <th className="text-left px-6 py-3 text-gray-600 font-medium">Tipo</th>
                            <th className="text-left px-6 py-3 text-gray-600 font-medium">Evento</th>
                            <th className="text-left px-6 py-3 text-gray-600 font-medium">Usuário</th>
                            <th className="text-left px-6 py-3 text-gray-600 font-medium">Detalhes</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {atividades.data.map((activity) => (
                            <tr key={activity.id} className="hover:bg-gray-50">
                                <td className="px-6 py-4 text-gray-600 whitespace-nowrap">
                                    {activity.created_at}
                                </td>
                                <td className="px-6 py-4">
                                    <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                        {getModelLabel(activity.subject_type)}
                                    </span>
                                </td>
                                <td className="px-6 py-4">
                                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getEventColor(activity.event)}`}>
                                        {getEventLabel(activity.event)}
                                    </span>
                                </td>
                                <td className="px-6 py-4 text-gray-900">
                                    {activity.causer?.name || 'Sistema'}
                                </td>
                                <td className="px-6 py-4 text-gray-600">
                                    {activity.event === 'created' && (
                                        <span>Novo registro criado</span>
                                    )}
                                    {activity.event === 'updated' && activity.properties.old && (
                                        <div className="text-xs">
                                            {Object.keys(activity.properties.old).map((key) => (
                                                <div key={key} className="mb-1">
                                                    <span className="font-medium">{key}:</span>{' '}
                                                    <span className="text-red-600">
                                                        {JSON.stringify(activity.properties.old?.[key])}
                                                    </span>
                                                    {' → '}
                                                    <span className="text-green-600">
                                                        {JSON.stringify(activity.properties.attributes?.[key])}
                                                    </span>
                                                </div>
                                            ))}
                                        </div>
                                    )}
                                    {activity.event === 'deleted' && (
                                        <span>Registro excluído</span>
                                    )}
                                </td>
                            </tr>
                        ))}
                        {atividades.data.length === 0 && (
                            <tr>
                                <td colSpan={5} className="px-6 py-8 text-center text-gray-400">
                                    Nenhuma atividade registrada.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            {atividades.last_page > 1 && (
                <div className="mt-6 flex items-center justify-between">
                    <p className="text-sm text-gray-600">
                        Mostrando {atividades.data.length} de {atividades.total} registros
                    </p>
                    <div className="flex gap-2">
                        {atividades.links.map((link, index) => (
                            <button
                                key={index}
                                onClick={() => link.url && router.get(link.url)}
                                disabled={!link.url || link.active}
                                className={`px-3 py-1 rounded text-sm ${
                                    link.active
                                        ? 'bg-primary text-white'
                                        : link.url
                                        ? 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                        : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                }`}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}

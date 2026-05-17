import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import ConfirmDialog from '@/Components/shared/ConfirmDialog';

interface Turma {
    id: number;
    name: string;
    period: string;
}

interface Jogo {
    id: number;
    categoria: { id: number; name: string };
    esporte: { id: number; name: string; type: string };
    time1?: Turma;
    time2?: Turma;
    vencedor_id?: number | null;
    placar_time1?: number | null;
    placar_time2?: number | null;
    data: string;
    hora: string;
    local: string;
    cancelado: boolean;
    has_resultado: boolean;
}

interface Props {
    jogos: Jogo[];
    categorias: Array<{ id: number; name: string }>;
    esportes: Array<{ id: number; name: string }>;
    filters: {
        data?: string;
        categoria_id?: string;
        esporte_id?: string;
        cancelado?: string;
    };
}

function formatDate(dateStr: string): string {
    const [year, month, day] = dateStr.split('T')[0].split('-');
    return `${day}/${month}/${year}`;
}

function formatHora(hora: string): string {
    return hora.substring(0, 5);
}

export default function JogosIndex({ jogos, categorias, esportes, filters }: Props) {
    const [data, setData] = useState(filters.data || '');
    const [categoriaId, setCategoriaId] = useState(filters.categoria_id || '');
    const [esporteId, setEsporteId] = useState(filters.esporte_id || '');
    const [confirmDelete, setConfirmDelete] = useState<number | null>(null);

    function handleFilter() {
        router.get('/admin/jogos', {
            data: data || undefined,
            categoria_id: categoriaId || undefined,
            esporte_id: esporteId || undefined,
        }, {
            preserveState: true,
            replace: true,
        });
    }

    function handleDelete(id: number) {
        router.delete(`/admin/jogos/${id}`, {
            onSuccess: () => setConfirmDelete(null),
        });
    }

    return (
        <AdminLayout title="Calendário de Jogos">
            <Head title="Jogos" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div className="flex gap-3 flex-wrap">
                        <div>
                            <label className="block text-xs font-medium text-gray-500 mb-1">Data</label>
                            <input
                                type="date"
                                value={data}
                                onChange={(e) => setData(e.target.value)}
                                className="border border-gray-300 rounded-md px-3 py-2 text-sm"
                            />
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-gray-500 mb-1">Categoria</label>
                            <select
                                value={categoriaId}
                                onChange={(e) => setCategoriaId(e.target.value)}
                                className="border border-gray-300 rounded-md px-3 py-2 text-sm"
                            >
                                <option value="">Todas</option>
                                {categorias.map(cat => (
                                    <option key={cat.id} value={cat.id}>{cat.name}</option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-gray-500 mb-1">Esporte</label>
                            <select
                                value={esporteId}
                                onChange={(e) => setEsporteId(e.target.value)}
                                className="border border-gray-300 rounded-md px-3 py-2 text-sm"
                            >
                                <option value="">Todos</option>
                                {esportes.map(esp => (
                                    <option key={esp.id} value={esp.id}>{esp.name}</option>
                                ))}
                            </select>
                        </div>
                        <div className="flex items-end">
                            <button
                                onClick={handleFilter}
                                className="bg-gray-100 text-gray-700 px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-200 transition-colors"
                            >
                                Filtrar
                            </button>
                        </div>
                    </div>
                    <Link
                        href="/admin/jogos/create"
                        className="bg-primary text-primary-foreground px-4 py-2 rounded-md text-sm font-medium hover:opacity-90 transition-opacity"
                    >
                        Cadastrar Jogo
                    </Link>
                </div>

                {/* Games List */}
                <div className="space-y-3">
                    {jogos.length === 0 && (
                        <div className="bg-surface rounded-xl shadow-sm p-8 text-center text-gray-400">
                            Nenhum jogo cadastrado.
                        </div>
                    )}
                    {jogos.map(jogo => (
                        <div
                            key={jogo.id}
                            className={`bg-surface rounded-xl shadow-sm p-4 flex justify-between items-center ${jogo.cancelado ? 'opacity-50' : ''}`}
                        >
                            <div className="flex gap-4 items-center">
                                <div className="text-center min-w-[70px]">
                                    <div className="text-xl font-bold text-gray-900">{formatHora(jogo.hora)}</div>
                                    <div className="text-xs text-gray-500">{formatDate(jogo.data)}</div>
                                </div>
                                <div className="border-l pl-4">
                                    <div className="flex gap-2 mb-1 flex-wrap">
                                        <span className="inline-flex items-center px-2 py-0.5 rounded text-xs bg-primary/10 text-primary font-medium">
                                            {jogo.categoria.name}
                                        </span>
                                        <span className="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-700 font-medium">
                                            {jogo.esporte.name}
                                        </span>
                                        {jogo.cancelado && (
                                            <span className="inline-flex items-center px-2 py-0.5 rounded text-xs bg-red-100 text-red-700 font-medium">
                                                Cancelado
                                            </span>
                                        )}
                                    </div>
                                    <div className="font-medium text-gray-900">
                                        {jogo.time1 && jogo.time2 ? (
                                            <span>{jogo.time1.name} ({jogo.time1.period.charAt(0)}) vs {jogo.time2.name} ({jogo.time2.period.charAt(0)})</span>
                                        ) : jogo.time1 ? (
                                            <span>{jogo.time1.name} ({jogo.time1.period})</span>
                                        ) : (
                                            <span className="text-gray-400 text-sm">Times a definir</span>
                                        )}
                                    </div>
                                    <div className="text-sm text-gray-500">Local: {jogo.local}</div>
                                </div>
                            </div>
                            <div className="flex gap-2">
                                {!jogo.cancelado && (
                                    <>
                                        <Link
                                            href={`/jogos/${jogo.id}/resultado`}
                                            className="text-green-600 hover:text-green-800 text-sm px-3 py-1 border border-green-200 rounded"
                                        >
                                            Resultado
                                        </Link>
                                        <Link
                                            href={`/jogos/${jogo.id}/presenca`}
                                            className="text-blue-600 hover:text-blue-800 text-sm px-3 py-1 border border-blue-200 rounded"
                                        >
                                            Presença
                                        </Link>
                                        {jogo.has_resultado && (
                                            <a
                                                href={`/admin/jogos/${jogo.id}/sumula`}
                                                className="text-purple-600 hover:text-purple-800 text-sm px-3 py-1 border border-purple-200 rounded"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                            >
                                                Súmula
                                            </a>
                                        )}
                                    </>
                                )}
                                <Link
                                    href={`/admin/jogos/${jogo.id}/edit`}
                                    className="text-primary hover:underline text-sm px-3 py-1 border border-primary/30 rounded"
                                >
                                    Editar
                                </Link>
                                <button
                                    onClick={() => setConfirmDelete(jogo.id)}
                                    className="text-red-600 hover:underline text-sm px-3 py-1 border border-red-200 rounded"
                                >
                                    Excluir
                                </button>
                            </div>
                        </div>
                    ))}
                </div>
            </div>

            <ConfirmDialog
                open={confirmDelete !== null}
                title="Excluir Jogo"
                message="Tem certeza que deseja EXCLUIR este jogo? Esta ação é irreversível e removerá todos os resultados, presenças e penalidades associadas."
                onConfirm={() => confirmDelete && handleDelete(confirmDelete)}
                onCancel={() => setConfirmDelete(null)}
                variant="danger"
                confirmText="Excluir"
            />
        </AdminLayout>
    );
}

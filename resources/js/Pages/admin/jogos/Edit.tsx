import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useEffect } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import ProfessorLayout from '@/Layouts/ProfessorLayout';
import { PageProps } from '@/types';

interface Turma {
    id: number;
    name: string;
    period: string;
}

interface Jogo {
    id: number;
    categoria_id: number;
    esporte_id: number;
    time1_id: number | null;
    time2_id: number | null;
    data: string;
    hora: string;
    local: string;
    cancelado: boolean;
}

interface Props {
    jogo: Jogo;
    categorias: Array<{ id: number; name: string }>;
    esportes: Array<{ id: number; name: string; type: string }>;
    turmas: Turma[];
}

export default function JogosEdit({ jogo, categorias, esportes, turmas }: Props) {
    const { props, props: { auth } } = usePage<PageProps & { errors: Record<string, string>; alternatives?: string[] }>();
    const alternatives: string[] = (props as any).alternatives ?? [];

    const { data, setData, put, processing, errors } = useForm({
        categoria_id: String(jogo.categoria_id),
        esporte_id: String(jogo.esporte_id),
        time1_id: jogo.time1_id ? String(jogo.time1_id) : '',
        time2_id: jogo.time2_id ? String(jogo.time2_id) : '',
        data: jogo.data ? jogo.data.substring(0, 10) : '',
        hora: jogo.hora ? jogo.hora.substring(0, 5) : '',
        local: jogo.local,
        cancelado: jogo.cancelado,
        force_update: false as boolean,
    });

    const hasConflict = Boolean((errors as any).conflict);

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        put(`/admin/jogos/${jogo.id}`);
    }

    function handleForceUpdate(e: React.MouseEvent) {
        e.preventDefault();
        setData('force_update', true);
    }

    useEffect(() => {
        if (data.force_update) {
            put(`/admin/jogos/${jogo.id}`);
        }
    }, [data.force_update]);

    const Layout = auth.user.is_admin ? AdminLayout : ProfessorLayout;

    return (
        <Layout title="Editar Jogo">
            <Head title="Editar Jogo" />

            <div className="max-w-2xl">
                <div className="bg-surface rounded-xl shadow-sm p-6">
                    {hasConflict && (
                        <div className="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <p className="text-yellow-800 font-medium text-sm">{(errors as any).conflict}</p>
                            {alternatives.length > 0 && (
                                <div className="mt-2">
                                    <p className="text-yellow-700 text-sm">Horários disponíveis neste local:</p>
                                    <div className="flex gap-2 mt-1">
                                        {alternatives.map(alt => (
                                            <button
                                                key={alt}
                                                type="button"
                                                onClick={() => setData('hora', alt)}
                                                className="px-3 py-1 bg-white border border-yellow-300 rounded text-sm text-yellow-800 hover:bg-yellow-50"
                                            >
                                                {alt}
                                            </button>
                                        ))}
                                    </div>
                                </div>
                            )}
                            <div className="mt-3">
                                <button
                                    type="button"
                                    onClick={handleForceUpdate}
                                    disabled={processing}
                                    className="px-4 py-2 bg-yellow-600 text-white rounded text-sm font-medium hover:bg-yellow-700 disabled:opacity-50"
                                >
                                    Salvar mesmo assim
                                </button>
                            </div>
                        </div>
                    )}

                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label htmlFor="categoria_id" className="block text-sm font-medium text-gray-700 mb-1">
                                    Categoria <span className="text-red-500">*</span>
                                </label>
                                <select
                                    id="categoria_id"
                                    value={data.categoria_id}
                                    onChange={(e) => setData('categoria_id', e.target.value)}
                                    className="w-full rounded-md border-gray-300 border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
                                >
                                    <option value="">Selecione...</option>
                                    {categorias.map(cat => (
                                        <option key={cat.id} value={cat.id}>{cat.name}</option>
                                    ))}
                                </select>
                                {errors.categoria_id && (
                                    <p className="mt-1 text-xs text-red-600">{errors.categoria_id}</p>
                                )}
                            </div>

                            <div>
                                <label htmlFor="esporte_id" className="block text-sm font-medium text-gray-700 mb-1">
                                    Esporte <span className="text-red-500">*</span>
                                </label>
                                <select
                                    id="esporte_id"
                                    value={data.esporte_id}
                                    onChange={(e) => setData('esporte_id', e.target.value)}
                                    className="w-full rounded-md border-gray-300 border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
                                >
                                    <option value="">Selecione...</option>
                                    {esportes.map(esp => (
                                        <option key={esp.id} value={esp.id}>{esp.name}</option>
                                    ))}
                                </select>
                                {errors.esporte_id && (
                                    <p className="mt-1 text-xs text-red-600">{errors.esporte_id}</p>
                                )}
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label htmlFor="time1_id" className="block text-sm font-medium text-gray-700 mb-1">
                                    Time 1
                                </label>
                                <select
                                    id="time1_id"
                                    value={data.time1_id}
                                    onChange={(e) => setData('time1_id', e.target.value)}
                                    className="w-full rounded-md border-gray-300 border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
                                >
                                    <option value="">Nenhum (esporte individual)</option>
                                    {turmas.map(t => (
                                        <option key={t.id} value={t.id}>{t.name} — {t.period}</option>
                                    ))}
                                </select>
                                {errors.time1_id && (
                                    <p className="mt-1 text-xs text-red-600">{errors.time1_id}</p>
                                )}
                            </div>

                            <div>
                                <label htmlFor="time2_id" className="block text-sm font-medium text-gray-700 mb-1">
                                    Time 2
                                </label>
                                <select
                                    id="time2_id"
                                    value={data.time2_id}
                                    onChange={(e) => setData('time2_id', e.target.value)}
                                    className="w-full rounded-md border-gray-300 border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
                                >
                                    <option value="">Nenhum (esporte individual)</option>
                                    {turmas.map(t => (
                                        <option key={t.id} value={t.id}>{t.name} — {t.period}</option>
                                    ))}
                                </select>
                                {errors.time2_id && (
                                    <p className="mt-1 text-xs text-red-600">{errors.time2_id}</p>
                                )}
                            </div>
                        </div>

                        <div className="grid grid-cols-3 gap-4">
                            <div>
                                <label htmlFor="data" className="block text-sm font-medium text-gray-700 mb-1">
                                    Data <span className="text-red-500">*</span>
                                </label>
                                <input
                                    id="data"
                                    type="date"
                                    value={data.data}
                                    onChange={(e) => setData('data', e.target.value)}
                                    className="w-full rounded-md border-gray-300 border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
                                />
                                {errors.data && (
                                    <p className="mt-1 text-xs text-red-600">{errors.data}</p>
                                )}
                            </div>

                            <div>
                                <label htmlFor="hora" className="block text-sm font-medium text-gray-700 mb-1">
                                    Hora <span className="text-red-500">*</span>
                                </label>
                                <input
                                    id="hora"
                                    type="time"
                                    value={data.hora}
                                    onChange={(e) => setData('hora', e.target.value)}
                                    className="w-full rounded-md border-gray-300 border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
                                />
                                {errors.hora && (
                                    <p className="mt-1 text-xs text-red-600">{errors.hora}</p>
                                )}
                            </div>

                            <div>
                                <label htmlFor="local" className="block text-sm font-medium text-gray-700 mb-1">
                                    Local <span className="text-red-500">*</span>
                                </label>
                                <input
                                    id="local"
                                    type="text"
                                    value={data.local}
                                    onChange={(e) => setData('local', e.target.value)}
                                    placeholder="Ex: Quadra 1"
                                    className="w-full rounded-md border-gray-300 border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
                                />
                                {errors.local && (
                                    <p className="mt-1 text-xs text-red-600">{errors.local}</p>
                                )}
                            </div>
                        </div>

                        <div>
                            <label className="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    checked={data.cancelado}
                                    onChange={(e) => setData('cancelado', e.target.checked)}
                                    className="rounded border-gray-300"
                                />
                                <span className="text-sm text-gray-700">Marcar como cancelado</span>
                            </label>
                        </div>

                        <div className="flex items-center gap-4 pt-2">
                            <button
                                type="submit"
                                disabled={processing}
                                className="bg-primary text-primary-foreground px-5 py-2 rounded-md text-sm font-medium hover:opacity-90 transition-opacity disabled:opacity-50"
                            >
                                {processing ? 'Salvando...' : 'Salvar Alterações'}
                            </button>
                            <Link
                                href="/calendario"
                                className="text-sm text-gray-500 hover:text-gray-700"
                            >
                                Cancelar
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </Layout>
    );
}

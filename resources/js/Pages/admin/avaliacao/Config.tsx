import { useForm } from '@inertiajs/react';
import { Head, Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

interface AvaliacaoConfig {
    id: number;
    num_jurados: number;
    nota_min: number;
    nota_max: number;
    pontos_bonus_melhor: number;
}

interface Props {
    config: AvaliacaoConfig | null;
}

export default function AvaliacaoConfigPage({ config }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        num_jurados: config?.num_jurados ?? 3,
        nota_min: config?.nota_min ?? 0,
        nota_max: config?.nota_max ?? 10,
        pontos_bonus_melhor: config?.pontos_bonus_melhor ?? 10,
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post(route('admin.avaliacao-config.store'));
    }

    return (
        <AdminLayout title="Configuração de Avaliação">
            <Head title="Configuração de Avaliação" />

            <div className="max-w-xl">
                <div className="mb-6">
                    <h1 className="text-2xl font-bold text-gray-900">Configuração de Avaliação de Bandeira/Grito</h1>
                    <p className="text-sm text-gray-500 mt-1">
                        Defina os parâmetros que serão usados no registro de notas dos jurados.
                    </p>
                </div>

                <div className="bg-surface rounded-xl shadow-sm p-6">
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div>
                            <label htmlFor="num_jurados" className="block text-sm font-medium text-gray-700 mb-1">
                                Número de Jurados
                            </label>
                            <input
                                id="num_jurados"
                                type="number"
                                min="1"
                                max="10"
                                value={data.num_jurados}
                                onChange={(e) => setData('num_jurados', parseInt(e.target.value) || 1)}
                                className="w-full rounded-md border-gray-300 border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
                            />
                            {errors.num_jurados && (
                                <p className="mt-1 text-xs text-red-600">{errors.num_jurados}</p>
                            )}
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label htmlFor="nota_min" className="block text-sm font-medium text-gray-700 mb-1">
                                    Nota Mínima
                                </label>
                                <input
                                    id="nota_min"
                                    type="number"
                                    step="0.1"
                                    min="0"
                                    value={data.nota_min}
                                    onChange={(e) => setData('nota_min', parseFloat(e.target.value) || 0)}
                                    className="w-full rounded-md border-gray-300 border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
                                />
                                {errors.nota_min && (
                                    <p className="mt-1 text-xs text-red-600">{errors.nota_min}</p>
                                )}
                            </div>

                            <div>
                                <label htmlFor="nota_max" className="block text-sm font-medium text-gray-700 mb-1">
                                    Nota Máxima
                                </label>
                                <input
                                    id="nota_max"
                                    type="number"
                                    step="0.1"
                                    min="0"
                                    value={data.nota_max}
                                    onChange={(e) => setData('nota_max', parseFloat(e.target.value) || 10)}
                                    className="w-full rounded-md border-gray-300 border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
                                />
                                {errors.nota_max && (
                                    <p className="mt-1 text-xs text-red-600">{errors.nota_max}</p>
                                )}
                            </div>
                        </div>

                        <div>
                            <label htmlFor="pontos_bonus_melhor" className="block text-sm font-medium text-gray-700 mb-1">
                                Pontos Bônus para Melhor Bandeira
                            </label>
                            <input
                                id="pontos_bonus_melhor"
                                type="number"
                                min="0"
                                value={data.pontos_bonus_melhor}
                                onChange={(e) => setData('pontos_bonus_melhor', parseInt(e.target.value) || 0)}
                                className="w-full rounded-md border-gray-300 border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
                            />
                            <p className="text-xs text-gray-500 mt-1">
                                Pontos extras para a turma com maior nota total na categoria
                            </p>
                            {errors.pontos_bonus_melhor && (
                                <p className="mt-1 text-xs text-red-600">{errors.pontos_bonus_melhor}</p>
                            )}
                        </div>

                        <div className="flex items-center gap-4 pt-2">
                            <button
                                type="submit"
                                disabled={processing}
                                className="bg-primary text-primary-foreground px-5 py-2 rounded-md text-sm font-medium hover:opacity-90 transition-opacity disabled:opacity-50"
                            >
                                {processing ? 'Salvando...' : 'Salvar Configuração'}
                            </button>
                            <Link
                                href={route('admin.avaliacao-notas.index')}
                                className="text-sm text-gray-500 hover:text-gray-700"
                            >
                                Ir para Registro de Notas
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}

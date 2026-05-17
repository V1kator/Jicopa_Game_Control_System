import { useForm } from '@inertiajs/react';
import { Head, Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

interface Categoria {
    id: number;
    name: string;
}

interface Turma {
    id: number;
    name: string;
    period: string;
    categorias: Categoria[];
}

interface Props {
    turma: Turma;
    categorias: Categoria[];
}

export default function TurmasEdit({ turma, categorias }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        name: turma.name,
        period: turma.period,
        categorias: turma.categorias.map((c) => c.id),
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        put(`/admin/turmas/${turma.id}`);
    }

    function toggleCategoria(id: number) {
        if (data.categorias.includes(id)) {
            setData('categorias', data.categorias.filter((c) => c !== id));
        } else {
            setData('categorias', [...data.categorias, id]);
        }
    }

    return (
        <AdminLayout title="Editar Turma">
            <Head title="Editar Turma" />

            <div className="max-w-xl">
                <div className="bg-surface rounded-xl shadow-sm p-6">
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div>
                            <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-1">
                                Nome da Turma
                            </label>
                            <input
                                id="name"
                                type="text"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                className="w-full rounded-md border-gray-300 border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
                                placeholder="Ex: A, B, 1º Ano..."
                                autoFocus
                            />
                            {errors.name && (
                                <p className="mt-1 text-xs text-red-600">{errors.name}</p>
                            )}
                        </div>

                        <div>
                            <label htmlFor="period" className="block text-sm font-medium text-gray-700 mb-1">
                                Período
                            </label>
                            <select
                                id="period"
                                value={data.period}
                                onChange={(e) => setData('period', e.target.value)}
                                className="w-full rounded-md border-gray-300 border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
                            >
                                <option value="Matutino">Matutino</option>
                                <option value="Vespertino">Vespertino</option>
                            </select>
                            {errors.period && (
                                <p className="mt-1 text-xs text-red-600">{errors.period}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Categorias
                            </label>
                            <div className="border border-gray-300 rounded-md p-3 space-y-2 max-h-48 overflow-y-auto">
                                {categorias.length > 0 ? (
                                    categorias.map((categoria) => (
                                        <label key={categoria.id} className="flex items-center gap-2 cursor-pointer">
                                            <input
                                                type="checkbox"
                                                checked={data.categorias.includes(categoria.id)}
                                                onChange={() => toggleCategoria(categoria.id)}
                                                className="rounded border-gray-300 text-primary focus:ring-primary/40"
                                            />
                                            <span className="text-sm text-gray-700">{categoria.name}</span>
                                        </label>
                                    ))
                                ) : (
                                    <p className="text-sm text-gray-400">Nenhuma categoria disponível</p>
                                )}
                            </div>
                            {errors.categorias && (
                                <p className="mt-1 text-xs text-red-600">{errors.categorias}</p>
                            )}
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
                                href="/admin/turmas"
                                className="text-sm text-gray-500 hover:text-gray-700"
                            >
                                Cancelar
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}

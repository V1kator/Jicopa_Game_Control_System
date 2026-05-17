import { useForm } from '@inertiajs/react';
import { Head, Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

interface Turma {
    id: number;
    name: string;
    period: string;
}

interface Esporte {
    id: number;
    name: string;
}

interface Categoria {
    id: number;
    name: string;
    turmas: Turma[];
    esportes: Esporte[];
}

interface Props {
    categoria: Categoria;
    turmas: Turma[];
}

export default function CategoriasEdit({ categoria, turmas }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        name: categoria.name,
        turmas: categoria.turmas.map((t) => t.id),
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        put(`/admin/categorias/${categoria.id}`);
    }

    function toggleTurma(id: number) {
        if (data.turmas.includes(id)) {
            setData('turmas', data.turmas.filter((t) => t !== id));
        } else {
            setData('turmas', [...data.turmas, id]);
        }
    }

    return (
        <AdminLayout title="Editar Categoria">
            <Head title="Editar Categoria" />

            <div className="max-w-xl">
                <div className="bg-surface rounded-xl shadow-sm p-6">
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div>
                            <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-1">
                                Nome da Categoria
                            </label>
                            <input
                                id="name"
                                type="text"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                className="w-full rounded-md border-gray-300 border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
                                placeholder="Ex: Mirim, Infantil, Juvenil..."
                                autoFocus
                            />
                            {errors.name && (
                                <p className="mt-1 text-xs text-red-600">{errors.name}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Turmas
                            </label>
                            <div className="border border-gray-300 rounded-md p-3 space-y-2 max-h-64 overflow-y-auto">
                                {turmas.length > 0 ? (
                                    turmas.map((turma) => (
                                        <label key={turma.id} className="flex items-center gap-2 cursor-pointer">
                                            <input
                                                type="checkbox"
                                                checked={data.turmas.includes(turma.id)}
                                                onChange={() => toggleTurma(turma.id)}
                                                className="rounded border-gray-300 text-primary focus:ring-primary/40"
                                            />
                                            <span className="text-sm text-gray-700">
                                                {turma.name} - {turma.period}
                                            </span>
                                        </label>
                                    ))
                                ) : (
                                    <p className="text-sm text-gray-400">Nenhuma turma disponível</p>
                                )}
                            </div>
                            {errors.turmas && (
                                <p className="mt-1 text-xs text-red-600">{errors.turmas}</p>
                            )}
                        </div>

                        {categoria.esportes.length > 0 && (
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Esportes Vinculados (gerenciado em Esportes)
                                </label>
                                <div className="border border-gray-200 rounded-md p-3 bg-gray-50">
                                    <div className="flex flex-wrap gap-2">
                                        {categoria.esportes.map((esporte) => (
                                            <span
                                                key={esporte.id}
                                                className="inline-flex items-center px-2.5 py-1 rounded text-xs bg-green-100 text-green-700"
                                            >
                                                {esporte.name}
                                            </span>
                                        ))}
                                    </div>
                                </div>
                            </div>
                        )}

                        <div className="flex items-center gap-4 pt-2">
                            <button
                                type="submit"
                                disabled={processing}
                                className="bg-primary text-primary-foreground px-5 py-2 rounded-md text-sm font-medium hover:opacity-90 transition-opacity disabled:opacity-50"
                            >
                                {processing ? 'Salvando...' : 'Salvar Alterações'}
                            </button>
                            <Link
                                href="/admin/categorias"
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

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

interface Aluno {
    id: number;
    name: string;
    turma_id: number;
    period: string;
    turma: Turma;
    esportes: Esporte[];
}

interface Props {
    aluno: Aluno;
    turmas: Turma[];
    esportes: Esporte[];
}

export default function AlunosEdit({ aluno, turmas, esportes }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        name: aluno.name,
        turma_id: aluno.turma_id.toString(),
        period: aluno.period,
        esportes: aluno.esportes.map((e) => e.id),
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        put(`/admin/alunos/${aluno.id}`);
    }

    function toggleEsporte(id: number) {
        if (data.esportes.includes(id)) {
            setData('esportes', data.esportes.filter((e) => e !== id));
        } else {
            setData('esportes', [...data.esportes, id]);
        }
    }

    function handleTurmaChange(turmaId: string) {
        setData('turma_id', turmaId);
        
        // Auto-fill period based on selected turma
        if (turmaId) {
            const selectedTurma = turmas.find(t => t.id === parseInt(turmaId));
            if (selectedTurma) {
                setData('period', selectedTurma.period);
            }
        }
    }

    return (
        <AdminLayout title="Editar Aluno">
            <Head title="Editar Aluno" />

            <div className="max-w-xl">
                <div className="bg-surface rounded-xl shadow-sm p-6">
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div>
                            <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-1">
                                Nome do Aluno
                            </label>
                            <input
                                id="name"
                                type="text"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                className="w-full rounded-md border-gray-300 border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
                                placeholder="Nome completo do aluno"
                                autoFocus
                            />
                            {errors.name && (
                                <p className="mt-1 text-xs text-red-600">{errors.name}</p>
                            )}
                        </div>

                        <div>
                            <label htmlFor="turma_id" className="block text-sm font-medium text-gray-700 mb-1">
                                Turma
                            </label>
                            <select
                                id="turma_id"
                                value={data.turma_id}
                                onChange={(e) => handleTurmaChange(e.target.value)}
                                className="w-full rounded-md border-gray-300 border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
                            >
                                <option value="">Selecione uma turma</option>
                                {turmas.map((turma) => (
                                    <option key={turma.id} value={turma.id}>
                                        {turma.name} - {turma.period}
                                    </option>
                                ))}
                            </select>
                            {errors.turma_id && (
                                <p className="mt-1 text-xs text-red-600">{errors.turma_id}</p>
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
                            <p className="mt-1 text-xs text-gray-500">
                                Deve corresponder ao período da turma selecionada
                            </p>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Esportes
                            </label>
                            <div className="border border-gray-300 rounded-md p-3 space-y-2 max-h-48 overflow-y-auto">
                                {esportes.length > 0 ? (
                                    esportes.map((esporte) => (
                                        <label key={esporte.id} className="flex items-center gap-2 cursor-pointer">
                                            <input
                                                type="checkbox"
                                                checked={data.esportes.includes(esporte.id)}
                                                onChange={() => toggleEsporte(esporte.id)}
                                                className="rounded border-gray-300 text-primary focus:ring-primary/40"
                                            />
                                            <span className="text-sm text-gray-700">{esporte.name}</span>
                                        </label>
                                    ))
                                ) : (
                                    <p className="text-sm text-gray-400">Nenhum esporte disponível</p>
                                )}
                            </div>
                            {errors.esportes && (
                                <p className="mt-1 text-xs text-red-600">{errors.esportes}</p>
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
                                href="/admin/alunos"
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

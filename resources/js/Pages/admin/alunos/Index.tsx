import { Link, router } from '@inertiajs/react';
import { Head } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useState } from 'react';
import ConfirmDialog from '@/Components/shared/ConfirmDialog';

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
    period: string;
    active: boolean;
    turma: Turma;
    esportes: Esporte[];
}

interface Props {
    alunos: Aluno[];
    turmas: Turma[];
    filters: {
        turma_id?: string;
        period?: string;
        active?: string;
    };
}

export default function AlunosIndex({ alunos, turmas, filters }: Props) {
    const [turmaId, setTurmaId] = useState(filters.turma_id || '');
    const [period, setPeriod] = useState(filters.period || '');
    const [active, setActive] = useState(filters.active || 'true');

    const [confirmDialog, setConfirmDialog] = useState<{
        open: boolean;
        title: string;
        message: string;
        onConfirm: () => void;
        variant: 'danger' | 'default';
    }>({ open: false, title: '', message: '', onConfirm: () => {}, variant: 'danger' });

    function closeDialog() {
        setConfirmDialog(prev => ({ ...prev, open: false }));
    }

    function handleFilter() {
        router.get('/admin/alunos', {
            turma_id: turmaId || undefined,
            period: period || undefined,
            active: active === 'true' ? undefined : active,
        }, {
            preserveState: true,
            replace: true,
        });
    }

    function handleDeactivate(aluno: Aluno) {
        setConfirmDialog({
            open: true,
            title: 'Desativar Aluno',
            message: `Desativar o aluno "${aluno.name}"?`,
            onConfirm: () => {
                closeDialog();
                router.delete(`/admin/alunos/${aluno.id}`);
            },
            variant: 'danger',
        });
    }

    function handleRestore(aluno: Aluno) {
        setConfirmDialog({
            open: true,
            title: 'Reativar Aluno',
            message: `Reativar o aluno "${aluno.name}"?`,
            onConfirm: () => {
                closeDialog();
                router.post(`/admin/alunos/${aluno.id}/restore`);
            },
            variant: 'default',
        });
    }

    return (
        <AdminLayout title="Alunos">
            <Head title="Alunos" />

            <div className="mb-6 flex items-center justify-between">
                <div className="flex gap-3">
                    <select
                        value={turmaId}
                        onChange={(e) => setTurmaId(e.target.value)}
                        className="border border-gray-300 rounded-md px-3 py-2 text-sm"
                    >
                        <option value="">Todas as turmas</option>
                        {turmas.map((turma) => (
                            <option key={turma.id} value={turma.id}>
                                {turma.name} - {turma.period}
                            </option>
                        ))}
                    </select>
                    <select
                        value={period}
                        onChange={(e) => setPeriod(e.target.value)}
                        className="border border-gray-300 rounded-md px-3 py-2 text-sm"
                    >
                        <option value="">Todos os períodos</option>
                        <option value="Matutino">Matutino</option>
                        <option value="Vespertino">Vespertino</option>
                    </select>
                    <select
                        value={active}
                        onChange={(e) => setActive(e.target.value)}
                        className="border border-gray-300 rounded-md px-3 py-2 text-sm"
                    >
                        <option value="true">Ativos</option>
                        <option value="false">Inativos</option>
                        <option value="all">Todos</option>
                    </select>
                    <button
                        onClick={handleFilter}
                        className="bg-gray-100 text-gray-700 px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-200 transition-colors"
                    >
                        Filtrar
                    </button>
                </div>
                <Link
                    href="/admin/alunos/create"
                    className="bg-primary text-primary-foreground px-4 py-2 rounded-md text-sm font-medium hover:opacity-90 transition-opacity"
                >
                    Novo Aluno
                </Link>
            </div>

            <div className="bg-surface rounded-xl shadow-sm overflow-hidden">
                <table className="w-full text-sm">
                    <thead className="bg-gray-50 border-b">
                        <tr>
                            <th className="text-left px-6 py-3 text-gray-600 font-medium">Nome</th>
                            <th className="text-left px-6 py-3 text-gray-600 font-medium">Turma</th>
                            <th className="text-left px-6 py-3 text-gray-600 font-medium">Período</th>
                            <th className="text-left px-6 py-3 text-gray-600 font-medium">Esportes</th>
                            <th className="text-left px-6 py-3 text-gray-600 font-medium">Status</th>
                            <th className="text-right px-6 py-3 text-gray-600 font-medium">Ações</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {alunos.map((aluno) => (
                            <tr key={aluno.id} className="hover:bg-gray-50">
                                <td className="px-6 py-4 font-medium text-gray-900">{aluno.name}</td>
                                <td className="px-6 py-4 text-gray-600">
                                    {aluno.turma.name}
                                </td>
                                <td className="px-6 py-4 text-gray-600">{aluno.period}</td>
                                <td className="px-6 py-4">
                                    {aluno.esportes.length > 0 ? (
                                        <div className="flex flex-wrap gap-1">
                                            {aluno.esportes.map((esporte) => (
                                                <span
                                                    key={esporte.id}
                                                    className="inline-flex items-center px-2 py-0.5 rounded text-xs bg-green-100 text-green-700"
                                                >
                                                    {esporte.name}
                                                </span>
                                            ))}
                                        </div>
                                    ) : (
                                        <span className="text-gray-400 text-xs">Nenhum</span>
                                    )}
                                </td>
                                <td className="px-6 py-4">
                                    {aluno.active ? (
                                        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-light/20 text-primary">
                                            Ativo
                                        </span>
                                    ) : (
                                        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                            Inativo
                                        </span>
                                    )}
                                </td>
                                <td className="px-6 py-4 text-right space-x-2">
                                    <Link
                                        href={`/admin/alunos/${aluno.id}/edit`}
                                        className="text-primary hover:underline text-sm"
                                    >
                                        Editar
                                    </Link>
                                    {aluno.active && (
                                        <button
                                            onClick={() => handleDeactivate(aluno)}
                                            className="text-accent-orange hover:underline text-sm"
                                        >
                                            Desativar
                                        </button>
                                    )}
                                    {!aluno.active && (
                                        <button
                                            onClick={() => handleRestore(aluno)}
                                            className="text-green-600 hover:underline text-sm"
                                        >
                                            Reativar
                                        </button>
                                    )}
                                </td>
                            </tr>
                        ))}
                        {alunos.length === 0 && (
                            <tr>
                                <td colSpan={6} className="px-6 py-8 text-center text-gray-400">
                                    Nenhum aluno cadastrado.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <ConfirmDialog
                open={confirmDialog.open}
                title={confirmDialog.title}
                message={confirmDialog.message}
                onConfirm={confirmDialog.onConfirm}
                onCancel={closeDialog}
                variant={confirmDialog.variant}
            />
        </AdminLayout>
    );
}

import { Link, router, usePage } from '@inertiajs/react';
import { Head } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useState } from 'react';
import ConfirmDialog from '@/Components/shared/ConfirmDialog';

interface Categoria {
    id: number;
    name: string;
}

interface Turma {
    id: number;
    name: string;
    period: string;
    active: boolean;
    categorias: Categoria[];
}

interface Props {
    turmas: Turma[];
    filters: {
        period?: string;
        active?: string;
    };
}

export default function TurmasIndex({ turmas, filters }: Props) {
    const [period, setPeriod] = useState(filters.period || '');
    const [active, setActive] = useState(filters.active || 'true');
    const reactivateId = (usePage().props as any).reactivate_id as number | undefined;

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
        router.get('/admin/turmas', {
            period: period || undefined,
            active: active === 'true' ? undefined : active,
        }, {
            preserveState: true,
            replace: true,
        });
    }

    function handleDeactivate(turma: Turma) {
        setConfirmDialog({
            open: true,
            title: 'Desativar Turma',
            message: `Desativar a turma "${turma.name}" (${turma.period})?`,
            onConfirm: () => {
                closeDialog();
                router.delete(`/admin/turmas/${turma.id}`);
            },
            variant: 'danger',
        });
    }

    function handleRestore(turma: Turma) {
        setConfirmDialog({
            open: true,
            title: 'Reativar Turma',
            message: `Reativar a turma "${turma.name}" (${turma.period})?`,
            onConfirm: () => {
                closeDialog();
                router.post(`/admin/turmas/${turma.id}/restore`);
            },
            variant: 'default',
        });
    }

    return (
        <AdminLayout title="Turmas">
            <Head title="Turmas" />

            <div className="mb-6 flex items-center justify-between">
                <div className="flex gap-3">
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
                        <option value="true">Ativas</option>
                        <option value="false">Inativas</option>
                        <option value="all">Todas</option>
                    </select>
                    <button
                        onClick={handleFilter}
                        className="bg-gray-100 text-gray-700 px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-200 transition-colors"
                    >
                        Filtrar
                    </button>
                </div>
                <Link
                    href="/admin/turmas/create"
                    className="bg-primary text-primary-foreground px-4 py-2 rounded-md text-sm font-medium hover:opacity-90 transition-opacity"
                >
                    Nova Turma
                </Link>
            </div>

            {reactivateId && (
                <div className="mb-4 p-4 bg-yellow-50 border border-yellow-300 rounded-md flex items-center justify-between">
                    <p className="text-sm text-yellow-800">
                        Já existe um registro inativo com esse nome. Deseja reativá-lo?
                    </p>
                    <button
                        onClick={() => router.post(`/admin/turmas/${reactivateId}/restore`)}
                        className="ml-4 bg-green-600 text-white px-3 py-1.5 rounded-md text-sm font-medium hover:bg-green-700"
                    >
                        Reativar
                    </button>
                </div>
            )}

            <div className="bg-surface rounded-xl shadow-sm overflow-hidden">
                <table className="w-full text-sm">
                    <thead className="bg-gray-50 border-b">
                        <tr>
                            <th className="text-left px-6 py-3 text-gray-600 font-medium">Nome</th>
                            <th className="text-left px-6 py-3 text-gray-600 font-medium">Período</th>
                            <th className="text-left px-6 py-3 text-gray-600 font-medium">Categorias</th>
                            <th className="text-left px-6 py-3 text-gray-600 font-medium">Status</th>
                            <th className="text-right px-6 py-3 text-gray-600 font-medium">Ações</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {turmas.map((turma) => (
                            <tr key={turma.id} className="hover:bg-gray-50">
                                <td className="px-6 py-4 font-medium text-gray-900">{turma.name}</td>
                                <td className="px-6 py-4 text-gray-600">{turma.period}</td>
                                <td className="px-6 py-4">
                                    {turma.categorias.length > 0 ? (
                                        <div className="flex flex-wrap gap-1">
                                            {turma.categorias.map((cat) => (
                                                <span
                                                    key={cat.id}
                                                    className="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-700"
                                                >
                                                    {cat.name}
                                                </span>
                                            ))}
                                        </div>
                                    ) : (
                                        <span className="text-gray-400 text-xs">Nenhuma</span>
                                    )}
                                </td>
                                <td className="px-6 py-4">
                                    {turma.active ? (
                                        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-light/20 text-primary">
                                            Ativa
                                        </span>
                                    ) : (
                                        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                            Inativa
                                        </span>
                                    )}
                                </td>
                                <td className="px-6 py-4 text-right space-x-2">
                                    <Link
                                        href={`/admin/turmas/${turma.id}/edit`}
                                        className="text-primary hover:underline text-sm"
                                    >
                                        Editar
                                    </Link>
                                    {turma.active && (
                                        <button
                                            onClick={() => handleDeactivate(turma)}
                                            className="text-accent-orange hover:underline text-sm"
                                        >
                                            Desativar
                                        </button>
                                    )}
                                    {!turma.active && (
                                        <button
                                            onClick={() => handleRestore(turma)}
                                            className="text-green-600 hover:underline text-sm"
                                        >
                                            Reativar
                                        </button>
                                    )}
                                </td>
                            </tr>
                        ))}
                        {turmas.length === 0 && (
                            <tr>
                                <td colSpan={5} className="px-6 py-8 text-center text-gray-400">
                                    Nenhuma turma cadastrada.
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

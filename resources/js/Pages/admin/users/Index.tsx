import { Link, router } from '@inertiajs/react';
import { Head } from '@inertiajs/react';
import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import ConfirmDialog from '@/Components/shared/ConfirmDialog';

interface Professor {
    id: number;
    name: string;
    email: string;
    active: boolean;
    created_at: string;
}

interface Props {
    professors: Professor[];
}

export default function UsersIndex({ professors }: Props) {
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

    function handleDeactivate(professor: Professor) {
        setConfirmDialog({
            open: true,
            title: 'Desativar Conta',
            message: `Desativar a conta de "${professor.name}"? O professor não conseguirá mais acessar o sistema.`,
            onConfirm: () => {
                closeDialog();
                router.delete(`/admin/users/${professor.id}`);
            },
            variant: 'danger',
        });
    }

    function handleRestore(professor: Professor) {
        setConfirmDialog({
            open: true,
            title: 'Reativar Conta',
            message: `Reativar a conta de "${professor.name}"?`,
            onConfirm: () => {
                closeDialog();
                router.post(`/admin/users/${professor.id}/restore`);
            },
            variant: 'default',
        });
    }

    return (
        <AdminLayout title="Professores">
            <Head title="Professores" />

            <div className="mb-6 flex items-center justify-between">
                <p className="text-gray-500 text-sm">{professors.length} professor(es) cadastrado(s)</p>
                <Link
                    href="/admin/users/create"
                    className="bg-primary text-primary-foreground px-4 py-2 rounded-md text-sm font-medium hover:opacity-90 transition-opacity"
                >
                    Novo Professor
                </Link>
            </div>

            <div className="bg-surface rounded-xl shadow-sm overflow-hidden">
                <table className="w-full text-sm">
                    <thead className="bg-gray-50 border-b">
                        <tr>
                            <th className="text-left px-6 py-3 text-gray-600 font-medium">Nome</th>
                            <th className="text-left px-6 py-3 text-gray-600 font-medium">Email</th>
                            <th className="text-left px-6 py-3 text-gray-600 font-medium">Status</th>
                            <th className="text-right px-6 py-3 text-gray-600 font-medium">Ações</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {professors.map((professor) => (
                            <tr key={professor.id} className="hover:bg-gray-50">
                                <td className="px-6 py-4 font-medium text-gray-900">{professor.name}</td>
                                <td className="px-6 py-4 text-gray-600">{professor.email}</td>
                                <td className="px-6 py-4">
                                    {professor.active ? (
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
                                        href={`/admin/users/${professor.id}/edit`}
                                        className="text-primary hover:underline text-sm"
                                    >
                                        Editar
                                    </Link>
                                    {professor.active && (
                                        <button
                                            onClick={() => handleDeactivate(professor)}
                                            className="text-accent-orange hover:underline text-sm"
                                        >
                                            Desativar
                                        </button>
                                    )}
                                    {!professor.active && (
                                        <button
                                            onClick={() => handleRestore(professor)}
                                            className="text-green-600 hover:underline text-sm"
                                        >
                                            Reativar
                                        </button>
                                    )}
                                </td>
                            </tr>
                        ))}
                        {professors.length === 0 && (
                            <tr>
                                <td colSpan={4} className="px-6 py-8 text-center text-gray-400">
                                    Nenhum professor cadastrado.
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

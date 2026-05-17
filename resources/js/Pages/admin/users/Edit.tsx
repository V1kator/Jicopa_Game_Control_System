import { useForm, router } from '@inertiajs/react';
import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import ConfirmDialog from '@/Components/shared/ConfirmDialog';

interface Professor {
    id: number;
    name: string;
    email: string;
    active: boolean;
}

interface Props {
    professor: Professor;
}

export default function UsersEdit({ professor }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        name: professor.name,
        email: professor.email,
    });

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

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        put(`/admin/users/${professor.id}`);
    }

    function handleDeactivate() {
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

    return (
        <AdminLayout title="Editar Professor">
            <Head title="Editar Professor" />

            <div className="max-w-xl space-y-6">
                {/* Edit form */}
                <div className="bg-surface rounded-xl shadow-sm p-6">
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div>
                            <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-1">
                                Nome
                            </label>
                            <input
                                id="name"
                                type="text"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                className="w-full rounded-md border-gray-300 border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
                                autoFocus
                            />
                            {errors.name && (
                                <p className="mt-1 text-xs text-red-600">{errors.name}</p>
                            )}
                        </div>

                        <div>
                            <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-1">
                                Email
                            </label>
                            <input
                                id="email"
                                type="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                className="w-full rounded-md border-gray-300 border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
                            />
                            {errors.email && (
                                <p className="mt-1 text-xs text-red-600">{errors.email}</p>
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
                                href="/admin/users"
                                className="text-sm text-gray-500 hover:text-gray-700"
                            >
                                Cancelar
                            </Link>
                        </div>
                    </form>
                </div>

                {/* Danger zone — deactivate account */}
                {professor.active && (
                    <div className="bg-surface rounded-xl shadow-sm p-6 border border-accent-orange/30">
                        <h3 className="text-sm font-semibold text-gray-800 mb-1">Desativar Conta</h3>
                        <p className="text-xs text-gray-500 mb-4">
                            O professor não conseguirá mais acessar o sistema. Os dados históricos são preservados.
                        </p>
                        <button
                            onClick={handleDeactivate}
                            className="bg-accent-orange text-white px-4 py-2 rounded-md text-sm font-medium hover:opacity-90 transition-opacity"
                        >
                            Desativar Conta
                        </button>
                    </div>
                )}
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

import { Link, router, usePage } from '@inertiajs/react';
import { Head } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useState } from 'react';
import ConfirmDialog from '@/Components/shared/ConfirmDialog';

interface Categoria {
    id: number;
    name: string;
}

interface Esporte {
    id: number;
    name: string;
    type: string;
    active: boolean;
    categorias: Categoria[];
}

interface Props {
    esportes: Esporte[];
    filters: {
        type?: string;
        active?: string;
    };
}

export default function EsportesIndex({ esportes, filters }: Props) {
    const [type, setType] = useState(filters.type || '');
    const [active, setActive] = useState(filters.active || '');
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
        router.get('/admin/esportes', {
            type: type || undefined,
            active: active || undefined,
        }, {
            preserveState: true,
            replace: true,
        });
    }

    function handleDeactivate(esporte: Esporte) {
        setConfirmDialog({
            open: true,
            title: 'Desativar Esporte',
            message: `Desativar o esporte "${esporte.name}"?`,
            onConfirm: () => {
                closeDialog();
                router.delete(`/admin/esportes/${esporte.id}`);
            },
            variant: 'danger',
        });
    }

    function handleRestore(esporte: Esporte) {
        setConfirmDialog({
            open: true,
            title: 'Reativar Esporte',
            message: `Reativar o esporte "${esporte.name}"?`,
            onConfirm: () => {
                closeDialog();
                router.post(`/admin/esportes/${esporte.id}/restore`);
            },
            variant: 'default',
        });
    }

    return (
        <AdminLayout title="Esportes">
            <Head title="Esportes" />

            <div className="mb-6 flex items-center justify-between">
                <div className="flex gap-3">
                    <select
                        value={type}
                        onChange={(e) => setType(e.target.value)}
                        className="border border-gray-300 rounded-md px-3 py-2 text-sm"
                    >
                        <option value="">Todos os tipos</option>
                        <option value="coletivo">Coletivo</option>
                        <option value="individual">Individual</option>
                    </select>
                    <select
                        value={active}
                        onChange={(e) => setActive(e.target.value)}
                        className="border border-gray-300 rounded-md px-3 py-2 text-sm"
                    >
                        <option value="">Todos os status</option>
                        <option value="true">Ativos</option>
                        <option value="false">Inativos</option>
                    </select>
                    <button
                        onClick={handleFilter}
                        className="bg-gray-100 text-gray-700 px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-200 transition-colors"
                    >
                        Filtrar
                    </button>
                </div>
                <Link
                    href="/admin/esportes/create"
                    className="bg-primary text-primary-foreground px-4 py-2 rounded-md text-sm font-medium hover:opacity-90 transition-opacity"
                >
                    Novo Esporte
                </Link>
            </div>

            {reactivateId && (
                <div className="mb-4 p-4 bg-yellow-50 border border-yellow-300 rounded-md flex items-center justify-between">
                    <p className="text-sm text-yellow-800">
                        Já existe um registro inativo com esse nome. Deseja reativá-lo?
                    </p>
                    <button
                        onClick={() => router.post(`/admin/esportes/${reactivateId}/restore`)}
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
                            <th className="text-left px-6 py-3 text-gray-600 font-medium">Tipo</th>
                            <th className="text-left px-6 py-3 text-gray-600 font-medium">Categorias</th>
                            <th className="text-left px-6 py-3 text-gray-600 font-medium">Status</th>
                            <th className="text-right px-6 py-3 text-gray-600 font-medium">Ações</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {esportes.map((esporte) => (
                            <tr key={esporte.id} className="hover:bg-gray-50">
                                <td className="px-6 py-4 font-medium text-gray-900">{esporte.name}</td>
                                <td className="px-6 py-4">
                                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                                        esporte.type === 'coletivo' 
                                            ? 'bg-blue-100 text-blue-700' 
                                            : 'bg-purple-100 text-purple-700'
                                    }`}>
                                        {esporte.type === 'coletivo' ? 'Coletivo' : 'Individual'}
                                    </span>
                                </td>
                                <td className="px-6 py-4">
                                    {esporte.categorias.length > 0 ? (
                                        <div className="flex flex-wrap gap-1">
                                            {esporte.categorias.map((cat) => (
                                                <span
                                                    key={cat.id}
                                                    className="inline-flex items-center px-2 py-0.5 rounded text-xs bg-green-100 text-green-700"
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
                                    {esporte.active ? (
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
                                        href={`/admin/esportes/${esporte.id}/edit`}
                                        className="text-primary hover:underline text-sm"
                                    >
                                        Editar
                                    </Link>
                                    {esporte.active && (
                                        <button
                                            onClick={() => handleDeactivate(esporte)}
                                            className="text-accent-orange hover:underline text-sm"
                                        >
                                            Desativar
                                        </button>
                                    )}
                                    {!esporte.active && (
                                        <button
                                            onClick={() => handleRestore(esporte)}
                                            className="text-green-600 hover:underline text-sm"
                                        >
                                            Reativar
                                        </button>
                                    )}
                                </td>
                            </tr>
                        ))}
                        {esportes.length === 0 && (
                            <tr>
                                <td colSpan={5} className="px-6 py-8 text-center text-gray-400">
                                    Nenhum esporte cadastrado.
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

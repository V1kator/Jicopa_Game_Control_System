import { usePage } from '@inertiajs/react';
import { Head } from '@inertiajs/react';
import type { PageProps } from '@/types';
import AdminLayout from '@/Layouts/AdminLayout';
import ProfessorLayout from '@/Layouts/ProfessorLayout';

export default function Dashboard() {
    const { auth } = usePage<PageProps>().props;
    const isAdmin = auth.user?.roles.includes('admin') ?? false;
    const Layout = isAdmin ? AdminLayout : ProfessorLayout;

    return (
        <Layout title="Dashboard">
            <Head title="Dashboard" />
            <div className="bg-surface rounded-xl p-6 shadow-sm">
                <h3 className="text-lg font-medium text-gray-800">
                    Bem-vindo, {auth.user?.name}
                </h3>
                <p className="text-gray-500 mt-1">
                    {isAdmin ? 'Painel do Administrador' : 'Painel do Professor'}
                </p>
            </div>
        </Layout>
    );
}

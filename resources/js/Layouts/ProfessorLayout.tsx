import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import { LayoutDashboard, LogOut, Calendar, AlertTriangle, Trophy, School, Users, UserCircle, UserCheck, ClipboardList, FileText } from 'lucide-react';
import type { PageProps } from '@/types';
import NextGameCard from '@/Components/shared/NextGameCard';

interface ProfessorLayoutProps {
    children: React.ReactNode;
    title?: string;
}

class NextGameErrorBoundary extends React.Component<
    { children: React.ReactNode },
    { hasError: boolean }
> {
    constructor(props: { children: React.ReactNode }) {
        super(props);
        this.state = { hasError: false };
    }
    static getDerivedStateFromError() {
        return { hasError: true };
    }
    render() {
        if (this.state.hasError) {
            return (
                <p className="text-sm text-gray-500 px-2">
                    Erro ao carregar próximo jogo.
                </p>
            );
        }
        return this.props.children;
    }
}

export default function ProfessorLayout({ children, title }: ProfessorLayoutProps) {
    const { auth, nextGame } = usePage<PageProps>().props;

    return (
        <div className="flex h-screen bg-gray-100">
            {/* Sidebar — dark green per design system, restricted navigation for professors */}
            <aside className="w-64 bg-primary text-primary-foreground flex flex-col">
                {/* Logo/brand */}
                <div className="p-6 border-b border-primary-light/20">
                    <h1 className="text-xl font-bold">Jicopa</h1>
                    <p className="text-sm text-primary-foreground/70 mt-1">{auth.user?.name}</p>
                </div>

                {/* Navigation — all operations except admin-only (Professores, Pontuação, Avaliação) */}
                <nav className="flex-1 p-4 space-y-1">
                    <Link
                        href="/dashboard"
                        className="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-primary-light/20 transition-colors"
                    >
                        <LayoutDashboard size={18} />
                        <span>Dashboard</span>
                    </Link>
                    <Link
                        href="/admin/turmas"
                        className="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-primary-light/20 transition-colors"
                    >
                        <School size={18} />
                        <span>Turmas</span>
                    </Link>
                    <Link
                        href="/admin/categorias"
                        className="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-primary-light/20 transition-colors"
                    >
                        <Users size={18} />
                        <span>Categorias</span>
                    </Link>
                    <Link
                        href="/admin/esportes"
                        className="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-primary-light/20 transition-colors"
                    >
                        <Trophy size={18} />
                        <span>Esportes</span>
                    </Link>
                    <Link
                        href="/admin/alunos"
                        className="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-primary-light/20 transition-colors"
                    >
                        <UserCircle size={18} />
                        <span>Atletas</span>
                    </Link>
                    <Link
                        href="/calendario"
                        className="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-primary-light/20 transition-colors"
                    >
                        <Calendar size={18} />
                        <span>Jogos</span>
                    </Link>

                    <Link
                        href="/penalidades"
                        className="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-primary-light/20 transition-colors"
                    >
                        <AlertTriangle size={18} />
                        <span>Penalidades</span>
                    </Link>
                    <Link
                        href={route('ranking.index')}
                        className="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-primary-light/20 transition-colors"
                    >
                        <Trophy size={18} />
                        <span>Ranking</span>
                    </Link>
                    <Link
                        href="/admin/relatorios"
                        className="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-primary-light/20 transition-colors"
                    >
                        <FileText size={18} />
                        <span>Relatórios</span>
                    </Link>
                </nav>

                {/* Logout */}
                <div className="p-4 border-t border-primary-light/20">
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        className="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-primary-light/20 transition-colors w-full text-left"
                    >
                        <LogOut size={18} />
                        <span>Sair</span>
                    </Link>
                </div>
            </aside>

            {/* Main content */}
            <main className="flex-1 overflow-y-auto">
                {title && (
                    <header className="bg-surface border-b px-8 py-4">
                        <h2 className="text-xl font-semibold text-gray-800">{title}</h2>
                    </header>
                )}
                <div className="p-8 space-y-6">
                    {nextGame && (
                        <NextGameErrorBoundary>
                            <NextGameCard nextGame={nextGame} />
                        </NextGameErrorBoundary>
                    )}
                    {children}
                </div>
            </main>
        </div>
    );
}

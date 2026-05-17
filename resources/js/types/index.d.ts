import type { PageProps as InertiaPageProps } from '@inertiajs/core';

export interface User {
    id: number;
    name: string;
    email: string;
    active: boolean;
    roles: string[];
    is_admin: boolean;
    email_verified_at?: string | null;
}

export interface Jogo {
    id: number;
    categoria: { name: string };
    esporte: { name: string; type: string };
    time1?: { name: string; period: string };
    time2?: { name: string; period: string };
    data: string;
    hora: string;
    local: string;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = InertiaPageProps & T & {
    auth: {
        user: User & Record<string, unknown>;
    };
    nextGame?: Jogo | null;
};

export type UserRole = 'desa' | 'pimpasa' | 'kanwil';

export interface User {
    id: number;
    name: string;
    email: string;
    role: UserRole;
    is_active: boolean;
    desa_id?: number | null;
    upt_id?: number | null;
    nip?: string | null;
    golongan?: string | null;
    kontak?: string | null;
}

export interface Auth {
    user: User | null;
}

export interface Flash {
    success?: string | null;
    error?: string | null;
    info?: string | null;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: Auth;
    flash: Flash;
    errors: Record<string, string>;
};

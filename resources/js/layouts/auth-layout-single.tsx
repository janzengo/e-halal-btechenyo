import AuthLayoutTemplate from '@/layouts/auth/auth-card-layout';
import { useEffect } from 'react';

export default function AuthLayout({
    children,
    title,
    description,
    ...props
}: {
    children: React.ReactNode;
    title: string;
    description: string;
}) {
    useEffect(() => {
        // Force light mode by removing dark class and adding light class
        document.documentElement.classList.remove('dark');
        document.documentElement.classList.add('light');
        document.body.classList.remove('dark');
        document.body.classList.add('light');
    }, []);

    return (
        <div className="light min-h-screen bg-gradient-to-tr from-green-50 via-zinc-50 to-emerald-50">
            <AuthLayoutTemplate title={title} description={description} {...props}>
                {children}
            </AuthLayoutTemplate>
        </div>
    );
}

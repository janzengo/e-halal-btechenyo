import AuthLayoutTemplate from '@/layouts/auth/auth-split-layout';
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
        <div className="light">
            <AuthLayoutTemplate title={title} description={description} {...props}>
                {children}
            </AuthLayoutTemplate>
        </div>
    );
}

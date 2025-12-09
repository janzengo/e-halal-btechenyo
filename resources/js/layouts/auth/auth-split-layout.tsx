import AppLogo from '@/components/app-logo';
import AppLogoIcon from '@/components/app-logo-icon';
import { home } from '@/routes';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { type PropsWithChildren } from 'react';

interface AuthLayoutProps {
    title?: string;
    description?: string;
}

export default function AuthSplitLayout({
    children,
    title,
    description,
}: PropsWithChildren<AuthLayoutProps>) {
    const { name } = usePage<SharedData>().props;

    return (
        <div className="relative grid min-h-screen flex-col items-center justify-center px-4 sm:px-8 md:px-12 lg:max-w-none lg:grid-cols-2 lg:h-dvh lg:px-0">
            <div className="bg-muted relative hidden h-full flex-col p-10 text-white lg:flex dark:border-r overflow-hidden">
                {/* Background Image with Overlay */}
                <div 
                    className="absolute inset-0 bg-cover bg-center bg-no-repeat" 
                    style={{ backgroundImage: 'url(/images/cover-images/voter-image.jpg)' }}
                />
                <div className="absolute inset-0 bg-green-500/50" />
                
                {/* Content */}
                <div className="relative z-20 flex flex-col h-full">
                    <Link href={home()} className="flex items-center text-lg font-medium">
                        <AppLogo variant="white" /> 
                    </Link>
                </div>
            </div>
            <div className="w-full py-8 lg:p-8">
                <div className="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[400px] md:w-[500px] lg:w-[350px]">
                    <Link
                        href={home()}
                        className="relative z-20 flex items-center justify-center pt-8 lg:hidden"
                    >
                        <AppLogoIcon className="h-20 fill-current text-black sm:h-12" />
                    </Link>
                    <div className="flex flex-col items-center text-center gap-1">
                        {title === "E-Halal BTECHenyo" ? (
                            <h1 className="text-2xl md:text-4xl font-bold text-muted-foreground">
                                E-Halal<br />BTECHenyo
                            </h1>
                        ) : (
                            <h1 className="text-2xl md:text-4xl font-bold text-muted-foreground">{title}</h1>
                        )}
                        <p className="text-muted-foreground text-[10px] md:text-xs text-balance">{description}</p>
                    </div>
                    {children}
                </div>
            </div>
        </div>
    );
}

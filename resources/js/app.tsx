import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { Toaster } from '@/components/ui/sonner';
import { LoadingProvider } from '@/contexts/loading-context';
import { SkeletonProvider } from '@/components/@admin/@loading/skeleton-provider';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.tsx`,
            import.meta.glob('./pages/**/*.tsx'),
        ),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <LoadingProvider>
                <SkeletonProvider>
                    <App {...props} />
                    <Toaster 
                        position="bottom-right" 
                        richColors 
                        closeButton={true}
                        toastOptions={{
                            style: {
                                zIndex: 9999,
                            },
                        }}
                    />
                </SkeletonProvider>
            </LoadingProvider>
        );
    },
    progress: {
        color: '#16a34a', // Green color to match the theme
        delay: 0,
        includeCSS: true,
        showSpinner: false,
    },
});

// Force light mode globally
document.documentElement.classList.remove('dark');
document.documentElement.classList.add('light');
document.body.classList.remove('dark');
document.body.classList.add('light');

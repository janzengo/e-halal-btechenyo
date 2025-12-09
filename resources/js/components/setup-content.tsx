import * as React from 'react';

interface SetupContentProps extends React.ComponentProps<'main'> {
    children: React.ReactNode;
}

export function SetupContent({
    children,
    className = '',
    ...props
}: SetupContentProps) {
    return (
        <main
            className={`mx-auto flex h-full w-full max-w-7xl flex-1 flex-col gap-4 rounded-xl p-6 md:p-8 lg:p-10 pb-12 md:pb-16 lg:pb-20 ${className}`}
            {...props}
        >
            {children}
        </main>
    );
}

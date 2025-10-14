import { type ComponentProps } from 'react';

interface AppLogoIconProps extends ComponentProps<'img'> {
    className?: string;
}

export default function AppLogoIcon({ className, ...props }: AppLogoIconProps) {
    return (
        <img
                src="/images/logos/logo.png"
                alt="E-Halal BTECHenyo"
                className={className}
                {...props}
                onError={(e) => {
                    const target = e.target as HTMLImageElement;
                    // Fallback to a simple text logo if emblem fails to load
                    target.style.display = 'none';
                    const parent = target.parentElement;
                    if (parent) {
                        parent.innerHTML = '<div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center"><span class="text-white font-bold text-sm">DPLB</span></div>';
                    }
                }}
            />
    );
}

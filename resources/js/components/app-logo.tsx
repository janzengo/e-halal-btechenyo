import React from 'react';

type AppLogoProps = {
    /**
     * Variant of the logo.
     * - "default": colored text + emblem (default)
     * - "white": white text + white logo
     */
    variant?: 'default' | 'white';
};

export default function AppLogo({ variant = 'default' }: AppLogoProps) {
    const isWhite = variant === 'white';

    // Logo image source and alt text based on variant
    const logoSrc = isWhite ? '/images/logos/white-logo.png' : '/images/logos/logo.png';
    const logoAlt = isWhite ? 'DPLB Emblem (White)' : 'DPLB Emblem';

    // Text color classes based on variant
    const titleClass = isWhite
        ? 'text-white'
        : 'text-gray-900';
    const subtitleClass = isWhite
        ? 'text-white/80'
        : 'text-green-600';

    // Fallback for image error
    const handleImgError = (e: React.SyntheticEvent<HTMLImageElement, Event>) => {
        const target = e.target as HTMLImageElement;
        target.style.display = 'none';
        const parent = target.parentElement;
        if (parent) {
            parent.innerHTML = isWhite
                ? '<div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center"><span class="text-white font-bold text-sm">DPLB</span></div>'
                : '<div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center"><span class="text-white font-bold text-sm">DPLB</span></div>';
        }
    };

    return (
        <div className="flex items-center gap-2">
            <img
                src={logoSrc}
                alt={logoAlt}
                className="h-10 w-10 object-contain"
                onError={handleImgError}
            />

            {/* Logo text - always visible */}
            <div className="flex-1 text-left group-data-[collapsible=icon]:hidden">
                <span className={`block text-sm sm:text-base font-semibold truncate leading-tight ${titleClass}`}>
                    E-Halal BTECHenyo
                </span>
                <span className={`block text-xs sm:text-sm truncate leading-tight -mt-0.5 ${subtitleClass}`}>
                    <span className="sm:hidden">DPLB</span>
                    <span className="hidden sm:block">Dalubhasaang Politekniko ng Lungsod ng Baliwag</span>
                </span>
            </div>
        </div>
    );
}

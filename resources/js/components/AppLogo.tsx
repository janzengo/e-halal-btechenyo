import React from 'react';

export default function AppLogo() {
    return (
        <div className="flex items-center gap-2">
            <img
                src="/images/ehalal.jpg"
                alt="E-Halal BTECHenyo Logo"
                className="h-10 w-auto"
                onError={(e) => {
                    const target = e.target as HTMLImageElement;
                    // Fallback to a simple text logo if emblem fails to load
                    target.style.display = 'none';
                    const parent = target.parentElement;
                    if (parent) {
                        parent.innerHTML = '<div class="w-10 h-10 bg-brand-primary rounded-lg flex items-center justify-center"><span class="text-white font-bold text-sm">EH</span></div>';
                    }
                }}
            />
            
            {/* Logo text - hidden on small screens */}
            <div className="grid flex-1 text-left text-sm sm:text-base md:grid group-data-[collapsible=icon]:hidden">
                <span className="mb-0.5 truncate leading-none font-semibold text-gray-900">E-Halal BTECHenyo</span>
                <span className="text-xs text-brand-primary">Dalubhasaang Politekniko ng Lungsod ng Baliwag</span>
            </div>
        </div>
    );
}

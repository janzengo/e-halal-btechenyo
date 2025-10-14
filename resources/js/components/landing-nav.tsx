import React from 'react';
import { Link } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import AppLogo from '@/components/app-logo';
import { ArrowRight } from 'lucide-react';
import HamburgerMenu from './hamburger-menu';
// Using direct route paths instead of route helpers

interface LandingNavProps {
    activeSection: string;
    onNavigate: (sectionId: string) => void;
    auth: any;
}

// The process section in @landing-page/process-section.tsx should have id="how-to-vote"
export default function LandingNav({ activeSection, onNavigate, auth }: LandingNavProps) {
    const navItems = [
        { name: 'Home', sectionId: 'home' },
        { name: 'Process', sectionId: 'how-to-vote' }, // Use the correct id for the process section
        { name: 'FAQ', sectionId: 'faq' },
        { name: 'Contact', sectionId: 'contact' }
    ];

    return (
        <header className="sticky top-0 z-50 bg-white border-b border-gray-200 w-full">
            <div className="w-full px-4 py-6 flex justify-between items-center max-w-7xl mx-auto">
                <AppLogo />

                <nav className="flex items-center gap-4">
                    {navItems.map((item) => (
                        <button
                            key={item.name}
                            onClick={() => onNavigate(item.sectionId)}
                            className={cn(
                                "text-gray-700 hover:text-green-700 font-medium relative pb-1 cursor-pointer",
                                "after:content-[''] after:absolute after:left-0 after:bottom-0 after:h-0.5 after:w-full after:bg-green-600 hidden lg:inline-block",
                                activeSection === item.sectionId
                                    ? "after:scale-x-100"
                                    : "after:scale-x-0 hover:after:scale-x-100 after:transition-transform"
                            )}
                        >
                            {item.name}
                        </button>
                    ))}
                    
                    {/* Auth buttons - hidden on mobile */}
                    <div className="ml-4 border-l border-gray-200 pl-4 flex items-center gap-4 hidden lg:flex">
                        {auth.user ? (
                            <Link
                                href="/dashboard"
                                className="inline-flex items-center gap-2 rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 transition-colors"
                            >
                                Dashboard
                                <ArrowRight className="h-4 w-4" />
                            </Link>
                        ) : (
                            <Link
                                href="/auth/login"
                                className="rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 transition-colors inline-flex items-center gap-2"
                            >
                                Voter Login
                                <ArrowRight className="h-4 w-4" />
                            </Link>
                        )}
                    </div>

                    <HamburgerMenu activeSection={activeSection} onNavigate={onNavigate} />
                </nav>
            </div>
        </header>
    );
}

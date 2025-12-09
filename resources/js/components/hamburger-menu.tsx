import React, { useState } from 'react';
import { Menu, X, ArrowRight } from 'lucide-react';
import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
// Using direct route paths instead of route helpers

interface HamburgerMenuProps {
    activeSection: string;
    onNavigate: (sectionId: string) => void;
    auth: any;
}

export default function HamburgerMenu({ activeSection, onNavigate, auth }: HamburgerMenuProps) {
    const [isOpen, setIsOpen] = useState(false);

    const navItems = [
        { name: 'Home', sectionId: 'home' },
        { name: 'FAQ', sectionId: 'faq' },
        { name: 'Contact', sectionId: 'contact' }
    ];

    const handleToggleMenu = () => {
        setIsOpen(!isOpen);
    };

    const handleCloseMenu = () => {
        setIsOpen(false);
    };

    const handleNavigation = (sectionId: string) => {
        onNavigate(sectionId);
        handleCloseMenu();
    };

    return (
        <div className="lg:hidden">
            {/* Hamburger Icon */}
            <button
                className="text-gray-700 focus:outline-none hover:text-green-600 p-2 transition-colors"
                onClick={handleToggleMenu}
                aria-label="Toggle Menu"
            >
                {isOpen ? <X size={24} /> : <Menu size={24} />}
            </button>

            {/* Mobile Menu Overlay */}
            {/* Backdrop */}
            <div
                className={`fixed inset-0 bg-black z-40 transition-opacity duration-300 ease-in-out ${
                    isOpen ? 'bg-opacity-50 opacity-100 pointer-events-auto' : 'bg-opacity-0 opacity-0 pointer-events-none'
                }`}
                onClick={handleCloseMenu}
            />

            {/* Menu Panel */}
            <div className={`fixed top-0 left-0 h-full w-full bg-white shadow-lg z-50 transform transition-transform duration-300 ease-in-out ${
                isOpen ? 'translate-x-0' : 'translate-x-full'
            }`}>
                <div className="p-6 h-full flex flex-col">
                    {/* Close Button */}
                    <div className="flex justify-end mb-6">
                        <button
                            onClick={handleCloseMenu}
                            className="text-gray-500 hover:text-gray-700 transition-colors"
                        >
                            <X size={24} />
                        </button>
                    </div>

                    {/* Main Content Area */}
                    <div className="flex-1 flex flex-col">
                        {/* Navigation Links - Left Aligned */}
                        <nav className="space-y-2 mb-8">
                            {navItems.map((item) => (
                                <button
                                    key={item.name}
                                    onClick={() => handleNavigation(item.sectionId)}
                                    className={`block w-full text-left px-4 py-3 rounded-lg font-medium transition-colors cursor-pointer ${
                                        activeSection === item.sectionId
                                            ? "bg-green-100 text-green-700"
                                            : "text-gray-700 hover:bg-green-50 hover:text-green-700"
                                    }`}
                                >
                                    {item.name}
                                </button>
                            ))}
                        </nav>

                        {/* Auth Button - Centered */}
                        <div className="border-t border-gray-200 pt-4 mb-8">
                            <div className="flex justify-center">
                                {auth.user ? (
                                    // Dynamic button based on user role
                                    (() => {
                                        // Check if user is admin (has role property) vs voter (has student_number)
                                        if (auth.user.role) {
                                            // Admin user (officer or head)
                                            const dashboardUrl = auth.user.role === 'head' ? '/head/dashboard' : '/officers/dashboard';
                                            return (
                                                <Link 
                                                    href={dashboardUrl} 
                                                    className="block w-full max-w-xs"
                                                >
                                                    <Button variant="default" className="w-full">
                                                        Dashboard
                                                        <ArrowRight className="ml-2 w-4 h-4" />
                                                    </Button>
                                                </Link>
                                            );
                                        } else {
                                            // Voter user
                                            return (
                                                <Link 
                                                    href="/voters/vote" 
                                                    className="block w-full max-w-xs"
                                                >
                                                    <Button variant="default" className="w-full">
                                                        Vote Now
                                                        <ArrowRight className="ml-2 w-4 h-4" />
                                                    </Button>
                                                </Link>
                                            );
                                        }
                                    })()
                                ) : (
                                    <Link 
                                        href="/auth/login" 
                                        className="block w-full max-w-xs"
                                    >
                                        <Button variant="default" className="w-full">
                                            Voter Login
                                            <ArrowRight className="ml-2 w-4 h-4" />
                                        </Button>
                                    </Link>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
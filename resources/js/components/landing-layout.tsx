import React, { useEffect } from 'react';
import { Head, usePage } from '@inertiajs/react';
import SmoothScrollButton from './ui/smooth-scroll-button';
import { type SharedData } from '@/types';
import LandingNav from '@/components/landing-nav';
import AOS from 'aos';
import 'aos/dist/aos.css';

interface LandingLayoutProps {
    children: React.ReactNode;
    title?: string;
    activeSection: string;
    onNavigate: (sectionId: string) => void;
}

export default function LandingLayout({ children, title, activeSection, onNavigate }: LandingLayoutProps) {
    const { auth } = usePage<SharedData>().props;

    useEffect(() => {
        AOS.init({
            duration: 800,
            once: true,
            offset: 100,
        });
    }, []);

    const pageTitle = title ? `${title} - E-Halal BTECHenyo` : 'E-Halal BTECHenyo';
    const pageDescription = title
        ? `${title} - Secure online voting system for Dalubhasaang Politekniko ng Lungsod ng Baliwag.`
        : 'Secure online voting system for Dalubhasaang Politekniko ng Lungsod ng Baliwag. Built with modern technology for transparent student elections.';

    return (
        <>
            <Head title={pageTitle}>
                {/* Basic Meta Tags */}
                <meta name="description" content={pageDescription} />
                <meta name="keywords" content="E-Halal, BTECHenyo, online voting, student elections, DPLB, Bulacan, secure voting" />
                <meta name="author" content="E-Halal BTECHenyo" />
                <meta name="viewport" content="width=device-width, initial-scale=1.0" />

                {/* Open Graph Tags */}
                <meta property="og:title" content={pageTitle} />
                <meta property="og:description" content={pageDescription} />
                <meta property="og:type" content="website" />
                <meta property="og:site_name" content="E-Halal BTECHenyo" />
                <meta property="og:locale" content="en_PH" />

                {/* Twitter Card Tags */}
                <meta name="twitter:card" content="summary_large_image" />
                <meta name="twitter:title" content={pageTitle} />
                <meta name="twitter:description" content={pageDescription} />
            </Head>
            
            <div className="min-h-screen bg-gradient-to-b from-green-50 via-white via-blue-50 to-gray-50 flex flex-col">
                <LandingNav 
                    activeSection={activeSection} 
                    onNavigate={onNavigate} 
                    auth={auth} 
                />

                {/* Main Content */}
                <main className="flex-grow">
                    {children}
                </main>
                
                {/* Smooth Scroll Button */}
                <SmoothScrollButton />
            </div>
        </>
    );
}

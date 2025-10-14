import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Vote, Shield, Clock, ArrowRight, CheckCircle, ArrowUp10 } from 'lucide-react';
import { useState, useEffect } from 'react';
import LandingLayout from '@/components/landing-layout';
import AOS from 'aos';
import 'aos/dist/aos.css';

// Landing Page Components
import ProcessSection from '@/components/@landing-page/process-section';
import FAQSection from '@/components/@landing-page/faq-section';
import ContactSection from '@/components/@landing-page/contact-section';
import CallToActionSection from '@/components/@landing-page/call-to-action-section';
import Footer from '@/components/@landing-page/footer-section';
import { Separator } from '@/components/ui/separator';

export default function Welcome() {
    const { auth } = usePage<SharedData>().props;
    const [activeSection, setActiveSection] = useState('home');

    // Smooth scroll function
    const scrollToSection = (sectionId: string) => {
        const element = document.getElementById(sectionId);
        if (element) {
            element.scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
            });
        }
        setActiveSection(sectionId);
    };

    // Intersection Observer to track active section
    useEffect(() => {
        const sections = ['home', 'how-to-vote', 'faq', 'contact'];
        const observerOptions = {
            root: null,
            rootMargin: '-20% 0px -70% 0px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    setActiveSection(entry.target.id);
                }
            });
        }, observerOptions);

        sections.forEach((sectionId) => {
            const element = document.getElementById(sectionId);
            if (element) {
                observer.observe(element);
            }
        });

        return () => {
            sections.forEach((sectionId) => {
                const element = document.getElementById(sectionId);
                if (element) {
                    observer.unobserve(element);
                }
            });
        };
    }, []);

    // Force light mode
    useEffect(() => {
        document.documentElement.classList.remove('dark');
        document.documentElement.setAttribute('data-theme', 'light');
    }, []);

    // Initialize AOS
    useEffect(() => {
        AOS.init({
            duration: 800,
            once: true,
            offset: 100,
        });
    }, []);

    return (
        <LandingLayout 
            activeSection={activeSection} 
            onNavigate={scrollToSection}
        >

            {/* Home Section */}
            <section id="home" className="pt-16 pb-20 px-6 lg:px-8">
                <div className="max-w-7xl mx-auto">
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-16 lg:gap-10 items-center">
                        {/* Left side - Text content */}
                        <div className="space-y-4">
                            <div 
                                className="inline-flex items-center gap-2 bg-green-100 text-green-800 px-4 py-2 rounded-full text-sm font-medium"
                                data-aos="fade-up"
                                data-aos-delay="100"
                            >
                                <div className="w-2 h-2 bg-green-500 rounded-full"></div>
                                Official BTECH Sangguniang Mag-aaral Election Platform
                            </div>
                            
                            <div className="space-y-6">
                                <h1 
                                    className="text-xl lg:text-2xl xl:text-6xl font-bold text-gray-900 leading-tight"
                                    data-aos="fade-up"
                                    data-aos-delay="200"
                                ><span className="text-lg lg:text-3xl xl:text-5xl">Secure Online Voting</span>
                                    <span className="text-green-600 block">for BTECHenyos</span>
                                </h1>
                                
                                <p 
                                    className="text-lg text-gray-600 leading-relaxed max-w-lg"
                                    data-aos="fade-up"
                                    data-aos-delay="300"
                                >
                                    Participate in student elections with confidence. Our secure platform ensures your vote is counted accurately and your voice shapes the future of BTECHenyo.
                                </p>
                            </div>

                            <div 
                                className="flex flex-col sm:flex-row gap-4"
                                data-aos="fade-up"
                                data-aos-delay="400"
                            >
                                <Button 
                                >
                                    Get Started
                                    <ArrowRight className="ml-2 h-5 w-5" />
                                </Button>
                            </div>
                        </div>

                        {/* Right side - 3D Logo */}
                        <div 
                            className="flex justify-center lg:justify-end"
                            data-aos="fade-left"
                            data-aos-delay="500"
                        >
                            <div className="relative">
                                <img 
                                    src="/images/hero-images/ehalal-3d.png" 
                                    alt="E-Halal 3D Logo" 
                                    className="w-100 h-100 lg:w-130 lg:h-130 object-contain drop-shadow-2xl float-animation"
                                    style={{
                                        filter: 'brightness(1.2) contrast(1.1) saturate(1.1)'
                                    }}
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Process Section */}
            <ProcessSection />

            {/* FAQ Section */}
            <FAQSection />

            {/* Contact Section */}
            <ContactSection />

            {/* CTA Section */}
            <CallToActionSection />

            {/* Footer */}
            <Footer />
        </LandingLayout>
    );
}
import { Link } from "@inertiajs/react";
import { MapPin, Phone, Mail, Clock, ArrowRight } from "lucide-react";

export default function FooterSection() {
    const scrollToSection = (sectionId: string) => {
        const element = document.getElementById(sectionId);
        if (element) {
            element.scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
            });
        }
    };
    return (
<>
            <footer className="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-gray-300">
                <div className="max-w-7xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12">
                        <div>
                            <div className="mb-6">
                                <div className="flex items-center space">
                                    <div>
                                        <h3 className="text-white text-lg font-semibold">E-Halal BTECHenyo</h3>
                                        <p className="text-sm text-gray-400">Dalubhasaang Politekniko ng Lungsod ng Baliwag</p>
                                    </div>
                                </div>
                            </div>
                            <p className="text-sm mb-4 leading-relaxed max-w-md">
                                Secure online voting system for Dalubhasaang Politekniko ng Lungsod ng Baliwag. 
                                Built with modern technology for transparent student elections.
                            </p>
                            <p className="text-sm text-green-400">
                                Developed and powered by <a 
                                href="https://janzengo.tech" 
                                target="_blank" 
                                rel="noopener noreferrer"
                                className="inline-flex items-center gap-3 hover:text-white transition-colors"
                            > JRG</a> and KDC for the Dalubhasaang Politekniko ng Lungsod ng Baliwag
                            </p>
                        </div>
                        
                        <div>
                            <h3 className="text-white text-lg font-semibold mb-6">Quick Links</h3>
                            <ul className="space-y-3 text-sm">
                                <li>
                                    <button 
                                        onClick={() => scrollToSection('home')}
                                        className="hover:text-white transition-colors text-left cursor-pointer"
                                    >
                                        Home
                                    </button>
                                </li>
                                <li>
                                    <button 
                                        onClick={() => scrollToSection('faq')}
                                        className="hover:text-white transition-colors text-left cursor-pointer"
                                    >
                                        FAQ
                                    </button>
                                </li>
                                <li>
                                    <button 
                                        onClick={() => scrollToSection('contact')}
                                        className="hover:text-white transition-colors text-left cursor-pointer"
                                    >
                                        Contact
                                    </button>
                                </li>
                                <li>
                                    <Link href="/auth/login" className="hover:text-white transition-colors">
                                        Voter Login
                                    </Link>
                                </li>
                                <li>
                                    <Link href="/voters/receipt" className="hover:text-white transition-colors">
                                        View Receipt
                                    </Link>
                                </li>
                            </ul>
                        </div>
                        
                        <div>
                            <h3 className="text-white text-lg font-semibold mb-6">Contact</h3>
                            <ul className="space-y-3 text-sm">
                                <li className="flex items-start gap-3">
                                    <MapPin className="w-4 h-4 text-green-400 mt-0.5 flex-shrink-0" />
                                    <span className="text-sm leading-relaxed">
                                        2nd Floor, BMG Building, Barrera Street, Poblacion <br /> 
                                        Baliuag, Bulacan, Philippines
                                    </span>
                                </li>
                                <li className="flex items-center gap-3">
                                    <Phone className="w-4 h-4 text-green-400" />
                                    (044) 123-4567
                                </li>
                                <li className="flex items-center gap-3">
                                    <Mail className="w-4 h-4 text-green-400" />
                                    admin@ehalal.tech
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <div className="border-t border-gray-800 mt-12 pt-8 text-center">
                        <p className="text-sm text-gray-400 mb-2">
                            &copy; 2025 E-Halal BTECHenyo. All rights reserved.
                        </p>
                    </div>
                </div>
            </footer>
        </>
    );
}
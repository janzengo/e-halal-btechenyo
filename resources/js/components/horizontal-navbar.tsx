import { Link, usePage } from "@inertiajs/react";
import { ArrowRight } from "lucide-react";
import { type SharedData } from "@/types";


export default function HorizontalNavbar({ handleLogout }: { handleLogout: () => void }) {
    const page = usePage<SharedData>();
    const { auth } = page.props;
    
    return (
        <nav className="top-0 z-50 bg-gradient-to-r from-green-800 to-green-900 shadow-sm">
            <div className="container mx-auto px-4">
                <div className="flex h-14 items-center justify-between">
                    <div className="flex items-center gap-3">
                        <img 
                            src="/images/h-logo.jpg" 
                            alt="E-Halal Logo" 
                            className="h-14 w-auto"
                        />
                    </div>
                    <div className="flex items-center gap-6 text-sm">
                        <span className="text-gray-300">
                            Voter ID: <span>{auth.user?.student_number || 'User'}</span>
                        </span>
                        <Link
                            className="inline-flex items-center gap-1 text-white hover:text-green-200 transition-colors duration-200"
                        >   Sign Out
                            <span className="text-white"><ArrowRight className="h-4 w-4" /></span>
                            
                        </Link>
                    </div>
                </div>
            </div>
        </nav>
    );
}

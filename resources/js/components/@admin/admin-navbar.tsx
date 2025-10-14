import { LogOut } from "lucide-react";
import { Button } from "@/components/ui/button";
import AppLogo from "@/components/app-logo";

export default function AdminNavbar({ handleLogout }: { handleLogout: () => void }) {
    return (
        <nav className="sticky top-0 z-50 border-b bg-white shadow-sm">
            <div className="container mx-auto px-4">
                <div className="flex h-16 items-center justify-between">
                    <AppLogo />
                    <Button onClick={handleLogout} variant="outline" size="sm">
                        <LogOut className="h-4 w-4" />
                        Logout
                    </Button>
                </div>
            </div>
        </nav>
    );
}

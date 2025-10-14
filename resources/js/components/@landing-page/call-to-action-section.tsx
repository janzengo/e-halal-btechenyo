import { Button } from "@/components/ui/button";
import { ArrowRight, CircleChevronRight } from "lucide-react";
import { Link } from "@inertiajs/react";

export default function CallToActionSection() {
    return (
        <section className="py-20 bg-gradient-to-r from-green-600 via-green-700 to-green-600">
                <div className="max-w-4xl mx-auto text-center px-6">
                    <h2 className="text-4xl md:text-5xl font-bold text-white mb-6">
                        Ready to make your voice heard?
                    </h2>
                    <p className="text-xl text-green-100 mb-8 max-w-2xl mx-auto">
                        Join thousands of BTECHenyos in shaping the future of your institution through secure, transparent elections.
                    </p>
                    <div className="flex flex-col sm:flex-row gap-4 justify-center">
                        <Link href="/auth/login">
                            <Button variant="white">
                                Start Voting Now
                                <CircleChevronRight className="ml-2 w-5 h-5" />
                            </Button>
                        </Link>
                    </div>
                </div>
        </section>
    );
}
import { User, Shield, Mail, CheckCircle } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useEffect, useRef } from 'react';
import AOS from 'aos';
import 'aos/dist/aos.css';
import { TiltCard } from './@tilt-cards/tilt-cards';

interface Step {
    number: string;
    title: string;
    description: string;
    icon: React.ElementType;
    bgColor: string;
    borderColor: string;
    iconColor: string;
    numberColor: string;
}

// 3D Tilt Card Component using the reusable TiltCard
function Card3D({ step, index }: { step: Step; index: number }) {
    const IconComponent = step.icon;

    return (
        <TiltCard 
            className={`relative h-80 w-full max-w-sm mx-auto rounded-2xl ${step.bgColor} ${step.borderColor} border-2 shadow-lg hover:shadow-xl overflow-hidden`}
            dataAos="fade-up"
            dataAosDelay={`${(index + 1) * 100}`}
            bgColor={step.bgColor}
        >
            {/* Glow effect */}
            <div className="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent opacity-0 hover:opacity-100 transition-opacity duration-300" />
            
            {/* Card number */}
            <div className={`absolute top-4 right-4 text-2xl font-bold ${step.numberColor} opacity-20`}>
                {step.number}
            </div>
            
            {/* Card content */}
            <div className="relative h-full flex flex-col items-center justify-center p-8 text-center">
                {/* Icon */}
                <div className={`mb-6 p-4 rounded-full bg-white/30 ${step.iconColor}`}>
                    <IconComponent className="w-8 h-8" />
                </div>
                
                {/* Title */}
                <h3 className="text-xl font-bold text-gray-900 mb-3">
                    {step.title}
                </h3>
                
                {/* Description */}
                <p className="text-gray-600 leading-relaxed">
                    {step.description}
                </p>
            </div>
        </TiltCard>
    );
}

export default function ProcessSection() {
    // Initialize AOS for this component
    useEffect(() => {
        AOS.init({
            duration: 600,
            once: true,
            offset: 100,
        });
    }, []);

    const steps: Step[] = [
        {
            number: "01",
            title: "Student Login",
            description: "Enter your student number to begin",
            icon: User,
            bgColor: "bg-green-50",
            borderColor: "border-green-200",
            iconColor: "text-green-600",
            numberColor: "text-green-600"
        },
        {
            number: "02",
            title: "OTP Verification",
            description: "Verify with 6-digit code from email",
            icon: Shield,
            bgColor: "bg-blue-50",
            borderColor: "border-blue-200",
            iconColor: "text-blue-600",
            numberColor: "text-blue-600"
        },
        {
            number: "03",
            title: "Cast Your Vote",
            description: "Select your preferred candidates",
            icon: CheckCircle,
            bgColor: "bg-yellow-50",
            borderColor: "border-yellow-200",
            iconColor: "text-yellow-600",
            numberColor: "text-yellow-600"
        },
        {
            number: "04",
            title: "Get Receipt",
            description: "Receive confirmation and receipt",
            icon: Mail,
            bgColor: "bg-orange-50",
            borderColor: "border-orange-200",
            iconColor: "text-orange-600",
            numberColor: "text-orange-600"
        }
    ];

    return (
        <section id="how-to-vote" className="py-20">
            <div className="max-w-6xl mx-auto px-6" style={{ perspective: '1500px' }}>
                <div className="text-center mb-16" data-aos="fade-up"> 
                <h2 className="text-3xl font-bold text-gray-900 mb-4">How to Vote</h2>
                    <p className="text-lg text-gray-600 max-w-2xl mx-auto">
                        Follow these simple steps to participate in BTECHenyo student elections.
                    </p>
                </div>

                <div className="relative">
                    {/* Desktop Layout */}
                    <div className="hidden lg:block">
                        <div className="grid grid-cols-4 gap-16">
                            {steps.map((step, index) => (
                                <div key={index} className="relative">
                                    <Card3D step={step} index={index} />
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* Mobile/Tablet Layout */}
                    <div className="lg:hidden space-y-16">
                        {steps.map((step, index) => (
                            <div key={index} className="relative">
                                <Card3D step={step} index={index} />
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </section>
    );
}

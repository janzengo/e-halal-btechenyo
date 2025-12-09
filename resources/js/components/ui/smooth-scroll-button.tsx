import React, { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { ArrowUp } from 'lucide-react';
import { cn } from '@/lib/utils';

interface SmoothScrollButtonProps {
    className?: string;
    threshold?: number; // Scroll threshold to show button (default: 300px)
}

export default function SmoothScrollButton({ 
    className,
    threshold = 300 
}: SmoothScrollButtonProps) {
    const [isVisible, setIsVisible] = useState(false);

    useEffect(() => {
        const toggleVisibility = () => {
            if (window.pageYOffset > threshold) {
                setIsVisible(true);
            } else {
                setIsVisible(false);
            }
        };

        // Listen for scroll events
        window.addEventListener('scroll', toggleVisibility);

        // Cleanup
        return () => {
            window.removeEventListener('scroll', toggleVisibility);
        };
    }, [threshold]);

    const scrollToTop = () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    };

    if (!isVisible) {
        return null;
    }

    return (
        <Button
            onClick={scrollToTop}
            size="icon"
            className={cn(
                "fixed bottom-6 right-6 z-[60] h-12 w-12 rounded-sm shadow-lg",
                "bg-green-600 hover:bg-green-700 text-white",
                "transition-all duration-300 ease-in-out",
                "hover:scale-105 active:scale-50",
                "focus:outline-none",
                className
            )}
            aria-label="Scroll to top"
        >
            <ArrowUp className="h-5 w-5" />
        </Button>
    );
}

import React, { createContext, useContext, useState, useEffect, useRef } from 'react';
import { router } from '@inertiajs/react';

interface LoadingContextType {
    isPageLoading: boolean;
    setIsPageLoading: (loading: boolean) => void;
}

const LoadingContext = createContext<LoadingContextType | undefined>(undefined);

export function LoadingProvider({ children }: { children: React.ReactNode }) {
    const [isPageLoading, setIsPageLoading] = useState(false);
    const loadingTimeoutRef = useRef<NodeJS.Timeout | null>(null);
    const navigationStartTimeRef = useRef<number | null>(null);

    useEffect(() => {
        const handleProgress = (event: any) => {
            const progress = event.detail.progress;
            
            // When progress starts (is an object with percentage)
            if (progress && typeof progress === 'object') {
                // Mark when navigation actually started
                if (!navigationStartTimeRef.current) {
                    navigationStartTimeRef.current = Date.now();
                    
                    // Only show skeleton after 200ms if still loading
                    loadingTimeoutRef.current = setTimeout(() => {
                        // Check if we're still loading (navigation hasn't finished)
                        if (navigationStartTimeRef.current) {
                            setIsPageLoading(true);
                        }
                    }, 200);
                }
            }
        };

        const handleFinish = () => {
            // Clear navigation start time
            navigationStartTimeRef.current = null;
            
            // Clear the timeout if page loads before delay
            if (loadingTimeoutRef.current) {
                clearTimeout(loadingTimeoutRef.current);
                loadingTimeoutRef.current = null;
            }
            
            // Hide skeleton
            setIsPageLoading(false);
        };

        // Use 'progress' event which only fires during actual page loads
        router.on('progress', handleProgress);
        router.on('finish', handleFinish);
        router.on('error', handleFinish); // Also handle errors

        return () => {
            router.off('progress', handleProgress);
            router.off('finish', handleFinish);
            router.off('error', handleFinish);
            
            // Clean up timeout on unmount
            if (loadingTimeoutRef.current) {
                clearTimeout(loadingTimeoutRef.current);
            }
        };
    }, []);

    return (
        <LoadingContext.Provider value={{ isPageLoading, setIsPageLoading }}>
            {children}
        </LoadingContext.Provider>
    );
}

export function useLoading() {
    const context = useContext(LoadingContext);
    if (context === undefined) {
        throw new Error('useLoading must be used within a LoadingProvider');
    }
    return context;
}

import { useCallback, useEffect } from 'react';

export function useMobileNavigation() {
    const cleanup = useCallback(() => {
        // Remove pointer-events style from body
        document.body.style.removeProperty('pointer-events');
        // Remove any overflow hidden that might have been set
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('overflow-x');
        // Remove any transform that might have been set
        document.body.style.removeProperty('transform');
    }, []);

    const preventBodyScroll = useCallback(() => {
        document.body.style.overflow = 'hidden';
        document.body.style.overflowX = 'hidden';
    }, []);

    const enableBodyScroll = useCallback(() => {
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('overflow-x');
    }, []);

    // Cleanup on unmount
    useEffect(() => {
        return () => {
            cleanup();
        };
    }, [cleanup]);

    return {
        cleanup,
        preventBodyScroll,
        enableBodyScroll
    };
}
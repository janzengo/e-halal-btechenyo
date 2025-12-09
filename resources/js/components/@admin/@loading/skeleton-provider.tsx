import { SkeletonTheme } from 'react-loading-skeleton';
import 'react-loading-skeleton/dist/skeleton.css';

interface SkeletonProviderProps {
    children: React.ReactNode;
}

export function SkeletonProvider({ children }: SkeletonProviderProps) {
    return (
        <SkeletonTheme
            baseColor="hsl(var(--muted))"
            highlightColor="hsl(var(--muted-foreground))"
            borderRadius="0.5rem"
        >
            {children}
        </SkeletonTheme>
    );
}

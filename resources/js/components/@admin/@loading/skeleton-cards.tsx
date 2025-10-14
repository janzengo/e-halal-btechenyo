import Skeleton from 'react-loading-skeleton';

interface SkeletonCardsProps {
    count?: number;
    className?: string;
}

export function SkeletonCards({ count = 6, className = "" }: SkeletonCardsProps) {
    return (
        <div className={`grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 ${className}`}>
            {Array.from({ length: count }).map((_, index) => (
                <div key={index} className="border border-gray-200 rounded-lg p-6">
                    {/* Header with avatar and name */}
                    <div className="flex items-center gap-3 mb-4">
                        <Skeleton circle height={48} width={48} />
                        <div className="flex-1">
                            <Skeleton height={20} width="70%" className="mb-2" />
                            <Skeleton height={16} width="50%" />
                        </div>
                    </div>
                    
                    {/* Content area */}
                    <div className="space-y-3">
                        <div className="flex justify-between">
                            <Skeleton height={24} width={60} />
                            <Skeleton height={24} width={80} />
                        </div>
                        
                        <div className="space-y-2">
                            <div className="flex justify-between">
                                <Skeleton height={16} width={40} />
                                <Skeleton height={16} width={100} />
                            </div>
                            <div className="flex justify-between">
                                <Skeleton height={16} width={50} />
                                <Skeleton height={16} width={80} />
                            </div>
                        </div>
                        
                        <div className="pt-2 border-t border-gray-100">
                            <Skeleton height={14} width="80%" />
                        </div>
                    </div>
                </div>
            ))}
        </div>
    );
}

export function SkeletonHeader({ showButton = false, className = "" }: { showButton?: boolean; className?: string }) {
    return (
        <div className={`flex justify-between items-center ${className}`}>
            <div className="space-y-2">
                <Skeleton height={32} width={300} />
                <Skeleton height={20} width={400} />
            </div>
            {showButton && <Skeleton height={40} width={140} />}
        </div>
    );
}

export function SkeletonTable({ rows = 5, className = "" }: { rows?: number; className?: string }) {
    return (
        <div className={`space-y-4 ${className}`}>
            {/* Table header */}
            <div className="grid grid-cols-4 gap-4 pb-2 border-b border-gray-200">
                <Skeleton height={20} width="80%" />
                <Skeleton height={20} width="60%" />
                <Skeleton height={20} width="70%" />
                <Skeleton height={20} width="50%" />
            </div>
            
            {/* Table rows */}
            {Array.from({ length: rows }).map((_, index) => (
                <div key={index} className="grid grid-cols-4 gap-4 py-3">
                    <Skeleton height={16} width="90%" />
                    <Skeleton height={16} width="75%" />
                    <Skeleton height={16} width="85%" />
                    <Skeleton height={16} width="60%" />
                </div>
            ))}
        </div>
    );
}

export function SkeletonStatsCard({ count = 4, className = "" }: { count?: number; className?: string }) {
    return (
        <div className={`grid gap-4 md:grid-cols-2 lg:grid-cols-4 ${className}`}>
            {Array.from({ length: count }).map((_, index) => (
                <div key={index} className="rounded-lg border border-gray-200 bg-white p-6">
                    <div className="flex items-center justify-between mb-2">
                        <Skeleton height={16} width={80} />
                        <Skeleton circle height={32} width={32} />
                    </div>
                    <Skeleton height={32} width={100} className="mb-2" />
                    <Skeleton height={14} width={120} />
                </div>
            ))}
        </div>
    );
}

export function SkeletonChart({ className = "" }: { className?: string }) {
    return (
        <div className={`rounded-lg border border-gray-200 bg-white p-6 ${className}`}>
            <div className="mb-4">
                <Skeleton height={24} width={200} className="mb-2" />
                <Skeleton height={16} width={300} />
            </div>
            <div className="h-80 flex items-end gap-2">
                {Array.from({ length: 12 }).map((_, index) => (
                    <Skeleton 
                        key={index} 
                        height={Math.random() * 200 + 50} 
                        className="flex-1"
                    />
                ))}
            </div>
        </div>
    );
}

export function SkeletonDialogList({ className = "" }: { className?: string }) {
    return (
        <div className={`space-y-3 ${className}`}>
            {Array.from({ length: 5 }).map((_, index) => (
                <div key={index} className="flex items-center gap-3 p-3 border border-gray-200 rounded-lg">
                    <Skeleton circle height={40} width={40} />
                    <div className="flex-1">
                        <Skeleton height={18} width="60%" className="mb-2" />
                        <Skeleton height={14} width="40%" />
                    </div>
                </div>
            ))}
        </div>
    );
}

export function SkeletonCandidateView({ className = "" }: { className?: string }) {
    return (
        <div className={`space-y-6 ${className}`}>
            {/* Header with avatar */}
            <div className="flex items-center gap-4">
                <Skeleton circle height={80} width={80} />
                <div className="flex-1">
                    <Skeleton height={28} width="60%" className="mb-2" />
                    <Skeleton height={18} width="40%" className="mb-2" />
                    <Skeleton height={16} width="30%" />
                </div>
            </div>
            
            {/* Details */}
            <div className="space-y-4">
                <div>
                    <Skeleton height={16} width={80} className="mb-2" />
                    <Skeleton height={20} width="50%" />
                </div>
                <div>
                    <Skeleton height={16} width={80} className="mb-2" />
                    <Skeleton height={20} width="70%" />
                </div>
                <div>
                    <Skeleton height={16} width={80} className="mb-2" />
                    <Skeleton height={60} width="100%" />
                </div>
            </div>
        </div>
    );
}
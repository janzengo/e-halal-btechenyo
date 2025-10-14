import React from 'react';

interface StatisticsSectionProps {
    isLoading?: boolean;
}

const StatCardSkeleton = () => (
    <div className="text-center">
        <div className="h-12 w-24 bg-gray-300 rounded mx-auto mb-2 animate-pulse"></div>
        <div className="h-6 w-32 bg-gray-300 rounded mx-auto animate-pulse"></div>
    </div>
);

export default function StatisticsSection({ isLoading = false }: StatisticsSectionProps) {
    return (
        <section className="w-full py-16">
            <div className="container mx-auto px-4">
                <div className="text-center mb-12">
                    {isLoading ? (
                        <>
                            <div className="h-9 w-80 bg-gray-300 rounded mx-auto mb-4 animate-pulse"></div>
                            <div className="h-6 w-96 bg-gray-300 rounded mx-auto animate-pulse"></div>
                        </>
                    ) : (
                        <>
                            <h2 className="text-3xl font-bold text-gray-900 mb-4">E-Halal BTECHenyo Impact</h2>
                            <p className="text-lg text-gray-600 max-w-2xl mx-auto">
                                Trusted by thousands of BTECHenyos for secure, transparent, and efficient student elections
                            </p>
                        </>
                    )}
                </div>

                <div className="grid md:grid-cols-4 gap-8 max-w-4xl mx-auto">
                    {isLoading ? (
                        <>
                            <StatCardSkeleton />
                            <StatCardSkeleton />
                            <StatCardSkeleton />
                            <StatCardSkeleton />
                        </>
                    ) : (
                        <>
                            <div className="text-center">
                                <div className="text-4xl font-bold text-green-600 mb-2">5,000+</div>
                                <div className="text-gray-600">Students Registered</div>
                            </div>
                            <div className="text-center">
                                <div className="text-4xl font-bold text-green-600 mb-2">100%</div>
                                <div className="text-gray-600">Secure Voting</div>
                            </div>
                            <div className="text-center">
                                <div className="text-4xl font-bold text-green-600 mb-2">24/7</div>
                                <div className="text-gray-600">System Availability</div>
                            </div>
                            <div className="text-center">
                                <div className="text-4xl font-bold text-green-600 mb-2">15+</div>
                                <div className="text-gray-600">Elections Conducted</div>
                            </div>
                        </>
                    )}
                </div>
            </div>
        </section>
    );
}

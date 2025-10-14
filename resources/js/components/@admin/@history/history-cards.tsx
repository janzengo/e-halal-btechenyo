import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    MoreVertical,
    Eye,
    Download,
    Search,
    Calendar,
    Users,
    Vote,
    Award,
    TrendingUp
} from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { AdminRole } from '@/types/ehalal';

interface Election {
    id: number;
    title: string;
    description: string;
    start_date: string;
    end_date: string;
    status: 'completed' | 'active' | 'pending' | 'setup';
    total_voters: number;
    votes_cast: number;
    positions_count: number;
    candidates_count: number;
    created_at: string;
    completed_at?: string;
}

interface HistoryCardsProps {
    elections: Election[];
    userRole: AdminRole;
    onView?: (election: Election) => void;
    onExport?: (election: Election) => void;
}

export function HistoryCards({ 
    elections, 
    userRole, 
    onView, 
    onExport 
}: HistoryCardsProps) {
    const [searchTerm, setSearchTerm] = useState('');
    const [currentPage, setCurrentPage] = useState(1);
    const itemsPerPage = 6;
    const isHead = userRole === 'head';

    // Reset pagination when elections change
    useEffect(() => {
        setCurrentPage(1);
    }, [elections.length]);

    // Filter elections based on search term
    const filteredElections = elections.filter(election => {
        const searchableFields = `${election.title} ${election.description}`.toLowerCase();
        return searchableFields.includes(searchTerm.toLowerCase());
    });

    // Pagination
    const totalPages = Math.ceil(filteredElections.length / itemsPerPage);
    const safePage = Math.min(currentPage, totalPages);
    const startIndex = (safePage - 1) * itemsPerPage;
    const paginatedElections = filteredElections.slice(startIndex, startIndex + itemsPerPage);

    const getStatusStyles = (status: string) => {
        switch (status) {
            case 'completed':
                return 'text-white bg-green-600 border-green-600';
            case 'active':
                return 'text-white bg-red-600 border-red-600';
            case 'pending':
                return 'text-white bg-blue-600 border-blue-600';
            case 'setup':
                return 'text-gray-600 bg-gray-100 border-gray-300';
            default:
                return 'text-gray-600 bg-gray-100 border-gray-300';
        }
    };

    const getVotingRate = (votesCast: number, totalVoters: number) => {
        if (totalVoters === 0) return 0;
        return Math.round((votesCast / totalVoters) * 100);
    };

    return (
        <div className="flex flex-col gap-6">
            {/* Header */}
            <div className="flex justify-between items-center">
                <div>
                    <h2 className="text-2xl font-bold text-gray-900">Election History</h2>
                    <p className="text-gray-600">View and manage past and current elections</p>
                </div>
            </div>

            {/* Search */}
            <div className="relative">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
                <Input
                    type="text"
                    placeholder="Search elections..."
                    value={searchTerm}
                    onChange={(e) => {
                        setSearchTerm(e.target.value);
                        setCurrentPage(1);
                    }}
                    className="pl-10 border-gray-200 focus:border-gray-500 focus:ring-gray-500 h-11"
                />
            </div>

            {/* Results count */}
            <div className="text-sm text-gray-600">
                Showing {paginatedElections.length} of {filteredElections.length} elections
            </div>

            {/* Cards Grid */}
            {paginatedElections.length > 0 ? (
                <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    {paginatedElections.map((election) => (
                        <Card key={election.id} className="hover:shadow-lg transition-shadow duration-200 border-gray-100">
                            <CardHeader className="pb-3">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-3">
                                        <div className="h-12 w-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                            <Calendar className="h-6 w-6 text-gray-600" />
                                        </div>
                                        <div>
                                            <h3 className="font-semibold text-gray-900">{election.title}</h3>
                                            <p className="text-sm text-gray-500">
                                                {new Date(election.start_date).getFullYear()}
                                            </p>
                                        </div>
                                    </div>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger asChild>
                                            <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
                                                <MoreVertical className="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem onClick={() => onView?.(election)}>
                                                <Eye className="h-4 w-4 mr-2" />
                                                View Details
                                            </DropdownMenuItem>
                                            {election.status === 'completed' && (
                                                <DropdownMenuItem onClick={() => onExport?.(election)}>
                                                    <Download className="h-4 w-4 mr-2" />
                                                    Export Results
                                                </DropdownMenuItem>
                                            )}
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <div className="flex items-center justify-between">
                                    <Badge 
                                        variant="outline"
                                        className={getStatusStyles(election.status)}
                                    >
                                        {election.status}
                                    </Badge>
                                    <div className="text-sm text-muted-foreground">
                                        {getVotingRate(election.votes_cast, election.total_voters)}% voted
                                    </div>
                                </div>
                                
                                <p className="text-sm text-gray-600 line-clamp-3">
                                    {election.description}
                                </p>
                                
                                <div className="space-y-2 text-sm">
                                    <div className="flex items-center justify-between">
                                        <span className="text-gray-500">Period:</span>
                                        <span className="font-medium">
                                            {new Date(election.start_date).toLocaleDateString()} - {new Date(election.end_date).toLocaleDateString()}
                                        </span>
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <span className="text-gray-500">Voters:</span>
                                        <span className="font-medium">{election.total_voters}</span>
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <span className="text-gray-500">Votes Cast:</span>
                                        <span className="font-medium text-green-600">{election.votes_cast}</span>
                                    </div>
                                </div>
                                
                                <div className="flex items-center gap-4 pt-2 border-t border-gray-100">
                                    <div className="flex items-center gap-2">
                                        <Award className="h-4 w-4 text-gray-400" />
                                        <span className="text-sm font-medium text-gray-600">
                                            {election.positions_count} positions
                                        </span>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <Users className="h-4 w-4 text-gray-400" />
                                        <span className="text-sm font-medium text-gray-600">
                                            {election.candidates_count} candidates
                                        </span>
                                    </div>
                                </div>

                                {election.status === 'completed' && election.completed_at && (
                                    <div className="flex items-center gap-2 pt-2 border-t border-gray-100">
                                        <TrendingUp className="h-4 w-4 text-green-500" />
                                        <span className="text-xs text-gray-500">
                                            Completed: {new Date(election.completed_at).toLocaleDateString()}
                                        </span>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    ))}
                </div>
            ) : (
                <div className="text-center py-12">
                    <div className="bg-gray-50 rounded-lg p-8 max-w-md mx-auto">
                        <div className="text-gray-400 mb-4">
                            <Search className="h-12 w-12 mx-auto" />
                        </div>
                        <p className="text-lg font-medium text-gray-600">No elections found</p>
                        <p className="text-sm text-gray-500 mt-2">Try adjusting your search criteria.</p>
                    </div>
                </div>
            )}

            {/* Pagination */}
            {totalPages > 1 && (
                <div className="flex justify-center items-center space-x-3 mt-8 py-4">
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={() => setCurrentPage(prev => Math.max(prev - 1, 1))}
                        disabled={safePage === 1}
                        className="border-green-200 hover:bg-green-50 disabled:opacity-50 px-4"
                    >
                        Previous
                    </Button>
                    
                    <div className="flex items-center space-x-1">
                        {Array.from({ length: totalPages }, (_, i) => i + 1).map((page) => (
                            <Button
                                key={page}
                                variant={page === safePage ? "default" : "outline"}
                                size="sm"
                                onClick={() => setCurrentPage(page)}
                                className={`w-8 h-8 p-0 ${
                                    page === safePage 
                                        ? "bg-green-600 hover:bg-green-700" 
                                        : "border-green-200 hover:bg-green-50"
                                }`}
                            >
                                {page}
                            </Button>
                        ))}
                    </div>
                    
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={() => setCurrentPage(prev => Math.min(prev + 1, totalPages))}
                        disabled={safePage === totalPages}
                        className="border-green-200 hover:bg-green-50 disabled:opacity-50 px-4"
                    >
                        Next
                    </Button>
                </div>
            )}
        </div>
    );
}

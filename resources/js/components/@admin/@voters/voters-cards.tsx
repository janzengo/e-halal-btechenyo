import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    MoreVertical,
    Eye,
    Edit,
    Search,
    UserPlus,
    CheckCircle,
    XCircle,
    GraduationCap
} from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { AdminRole } from '@/types/ehalal';

interface Voter {
    id: number;
    student_id: string;
    firstname: string;
    lastname: string;
    email: string;
    photo: string;
    course: string;
    year_level: number;
    has_voted: boolean;
    voted_at?: string;
    status: 'active' | 'inactive';
    created_at: string;
}

interface VotersCardsProps {
    voters: Voter[];
    userRole: AdminRole;
    onView?: (voter: Voter) => void;
    onEdit?: (voter: Voter) => void;
    onAddNew?: () => void;
}

export function VotersCards({ 
    voters, 
    userRole, 
    onView, 
    onEdit, 
    onAddNew 
}: VotersCardsProps) {
    const [searchTerm, setSearchTerm] = useState('');
    const [currentPage, setCurrentPage] = useState(1);
    const itemsPerPage = 6;
    const isHead = userRole === 'head';

    // Reset pagination when voters change
    useEffect(() => {
        setCurrentPage(1);
    }, [voters.length]);

    // Filter voters based on search term
    const filteredVoters = voters.filter(voter => {
        const searchableFields = `${voter.firstname} ${voter.lastname} ${voter.email} ${voter.student_id} ${voter.course}`.toLowerCase();
        return searchableFields.includes(searchTerm.toLowerCase());
    });

    // Pagination
    const totalPages = Math.ceil(filteredVoters.length / itemsPerPage);
    const safePage = Math.min(currentPage, totalPages);
    const startIndex = (safePage - 1) * itemsPerPage;
    const paginatedVoters = filteredVoters.slice(startIndex, startIndex + itemsPerPage);

    return (
        <div className="flex flex-col gap-6">
            {/* Header with Add Button */}
            <div className="flex justify-between items-center">
                <div>
                    <h2 className="text-2xl font-bold text-gray-900">Voters</h2>
                    <p className="text-gray-600">Manage registered students and their voting status</p>
                </div>
                {isHead && (
                    <Button onClick={onAddNew} variant="outlinePrimary">
                        <UserPlus className="h-4 w-4" />
                        Add New Voter
                    </Button>
                )}
            </div>

            {/* Search */}
            <div className="relative">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
                <Input
                    type="text"
                    placeholder="Search voters..."
                    value={searchTerm}
                    onChange={(e) => {
                        setSearchTerm(e.target.value);
                        setCurrentPage(1);
                    }}
                    className="pl-10 border-green-200 focus:border-green-500 focus:ring-green-500 h-11"
                />
            </div>

            {/* Results count */}
            <div className="text-sm text-gray-600">
                Showing {paginatedVoters.length} of {filteredVoters.length} voters
            </div>

            {/* Cards Grid */}
            {paginatedVoters.length > 0 ? (
                <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    {paginatedVoters.map((voter) => (
                        <Card key={voter.id} className="hover:shadow-lg transition-shadow duration-200 border-green-100">
                            <CardHeader className="pb-3">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-3">
                                        <Avatar className="h-12 w-12">
                                            <AvatarImage src={voter.photo} alt={`${voter.firstname} ${voter.lastname}`} />
                                            <AvatarFallback className="bg-green-100 text-green-700">
                                                {voter.firstname[0]}{voter.lastname[0]}
                                            </AvatarFallback>
                                        </Avatar>
                                        <div>
                                            <h3 className="font-semibold text-gray-900">{voter.firstname} {voter.lastname}</h3>
                                            <p className="text-sm text-gray-500">{voter.student_id}</p>
                                        </div>
                                    </div>
                                    {isHead && (
                                        <DropdownMenu>
                                            <DropdownMenuTrigger asChild>
                                                <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
                                                    <MoreVertical className="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem onClick={() => onView?.(voter)}>
                                                    <Eye className="h-4 w-4 mr-2" />
                                                    View Details
                                                </DropdownMenuItem>
                                                <DropdownMenuItem onClick={() => onEdit?.(voter)}>
                                                    <Edit className="h-4 w-4 mr-2" />
                                                    Edit
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    )}
                                </div>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <div className="flex items-center justify-between">
                                    <Badge variant={voter.has_voted ? 'default' : 'secondary'}>
                                        {voter.has_voted ? 'Voted' : 'Not Voted'}
                                    </Badge>
                                    <Badge variant={voter.status === 'active' ? 'default' : 'secondary'}>
                                        {voter.status}
                                    </Badge>
                                </div>
                                
                                <div className="space-y-2 text-sm">
                                    <div className="flex items-center gap-2">
                                        <GraduationCap className="h-4 w-4 text-gray-400" />
                                        <span className="text-gray-500">Course:</span>
                                        <span className="font-medium">{voter.course}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-gray-500">Year Level:</span>
                                        <span className="font-medium">{voter.year_level}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-gray-500">Email:</span>
                                        <span className="font-medium text-xs truncate max-w-32">{voter.email}</span>
                                    </div>
                                    {voter.voted_at && (
                                        <div className="flex items-center gap-2 pt-2 border-t border-gray-100">
                                            <CheckCircle className="h-4 w-4 text-green-500" />
                                            <span className="text-xs text-gray-500">
                                                Voted: {new Date(voter.voted_at).toLocaleDateString()}
                                            </span>
                                        </div>
                                    )}
                                </div>
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
                        <p className="text-lg font-medium text-gray-600">No voters found</p>
                        <p className="text-sm text-gray-500 mt-2">Try adjusting your search criteria or add a new voter.</p>
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

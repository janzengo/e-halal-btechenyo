import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    MoreVertical,
    Search,
    UserCheck,
    UserX,
    Clock
} from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { AdminRole } from '@/types/ehalal';

interface Officer {
    id: number;
    username: string;
    email: string;
    firstname: string;
    lastname: string;
    photo: string;
    role: 'officer';
    status: 'active' | 'inactive';
    last_login: string;
    created_at: string;
}

interface OfficersCardsProps {
    officers: Officer[];
    userRole: AdminRole;
    onAddNew?: () => void;
    onToggleStatus?: (officer: Officer) => void;
}

export function OfficersCards({ 
    officers, 
    userRole, 
    onAddNew,
    onToggleStatus 
}: OfficersCardsProps) {
    const [searchTerm, setSearchTerm] = useState('');
    const [currentPage, setCurrentPage] = useState(1);
    const itemsPerPage = 6;
    const isHead = userRole === 'head';

    // Reset pagination when officers change
    useEffect(() => {
        setCurrentPage(1);
    }, [officers.length]);

    // Filter officers based on search term
    const filteredOfficers = officers.filter(officer => {
        const searchableFields = `${officer.firstname} ${officer.lastname} ${officer.email} ${officer.username}`.toLowerCase();
        return searchableFields.includes(searchTerm.toLowerCase());
    });

    // Pagination
    const totalPages = Math.ceil(filteredOfficers.length / itemsPerPage);
    const safePage = Math.min(currentPage, totalPages);
    const startIndex = (safePage - 1) * itemsPerPage;
    const paginatedOfficers = filteredOfficers.slice(startIndex, startIndex + itemsPerPage);

    return (
        <div className="flex flex-col gap-6">
            {/* Search */}
            <div className="relative">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
                <Input
                    type="text"
                    placeholder="Search officers..."
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
                Showing {paginatedOfficers.length} of {filteredOfficers.length} officers
            </div>

            {/* Cards Grid */}
            {paginatedOfficers.length > 0 ? (
                <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    {paginatedOfficers.map((officer) => (
                        <Card key={officer.id} className="hover:shadow-lg transition-shadow duration-200 border-green-100">
                            <CardHeader className="pb-3">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-3">
                                        <Avatar className="h-12 w-12">
                                            <AvatarImage 
                                                src={officer.photo.startsWith('/storage/') ? officer.photo : `/storage/${officer.photo}`} 
                                                alt={`${officer.firstname} ${officer.lastname}`} 
                                            />
                                            <AvatarFallback className="bg-green-100 text-green-700">
                                                {officer.firstname[0]}{officer.lastname[0]}
                                            </AvatarFallback>
                                        </Avatar>
                                        <div>
                                            <h3 className="font-semibold text-gray-900">{officer.firstname} {officer.lastname}</h3>
                                            <p className="text-sm text-gray-500 font-mono">{officer.username}</p>
                                        </div>
                                    </div>
                                    {isHead && (
                                        <DropdownMenu>
                                            <DropdownMenuTrigger asChild>
                                                <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
                                                    <MoreVertical className="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" className="cursor-pointer">
                                                <DropdownMenuItem 
                                                    onClick={() => onToggleStatus?.(officer)}
                                                    className={`cursor-pointer ${officer.status === 'active' ? 'text-red-600' : 'text-green-600'}`}
                                                >
                                                    {officer.status === 'active' ? (
                                                        <>
                                                            <UserX className="h-4 w-4 mr-2" />
                                                            Deactivate Account
                                                        </>
                                                    ) : (
                                                        <>
                                                            <UserCheck className="h-4 w-4 mr-2" />
                                                            Activate Account
                                                        </>
                                                    )}
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    )}
                                </div>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <div className="flex items-center justify-between">
                                    <Badge variant={officer.status === 'active' ? 'default' : 'secondary'}>
                                        {officer.status}
                                    </Badge>
                                    <Badge variant="outline" className="border-green-200 text-green-700">
                                        {officer.role}
                                    </Badge>
                                </div>
                                
                                <div className="space-y-2 text-sm">
                                    <div className="flex justify-between">
                                        <span className="text-gray-500">Email:</span>
                                        <span className="font-medium text-xs truncate max-w-32">{officer.email}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-gray-500">Created:</span>
                                        <span className="font-medium">{new Date(officer.created_at).toLocaleDateString()}</span>
                                    </div>
                                </div>
                                
                                <div className="flex items-center gap-2 pt-2 border-t border-gray-100">
                                    <Clock className="h-4 w-4 text-gray-400" />
                                    <span className="text-xs text-gray-500">
                                        Last login: {new Date(officer.last_login).toLocaleString()}
                                    </span>
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
                        <p className="text-lg font-medium text-gray-600">No officers found</p>
                        <p className="text-sm text-gray-500 mt-2">Try adjusting your search criteria or add a new officer.</p>
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

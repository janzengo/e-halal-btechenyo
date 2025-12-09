import React, { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { AdminRole } from '@/types/ehalal';
import { Search, ChevronLeft, ChevronRight } from 'lucide-react';
import { router } from '@inertiajs/react';

interface AdminLog {
    id: number;
    timestamp: string;
    user_id: string;
    role: string;
    action: string;
    action_type: string;
    model_type: string | null;
    ip_address: string;
    admin_name: string;
}

interface PaginationInfo {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    has_more_pages: boolean;
}

interface AdminLogsTableProps {
    logs: AdminLog[];
    pagination: PaginationInfo;
    userRole: AdminRole;
}

export function AdminLogsTable({
    logs,
    pagination,
    userRole
}: AdminLogsTableProps) {
    const [searchTerm, setSearchTerm] = useState('');
    const [roleFilter, setRoleFilter] = useState<string>('all');
    const [actionFilter, setActionFilter] = useState<string>('all');

    // Initialize filters from URL parameters
    React.useEffect(() => {
        const urlParams = new URLSearchParams(window.location.search);
        const search = urlParams.get('search') || '';
        const role = urlParams.get('role') || 'all';
        const action = urlParams.get('action') || 'all';

        setSearchTerm(search);
        setRoleFilter(role);
        setActionFilter(action);
    }, []);

    // Check if any filters are active
    const hasActiveFilters = searchTerm !== '' || roleFilter !== 'all' || actionFilter !== 'all';

    // Clear all filters function
    const clearAllFilters = () => {
        setSearchTerm('');
        setRoleFilter('all');
        setActionFilter('all');
        router.get('/head/logs', {}, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    // Handle pagination navigation
    const handlePageChange = (page: number) => {
        router.get('/head/logs', {
            page,
            search: searchTerm || undefined,
            role: roleFilter !== 'all' ? roleFilter : undefined,
            action: actionFilter !== 'all' ? actionFilter : undefined,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    // Handle filter changes
    const handleFilterChange = () => {
        router.get('/head/logs', {
            search: searchTerm || undefined,
            role: roleFilter !== 'all' ? roleFilter : undefined,
            action: actionFilter !== 'all' ? actionFilter : undefined,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    // Debounced search
    useEffect(() => {
        const timeoutId = setTimeout(() => {
            handleFilterChange();
        }, 500);
        return () => clearTimeout(timeoutId);
    }, [searchTerm]);

    // Handle role and action filter changes immediately
    useEffect(() => {
        handleFilterChange();
    }, [roleFilter, actionFilter]);

    const getActionTypeStyles = (actionType: string) => {
        switch (actionType) {
            case 'create':
                return 'text-green-700 bg-green-50 border-green-200';
            case 'update':
                return 'text-blue-700 bg-blue-50 border-blue-200';
            case 'delete':
                return 'text-red-700 bg-red-50 border-red-200';
            case 'login':
            case 'otp_verification_success':
                return 'text-emerald-700 bg-emerald-50 border-emerald-200';
            case 'logout':
                return 'text-gray-700 bg-gray-50 border-gray-200';
            default:
                return 'text-gray-600 bg-gray-100 border-gray-300';
        }
    };

    return (
        <div className="flex flex-col gap-6">
            {/* Filters */}
            <div className="flex flex-col sm:flex-row gap-4">
                <div className="relative flex-1">
                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
                    <Input
                        type="text"
                        placeholder="Search logs..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                        className="pl-10 focus:border-green-500 focus:ring-green-500 h-11"
                    />
                </div>
                <Select value={roleFilter} onValueChange={setRoleFilter}>
                    <SelectTrigger className="w-full sm:w-40">
                        <SelectValue placeholder="Role" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Roles</SelectItem>
                        <SelectItem value="head">Electoral Head</SelectItem>
                        <SelectItem value="officer">Officer</SelectItem>
                        <SelectItem value="system">System</SelectItem>
                    </SelectContent>
                </Select>
                <Select value={actionFilter} onValueChange={setActionFilter}>
                    <SelectTrigger className="w-full sm:w-40">
                        <SelectValue placeholder="Action" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Actions</SelectItem>
                        <SelectItem value="create">Create</SelectItem>
                        <SelectItem value="update">Update</SelectItem>
                        <SelectItem value="delete">Delete</SelectItem>
                        <SelectItem value="login">Login</SelectItem>
                        <SelectItem value="logout">Logout</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            {/* Results count */}
            <div className="text-sm text-gray-600">
                Showing {pagination.from || 0} to {pagination.to || 0} of {pagination.total} logs
            </div>

            {/* Table */}
            <div className="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Admin</TableHead>
                            <TableHead>Role</TableHead>
                            <TableHead>Action Type</TableHead>
                            <TableHead>Description</TableHead>
                            <TableHead>Resource</TableHead>
                            <TableHead>IP Address</TableHead>
                            <TableHead>Timestamp</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {logs.map((log) => (
                            <TableRow key={log.id}>
                                <TableCell>
                                    <div className="font-medium">
                                        {log.admin_name || `User #${log.user_id}`}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="outline" className="capitalize">
                                        {log.role}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        variant="outline"
                                        className={getActionTypeStyles(log.action_type)}
                                    >
                                        {log.action_type.replace(/_/g, ' ')}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div className="max-w-md">
                                        <p className="text-sm">{log.action}</p>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div className="text-sm text-muted-foreground">
                                        {log.model_type || '-'}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div className="font-mono text-xs text-muted-foreground">
                                        {log.ip_address}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div className="text-sm text-muted-foreground">
                                        {new Date(log.timestamp).toLocaleString('en-PH', {
                                            timeZone: 'Asia/Manila',
                                            year: 'numeric',
                                            month: '2-digit',
                                            day: '2-digit',
                                            hour: '2-digit',
                                            minute: '2-digit',
                                            second: '2-digit',
                                            hour12: true
                                        })}
                                    </div>
                                </TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            </div>

            {logs.length === 0 && (
                <div className="text-center py-12">
                    <div className="flex flex-col items-center gap-4">
                        <div className="h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center">
                            <Search className="h-6 w-6 text-gray-400" />
                        </div>
                        <div className="text-center">
                            <h3 className="text-lg font-medium text-gray-900 mb-2">
                                {hasActiveFilters ? 'No logs found' : 'No logs available'}
                            </h3>
                            <p className="text-sm text-gray-500 mb-4">
                                {hasActiveFilters
                                    ? 'No admin logs match your current search criteria. Try adjusting your filters or search terms.'
                                    : 'No admin logs are available at this time.'
                                }
                            </p>
                            {hasActiveFilters && (
                                <div className="flex justify-center">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={clearAllFilters}
                                        className="flex items-center gap-2"
                                    >
                                        Clear all filters
                                    </Button>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            )}

            {/* Pagination */}
            {pagination.last_page > 1 && (
                <div className="flex justify-between items-center mt-8 py-4">
                    <div className="text-sm text-gray-600">
                        Page {pagination.current_page} of {pagination.last_page}
                    </div>

                    <div className="flex items-center space-x-2">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handlePageChange(pagination.current_page - 1)}
                            disabled={pagination.current_page === 1}
                        >
                            <ChevronLeft className="h-4 w-4" />
                            Previous
                        </Button>

                        <div className="flex items-center space-x-1">
                            {Array.from({ length: Math.min(5, pagination.last_page) }, (_, i) => {
                                const page = i + 1;
                                return (
                                    <Button
                                        key={page}
                                        variant={page === pagination.current_page ? "default" : "outline"}
                                        size="sm"
                                        onClick={() => handlePageChange(page)}
                                        className="w-8 h-8 p-0"
                                    >
                                        {page}
                                    </Button>
                                );
                            })}
                        </div>

                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handlePageChange(pagination.current_page + 1)}
                            disabled={pagination.current_page === pagination.last_page}
                        >
                            Next
                            <ChevronRight className="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            )}
        </div>
    );
}

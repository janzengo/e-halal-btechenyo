import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import AdminLayout from '@/layouts/admin/admin-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import { Save, AlertCircle, Pause, Settings2, Plus } from 'lucide-react';

interface Election {
    id: number;
    election_name: string;
    status: string;
    end_time: string | null;
    control_number: string;
    last_status_change: string | null;
    created_at: string | null;
    updated_at: string | null;
}

interface ConfigureElectionProps extends Record<string, unknown> {
    election: Election | null;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Settings',
        href: '/head/dashboard',
    },
    {
        title: 'Configure Election',
        href: '/head/configure',
    },
];

export default function ConfigureElection() {
    const { election } = usePage<ConfigureElectionProps>().props;

    const handleSave = () => {
        console.log('Saving election configuration...');
        // Implement save logic here
    };

    const handleEndElection = () => {
        if (confirm('Complete Election?\n\nThis will end the election permanently. This action cannot be undone!')) {
            console.log('Ending election...');
            // Implement end election logic here
        }
    };

    const handleCreateElection = () => {
        console.log('Creating new election...');
        // Implement create election logic here
    };

    // Format end time for datetime-local input
    const formatEndTimeForInput = (endTime: string | null) => {
        if (!endTime) return '';
        const date = new Date(endTime);
        return date.toISOString().slice(0, 16);
    };

    return (
        <AdminLayout
            userRole="head"
            currentPath="/head/configure"
            breadcrumbs={breadcrumbs}
        >
            <Head title="Configure Election" />

            {/* Header */}
            <div className="flex justify-between items-center">
                <div>
                    <h2 className="text-2xl font-bold text-gray-900">Configure Election</h2>
                    <p className="text-gray-600">Set up election parameters</p>
                </div>
            </div>

            {!election ? (
                /* No Election State */
                <Empty className="border my-8">
                    <EmptyHeader>
                        <EmptyMedia variant="icon">
                            <Settings2 />
                        </EmptyMedia>
                        <EmptyTitle>No Election Configured</EmptyTitle>
                        <EmptyDescription>
                            There is no election set up yet. Create a new election to get started with the voting system.
                        </EmptyDescription>
                    </EmptyHeader>
                    <EmptyContent>
                        <Button variant="outlinePrimary" onClick={handleCreateElection}>
                            <Plus className="h-4 w-4" />
                            Create New Election
                        </Button>
                    </EmptyContent>
                </Empty>
            ) : (
                /* Election Configuration Form */
                <form onSubmit={(e) => { e.preventDefault(); handleSave(); }} className="space-y-6">
                    {/* Basic Election Configuration */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Basic Election Configuration</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="election_name">Election Name</Label>
                                <Input
                                    id="election_name"
                                    defaultValue={election.election_name}
                                    placeholder="Enter election name"
                                    required
                                />
                            </div>
                            
                            <div className="space-y-2">
                                <Label htmlFor="end_time">End Time & Date</Label>
                                <Input
                                    id="end_time"
                                    type="datetime-local"
                                    defaultValue={formatEndTimeForInput(election.end_time)}
                                />
                                <p className="text-sm text-gray-500">All times are in Philippine Time (UTC+8:00)</p>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="status">Election Status</Label>
                                <Select defaultValue={election.status}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="setup">Setup (Initial Configuration)</SelectItem>
                                        <SelectItem value="pending">Pending (Ready for Activation)</SelectItem>
                                        <SelectItem value="active">Active (Voting Ongoing)</SelectItem>
                                        <SelectItem value="paused">Paused (Maintenance)</SelectItem>
                                        <SelectItem value="completed">Completed (Election Ended)</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Current Election Status */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Current Election Status</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <p className="text-sm font-medium text-gray-500">Name</p>
                                    <p className="text-lg">{election.election_name}</p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-gray-500">Status</p>
                                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                                        election.status === 'active' ? 'bg-green-100 text-green-800' :
                                        election.status === 'paused' ? 'bg-yellow-100 text-yellow-800' :
                                        election.status === 'pending' ? 'bg-blue-100 text-blue-800' :
                                        election.status === 'setup' ? 'bg-gray-100 text-gray-800' :
                                        'bg-red-100 text-red-800'
                                    }`}>
                                        {election.status.charAt(0).toUpperCase() + election.status.slice(1)}
                                    </span>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-gray-500">End Time</p>
                                    <p className="text-lg">
                                        {election.end_time ? new Date(election.end_time).toLocaleDateString('en-PH', {
                                            year: 'numeric',
                                            month: 'long',
                                            day: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        }) + ' (Philippine Time)' : 'Not set'}
                                    </p>
                                </div>
                            </div>

                            {/* Control Number */}
                            {election.control_number && (
                                <div className="bg-gray-50 border border-gray-200 p-4 rounded-lg">
                                    <p className="text-sm font-medium text-gray-500">Control Number</p>
                                    <p className="text-lg font-mono">{election.control_number}</p>
                                </div>
                            )}

                            {/* Status Messages */}
                            <div className={`p-4 rounded-lg ${
                                election.status === 'active' ? 'bg-green-50 border border-green-200' :
                                election.status === 'paused' ? 'bg-yellow-50 border border-yellow-200' :
                                election.status === 'pending' ? 'bg-blue-50 border border-blue-200' :
                                election.status === 'setup' ? 'bg-gray-50 border border-gray-200' :
                                'bg-red-50 border border-red-200'
                            }`}>
                                <div className="flex items-center gap-2">
                                    <AlertCircle className={`h-4 w-4 ${
                                        election.status === 'active' ? 'text-green-600' :
                                        election.status === 'paused' ? 'text-yellow-600' :
                                        election.status === 'pending' ? 'text-blue-600' :
                                        election.status === 'setup' ? 'text-gray-600' :
                                        'text-red-600'
                                    }`} />
                                    <p className={`text-sm ${
                                        election.status === 'active' ? 'text-green-800' :
                                        election.status === 'paused' ? 'text-yellow-800' :
                                        election.status === 'pending' ? 'text-blue-800' :
                                        election.status === 'setup' ? 'text-gray-800' :
                                        'text-red-800'
                                    }`}>
                                        {election.status === 'setup' && 'Election is in setup mode. Configure all settings before proceeding.'}
                                        {election.status === 'pending' && 'Election is ready for activation. Review all settings before starting.'}
                                        {election.status === 'active' && 'Election is active. Voting is enabled.'}
                                        {election.status === 'paused' && 'Election is paused. Voting is temporarily disabled.'}
                                        {election.status === 'completed' && 'Election is completed. No further changes allowed.'}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Status Rules */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Status Rules</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ul className="space-y-2 text-sm text-gray-600">
                                <li><strong>Setup:</strong> Initial configuration phase. All modifications allowed.</li>
                                <li><strong>Pending:</strong> Ready for activation. All modifications allowed.</li>
                                <li><strong>Active:</strong> Election is running. No modifications allowed.</li>
                                <li><strong>Paused:</strong> Temporary halt. Limited modifications allowed. No voting possible.</li>
                                <li><strong>Completed:</strong> Election ended. No modifications allowed. Results available.</li>
                                <li>End time must be set before activating the election.</li>
                                <li>After end time is reached, election must be completed.</li>
                                <li>To make major changes, pause the election or return to setup status.</li>
                            </ul>
                        </CardContent>
                    </Card>

                    {/* Action Buttons */}
                    <div className="flex justify-end gap-4">
                        <Button type="submit" variant="outlinePrimary">
                            <Save className="h-4 w-4" />
                            Save Changes
                        </Button>
                        {election.status !== 'completed' && (
                            <Button type="button" variant="destructive" onClick={handleEndElection}>
                                <Pause className="h-4 w-4" />
                                End Election
                            </Button>
                        )}
                    </div>
                </form>
            )}
        </AdminLayout>
    );
}
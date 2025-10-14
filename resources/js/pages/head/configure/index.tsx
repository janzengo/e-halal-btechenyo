import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AdminLayout from '@/layouts/admin/admin-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Save, AlertCircle, Pause } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Settings',
        href: '/head/dashboard',
    },
    {
        title: 'Configure Election',
        href: '/head/settings',
    },
];

export default function ConfigureElection() {
    // Dummy current election data - replace with actual data from backend
    const currentElection = {
        election_name: 'Sangguniang Mag-aaral 2025',
        status: 'pending',
        end_time: '2024-02-29T23:59'
    };

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

    return (
        <AdminLayout
            userRole="head"
            currentPath="/head/settings"
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
                                defaultValue={currentElection.election_name}
                                placeholder="Enter election name"
                                required
                            />
                        </div>
                        
                        <div className="space-y-2">
                            <Label htmlFor="end_time">End Time & Date</Label>
                            <Input
                                id="end_time"
                                type="datetime-local"
                                defaultValue={currentElection.end_time}
                                required
                            />
                            <p className="text-sm text-gray-500">All times are in Philippine Time (UTC+8:00)</p>
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="status">Election Status</Label>
                            <Select defaultValue={currentElection.status}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent>
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
                                <p className="text-lg">{currentElection.election_name}</p>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-gray-500">Status</p>
                                <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                                    currentElection.status === 'active' ? 'bg-green-100 text-green-800' :
                                    currentElection.status === 'paused' ? 'bg-yellow-100 text-yellow-800' :
                                    currentElection.status === 'pending' ? 'bg-blue-100 text-blue-800' :
                                    'bg-red-100 text-red-800'
                                }`}>
                                    {currentElection.status.charAt(0).toUpperCase() + currentElection.status.slice(1)}
                                </span>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-gray-500">End Time</p>
                                <p className="text-lg">
                                    {new Date(currentElection.end_time).toLocaleDateString('en-PH', {
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    })} (Philippine Time)
                                </p>
                            </div>
                        </div>

                        {/* Status Messages */}
                        <div className={`p-4 rounded-lg ${
                            currentElection.status === 'active' ? 'bg-green-50 border border-green-200' :
                            currentElection.status === 'paused' ? 'bg-yellow-50 border border-yellow-200' :
                            currentElection.status === 'pending' ? 'bg-blue-50 border border-blue-200' :
                            'bg-red-50 border border-red-200'
                        }`}>
                            <div className="flex items-center gap-2">
                                <AlertCircle className={`h-4 w-4 ${
                                    currentElection.status === 'active' ? 'text-green-600' :
                                    currentElection.status === 'paused' ? 'text-yellow-600' :
                                    currentElection.status === 'pending' ? 'text-blue-600' :
                                    'text-red-600'
                                }`} />
                                <p className={`text-sm ${
                                    currentElection.status === 'active' ? 'text-green-800' :
                                    currentElection.status === 'paused' ? 'text-yellow-800' :
                                    currentElection.status === 'pending' ? 'text-blue-800' :
                                    'text-red-800'
                                }`}>
                                    {currentElection.status === 'pending' && 'Election is ready for activation. Review all settings before starting.'}
                                    {currentElection.status === 'active' && 'Election is active. Voting is enabled.'}
                                    {currentElection.status === 'paused' && 'Election is paused. Voting is temporarily disabled.'}
                                    {currentElection.status === 'completed' && 'Election is completed. No further changes allowed.'}
                                </p>
                            </div>
                        </div>

                        {/* Time Remaining */}
                        <div className="bg-blue-50 border border-blue-200 p-4 rounded-lg">
                            <div className="flex items-center gap-2">
                                <AlertCircle className="h-4 w-4 text-blue-600" />
                                <p className="text-sm text-blue-800">
                                    <strong>Time Remaining:</strong> 2 hours and 30 minutes
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
                    <Button type="button" variant="destructive" onClick={handleEndElection}>
                        <Pause className="h-4 w-4" />
                        End Election
                    </Button>
                </div>
            </form>
        </AdminLayout>
    );
}
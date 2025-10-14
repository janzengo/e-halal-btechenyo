import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { 
  UsersIcon, 
  PlusIcon, 
  EditIcon, 
  TrashIcon, 
  InfoIcon,
  CheckCircleIcon,
  UserIcon
} from 'lucide-react';

interface Officer {
  id: number;
  username: string;
  firstname: string;
  lastname: string;
  gender: string;
  role: string;
  created_on: string;
}

interface OfficersStepProps {
  onOfficersChange: (officers: Officer[]) => void;
  initialOfficers?: Officer[];
}

export function OfficersStep({ onOfficersChange, initialOfficers = [] }: OfficersStepProps) {
  const [officers, setOfficers] = useState<Officer[]>(initialOfficers);
  const [isLoading, setIsLoading] = useState(false);

  // Update parent component when officers change
  useEffect(() => {
    onOfficersChange(officers);
  }, [officers]);

  const handleAddOfficer = () => {
    // This would typically open a modal or navigate to an add officer page
    // For now, we'll just show an alert
    alert('Add Officer functionality would be implemented here');
  };

  const handleEditOfficer = (officer: Officer) => {
    // This would typically open an edit modal
    alert(`Edit Officer: ${officer.username}`);
  };

  const handleDeleteOfficer = (officerId: number) => {
    if (confirm('Are you sure you want to delete this officer?')) {
      setOfficers(prev => prev.filter(officer => officer.id !== officerId));
    }
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  };

  const getRoleBadgeVariant = (role: string) => {
    switch (role.toLowerCase()) {
      case 'head':
        return 'default';
      case 'officer':
        return 'secondary';
      default:
        return 'outline';
    }
  };

  return (
    <Card className="w-full">
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <UsersIcon className="h-5 w-5" />
          Election Officers
        </CardTitle>
        <CardDescription>
          Manage election officers who can help configure and manage the election
        </CardDescription>
      </CardHeader>
      <CardContent className="space-y-6">
        <Alert>
          <InfoIcon className="h-4 w-4" />
          <AlertDescription>
            Adding officers is optional but recommended. Officers can help manage positions, candidates, and voters during the election.
          </AlertDescription>
        </Alert>

        <div className="flex justify-between items-center">
          <div>
            <h4 className="font-medium">Current Officers ({officers.length})</h4>
            <p className="text-sm text-muted-foreground">
              Officers can help manage the election setup and administration
            </p>
          </div>
          <Button onClick={handleAddOfficer} className="flex items-center gap-2">
            <PlusIcon className="h-4 w-4" />
            Add Officer
          </Button>
        </div>

        {officers.length === 0 ? (
          <div className="text-center py-8 border-2 border-dashed border-muted-foreground/25 rounded-lg">
            <UserIcon className="h-12 w-12 text-muted-foreground/50 mx-auto mb-4" />
            <h3 className="text-lg font-medium text-muted-foreground mb-2">No Officers Added</h3>
            <p className="text-sm text-muted-foreground mb-4">
              You can proceed without officers, but adding them will help with election management.
            </p>
            <Button onClick={handleAddOfficer} variant="outline">
              <PlusIcon className="h-4 w-4 mr-2" />
              Add Your First Officer
            </Button>
          </div>
        ) : (
          <div className="border rounded-lg">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Username</TableHead>
                  <TableHead>Name</TableHead>
                  <TableHead>Gender</TableHead>
                  <TableHead>Role</TableHead>
                  <TableHead>Created On</TableHead>
                  <TableHead className="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {officers.map((officer) => (
                  <TableRow key={officer.id}>
                    <TableCell className="font-medium">{officer.username}</TableCell>
                    <TableCell>{officer.firstname} {officer.lastname}</TableCell>
                    <TableCell className="capitalize">{officer.gender}</TableCell>
                    <TableCell>
                      <Badge variant={getRoleBadgeVariant(officer.role)}>
                        {officer.role.charAt(0).toUpperCase() + officer.role.slice(1)}
                      </Badge>
                    </TableCell>
                    <TableCell>{formatDate(officer.created_on)}</TableCell>
                    <TableCell className="text-right">
                      <div className="flex justify-end gap-2">
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => handleEditOfficer(officer)}
                        >
                          <EditIcon className="h-4 w-4" />
                        </Button>
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => handleDeleteOfficer(officer.id)}
                          className="text-red-600 hover:text-red-700"
                        >
                          <TrashIcon className="h-4 w-4" />
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </div>
        )}

        <div className="bg-muted/50 p-4 rounded-lg">
          <h4 className="font-medium text-sm mb-3">Officer Responsibilities</h4>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div className="space-y-2">
              <div className="flex items-center gap-2">
                <CheckCircleIcon className="h-4 w-4 text-green-600" />
                <span className="font-medium">Manage Positions</span>
              </div>
              <p className="text-muted-foreground ml-6">Add and configure election positions</p>
            </div>
            <div className="space-y-2">
              <div className="flex items-center gap-2">
                <CheckCircleIcon className="h-4 w-4 text-green-600" />
                <span className="font-medium">Manage Candidates</span>
              </div>
              <p className="text-muted-foreground ml-6">Add candidates and their details</p>
            </div>
            <div className="space-y-2">
              <div className="flex items-center gap-2">
                <CheckCircleIcon className="h-4 w-4 text-green-600" />
                <span className="font-medium">Manage Voters</span>
              </div>
              <p className="text-muted-foreground ml-6">Handle voter registration and verification</p>
            </div>
            <div className="space-y-2">
              <div className="flex items-center gap-2">
                <CheckCircleIcon className="h-4 w-4 text-green-600" />
                <span className="font-medium">View Reports</span>
              </div>
              <p className="text-muted-foreground ml-6">Access election statistics and reports</p>
            </div>
          </div>
        </div>

        <div className="bg-blue-50 border border-blue-200 p-4 rounded-lg">
          <div className="flex items-start gap-3">
            <InfoIcon className="h-5 w-5 text-blue-600 mt-0.5" />
            <div>
              <h4 className="font-medium text-blue-900 mb-1">Suggestion</h4>
              <p className="text-sm text-blue-800">
                Consider adding officers first to help manage the election setup process. 
                You can always add more officers later or proceed without them.
              </p>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  );
}

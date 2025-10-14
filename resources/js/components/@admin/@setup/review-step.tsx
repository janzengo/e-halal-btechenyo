import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { 
  CheckCircleIcon, 
  CalendarIcon, 
  ClockIcon, 
  UsersIcon,
  InfoIcon,
  FileTextIcon
} from 'lucide-react';

interface ElectionData {
  electionName: string;
  endTime: string;
  isValid: boolean;
}

interface Officer {
  id: number;
  username: string;
  firstname: string;
  lastname: string;
  gender: string;
  role: string;
  created_on: string;
}

interface ReviewStepProps {
  electionData: ElectionData;
  officers: Officer[];
}

export function ReviewStep({ electionData, officers }: ReviewStepProps) {
  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  const formatTime = (dateString: string) => {
    return new Date(dateString).toLocaleTimeString('en-US', {
      hour: '2-digit',
      minute: '2-digit',
      timeZoneName: 'short'
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
    <div className="space-y-6">
      <Alert>
        <InfoIcon className="h-4 w-4" />
        <AlertDescription>
          Please review all the election details and officer information before completing the setup. 
          You can go back to previous steps to make changes if needed.
        </AlertDescription>
      </Alert>

      {/* Election Details Review */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <FileTextIcon className="h-5 w-5" />
            Election Details
          </CardTitle>
          <CardDescription>
            Review the basic election configuration
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div className="space-y-3">
              <div className="flex items-center gap-2">
                <CheckCircleIcon className="h-5 w-5 text-green-600" />
                <span className="text-base font-medium text-green-600">Election Name</span>
              </div>
              <p className="text-xl font-semibold">{electionData.electionName}</p>
            </div>
            
            <div className="space-y-3">
              <div className="flex items-center gap-2">
                <CalendarIcon className="h-5 w-5 text-blue-600" />
                <span className="text-base font-medium text-blue-600">End Date & Time</span>
              </div>
              <div>
                <p className="text-xl font-semibold">{formatDate(electionData.endTime)}</p>
                <p className="text-sm text-muted-foreground">
                  {formatTime(electionData.endTime)} Philippine Time
                </p>
              </div>
            </div>
          </div>

          <div className="bg-green-50 border border-green-200 p-4 rounded-lg">
            <div className="flex items-center gap-2">
              <CheckCircleIcon className="h-5 w-5 text-green-600" />
              <span className="font-medium text-green-900">Configuration Complete</span>
            </div>
            <p className="text-sm text-green-800 mt-1">
              All required election settings have been configured successfully.
            </p>
          </div>
        </CardContent>
      </Card>

      {/* Officers Review */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <UsersIcon className="h-5 w-5" />
            Election Officers
          </CardTitle>
          <CardDescription>
            Review the officers who will help manage the election
          </CardDescription>
        </CardHeader>
        <CardContent>
          {officers.length === 0 ? (
            <div className="text-center py-8 border-2 border-dashed border-muted-foreground/25 rounded-lg">
              <UsersIcon className="h-12 w-12 text-muted-foreground/50 mx-auto mb-4" />
              <h3 className="text-lg font-medium text-muted-foreground mb-2">No Officers Added</h3>
              <p className="text-sm text-muted-foreground">
                You can proceed without officers, but adding them will help with election management.
              </p>
            </div>
          ) : (
            <div className="space-y-4">
              <div className="flex items-center justify-between">
                <div>
                  <h4 className="font-medium">Total Officers: {officers.length}</h4>
                  <p className="text-sm text-muted-foreground">
                    These officers can help manage positions, candidates, and voters
                  </p>
                </div>
                <Badge variant="outline" className="flex items-center gap-1">
                  <UsersIcon className="h-3 w-3" />
                  {officers.length} Officer{officers.length !== 1 ? 's' : ''}
                </Badge>
              </div>

              <div className="border rounded-lg">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Username</TableHead>
                      <TableHead>Name</TableHead>
                      <TableHead>Gender</TableHead>
                      <TableHead>Role</TableHead>
                      <TableHead>Created On</TableHead>
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
                        <TableCell>
                          {new Date(officer.created_on).toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                          })}
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </div>
            </div>
          )}
        </CardContent>
      </Card>

      {/* Summary */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <CheckCircleIcon className="h-5 w-5" />
            Setup Summary
          </CardTitle>
          <CardDescription>
            Overview of your election setup
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div className="text-center p-6 bg-green-50 rounded-lg">
              <CheckCircleIcon className="h-10 w-10 text-green-600 mx-auto mb-3" />
              <h3 className="text-lg font-semibold text-green-900">Election Configured</h3>
              <p className="text-sm text-green-700">Ready to proceed</p>
            </div>
            
            <div className="text-center p-6 bg-blue-50 rounded-lg">
              <UsersIcon className="h-10 w-10 text-blue-600 mx-auto mb-3" />
              <h3 className="text-lg font-semibold text-blue-900">{officers.length} Officers</h3>
              <p className="text-sm text-blue-700">
                {officers.length > 0 ? 'Ready to help' : 'Optional'}
              </p>
            </div>
            
            <div className="text-center p-6 bg-purple-50 rounded-lg">
              <ClockIcon className="h-10 w-10 text-purple-600 mx-auto mb-3" />
              <h3 className="text-lg font-semibold text-purple-900">End Time Set</h3>
              <p className="text-sm text-purple-700">
                {formatTime(electionData.endTime)}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}

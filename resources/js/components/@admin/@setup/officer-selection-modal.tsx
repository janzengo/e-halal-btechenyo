import { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { Checkbox } from '@/components/ui/checkbox';
import { SearchIcon, UsersIcon, PlusIcon } from 'lucide-react';
import { toast } from 'sonner';

interface Officer {
  id: number;
  username: string;
  firstname: string;
  lastname: string;
  gender: string;
  role: string;
  created_on: string;
}

interface OfficerSelectionModalProps {
  onOfficersAdded: (officers: Officer[]) => void;
  currentOfficers: Officer[];
}

export function OfficerSelectionModal({ onOfficersAdded, currentOfficers }: OfficerSelectionModalProps) {
  const [isOpen, setIsOpen] = useState(false);
  const [availableOfficers, setAvailableOfficers] = useState<Officer[]>([]);
  const [selectedOfficers, setSelectedOfficers] = useState<number[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');

  // Fetch available officers when modal opens
  useEffect(() => {
    if (isOpen) {
      fetchAvailableOfficers();
    }
  }, [isOpen]);

  const fetchAvailableOfficers = async () => {
    setIsLoading(true);
    try {
      // First test the database connection
      console.log('Testing database connection...');
      const testResponse = await fetch('/head/api/test-officers', {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/json',
        }
      });
      
      if (testResponse.ok) {
        const testData = await testResponse.json();
        console.log('Database test result:', testData);
      } else {
        console.error('Database test failed:', testResponse.status);
      }
      
      // Now fetch the actual officers
      const response = await fetch('/head/api/officers', {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/json',
        }
      });
      
      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`);
      }
      
      const data = await response.json();
      console.log('Fetched officers data:', data); // Debug log
      
      if (data.error) {
        throw new Error(data.error);
      }
      
      // Handle the date format - created_on is a date field, not datetime
      const officers = (data.officers || []).map((officer: any) => ({
        ...officer,
        created_on: officer.created_on || new Date().toISOString().split('T')[0] // Fallback to today
      }));
      
      console.log('Processed officers:', officers); // Debug log
      setAvailableOfficers(officers);
      
      if (officers.length === 0) {
        toast.info('No officers found in the system');
      } else {
        toast.success(`Loaded ${officers.length} officer(s)`);
      }
    } catch (error) {
      console.error('Error fetching officers:', error);
      toast.error(`Failed to load officers: ${error instanceof Error ? error.message : 'Unknown error'}`);
    } finally {
      setIsLoading(false);
    }
  };

  const handleToggleOfficer = (officerId: number) => {
    setSelectedOfficers(prev => 
      prev.includes(officerId)
        ? prev.filter(id => id !== officerId)
        : [...prev, officerId]
    );
  };

  const handleAddOfficers = () => {
    const selectedOfficerData = availableOfficers.filter(officer => 
      selectedOfficers.includes(officer.id)
    );
    
    // Filter out officers that are already added
    const newOfficers = selectedOfficerData.filter(officer => 
      !currentOfficers.some(current => current.id === officer.id)
    );
    
    if (newOfficers.length === 0) {
      toast.info('No new officers to add');
      return;
    }
    
    onOfficersAdded(newOfficers);
    setSelectedOfficers([]);
    setIsOpen(false);
    toast.success(`Added ${newOfficers.length} officer(s)`);
  };

  const filteredOfficers = availableOfficers.filter(officer =>
    officer.username.toLowerCase().includes(searchTerm.toLowerCase()) ||
    officer.firstname.toLowerCase().includes(searchTerm.toLowerCase()) ||
    officer.lastname.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const isOfficerAlreadyAdded = (officerId: number) => {
    return currentOfficers.some(officer => officer.id === officerId);
  };

  return (
    <Dialog open={isOpen} onOpenChange={setIsOpen}>
      <DialogTrigger asChild>
        <Button className="flex items-center gap-2">
          <PlusIcon className="h-4 w-4" />
          Add Officer
        </Button>
      </DialogTrigger>
      <DialogContent className="max-w-4xl max-h-[80vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            <UsersIcon className="h-5 w-5" />
            Add Election Officers
          </DialogTitle>
          <DialogDescription>
            Select officers from the available admin users with "officer" role to help manage the election.
          </DialogDescription>
        </DialogHeader>

        <div className="space-y-4">
          {/* Search */}
          <div className="relative">
            <SearchIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
            <Input
              placeholder="Search officers by name or username..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="pl-10"
            />
          </div>

          {/* Officers Table */}
          {isLoading ? (
            <div className="text-center py-8">
              <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div>
              <p className="text-muted-foreground mt-2">Loading officers...</p>
            </div>
          ) : filteredOfficers.length === 0 ? (
            <div className="text-center py-8">
              <UsersIcon className="h-12 w-12 text-muted-foreground/50 mx-auto mb-4" />
              <h3 className="text-lg font-medium text-muted-foreground mb-2">
                {searchTerm ? 'No Officers Found' : 'No Available Officers'}
              </h3>
              <p className="text-sm text-muted-foreground">
                {searchTerm 
                  ? 'Try adjusting your search terms'
                  : 'There are no admin users with "officer" role available to add.'
                }
              </p>
            </div>
          ) : (
            <div className="border rounded-lg">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead className="w-12">Select</TableHead>
                    <TableHead>Username</TableHead>
                    <TableHead>Name</TableHead>
                    <TableHead>Gender</TableHead>
                    <TableHead>Role</TableHead>
                    <TableHead>Status</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {filteredOfficers.map((officer) => {
                    const isAlreadyAdded = isOfficerAlreadyAdded(officer.id);
                    const isSelected = selectedOfficers.includes(officer.id);
                    
                    return (
                      <TableRow 
                        key={officer.id}
                        className={isAlreadyAdded ? 'opacity-50' : ''}
                      >
                        <TableCell>
                          <Checkbox
                            checked={isSelected}
                            onCheckedChange={() => handleToggleOfficer(officer.id)}
                            disabled={isAlreadyAdded}
                          />
                        </TableCell>
                        <TableCell className="font-medium">{officer.username}</TableCell>
                        <TableCell>{officer.firstname} {officer.lastname}</TableCell>
                        <TableCell className="capitalize">{officer.gender}</TableCell>
                        <TableCell>
                          <Badge variant="secondary">
                            {officer.role.charAt(0).toUpperCase() + officer.role.slice(1)}
                          </Badge>
                        </TableCell>
                        <TableCell>
                          {isAlreadyAdded ? (
                            <Badge variant="outline">Already Added</Badge>
                          ) : (
                            <Badge variant="default">Available</Badge>
                          )}
                        </TableCell>
                      </TableRow>
                    );
                  })}
                </TableBody>
              </Table>
            </div>
          )}

          {/* Actions */}
          <div className="flex justify-between items-center pt-4 border-t">
            <div className="text-sm text-muted-foreground">
              {selectedOfficers.filter(id => !isOfficerAlreadyAdded(id)).length} officer(s) selected
            </div>
            <div className="flex gap-2">
              <Button variant="outline" onClick={() => setIsOpen(false)}>
                Cancel
              </Button>
              <Button 
                onClick={handleAddOfficers}
                disabled={selectedOfficers.filter(id => !isOfficerAlreadyAdded(id)).length === 0}
              >
                Add Selected Officers
              </Button>
            </div>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  );
}

import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { InfoIcon } from 'lucide-react';
import { DateTimePicker } from '@/components/ui/date-time-picker';

interface ElectionPreConfigStepProps {
  onDataChange: (data: { electionName: string; endTime: string; isValid: boolean }) => void;
  initialData?: {
    electionName: string;
    endTime: string;
  };
}

export function ElectionPreConfigStep({ onDataChange, initialData }: ElectionPreConfigStepProps) {
  const [electionName, setElectionName] = useState(initialData?.electionName || '');
  const [endTime, setEndTime] = useState<Date | undefined>(
    initialData?.endTime ? new Date(initialData.endTime) : undefined
  );

  // Simple validation for enabling/disabling Next button
  const isFormValid = () => {
    const now = new Date();
    return electionName.trim().length >= 5 && endTime && endTime > now;
  };

  // Update parent component when data changes
  useEffect(() => {
    const isValid = isFormValid();
    onDataChange({
      electionName: electionName.trim(),
      endTime: endTime ? endTime.toISOString() : '',
      isValid: !!isValid
    });
  }, [electionName, endTime]);

  const handleElectionNameChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setElectionName(e.target.value);
  };

  const handleEndTimeChange = (date: Date | undefined) => {
    setEndTime(date);
  };

  return (
    <div className="w-full space-y-6">
      {/* Setup Checklist - Moved to top */}
      <div className="bg-muted/50 p-4 rounded-lg">
        <h4 className="font-medium text-sm mb-3">Setup Checklist</h4>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
          <div className={`flex items-center gap-2 ${electionName.trim() ? 'text-green-600' : 'text-muted-foreground'}`}>
            <div className={`w-4 h-4 rounded-full border-2 flex items-center justify-center ${
              electionName.trim() ? 'bg-green-600 border-green-600' : 'border-muted-foreground'
            }`}>
              {electionName.trim() && <div className="w-2 h-2 bg-white rounded-full" />}
            </div>
            Set election name
          </div>
          <div className={`flex items-center gap-2 ${endTime ? 'text-green-600' : 'text-muted-foreground'}`}>
            <div className={`w-4 h-4 rounded-full border-2 flex items-center justify-center ${
              endTime ? 'bg-green-600 border-green-600' : 'border-muted-foreground'
            }`}>
              {endTime && <div className="w-2 h-2 bg-white rounded-full" />}
            </div>
            Set end date and time
          </div>
        </div>
      </div>

      {/* Form Fields - No container boxing */}
      <div className="space-y-4">
        <Alert>
          <InfoIcon className="h-4 w-4" />
          <AlertDescription>
            These settings are required to proceed with the election setup. Make sure to set an appropriate end time for your election.
          </AlertDescription>
        </Alert>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div className="space-y-2">
            <Label htmlFor="election-name" className="text-sm font-medium">Election Name</Label>
            <Input
              id="election-name"
              type="text"
              placeholder="Example: 2025 Sangguniang Mag-aaral Elections"
              value={electionName}
              onChange={handleElectionNameChange}
            />
            <p className="text-sm text-muted-foreground">
              Enter a descriptive name for this election
            </p>
          </div>

          <div className="space-y-2">
            <Label htmlFor="end-time" className="text-sm font-medium">Election End Time & Date</Label>
            <DateTimePicker
              value={endTime}
              onChange={handleEndTimeChange}
              placeholder="MM/DD/YYYY hh:mm aa"
            />
            <p className="text-sm text-muted-foreground">
              All times are in Philippine Time (UTC+8:00)
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}

import { Head, router, usePage } from '@inertiajs/react';
import { useState, useEffect, useCallback } from 'react';
import { defineStepper } from '@/components/ui/stepper';
import { ElectionPreConfigStep } from '@/components/@admin/@setup/election-pre-config-step';
import { OfficersStep } from '@/components/@admin/@setup/officers-step';
import { ReviewStep } from '@/components/@admin/@setup/review-step';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import AdminSetupLayout from '@/layouts/admin/admin-setup-layout';
import { toast } from 'sonner';

import {
  SettingsIcon,
  InfoIcon,
  ArrowRightIcon,
  CheckCircle2
} from 'lucide-react';

const STORAGE_KEY = 'ehalal_setup_data';

// Define the stepper steps using the correct Stepperize pattern
const { Stepper, utils } = defineStepper(
  { id: 'election-config', title: 'Configuration', description: 'Set up basic election details' },
  { id: 'officers', title: 'Election Officers', description: 'Add officers to help manage the election' },
  { id: 'review', title: 'Review & Confirm', description: 'Review all settings before completing setup' }
);

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

interface SetupPageProps extends Record<string, unknown> {
  existingOfficers?: Officer[];
  flash?: {
    success?: string;
    error?: string;
  };
}

export default function SetupPage() {
  const props = usePage<SetupPageProps & { auth: { user?: any } }>().props;
  const { existingOfficers = [], auth, flash } = props;
  
  // Debug: Log ALL props received from backend
  console.log('=== SETUP PAGE DEBUG ===');
  console.log('All props:', props);
  console.log('existingOfficers:', existingOfficers);
  console.log('existingOfficers type:', typeof existingOfficers);
  console.log('existingOfficers isArray:', Array.isArray(existingOfficers));
  console.log('existingOfficers length:', existingOfficers?.length);
  console.log('======================');
  
  const [electionData, setElectionData] = useState<ElectionData>({
    electionName: '',
    endTime: '',
    isValid: false
  });
  const [officers, setOfficers] = useState<Officer[]>(existingOfficers);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isLoaded, setIsLoaded] = useState(false);

  // Handle flash messages
  useEffect(() => {
    if (flash?.success) {
      toast.success(flash.success);
    }
    if (flash?.error) {
      toast.error(flash.error);
    }
  }, [flash]);

  // Check authentication and load data from browser storage on mount
  useEffect(() => {
    // Check if user is authenticated
    if (!auth.user) {
      window.location.href = '/auth/admin-btech';
      return;
    }

    const savedData = localStorage.getItem(STORAGE_KEY);
    if (savedData) {
      try {
        const parsed = JSON.parse(savedData);
        if (parsed.electionData) {
          setElectionData(parsed.electionData);
        }
        // Only restore officers from localStorage if we don't have backend data
        if (parsed.officers && existingOfficers.length === 0) {
          setOfficers(parsed.officers);
        }
        toast.info('Restored your previous setup progress');
      } catch (e) {
        console.error('Failed to parse saved setup data:', e);
      }
    }
    
    // Initialize with backend officers if available
    if (existingOfficers.length > 0) {
      setOfficers(existingOfficers);
    }
    setIsLoaded(true);
  }, [auth.user]);

  // Save data to browser storage whenever it changes
  const saveToStorage = useCallback(() => {
    const dataToSave = {
      electionData,
      officers,
      savedAt: new Date().toISOString()
    };
    localStorage.setItem(STORAGE_KEY, JSON.stringify(dataToSave));
  }, [electionData, officers]);

  useEffect(() => {
    if (isLoaded) {
      saveToStorage();
    }
  }, [electionData, officers, isLoaded]);

  const handleElectionDataChange = (data: ElectionData) => {
    setElectionData(data);
  };

  const handleOfficersChange = (newOfficers: Officer[]) => {
    setOfficers(newOfficers);
  };

  const clearStoredData = () => {
    localStorage.removeItem(STORAGE_KEY);
  };

  const handleCompleteSetup = async () => {
    if (!electionData.isValid) {
      toast.error('Please complete the election configuration first');
      return;
    }

    setIsSubmitting(true);
    
    router.post('/head/setup/complete', {
      election_name: electionData.electionName,
      end_time: electionData.endTime || null,
    }, {
      onSuccess: () => {
        // Clear stored data on successful setup
        clearStoredData();
        toast.success('Election created successfully!');
      },
      onError: (errors) => {
        toast.error('Failed to create election. Please try again.');
        console.error('Setup errors:', errors);
      },
      onFinish: () => {
        setIsSubmitting(false);
      }
    });
  };

  const handleLogout = () => {
    router.post('/auth/admin-btech/logout', {}, {
      onError: (errors) => {
        console.error('Logout error:', errors);
      }
    });
  };

  // Show loading while checking authentication
  if (!isLoaded) {
    return (
      <AdminSetupLayout
        userRole="head"
        handleLogout={handleLogout}
      >
        <Head title="Election Setup" />
        <div className="flex items-center justify-center min-h-96">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
        </div>
      </AdminSetupLayout>
    );
  }

  return (
    <AdminSetupLayout
      userRole="head"
      handleLogout={handleLogout}
    >
      <Head title="Election Setup" />

      <div className="space-y-8 pt-6">
        {/* Header */}
        <div className="flex justify-between items-center">
          <div>
            <h2 className="text-xl font-bold text-gray-900">Election Setup</h2>
            <p className="text-sm text-gray-600">Configure your election settings and manage officers</p>
          </div>
          <Badge variant="outline" className="flex items-center gap-2 text-xs">
            <SettingsIcon className="h-3 w-3" />
            Setup Phase
          </Badge>
        </div>

        <Alert>
          <InfoIcon className="h-4 w-4" />
          <AlertDescription>
            Complete the election setup by configuring general settings and adding election officers. 
            All required settings must be configured before proceeding.
          </AlertDescription>
        </Alert>

        {/* Stepper using correct Stepperize pattern */}
        <Stepper.Provider
          className="space-y-6"
          variant="horizontal"
          tracking={true}
        >
          {({ methods }) => (
            <>
              {/* Stepper Navigation - Hidden on mobile */}
              <div className="w-full overflow-hidden hidden sm:block relative mb-8">
                {/* Progress Bar Background */}
                <div className="stepper-progress-bar"></div>
                
                {/* Progress Bar Fill */}
                <div 
                  className="stepper-progress-fill"
                  style={{ 
                    width: `${((utils.getIndex(methods.current.id) + 1) / 3) * 100}%` 
                  }}
                ></div>
                
                <Stepper.Navigation className="scrollbar-hide relative z-10">
                  {methods.all.map((step) => (
                    <Stepper.Step
                      key={step.id}
                      of={step.id}
                      onClick={() => {
                        // Allow navigation to previous steps or current step
                        const currentIndex = methods.all.findIndex(s => s.id === methods.current.id);
                        const targetIndex = methods.all.findIndex(s => s.id === step.id);
                        if (targetIndex <= currentIndex) {
                          methods.goTo(step.id);
                        }
                      }}
                      disabled={false}
                    >
                      <Stepper.Title>{step.title}</Stepper.Title>
                      <Stepper.Description>{step.description}</Stepper.Description>
                    </Stepper.Step>
                  ))}
                </Stepper.Navigation>
              </div>

              {/* Mobile Step Indicator */}
              <div className="sm:hidden mb-6">
                <div className="flex items-center justify-center mb-4">
                  <div className="flex items-center">
                    {methods.all.map((step, index) => {
                      const isActive = methods.current.id === step.id;
                      const isCompleted = methods.all.findIndex(s => s.id === methods.current.id) > index;
                      
                      return (
                        <div key={step.id} className="flex items-center">
                          <div className={`
                            w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium
                            ${isActive ? 'bg-blue-600 text-white' : 
                              isCompleted ? 'bg-green-500 text-white' : 
                              'bg-gray-200 text-gray-500'}
                          `}>
                            {isCompleted ? '✓' : index + 1}
                          </div>
                          {index < methods.all.length - 1 && (
                            <div className={`w-4 h-0.5 mx-2 ${
                              isCompleted ? 'bg-green-500' : 'bg-gray-200'
                            }`} />
                          )}
                        </div>
                      );
                    })}
                  </div>
                </div>
                <div className="text-center">
                  <h3 className="text-lg font-semibold text-gray-900">
                    {methods.current.title}
                  </h3>
                  <p className="text-sm text-gray-600 mt-1">
                    Step {methods.all.findIndex(s => s.id === methods.current.id) + 1} of {methods.all.length}
                  </p>
                </div>
              </div>


              {/* Step Content */}
              {methods.switch({
                "election-config": () => (
                  <Stepper.Panel className="space-y-6">
                    <Card>
                      <CardContent className="pt-6">
                        <ElectionPreConfigStep
                          onDataChange={handleElectionDataChange}
                          initialData={{
                            electionName: electionData.electionName,
                            endTime: electionData.endTime
                          }}
                        />
                      </CardContent>
                    </Card>
                    <Stepper.Controls>
                      <Button
                        type="button"
                        onClick={methods.next}
                        disabled={!electionData.isValid}
                        variant="outlinePrimary"
                      >
                        Next
                        <ArrowRightIcon className="w-4 h-4" />
                      </Button>
                    </Stepper.Controls>
                  </Stepper.Panel>
                ),
                "officers": () => (
                  <Stepper.Panel className="space-y-6">
                    <Card>
                      <CardContent className="pt-6">
                        <OfficersStep
                          onOfficersChange={handleOfficersChange}
                          initialOfficers={existingOfficers}
                        />
                      </CardContent>
                    </Card>
                    <Stepper.Controls>
                      <Button
                        type="button"
                        variant="outline"
                        onClick={methods.prev}
                        disabled={methods.isFirst}
                        className="w-full sm:w-auto"
                      >
                        Previous
                      </Button>
                      <Button
                        type="button"
                        onClick={methods.next}
                        disabled={!electionData.isValid}
                        variant="outlinePrimary"
                      >
                        Next
                        <ArrowRightIcon className="w-4 h-4" />
                      </Button>
                    </Stepper.Controls>
                  </Stepper.Panel>
                ),
                "review": () => (
                  <Stepper.Panel className="space-y-6">
                    <Card>
                      <CardContent className="pt-6">
                        <ReviewStep
                          electionData={electionData}
                          officers={officers}
                        />
                      </CardContent>
                    </Card>
                    <Stepper.Controls>
                      <Button
                        type="button"
                        variant="outline"
                        onClick={methods.prev}
                        disabled={methods.isFirst}
                        className="w-full sm:w-auto"
                      >
                        Previous
                      </Button>
                      <Button
                        type="button"
                        onClick={handleCompleteSetup}
                        disabled={!electionData.isValid || isSubmitting}
                        variant="outlinePrimary"
                      >
                        {isSubmitting ? 'Completing Setup...' : 'Complete Setup'}
                        <ArrowRightIcon className="w-4 h-4" />
                      </Button>
                    </Stepper.Controls>
                  </Stepper.Panel>
                ),
              })}
            </>
          )}
        </Stepper.Provider>
      </div>
    </AdminSetupLayout>
  );
}


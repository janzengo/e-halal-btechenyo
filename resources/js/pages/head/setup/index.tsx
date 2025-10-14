import { Head } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { defineStepper } from '@/components/ui/stepper';
import { ElectionPreConfigStep } from '@/components/@admin/@setup/election-pre-config-step';
import { OfficersStep } from '@/components/@admin/@setup/officers-step';
import { ReviewStep } from '@/components/@admin/@setup/review-step';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import AdminSetupLayout from '@/layouts/admin/admin-setup-layout';

import {
  SettingsIcon,
  InfoIcon,
  ArrowRightIcon
} from 'lucide-react';
import { router } from '@inertiajs/react';

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

export default function SetupPage() {
  const [electionData, setElectionData] = useState<ElectionData>({
    electionName: '',
    endTime: '',
    isValid: false
  });
  const [officers, setOfficers] = useState<Officer[]>([]);
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleElectionDataChange = (data: ElectionData) => {
    setElectionData(data);
  };

  const handleOfficersChange = (newOfficers: Officer[]) => {
    setOfficers(newOfficers);
  };

  const handleCompleteSetup = async () => {
    if (!electionData.isValid) {
      alert('Please complete the election configuration first');
      return;
    }

    setIsSubmitting(true);
    try {
      // Here you would typically make an API call to save the setup
      console.log('Election Data:', electionData);
      console.log('Officers:', officers);
      
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      alert('Election setup completed successfully!');
    } catch (error) {
      console.error('Error completing setup:', error);
      alert('Error completing setup. Please try again.');
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleLogout = () => {
    router.post('/logout');
  };

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
                          initialOfficers={officers}
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
                        variant="outlinePrimary"
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


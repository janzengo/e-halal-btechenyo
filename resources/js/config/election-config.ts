// Election Configuration
// Change the status here to control what the login page displays

export type ElectionStatus = 'active' | 'paused' | 'ended' | 'pending' | 'no_election';

export interface ElectionConfig {
    status: ElectionStatus;
    name: string;
    timeRemaining?: {
        days: number;
        hours: number;
        minutes: number;
    };
}

// Current election configuration
// Change this to switch between different election states
export const electionConfig: ElectionConfig = {
    status: 'active', // Change this to: 'active', 'paused', 'ended', 'pending', or 'no_election'
    name: 'Sangguniang Mag-aaral 2025',
    timeRemaining: {
        days: 2,
        hours: 5,
        minutes: 30
    }
};

// Helper function to get current election status
export function getCurrentElectionStatus(): ElectionStatus {
    return electionConfig.status;
}

// Helper function to get election name
export function getElectionName(): string {
    return electionConfig.name;
}

// Helper function to get time remaining
export function getTimeRemaining() {
    return electionConfig.timeRemaining;
}

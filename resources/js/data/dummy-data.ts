import { Admin, AdminRole, Election, Position, Candidate, Voter, ElectionStats, SystemLog } from '@/types/ehalal';

// Dummy Admin Data
export const dummyAdmins: Admin[] = [
    {
        id: 1,
        username: 'head',
        email: 'head@ehalal.test',
        password: 'hashed_password',
        firstname: 'Electoral',
        lastname: 'Head',
        photo: '/images/profile.jpg',
        created_on: '2024-06-06',
        role: 'head',
        gender: 'Male',
        created_at: '2024-06-06T00:00:00Z',
        updated_at: '2024-06-06T00:00:00Z'
    },
    {
        id: 2,
        username: 'janzengo',
        email: 'janzengo@ehalal.test',
        password: 'hashed_password',
        firstname: 'Janzen',
        lastname: 'Go',
        photo: '/images/profile.jpg',
        created_on: '2025-06-09',
        role: 'head',
        gender: 'Male',
        created_at: '2025-06-09T00:00:00Z',
        updated_at: '2025-06-09T00:00:00Z'
    },
    {
        id: 3,
        username: 'janzen',
        email: 'janzen@ehalal.test',
        password: 'hashed_password',
        firstname: 'Janzen',
        lastname: 'Go',
        photo: '/images/profile.jpg',
        created_on: '2025-09-24',
        role: 'officer',
        gender: 'Male',
        created_at: '2025-09-24T00:00:00Z',
        updated_at: '2025-09-24T00:00:00Z'
    }
];

// Dummy Election Data
export const dummyElections: Election[] = [
    {
        id: 1,
        status: 'active',
        election_name: 'Sangguniang Mag-aaral 2025',
        created_at: '2025-01-01T00:00:00Z',
        end_time: '2025-12-31T23:59:59Z',
        last_status_change: '2025-01-01T00:00:00Z',
        control_number: 'SM2025-001'
    }
];

// Dummy Positions Data
export const dummyPositions: Position[] = [
    {
        id: 1,
        description: 'President',
        max_vote: 1,
        priority: 1,
        created_at: '2025-01-01T00:00:00Z',
        updated_at: '2025-01-01T00:00:00Z'
    },
    {
        id: 2,
        description: 'Vice President',
        max_vote: 1,
        priority: 2,
        created_at: '2025-01-01T00:00:00Z',
        updated_at: '2025-01-01T00:00:00Z'
    },
    {
        id: 3,
        description: 'Secretary',
        max_vote: 1,
        priority: 3,
        created_at: '2025-01-01T00:00:00Z',
        updated_at: '2025-01-01T00:00:00Z'
    },
    {
        id: 4,
        description: 'Treasurer',
        max_vote: 1,
        priority: 4,
        created_at: '2025-01-01T00:00:00Z',
        updated_at: '2025-01-01T00:00:00Z'
    },
    {
        id: 5,
        description: 'Auditor',
        max_vote: 1,
        priority: 5,
        created_at: '2025-01-01T00:00:00Z',
        updated_at: '2025-01-01T00:00:00Z'
    },
    {
        id: 6,
        description: 'P.R.O.',
        max_vote: 2,
        priority: 6,
        created_at: '2025-01-01T00:00:00Z',
        updated_at: '2025-01-01T00:00:00Z'
    },
    {
        id: 7,
        description: '1st Year Representative',
        max_vote: 1,
        priority: 7,
        created_at: '2025-01-01T00:00:00Z',
        updated_at: '2025-01-01T00:00:00Z'
    },
    {
        id: 8,
        description: '2nd Year Representative',
        max_vote: 1,
        priority: 8,
        created_at: '2025-01-01T00:00:00Z',
        updated_at: '2025-01-01T00:00:00Z'
    },
    {
        id: 9,
        description: '3rd Year Representative',
        max_vote: 1,
        priority: 9,
        created_at: '2025-01-01T00:00:00Z',
        updated_at: '2025-01-01T00:00:00Z'
    },
    {
        id: 10,
        description: '4th Year Representative',
        max_vote: 1,
        priority: 10,
        created_at: '2025-01-01T00:00:00Z',
        updated_at: '2025-01-01T00:00:00Z'
    }
];

// Dummy Candidates Data
export const dummyCandidates: Candidate[] = [
    {
        id: 1,
        position_id: 1,
        firstname: 'John',
        lastname: 'Doe',
        partylist_id: 1,
        photo: '/images/profile.jpg',
        platform: 'Leadership for all voters',
        votes: 0,
        created_at: '2025-01-01T00:00:00Z',
        updated_at: '2025-01-01T00:00:00Z',
        position: dummyPositions[0]
    },
    {
        id: 2,
        position_id: 1,
        firstname: 'Jane',
        lastname: 'Smith',
        partylist_id: 2,
        photo: '/images/profile.jpg',
        platform: 'Unity and progress',
        votes: 0,
        created_at: '2025-01-01T00:00:00Z',
        updated_at: '2025-01-01T00:00:00Z',
        position: dummyPositions[0]
    }
];

// Dummy Voters Data
export const dummyVoters: Voter[] = [
    {
        id: 1,
        course_id: 1,
        student_number: '2021-00001',
        has_voted: false,
        created_at: '2025-01-01T00:00:00Z',
        updated_at: '2025-01-01T00:00:00Z'
    },
    {
        id: 2,
        course_id: 1,
        student_number: '2021-00002',
        has_voted: false,
        created_at: '2025-01-01T00:00:00Z',
        updated_at: '2025-01-01T00:00:00Z'
    }
];

// Dummy Election Stats
export const dummyElectionStats: ElectionStats = {
    total_voters: 1500,
    voted_count: 0,
    remaining_votes: 1500,
    participation_rate: 0
};

// Dummy System Logs
export const dummySystemLogs: SystemLog[] = [
    {
        id: 1,
        type: 'admin',
        timestamp: '2025-01-15T10:30:00Z',
        email: 'head@ehalal.test',
        action: 'Login',
        details: { login_time: '2025-01-15T10:30:00Z' },
        ip_address: '192.168.1.1',
        user_agent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    },
    {
        id: 2,
        type: 'admin',
        timestamp: '2025-01-15T09:15:00Z',
        email: 'janzen@ehalal.test',
        action: 'Create Officer',
        details: { officer_username: 'new_officer' },
        ip_address: '192.168.1.2',
        user_agent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    },
    {
        id: 3,
        type: 'voters',
        timestamp: '2025-01-15T08:45:00Z',
        student_number: '2021-00001',
        action: 'OTP Request',
        details: { otp_sent: true },
        ip_address: '192.168.1.3',
        user_agent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    }
];

// Helper functions
export const getCurrentAdmin = (role: AdminRole = 'officer'): Admin => {
    return dummyAdmins.find(admin => admin.role === role) || dummyAdmins[0];
};

export const getDashboardStats = (): ElectionStats => {
    return dummyElectionStats;
};

export const getSystemLogs = (limit: number = 10): SystemLog[] => {
    return dummySystemLogs.slice(0, limit);
};

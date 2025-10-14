// Core Election Types
export interface Election {
  id: number;
  status: ElectionStatus;
  election_name: string;
  created_at: string;
  end_time: string | null;
  last_status_change: string;
  control_number: string;
}

export type ElectionStatus = 'setup' | 'pending' | 'active' | 'paused' | 'completed';

export interface ElectionHistory {
  id: number;
  election_name: string;
  status: ElectionStatus;
  end_time: string;
  last_status_change: string | null;
  details_pdf: string;
  results_pdf: string;
  created_at: string;
  control_number: string;
}

// Course Types
export interface Course {
  id: number;
  description: string | null;
  created_at: string;
  updated_at: string;
}

// Partylist Types
export interface Partylist {
  id: number;
  name: string;
  created_at: string;
  updated_at: string;
}

// Position Types
export interface Position {
  id: number;
  description: string;
  max_vote: number;
  priority: number;
  created_at: string;
  updated_at: string;
}

// Candidate Types
export interface Candidate {
  id: number;
  position_id: number;
  firstname: string;
  lastname: string;
  partylist_id: number | null;
  photo: string;
  platform: string;
  votes: number;
  created_at: string;
  updated_at: string;
  
  // Relationships
  position?: Position;
  partylist?: Partylist;
}

// Voter Types
export interface Voter {
  id: number;
  course_id: number | null;
  student_number: string;
  has_voted: boolean;
  created_at: string;
  updated_at: string;
  
  // Relationships
  course?: Course;
}

// Vote Types
export interface Vote {
  id: number;
  election_id: number;
  vote_ref: string;
  votes_data: VoteData;
  created_at: string;
  updated_at: string;
  
  // Relationships
  election?: Election;
}

export interface VoteData {
  [positionId: string]: number | number[]; // candidate ID(s) for each position
}

// Admin Types
export interface Admin {
  id: number;
  username: string;
  email: string | null;
  password: string;
  firstname: string;
  lastname: string;
  photo: string;
  created_on: string;
  role: AdminRole;
  gender: string;
  created_at: string;
  updated_at: string;
}

export type AdminRole = 'head' | 'officer';

// OTP Types
export interface OTPRequest {
  id: number;
  student_number: string;
  otp: string;
  attempts: number;
  created_at: string;
  expires_at: string;
}

export interface AdminOTPRequest {
  id: number;
  email: string | null;
  otp: string;
  attempts: number;
  created_at: string;
  expires_at: string;
}

// Password Reset Types
export interface PasswordResetRequest {
  id: number;
  email: string;
  reset_token: string;
  created_at: string;
  expires_at: string;
  used: boolean;
}

// API Response Types
export interface ApiResponse<T = any> {
  success: boolean;
  message: string;
  data?: T;
  errors?: Record<string, string[]>;
}

export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
}

// Election Time Types
export interface ElectionTimeRemaining {
  days: number;
  hours: number;
  minutes: number;
  seconds: number;
}

// Form Types
export interface LoginForm {
  student_number: string;
}

export interface OTPVerificationForm {
  student_number: string;
  otp: string;
}

export interface VoteForm {
  votes: VoteData;
}

export interface AdminLoginForm {
  username: string;
  password: string;
}

// Dashboard Types
export interface ElectionStats {
  total_voters: number;
  voted_count: number;
  remaining_votes: number;
  participation_rate: number;
}

export interface CandidateStats extends Candidate {
  vote_percentage: number;
  rank: number;
}

export interface ElectionSummary {
  election: Election;
  stats: ElectionStats;
  candidates_by_position: Record<string, CandidateStats[]>;
  time_remaining: ElectionTimeRemaining | null;
}

// File Upload Types
export interface FileUpload {
  file: File;
  progress: number;
  status: 'pending' | 'uploading' | 'success' | 'error';
  error?: string;
}

// Log Types
export interface SystemLog {
  id: number;
  type: 'voters' | 'admin';
  timestamp: string;
  student_number?: string;
  email?: string;
  action: string;
  details: Record<string, any>;
  ip_address: string;
  user_agent: string;
}

// Notification Types
export interface Notification {
  id: string;
  type: 'success' | 'error' | 'warning' | 'info';
  title: string;
  message: string;
  duration?: number;
  actions?: NotificationAction[];
}

export interface NotificationAction {
  label: string;
  action: () => void;
  variant?: 'primary' | 'secondary' | 'destructive';
}

// UI State Types
export interface LoadingState {
  isLoading: boolean;
  message?: string;
}

export interface ErrorState {
  hasError: boolean;
  message?: string;
  details?: Record<string, string[]>;
}

// Route Types
export interface RouteProps {
  auth: {
    user?: Admin | Voter;
  };
  flash?: {
    message?: string;
    error?: string;
    success?: string;
  };
  errors?: Record<string, string>;
}

// Component Props Types
export interface PageProps extends RouteProps {
  // Add common page props here
}

export interface VoterPageProps extends PageProps {
  voter?: Voter;
  election?: Election;
  timeRemaining?: ElectionTimeRemaining;
}

export interface AdminPageProps extends PageProps {
  admin?: Admin;
  stats?: ElectionStats;
  elections?: Election[];
}

// Utility Types
export type DeepPartial<T> = {
  [P in keyof T]?: T[P] extends object ? DeepPartial<T[P]> : T[P];
};

export type Optional<T, K extends keyof T> = Omit<T, K> & Partial<Pick<T, K>>;

export type RequiredFields<T, K extends keyof T> = T & Required<Pick<T, K>>;


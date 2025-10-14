// Analytics data for charts and voting statistics

export interface VotingProgressData {
  date: string;
  votes: number;
  cumulative: number;
}

export interface PositionResult {
  position: string;
  candidate: string;
  votes: number;
  percentage: number;
  partylist: string;
}

export interface PositionParticipation {
  position: string;
  totalVoters: number;
  voted: number;
  participationRate: number;
}

export interface PartylistPerformance {
  partylist: string;
  totalVotes: number;
  percentage: number;
  candidates: number;
}

export interface VoteDistribution {
  hour: string;
  votes: number;
}

export interface ElectionStats {
  totalVoters: number;
  totalVoted: number;
  votingRate: number;
  totalPositions: number;
  totalCandidates: number;
  totalPartylists: number;
}

// Mock data for voting progress over time
export const votingProgressData: VotingProgressData[] = [
  { date: "2024-01-20", votes: 45, cumulative: 45 },
  { date: "2024-01-21", votes: 78, cumulative: 123 },
  { date: "2024-01-22", votes: 92, cumulative: 215 },
  { date: "2024-01-23", votes: 156, cumulative: 371 },
  { date: "2024-01-24", votes: 203, cumulative: 574 },
  { date: "2024-01-25", votes: 189, cumulative: 763 },
  { date: "2024-01-26", votes: 167, cumulative: 930 },
  { date: "2024-01-27", votes: 134, cumulative: 1064 },
  { date: "2024-01-28", votes: 89, cumulative: 1153 },
  { date: "2024-01-29", votes: 47, cumulative: 1200 },
];

// Mock data for position-wise results
export const positionResults: PositionResult[] = [
  { position: "President", candidate: "Jane Smith", votes: 456, percentage: 38.0, partylist: "KABATAAN" },
  { position: "President", candidate: "Mike Johnson", votes: 387, percentage: 32.25, partylist: "PAG-ASA" },
  { position: "President", candidate: "Sarah Wilson", votes: 357, percentage: 29.75, partylist: "KABATAAN" },
  { position: "Vice President", candidate: "David Brown", votes: 523, percentage: 43.58, partylist: "PAG-ASA" },
  { position: "Vice President", candidate: "Lisa Garcia", votes: 677, percentage: 56.42, partylist: "KABATAAN" },
  { position: "Secretary", candidate: "Tom Anderson", votes: 612, percentage: 51.0, partylist: "KABATAAN" },
  { position: "Secretary", candidate: "Emma Davis", votes: 588, percentage: 49.0, partylist: "PAG-ASA" },
  { position: "Treasurer", candidate: "John Miller", votes: 634, percentage: 52.83, partylist: "PAG-ASA" },
  { position: "Treasurer", candidate: "Anna Taylor", votes: 566, percentage: 47.17, partylist: "KABATAAN" },
];

// Mock data for position participation rates
export const positionParticipation: PositionParticipation[] = [
  { position: "President", totalVoters: 1200, voted: 1156, participationRate: 96.33 },
  { position: "Vice President", totalVoters: 1200, voted: 1148, participationRate: 95.67 },
  { position: "Secretary", totalVoters: 1200, voted: 1134, participationRate: 94.5 },
  { position: "Treasurer", totalVoters: 1200, voted: 1128, participationRate: 94.0 },
  { position: "Auditor", totalVoters: 1200, voted: 1098, participationRate: 91.5 },
  { position: "P.R.O.", totalVoters: 1200, voted: 1087, participationRate: 90.58 },
  { position: "1st Year Rep", totalVoters: 300, voted: 287, participationRate: 95.67 },
  { position: "2nd Year Rep", totalVoters: 300, voted: 284, participationRate: 94.67 },
  { position: "3rd Year Rep", totalVoters: 300, voted: 278, participationRate: 92.67 },
  { position: "4th Year Rep", totalVoters: 300, voted: 279, participationRate: 93.0 },
];

// Mock data for partylist performance
export const partylistPerformance: PartylistPerformance[] = [
  { partylist: "KABATAAN", totalVotes: 2847, percentage: 52.3, candidates: 15 },
  { partylist: "PAG-ASA", totalVotes: 2593, percentage: 47.7, candidates: 12 },
];

// Mock data for vote distribution by hour
export const voteDistributionByHour: VoteDistribution[] = [
  { hour: "8:00", votes: 23 },
  { hour: "9:00", votes: 45 },
  { hour: "10:00", votes: 67 },
  { hour: "11:00", votes: 89 },
  { hour: "12:00", votes: 156 },
  { hour: "13:00", votes: 134 },
  { hour: "14:00", votes: 178 },
  { hour: "15:00", votes: 145 },
  { hour: "16:00", votes: 98 },
  { hour: "17:00", votes: 67 },
  { hour: "18:00", votes: 34 },
  { hour: "19:00", votes: 12 },
];

// Dashboard chart data - matching the image structure
export const dashboardVoteData = [
  { position: "President", votes: 1, color: "#ec4899" },
  { position: "Vice President", votes: 1.5, color: "#3b82f6" },
  { position: "Secretary", votes: 1.5, color: "#eab308" },
  { position: "PO", votes: 1.8, color: "#14b8a6" },
];

// Individual candidate data - matching the image structure
export const candidateVoteData = [
  {
    position: "President",
    candidates: [
      { name: "Janzen Go", votes: 1, color: "#ec4899" },
      { name: "Jahdiel Aleroza", votes: 0, color: "#ec4899" },
      { name: "Raven Manibo", votes: 0, color: "#ec4899" },
    ]
  },
  {
    position: "Vice President", 
    candidates: [
      { name: "Zeirah Manibo", votes: 1, color: "#ec4899" },
      { name: "Jahziel Aleroza", votes: 0, color: "#ec4899" },
      { name: "Justin Go", votes: 0, color: "#ec4899" },
    ]
  },
  {
    position: "Secretary",
    candidates: [
      { name: "Arnel Go", votes: 1, color: "#ec4899" },
      { name: "Larry Aleroza", votes: 0, color: "#ec4899" },
      { name: "Andrew Manibo", votes: 0, color: "#ec4899" },
    ]
  },
  {
    position: "PO",
    candidates: [
      { name: "Noli Ruiz", votes: 1, color: "#ec4899" },
      { name: "Minda Santiago", votes: 1, color: "#3b82f6" },
      { name: "Liezel Aleroza", votes: 0, color: "#ec4899" },
      { name: "Mayeth Go", votes: 0, color: "#ec4899" },
      { name: "Allan Manibo", votes: 0, color: "#ec4899" },
      { name: "Merly Manibo", votes: 0, color: "#ec4899" },
    ]
  }
];

// Overall election statistics
export const electionStats: ElectionStats = {
  totalVoters: 1200,
  totalVoted: 1145,
  votingRate: 95.42,
  totalPositions: 10,
  totalCandidates: 27,
  totalPartylists: 2,
};

"use client"

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Progress } from "@/components/ui/progress";
import { TrendingUp, Users, Clock, CheckCircle } from "lucide-react";
import { ElectionStats } from "@/data/analytics-data";

interface VotingProgressCardProps {
  stats: ElectionStats;
  daysRemaining?: number;
  startDate?: string;
  endDate?: string;
}

export function VotingProgressCard({ 
  stats, 
  daysRemaining = 5, 
  startDate = "2024-01-20",
  endDate = "2024-01-29"
}: VotingProgressCardProps) {
  const votingRate = stats.votingRate;
  const totalVoters = stats.totalVoters;
  const totalVoted = stats.totalVoted;
  const remainingVoters = totalVoters - totalVoted;

  // Calculate progress color based on voting rate
  const getProgressColor = (rate: number) => {
    if (rate >= 90) return "bg-green-500";
    if (rate >= 70) return "bg-blue-500";
    if (rate >= 50) return "bg-yellow-500";
    return "bg-red-500";
  };

  const getStatusText = (rate: number) => {
    if (rate >= 90) return "Excellent";
    if (rate >= 70) return "Good";
    if (rate >= 50) return "Fair";
    return "Low";
  };

  const getStatusColor = (rate: number) => {
    if (rate >= 90) return "text-green-600";
    if (rate >= 70) return "text-blue-600";
    if (rate >= 50) return "text-yellow-600";
    return "text-red-600";
  };

  return (
    <Card className="col-span-3">
      <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
        <div className="space-y-1">
          <CardTitle className="text-base font-medium">Voting Progress</CardTitle>
          <CardDescription>
            Current election participation status
          </CardDescription>
        </div>
        <div className="flex items-center space-x-2">
          <TrendingUp className="h-4 w-4 text-green-600" />
          <span className={`text-2xl font-bold ${getStatusColor(votingRate)}`}>
            {votingRate.toFixed(1)}%
          </span>
        </div>
      </CardHeader>
      <CardContent className="space-y-6">
        {/* Main Progress Bar */}
        <div className="space-y-2">
          <div className="flex justify-between text-sm">
            <span className="text-muted-foreground">Participation Rate</span>
            <span className={`font-medium ${getStatusColor(votingRate)}`}>
              {getStatusText(votingRate)}
            </span>
          </div>
          <Progress 
            value={votingRate} 
            className="h-3"
          />
          <div className="flex justify-between text-xs text-muted-foreground">
            <span>{totalVoted.toLocaleString()} voted</span>
            <span>{remainingVoters.toLocaleString()} remaining</span>
          </div>
        </div>

        {/* Statistics Grid */}
        <div className="grid grid-cols-2 gap-4">
          <div className="flex items-center space-x-3">
            <div className="p-2 bg-green-100 rounded-lg">
              <CheckCircle className="h-4 w-4 text-green-600" />
            </div>
            <div>
              <p className="text-sm font-medium">{totalVoted.toLocaleString()}</p>
              <p className="text-xs text-muted-foreground">Votes Cast</p>
            </div>
          </div>

          <div className="flex items-center space-x-3">
            <div className="p-2 bg-blue-100 rounded-lg">
              <Users className="h-4 w-4 text-blue-600" />
            </div>
            <div>
              <p className="text-sm font-medium">{remainingVoters.toLocaleString()}</p>
              <p className="text-xs text-muted-foreground">Still to Vote</p>
            </div>
          </div>

          <div className="flex items-center space-x-3">
            <div className="p-2 bg-purple-100 rounded-lg">
              <Clock className="h-4 w-4 text-purple-600" />
            </div>
            <div>
              <p className="text-sm font-medium">{daysRemaining}</p>
              <p className="text-xs text-muted-foreground">Days Remaining</p>
            </div>
          </div>

          <div className="flex items-center space-x-3">
            <div className="p-2 bg-orange-100 rounded-lg">
              <TrendingUp className="h-4 w-4 text-orange-600" />
            </div>
            <div>
              <p className="text-sm font-medium">{stats.totalPositions}</p>
              <p className="text-xs text-muted-foreground">Positions</p>
            </div>
          </div>
        </div>

        {/* Timeline */}
        <div className="space-y-2">
          <div className="flex justify-between text-sm">
            <span className="text-muted-foreground">Election Timeline</span>
          </div>
          <div className="flex items-center justify-between text-xs">
            <span className="text-muted-foreground">
              Started: {new Date(startDate).toLocaleDateString()}
            </span>
            <span className="text-muted-foreground">
              Ends: {new Date(endDate).toLocaleDateString()}
            </span>
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
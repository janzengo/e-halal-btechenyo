"use client"

import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { ChartContainer, ChartTooltip, ChartTooltipContent } from "@/components/ui/chart";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Bar, BarChart, CartesianGrid, XAxis, YAxis, ResponsiveContainer } from "recharts";
import { TrendingUp } from "lucide-react";

interface VoteDistributionDashboardProps {
  data: {
    position: string;
    votes: number;
    color: string;
  }[];
  candidateData: {
    position: string;
    candidates: {
      name: string;
      votes: number;
      color: string;
    }[];
  }[];
}

const COLORS = {
  pink: "#ec4899",
  blue: "#3b82f6", 
  yellow: "#eab308",
  teal: "#14b8a6",
  green: "#22c55e",
  purple: "#a855f7",
  orange: "#f97316",
  red: "#ef4444"
};

export function VoteDistributionDashboard({ data, candidateData }: VoteDistributionDashboardProps) {
  return (
    <div className="space-y-6">
      {/* Main Chart - Votes by Position */}
      <Card>
        <CardHeader>
          <div className="flex items-center justify-between">
            <CardTitle className="flex items-center gap-2">
              <TrendingUp className="h-5 w-5" />
              Vote Distribution by Position
            </CardTitle>
            <Select defaultValue="bar">
              <SelectTrigger className="w-[140px]">
                <SelectValue placeholder="Chart Type" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="bar">Bar Chart</SelectItem>
                <SelectItem value="line">Line Chart</SelectItem>
                <SelectItem value="pie">Pie Chart</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            <h3 className="text-lg font-semibold">Votes by Position</h3>
            <ChartContainer className="h-[300px] w-full">
              <ResponsiveContainer width="100%" height="100%">
                <BarChart
                  data={data}
                  margin={{
                    top: 20,
                    right: 30,
                    left: 20,
                    bottom: 5,
                  }}
                >
                  <CartesianGrid strokeDasharray="3 3" />
                  <XAxis 
                    dataKey="position" 
                    tickLine={false}
                    axisLine={false}
                    tickMargin={8}
                    fontSize={12}
                  />
                  <YAxis 
                    tickLine={false}
                    axisLine={false}
                    tickMargin={8}
                    fontSize={12}
                    domain={[0, 2]}
                    ticks={[0, 0.5, 1, 1.5, 2]}
                  />
                  <ChartTooltip content={<ChartTooltipContent />} />
                  <Bar 
                    dataKey="votes" 
                    radius={4}
                    fill={(entry) => entry.color}
                  />
                </BarChart>
              </ResponsiveContainer>
            </ChartContainer>
            <div className="flex items-center gap-2 text-sm text-gray-600">
              <div className="w-3 h-3 rounded" style={{ backgroundColor: COLORS.pink }}></div>
              <span>Total Votes Cast</span>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Individual Candidate Charts - 2x2 Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {candidateData.map((positionData, index) => (
          <Card key={positionData.position}>
            <CardHeader>
              <CardTitle className="text-lg">
                {positionData.position} - Candidate Votes
              </CardTitle>
            </CardHeader>
            <CardContent>
              <ChartContainer className="h-[250px] w-full">
                <ResponsiveContainer width="100%" height="100%">
                  <BarChart
                    data={positionData.candidates}
                    layout="horizontal"
                    margin={{
                      top: 20,
                      right: 30,
                      left: 80,
                      bottom: 5,
                    }}
                  >
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis 
                      type="number"
                      tickLine={false}
                      axisLine={false}
                      tickMargin={8}
                      fontSize={12}
                      domain={[0, 1]}
                      ticks={[0, 0.2, 0.4, 0.6, 0.8, 1]}
                    />
                    <YAxis 
                      type="category"
                      dataKey="name"
                      tickLine={false}
                      axisLine={false}
                      tickMargin={8}
                      fontSize={12}
                      width={70}
                    />
                    <ChartTooltip content={<ChartTooltipContent />} />
                    <Bar 
                      dataKey="votes" 
                      radius={4}
                      fill={(entry) => entry.color}
                    />
                  </BarChart>
                </ResponsiveContainer>
              </ChartContainer>
              <div className="flex items-center gap-2 text-sm text-gray-600 mt-2">
                <div className="w-3 h-3 rounded" style={{ backgroundColor: COLORS.pink }}></div>
                <span>{positionData.position} Candidates</span>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  );
}

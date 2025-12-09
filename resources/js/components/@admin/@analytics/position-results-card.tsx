"use client"

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { ChartContainer, ChartTooltip, ChartTooltipContent } from "@/components/ui/chart";
import { Bar, BarChart, CartesianGrid, XAxis, YAxis } from "recharts";
import { Trophy, Users } from "lucide-react";
import { PositionResult } from "@/data/analytics-data";

interface PositionResultsCardProps {
  data: PositionResult[];
}

export function PositionResultsCard({ data }: PositionResultsCardProps) {
  // Group data by position for better visualization
  const groupedData = data.reduce((acc, item) => {
    if (!acc[item.position]) {
      acc[item.position] = [];
    }
    acc[item.position].push(item);
    return acc;
  }, {} as Record<string, PositionResult[]>);

  // Flatten for chart display
  const chartData = Object.entries(groupedData).map(([position, candidates]) => ({
    position,
    ...candidates.reduce((acc, candidate, index) => {
      acc[`candidate${index + 1}`] = candidate.votes;
      acc[`candidate${index + 1}Name`] = candidate.candidate;
      acc[`candidate${index + 1}Party`] = candidate.partylist;
      return acc;
    }, {} as any)
  }));

  const chartConfig = {
    candidate1: {
      label: "Leading Candidate",
      color: "hsl(var(--chart-1))",
    },
    candidate2: {
      label: "Second Place",
      color: "hsl(var(--chart-2))",
    },
    candidate3: {
      label: "Third Place",
      color: "hsl(var(--chart-3))",
    },
  };

  return (
    <Card className="col-span-2">
      <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
        <div className="space-y-1">
          <CardTitle className="text-base font-medium">Position-wise Results</CardTitle>
          <CardDescription>
            Vote distribution across all positions
          </CardDescription>
        </div>
        <Trophy className="h-4 w-4 text-yellow-600" />
      </CardHeader>
      <CardContent>
        <ChartContainer config={chartConfig} className="h-[400px] w-full">
          <BarChart
            data={chartData}
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
              angle={-45}
              textAnchor="end"
              height={80}
            />
            <YAxis 
              tickLine={false}
              axisLine={false}
              tickMargin={8}
            />
            <ChartTooltip 
              content={<ChartTooltipContent />}
              formatter={(value, name, props) => {
                const candidateName = props.payload[`${name}Name`];
                const party = props.payload[`${name}Party`];
                return [`${value} votes (${candidateName} - ${party})`, chartConfig[name as keyof typeof chartConfig]?.label || name];
              }}
            />
            <Bar dataKey="candidate1" fill="#22c55e" radius={4} />
            <Bar dataKey="candidate2" fill="#16a34a" radius={4} />
            <Bar dataKey="candidate3" fill="#15803d" radius={4} />
          </BarChart>
        </ChartContainer>
      </CardContent>
    </Card>
  );
}
